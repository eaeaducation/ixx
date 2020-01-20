<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/6
 * Time: 11:25
 */

namespace app\educational\controller;

use app\common\model\Customer;
use controller\BasicAdmin;
use think\Db;
use think\Exception;
use think\facade\Log;
use service\LogService;

class Student extends BasicAdmin
{
    public $table = 'saas_customer';

    /**
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Hzb
     */
    public function index()
    {
        $this->title = '学生列表';
        $get = $this->request->get();
        $db = Db::name($this->table)
            ->where('status', '<>', 3)
            ->where('is_student', '=', 1)
            ->order('created_at desc');
        $lession = Db::name('saas_order_log l')
            ->field("sum(l.goods_num) as goods_num, sum(l.consume_num) as consume_num")
            ->join('saas_order o', 'o.id = l.order_id', 'left')
            ->join('saas_courses c', 'c.id = l.goods_id', 'left')
            ->where('o.status', '<>', 3);
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $get['keyword'] = trim($get['keyword']);
            if (preg_match('/^\d{4}$/', $get['keyword'])) {
                $db->where('parent_tel', 'like', "%{$get['keyword']}%");
            } elseif (preg_match('/^\d{11}$/', $get['keyword'])) {
                $db->where('parent_tel', '=', "{$get['keyword']}");
            } elseif (preg_match('/[a-zA-Z\x{4e00}-\x{9fa5}]/u', $get['keyword'])) {
                $db->where('name|nickname|parent_name', 'like', "%{$get['keyword']}%");
            } else {
                $db->where('1=0');
            }
        }

