<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/12
 * Time: 10:17
 */

namespace app\educational\controller;

use app\common\model\CourseLog;
use QRCode;
use service\DataService;
use controller\BasicAdmin;
use think\Db;
use app\common;
use service\LogService;
use think\facade\Log;

class Order extends BasicAdmin
{
    public $table = 'saas_order';

    public function orderList()
    {
        $this->title = '订单列表';
        $db = Db::name($this->table)->where('o.status', '<>', 3)->alias('o')
            ->join('saas_class c', 'o.class_id=c.id', 'left')
            ->join('saas_customer s', 'o.student_id=s.id', 'left')
            ->field('o.*,s.branch,c.title');
        if ($this->user['id'] != 10000 && !in_array($this->user['authorize'], [1, 3, 4])) {
            $branch = $this->user['employee']['department'];
            $db->where('s.branch', '=', $branch);
        }
        $get = $this->request->get();
        if (isset($get['status']) && $get['status'] != '') {
            $status = $get['status'];
            $db->where('o.status', '=', $status);
        }
        if (isset($get['name']) && $get['name'] != '') {
            $name = $get['name'];
            $db->where('s.name', '=', $name);
        }
        if (isset($get['branch']) && $get['branch'] != '') {
            $branch = $get['branch'];
            $db->where('c.branch', '=', $branch);
        }
        if (isset($get['class_id']) && $get['class_id'] != '') {
            $class_id = $get['class_id'];
            $db->where('o.class_id', '=', $class_id);
        }
        if (isset($get['orderno']) && $get['orderno'] != '') {
            $orderno = $get['orderno'];
            $db->where('o.orderno', '=', $orderno);
        }
        if (isset($get['name']) && $get['name'] != '') {
            $name = $get['name'];
            $id = Db::name('saas_customer')->where('name', '=', $name)->field('id')->select();
            $id_array = [];
            foreach ($id as $v) {
                $id_array[] = $v['id'];
            }
            $db->where('o.student_id', 'in', $id_array);
        }
        if (isset($get['time_range']) && $get['time_range'] != '') {
            $get['time_range'] = str_replace('+~+', ' ~ ', $get['time_range']);
            list($start, $end) = explode(' ~ ', $get['time_range']);
            $_start = strtotime($start);
            $_end = strtotime($end) + 86400;
            $db->whereBetween('o.created_at', [$_start, $_end]);
        }
        $db->order('id desc');
        return parent::_list($db);
    }

    /**
     * 删除
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $cid = Db::name('saas_order')->field('student_id, orderno')->where('id', '=', $this->request->get('id'))->find();
            Db::name('saas_cash_flow')->where('cid', '=', $cid['student_id'])->where('orderno', '=', $cid['orderno'])->delete();
            LogService::write('删除订单', '删除了订单', $cid['student_id']);
            $this->success("订单删除成功!", '');
        }
        $this->error("订单删除失败, 请稍候再试!");
    }

    /**
     * 修改订单的支付状态
     * @return mixed
     */
    public function priceEdit()
    {
        $request = $this->request;
        if ($request->isGet()) {
            $id = $request->get('id');
            $status = Db::name($this->table)->where('id', $id)->field('status')->find();
            return $this->fetch('', ['id' => $id, 'status' => $status]);
        }
        if ($request->isPost()) {
            $data = $request->post();
            // 插入跟进状态
            if ($data['status'] == 5) {
                $order_info = Db::name('saas_order')->field('student_id,class_id,orderno,price,status')->where('id', '=', $data['id'])->find();
                if ($order_info['status'] == 5) {
                    $this->error('订单已支付，请勿重复支付');
                }
                $userinfo = Db::name('saas_customer')->field('id,sales_id,collect_id')->where('id', '=', $order_info['student_id'])->find();
                $follow = array();
                $follow['customer_id'] = $order_info['student_id'];
                $follow['type'] = 2;
                $follow['follow_status'] = 7;
                $follow['keyword'] = '订单付款';
                $follow['content'] = '订单付款添加跟进状态';
                $follow['user_id'] = empty($userinfo['sales_id']) ? (empty($userinfo['collect_id']) ? '10000' : $userinfo['collect_id']) : $userinfo['sales_id'];
                $follow['created_at'] = time();
                Db::name('saas_customer_follow')->insert($follow);
                if ($data['pay_type'] == 1) {
                    cash_flow($order_info['orderno'], $order_info['student_id'], $order_info['price'], 1, $order_info['class_id'], "报名");//给资金流动表加一条数据
                }
            }
            $res = Db::name('saas_order')->update($data);
            if ($res) {
                LogService::write('订单状态', '变更订单id为【' . $data['id'] . '】的支付状态为【' . strip_tags(convert_category($data['status'], 35)) . '】支付方式为【' . convert_category($data['pay_type'], 36) . '】');
                $this->success('变更状态成功', '');
            } else {
                $this->error('变更状态失败');
            }
        }
    }