        if (isset($get['status']) && $get['status'] == 0) {
            $db->where('status', '=', 0);
        } elseif (isset($get['status']) && $get['status'] == 1) {
            $db->where('status', '=', 1);
        } elseif (isset($get['status']) && $get['status'] == -99) {
            $db->where('status', '=', -99);
        }
        if (isset($get['time_range']) && $get['time_range'] != '') {
            $get['time_range'] = str_replace('+~+', ' ~ ', $get['time_range']);
            list($start, $end) = explode(' ~ ', $get['time_range']);
            $_start = strtotime($start);
            $_end = strtotime($end) + 86400;
//            if (($_end - $_start) / 86400 > 60) {
//                $_start = strtotime('-60 days', $_end);
//            }
            $db->whereBetween('created_at', [$_start, $_end]);
        }
        foreach (['source', 'branch'] as $key) {
            if (isset($get[$key]) && $get[$key] != '') $db->where($key, '=', $get[$key]);
//            if (isset($get[$key]) && $get[$key] != '') $lession->where($key, '=', $get[$key]);
        }
        $lession_data = $lession->select();
        $this->assign('lession', $lession_data[0]);
//        Log::error($lession_data[0]);
        if (!in_array($this->user['authorize'], [1, 3, 4, 22])) {
            $db->where('branch', '=', $this->user['employee']['department']);
        }
        return parent::_list($db);
    }

    /**
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Hzb
     */
    public function add()
    {
        $this->title = '添加学生';
        return $this->_form($this->table, 'form');
    }

    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            if (empty($vo['student']['name'] && $vo['student']['parent_name'] && $vo['student']['parent_tel'])) $this->error('带星号为必填项');
            if (empty($vo['class_id']) && empty($vo['courses'])) $this->error('请选择班级或课程报名');
            !isset($vo['student']['birthday']) || $vo['student']['birthday'] = strtotime($vo['student']['birthday']);
            if ($vo['customer_id']) {
                // update
                Db::name($this->table)->where('id', '=', $this->request->post('customer_id'))->update($vo['student']);
                $result = $this->request->post('customer_id');
                $customer = Db::name('saas_customer')->where('id', '=', $result)->find();
                if (!$customer['trial_class_id']) {
                    $experience_lession = Db::name('saas_trial_class')->where(['top_id'=>$customer['trial_class_id'], 'student_id'=>$result])->find();
                    if ($experience_lession && $experience_lession['is_deal'] != 1) {
                        Db::name('saas_trial_class')->where(['top_id'=>$customer['trial_class_id'], 'student_id'=>$result])->update(['is_deal'=>1]);
                    }
                }
            } else {
                // add  同一手机号只能有一个用户
                $parent_info = Customer::get(['parent_tel' => $vo['student']['parent_tel'], 'status' => [0,1,99]]);
                if ($parent_info) {
                    $this->error('手机号码重复，客户 ' . $parent_info['parent_name'] . ' 已存在，请选择已有学生');
                }
                $result = Db::name($this->table)->where('id', '=', $this->request->post('customer_id'))->insertGetId($vo['student']);
            }
            if (!empty($vo['oid'])) {
                if (count($vo['courses']) > 1) {
                    $this->error('单个订单只能选择一门课程');
                }
                //对已支付的订单进行分班
                $orderlog = Db::name('saas_order_log')->where('id', '=', $vo['oid'])->find();
                Db::name('saas_order')->where('id', '=', $orderlog['order_id'])->update(['class_id' => $vo['class_id']]);
                foreach ($vo['courses'] as $key => $value) {
                    Db::name('saas_order_log')->where('id', '=', $vo['oid'])->update(['class_id' => $vo['class_id'], 'goods_id' => $value['id']]);
                }
                $this->class_student($vo);
                LogService::write('学生报名', 'id为【' . $result . '】的【' . get_customer_name($result) . '】报名了【' . convert_class($vo['class_id']) . '】班', $result);
                $this->success('添加学生成功！', '', $orderlog['order_id']);
            } else {
                //线下创建订单
                $this->add_order($result, $vo);
            }
        } elseif ($this->request->isGet()) {
            //咨询记录中快捷报名入口
            $id = $this->request->get('id');
            if ($id) {
                $customerinfo = Db::name($this->table)
                    ->where('id', '=', $id)
                    ->find();
                $orderinfo = Db::name('saas_order_log l')
                    ->join('saas_order o', 'o.id = l.order_id', 'left')
                    ->where('o.student_id', '=', $id)
                    ->where('o.status', '=', 5)
                    ->where('l.class_id', '=', 0)
                    ->field('l.id, o.orderno, l.price, l.goods_num, l.created_at, o.pay_type')
                    ->select();
                foreach ($orderinfo as $key => $item) {
                    if ($item['pay_type'] == 1) {
                        $orderinfo[$key]['pay_type'] = '线下支付';
                    } elseif ($item['pay_type'] == 2) {
                        $orderinfo[$key]['pay_type'] = '线上支付';
                    } elseif ($item['pay_type'] == 3) {
                        $orderinfo[$key]['pay_type'] = '现金支付';
                    } elseif ($item['pay_type'] == 4) {
                        $orderinfo[$key]['pay_type'] = '刷卡支付';
                    } elseif ($item['pay_type'] == 5) {
                        $orderinfo[$key]['pay_type'] = '其他支付';
                    }
                    $orderinfo[$key]['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
                    $orderinfo[$key]['price'] = round($item['price'], 2);

                }
                $this->assign('orderinfo', $orderinfo);
                $this->assign('customer_id',$id);
                return $this->fetch('form', ['vo' => $customerinfo]);
            }
        }
    }

    /**
     * 支付信息插入  课程-学生表插入
     * @param $result -客户id
     * @param $vo -post过来的值
     * @author Hzb
     */
    protected function add_order($result, $vo)
    {
        $order = array();
        $order['orderno'] = generate_order_no();
        $order['class_id'] = $vo['class_id'];
        $order['student_id'] = $result;
        $order['price'] = $vo['result_money'];
        $order['created_at'] = time();
        $insert_order = Db::name('saas_order')->insertGetId($order);
        $zf = array();
        if (!empty($vo['zf'])) {
            foreach ($vo['zf'] as $key => $value) {
                if (!empty($value['textbook_id'])) {
                    $zf['order_id'] = $insert_order;
                    $zf['goods_id'] = $value['textbook_id'];
                    if ($value['textbook_type'] == 1) {
                        $zf['goods_type'] = 2;
                    } elseif ($value['textbook_type'] == 2) {
                        $zf['goods_type'] = 3;
                    }
                    $zf['old_price'] = $value['textbook_price'];
                    $zf['price'] = $value['textbook_price'];
                    $zf['goods_num'] = 1;
                    $zf['remark'] = '杂费';
                    $zf['class_id'] = $vo['class_id'];
                    $zf['created_at'] = time();
                    $zfall[] = $zf;
                }
            }
        }
        $courses = array();
        foreach ($vo['courses'] as $key => $value) {
            $courses['order_id'] = $insert_order;
            $courses['goods_id'] = $value['id'];
            $courses['goods_type'] = 1;
            $courses['old_price'] = $value['pre_price'] * $value['total_class'];
            $courses['price'] = floatval($value['pre_price'] * $value['total_class']) - floatval(isset($value['cut_price']) ? $value['cut_price'] : 0);
            $courses['goods_num'] = $value['total_class'];
            $courses['remark'] = isset($value['remark']) ? $value['remark'] : '';
            $courses['class_id'] = $vo['class_id'];
            $courses['created_at'] = time();
            $coursesall[] = $courses;
        }
        if (isset($zfall)) {
            $zf_courses = array_merge($coursesall, $zfall);
        } else {
            $zf_courses = $coursesall;
        }
        Db::startTrans();
        try {
            Db::name('saas_order_log')->insertAll($zf_courses);
            Db::commit();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Db::rollback();
        }
        // 班级学生表关联插入
        if (empty($vo['customer_id'])) {
            $vo['customer_id'] = $result;
        }
        $this->class_student($vo);
        LogService::write('学生报名', 'id为【' . $order['student_id'] . '】的【' . get_customer_name($order['student_id']) . '】报名了【' . convert_class($order['class_id']) . '】班', $order['student_id']);
        $this->success('添加学生成功！', '', $insert_order);
    }

    /**
     * 班级学生关联表插入
     * @param $vo -post过来的值
     * @author Hzb
     */
    protected function class_student($vo)
    {
        // 插入课程-学生表
        $class_ids = $vo['class_id'];
        $class = [];
        $class['class_id'] = $class_ids;
        $class['customer_id'] = $vo['customer_id'];
        $class['created_at'] = time();
        Db::name('saas_class_student')->insert($class);
    }

    /**
     * 已有学生列表
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Hzb
     */
    public function choose_student()
    {
        if ($this->request->isGet()) {
            $get = $this->request->get();
            $branch = !empty($get['branch']) ? $get['branch'] : '';
            $db = Db::name($this->table)
                ->where('status', '<>', 3)
                ->order('created_at desc');
            if (isset($get['keyword']) && $get['keyword'] != "") {
                if (isset($get['keyword']) && $get['keyword'] != "") {
                    if (preg_match('/^\d{11}$/', trim($get['keyword']))) {
                        $db->where('parent_tel', '=', "{$get['keyword']}");
                    } else {
                        $db->where('name|nickname', 'like', "{$get['keyword']}%");
                    }
                }
            }
            if (!in_array($this->user['authorize'], [1, 3, 4])) {
                $db->where('branch', '=', $this->user['employee']['department']);
            }
            if (!empty($branch)) $db->where('branch', '=', $branch);
            return parent::_list($db, true);
        } elseif ($this->request->isPost()) {
            $id = $this->request->post('id');
            $res = Db::name($this->table)
                ->where('id', '=', $id)
                ->find();
            return $res;
        }
    }

    /**
     * 班级及班级所包含的课程
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Hzb
     */
    public function choose_class()
    {
        if ($this->request->isGet()) {
            // get请求 展示所有数据
            $get = $this->request->get();
            $branch = !empty($get['branch']) ? $get['branch'] : '';
            $db = Db::name('saas_class')
                ->alias('sc')
                ->field('sc.id as class_id,sc.branch,cou.id as course_id,sc.title as class_title,sc.student_count,cou.title as courses_title,SUM(cou.price*cou.total_class) as total,cou.total_class')
                ->join('saas_class_course cc', 'sc.id=cc.class_id')
                ->join('saas_courses cou', 'cc.course_id=cou.id')
                ->where('sc.status', '=', '1')
                ->where('cou.status', '=', '1')
                ->group('cc.class_id');
            if (isset($get['class_name']) && $get['class_name'] != "") {
                $db->where('sc.title', 'like', "{$get['class_name']}%");
            }
            if (!in_array($this->user['authorize'], [1, 3, 4])) {
                $db->where('sc.branch', '=', $this->user['employee']['department']);
            }
            if (!empty($branch)) $db->where('sc.branch', '=', $branch);
            return parent::_list($db, true);
        } elseif ($this->request->isPost()) {
            // post请求  通过班级id获取班级对应课程id，价格，名称；课程所包含杂费价格，名称
            $id = $this->request->post('ids');
            $res = Db::name('saas_class_course')
                ->alias('cc')
                ->join('saas_courses sc', 'cc.course_id=sc.id', 'right')
                ->where('sc.status', '=', '1')
                ->whereIn('cc.class_id', $id)
                ->field('cc.course_id,sc.price,sc.title,cc.class_id,sc.total_class')
                ->select();
            foreach ($res as $key => $value) {
                $course_id[] = $res[$key]['course_id'];
                $total_course[] = $value['price'] * $value['total_class'];
            }

            $textbook = Db::name('saas_course_textbook')
                ->alias('ct')
                ->field('st.title as textbook_title,st.price as textbook_price,st.cost_type,ct.course_id,st.id as textbook_id,st.content as textbook_content, st.type as textbook_type')
                ->join('saas_textbook st', 'ct.textbook_id=st.id', 'left')
                ->whereIn('course_id', $course_id)
                ->group('st.id')
                ->select();
            foreach ($textbook as $key => $value) {
                $textbook[$key]['textbook_type_mc'] = convert_category($value['textbook_type'], 33);
            }
            foreach ($res as $key => $value) {
                foreach ($textbook as $k => $item) {
                    if (!empty($item['textbook_id']) && $value['course_id'] == $item['course_id']) {
                        $res[$key]['textbook' . '_' . $item['textbook_id']] = $item;
                        $total_textbook[] = $item['textbook_price'];
                    }
                }
            }
            if (empty($total_textbook)) {
                $res['total_money'] = round(array_sum($total_course), 2);
            } else {
                $res['total_money'] = round(array_sum($total_course) + array_sum($total_textbook), 2);
            }
            return $res;
        }
    }

    public function choose_courses()
    {
        if ($this->request->isGet()) {
            $get = $this->request->get();
            $db = Db::name('saas_courses')->where('status', '<>', '3');
            foreach (['branch', 'title'] as $key) {
                if (isset($get[$key]) && $get[$key] != '') $db->where($key, '=', $get[$key]);
            }
            if (!in_array($this->user['authorize'], [1, 3, 4])) {
                $db->where('branch', '=', $this->user['employee']['department']);
            }
            $db->order('created_at desc');
            return $this->_list($db, 1);
        } elseif ($this->request->isPost()) {
            $post = $this->request->post();
            $ids = explode(',', $post['ids']);
            $course_info = Db::name('saas_courses')
                ->field('*')
                ->where('status', '<>', '3')
                ->whereIn('id', $ids)
                ->select();

            $textbook = Db::name('saas_course_textbook')
                ->alias('ct')
                ->join('saas_textbook st', 'ct.textbook_id=st.id', 'left')
                ->field('ct.*,st.*,st.title as textbook_title')
                ->whereIn('ct.course_id', $ids)
                ->group('st.id')
                ->select();


            foreach ($textbook as $key => $value) {
                $textbook[$key]['textbook_type_mc'] = convert_category($value['type'], 33);
            }
            foreach ($course_info as $key => $value) {
                $total_course[] = $value['price'] * $value['total_class'];
                foreach ($textbook as $k => $v) {
                    if ($value['id'] == $v['course_id']) {
                        $total_textbook[] = $v['price'];
                        $course_info[$key]['textbook_info'][$v['id']] = $textbook[$k];
                    }
                }
            }

            if (empty($total_textbook)) {
                $course_info['total_money'] = round(array_sum($total_course), 2);
            } else {
                $course_info['total_money'] = round(array_sum($total_course) + array_sum($total_textbook), 2);
            }

            return $course_info;
        }
    }


    public function edit()
    {
        $id = $this->request->get('id');
        $row = Db::name('saas_customer')->where('id', '=', $id)->find();
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $res = Db::name('saas_customer')->where('id', '=', $id)->update($post);
            if ($res) {
                $this->success('数据编辑成功', '');
            } else {
                $this->error('编辑失败，请稍后再试！');
            }
        }
        return view('', ['vo' => $row]);
    }

    public function query_orderinfo()
    {
        $id = $this->request->post('id');
        $orderinfo = Db::name('saas_order_log l')
            ->join('saas_order o', 'o.id = l.order_id', 'left')
            ->where('o.student_id', '=', $id)
            ->where('o.status', '=', 5)
            ->where('l.class_id', '=', 0)
            ->field('l.id, o.orderno, l.price, l.goods_num, l.created_at, o.pay_type')
            ->select();
        foreach ($orderinfo as $key => $item) {
            if ($item['pay_type'] == 1) {
                $orderinfo[$key]['pay_type'] = '线下支付';
            } elseif ($item['pay_type'] == 2) {
                $orderinfo[$key]['pay_type'] = '线上支付';
            } elseif ($item['pay_type'] == 3) {
                $orderinfo[$key]['pay_type'] = '现金支付';
            } elseif ($item['pay_type'] == 4) {
                $orderinfo[$key]['pay_type'] = '刷卡支付';
            } elseif ($item['pay_type'] == 5) {
                $orderinfo[$key]['pay_type'] = '其他支付';
            }
            $orderinfo[$key]['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
            $orderinfo[$key]['price'] = round($item['price'], 2);

        }
        return $orderinfo;
    }

    public function export()
    {
        $this->title = '学生列表';
        $get = $this->request->get();
        $data = Db::name('saas_order_log l')
            ->field("u.name,u.parent_tel,c.title,l.goods_num,l.consume_num,u.id,l.goods_num-l.consume_num as residue_num, l.order_id, o.orderno, l.id")
            ->join('saas_order o', 'o.id = l.order_id', 'left')
            ->join('saas_customer u', 'u.id = o.student_id', 'left')
            ->join('saas_courses c', 'c.id = l.goods_id', 'left')
            ->where('o.status', '<>', 3);
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $get['keyword'] = trim($get['keyword']);
            if (preg_match('/^\d{4}$/', $get['keyword'])) {
                $data->where('u.parent_tel', 'like', "%{$get['keyword']}%");
            } elseif (preg_match('/^\d{11}$/', $get['keyword'])) {
                $data->where('u.parent_tel', '=', "{$get['keyword']}");
            } elseif (preg_match('/[a-zA-Z\x{4e00}-\x{9fa5}]/u', $get['keyword'])) {
                $data->where('u.name|u.nickname|u.parent_name', 'like', "%{$get['keyword']}%");
            } else {
                $data->where('1=0');
            }
        }

        if (isset($get['status']) && $get['status'] == 0) {
            $data->where('u.status', '=', 0);
        } elseif (isset($get['status']) && $get['status'] == 1) {
            $data->where('u.status', '=', 1);
        } elseif (isset($get['status']) && $get['status'] == -99) {
            $data->where('u.status', '=', -99);
        }
        if (isset($get['time_range']) && $get['time_range'] != '') {
            $get['time_range'] = str_replace('+~+', ' ~ ', $get['time_range']);
            list($start, $end) = explode(' ~ ', $get['time_range']);
            $_start = strtotime($start);
            $_end = strtotime($end) + 86400;
            $data->whereBetween('u.created_at', [$_start, $_end]);
        }
        if (isset($get['source']) && $get['source'] != '') {
            $data->where('u.source', '=', $get['source']);
        }
        if (isset($get['branch']) && $get['branch'] != '') {
            $data->where('u.branch', '=', $get['branch']);
        }
        $data->order('u.created_at desc');
        $export_data = $data->select();
        $key = [
            'id' => '课程订单id',
            'order_id' => '订单id',
            'orderno' => '订单号',
            'name' => '姓名',
            'parent_tel' => '联系电话',
            'title' => '课程',
            'goods_num' => '购买课时',
            'consume_num' => '已消课时',
            'residue_num' => '剩余课时'
        ];
        $title = "学生课时";
        down_excel($export_data, $key, $title);
    }
}