    /**
     * 订单详细信息
     */
    public function orderView()
    {
        $this->title = '订单详情';
        $id = $this->request->get('id', false);
        $db = Db::name($this->table);
        $order = $db
            ->alias('o')
            ->join('saas_customer c', 'o.student_id=c.id')
            ->field('o.*,c.name,c.gender,c.parent_name,c.parent_tel')
            ->where('o.id', $id)
            ->find();
        $cash_flow = Db::name('saas_cash_flow')->where('orderno', $order['orderno'])->select();
        $orderInfo = Db::name('saas_order_log')->where('order_id', '=', $id)->select();
        return view('', [
            'order' => $order,
            'title' => $this->title,
            'order_info' => $orderInfo,
            'cash_flow' => $cash_flow
        ]);
    }

    public function editOrderInfo()
    {
        $request = $this->request;
        if ($request->isGet()) {
            $id = $request->get('id', false);
            $order_goods = Db::name('saas_order_log')->where('id', $id)->find();
            return $this->fetch('', ['order_goods' => $order_goods]);
        }
        if ($request->isPost()) {
            $data = $request->post();
            $res = Db::name('saas_order_log')->update($data);
            if ($res) {
                //重新计算订单价格
                $order_id = $data['order_id'];
                $orderno = Db::name('saas_order')->where('id', '=', $order_id)->field('orderno')->find();
                $price = Db::name('saas_order_log')->where('order_id', $order_id)->sum('price');
                Db::name('saas_order')->where('id', $order_id)->update(['price' => $price]);
                Db::name('saas_cash_flow')->where('orderno', '=', $orderno['orderno'])->update(['price' => $price]);
                $cid = Db::name('saas_order')->field('student_id')->where('id', '=', $order_id)->find();
                LogService::write('订单详情', '修改了订单id为【' . $data['order_id'] . '】中的商品序号(id)为【' . $data['id'] . '】的商品信息', $cid['student_id']);
                $this->success("修改订单商品信息成功", '');
            } else {
                $this->error("修改订单商品信息失败");
            }
        }
    }

    /**
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * 订单续费
     */
    public function renew()
    {
        $request = $this->request;
        if ($request->isGet()) {
            $id = $request->get('id', false);
            $order_goods = Db::name('saas_order_log')->where('id', $id)->find();
            $course_price = Db::name('saas_courses')->where('id', '=', $order_goods['goods_id'])->column('price');
            $teacher_id = Db::name('saas_class')->where('id', '=', $order_goods['class_id'])->value('teacher_id');
            $order_goods['course_price'] = $course_price[0];
            $order_goods['teacher_id'] = isset($teacher_id) && !empty($teacher_id) ? $teacher_id : 0;
            return $this->fetch('', ['order_goods' => $order_goods]);
        }

        //保存数据
        if ($request->isPost()) {
            $post = $request->post();
            $order = Db::name('saas_order')
                ->where('id', '=', $post['order_id'])
                ->field('price, orderno, student_id')
                ->find();
            $data = [];
            if ($post['preferential'] == '') {
                $data['price'] = $post['renew_price'] + $post['price'];
                $price = $order['price'] + $post['renew_price'];
                //记录资金流动记录
                $row = cash_flow($order['orderno'], $order['student_id'], $post['renew_price'], 2, $post['goods_id'], $post['remark'], $post['teacher_id']);
            } else {
                $data['price'] = $post['renew_price'] + $post['price'] - $post['preferential'];
                $price = $order['price'] + $post['renew_price'] - $post['preferential'];
                //记录资金流动记录
                $true_price = $post['renew_price'] - $post['preferential'];
                $row = cash_flow($order['orderno'], $order['student_id'], $true_price, 2, $post['goods_id'], $post['remark'], $post['teacher_id']);
            }

            $data['old_price'] = $post['old_price'] + $post['renew_price'];
            $data['goods_num'] = $post['goods_num'] + $post['num'];
            $data['remark'] = $post['remark'];

            Db::name('saas_order')->where('id', '=', $post['order_id'])->update(['price' => $price]);
            $res = Db::name('saas_order_log')->where('id', '=', $post['id'])->update($data);
            LogService::write('订单续费', '订单【'.$order['orderno'].'】续费成功', $order['student_id']);
            if ($res) {
                $this->success('数据修改成功！', '');
            } else {
                $this->success('数据修改失败，请稍后重试。。。');
            }
        }
    }
}
