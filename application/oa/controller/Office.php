<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/17
 * Time: 14:34
 */

namespace app\oa\controller;

use app\common\model\SystemUser;
use controller\BasicAdmin;
use service\DataService;
use service\LogService;
use think\Db;
use think\facade\Log;

class Office extends BasicAdmin
{


    public $table = "saas_record";

    /**
     * 工作日志
     */
    public function jobrecord()
    {
        $db = Db::name($this->table)
            ->where('status', '<>', 3)
            ->order('id desc')
            ->field('*');
        $get = $this->request->get();
        if (isset($get['title']) && $get['title'] != '') {
            $get['title'] = trim($get['title']);
            $db->where('title', 'like', "%{$get['title']}%");
        }
        if (isset($get['created_at']) && $get['created_at'] != '') {
            list($start_time, $end_time) = explode(' - ', $get['created_at']);
            $start_time = strtotime(trim($start_time) . " 00:00:00");
            $end_time = strtotime(trim($end_time) . " 23:59:59");
            $db->whereBetween('created_at', [$start_time, $end_time]);
        }

        if (isset($get['from']) && $get['from'] != '') {
            if ($get['from'] == 'get') {
                $db->where('to_user', '=', $this->user['id']);
            } elseif ($get['from'] == 'send') {
                $db->where('from_user', '=', $this->user['id']);
            }
        }
        $db->where('to_user = ' . $this->user['id'] . ' or from_user=' . $this->user['id']);
        $this->title = '工作日志';
        return parent::_list($db, true);
    }

    /**
     * 添加日志
     */
    public function add()
    {
        $this->assign('type', 1);
        return $this->_form($this->table, 'form');
    }

    protected function _form_filter(&$data)
    {
        if ($this->request->isPost()) {
            $touserinfo = Db::name('saas_employee')
                ->field('id,userid')
                ->where('id', '=', $data['to_user'])
                ->find();
            $touser = SystemUser::get($touserinfo['userid']);
            if (!empty($data['sendmail']) && isset($touser['mail'])) {
                $to = $touser['mail'];
                $subject = $data['title'];
                $nickname = $this->user['employee']['name'];
                $content = $data['content'];
                // 发送前请先配置好
                $res = sendmail($to, $nickname, $subject, $content);
            }
            unset($data['sendmail']);
            $insert = Db::name($this->table)->insert($data);
            list($base, $spm, $url) = [url('@admin'), $this->request->get('spm'), url('oa/office/approval')];
            if (!empty($res) && $insert) {
                $this->success('保存成功，邮件发送成功', "{$base}#{$url}?spm={$spm}");
            } else {
                $this->success('保存成功，未发送邮件', "{$base}#{$url}?spm={$spm}");
            }
        }
    }

    /**
     * @return bool
     * 快速回复
     */
    public function replay()
    {
        if ($this->request->isGet()) {
            $this->assign('type', 1);
            return $this->_form($this->table, 'form');
        } else {
            $post = $this->request->post();
            $touserinfo = Db::name($this->table)
                ->alias('r')
                ->join('saas_employee e', 'r.from_user=e.id')
                ->field('r.to_user,r.from_user,e.userid')
                ->where('r.id', '=', $post['id'])
                ->find();
            $touser = SystemUser::get($touserinfo['userid']);
            if (!empty($post['sendmail']) && isset($touser['mail'])) {
                $to = $touser['mail'];
                $subject = $post['title'];
                $nickname = $this->user['employee']['name'];
                $content = $post['content'];
                // 发送前请先配置好
                $res = sendmail($to, $nickname, $subject, $content);
            }
            $insert = array();
            $insert['s_id'] = $post['id'];
            $insert['content'] = $post['content'];
            $insert['to_user'] = $touserinfo['from_user'];
            $insert['created_at'] = time();
            $insert = Db::name('saas_replay')->insert($insert);
            if (!empty($res) && $insert) {
                $this->success('保存成功，邮件发送成功', '');
            } else {
                $this->success('保存成功，未发送邮件', '');
            }
        }
    }

    /**
     * @return array|string
     * 日志查看
     */
    public function recordview()
    {
        $id = $this->request->get('id');
        $content = Db::name('saas_replay')->where('type','=',1)->whereIn('s_id', $id)->field('content,created_at')->select();
        $replay = '';
        foreach ($content as $k => $v) {
            $replay .= format_date($v['created_at'], 'Y-m-d H:i:s') . $v['content'] . "<br>";
        }
        $this->assign('replay', $replay);
        return $this->_form($this->table, '');
    }

    /**
     * 日志删除
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("日志删除成功!", '');
        }
        $this->error("日志删除失败, 请稍候再试!");
    }

    /**
     * @return array|string
     * 审批列表
     */
    public function approval()
    {
        $db = Db::name('saas_approval')
            ->where('status', '<>', 3)
            ->order('id desc')
            ->field('*');
        $get = $this->request->get();
        if (isset($get['title']) && $get['title'] != '') {
            $get['title'] = trim($get['title']);
            $db->where('title', 'like', "%{$get['title']}%");
        }
        if (isset($get['created_at']) && $get['created_at'] != '') {
            list($start_time, $end_time) = explode(' - ', $get['created_at']);
            $start_time = strtotime(trim($start_time) . " 00:00:00");
            $end_time = strtotime(trim($end_time) . " 23:59:59");
            $db->whereBetween('created_at', [$start_time, $end_time]);
        }
        if (isset($get['is_pass']) && $get['is_pass'] != '') {
            $db->where('is_pass', '=', $get['is_pass']);
        }
        $this->title = '审批记录';
        return parent::_list($db, true);
    }

    protected function _approval_data_filter(&$data)
    {
        $res = Db::name('saas_replay')
            ->where('type', '=', '2')
            ->select();
        if (!empty($res)) {
            foreach ($data as $key => $value) {
                foreach ($res as $k => $v) {
                    if ($value['id'] == $v['s_id']) {
                        $data[$key]['shenpi'][] = $res[$k]['to_user'];
                    }
                }
            }
        }
    }

    /**
     * @return array|string
     * 添加审批
     */
    public function addapproval()
    {
        if ($this->request->isGet()) {
            $employee = Db::name('saas_employee')->where('issp', '=', '1')->column('name', 'id');
            $this->assign('employee', $employee);
            $this->assign('type', 2);
            return $this->_form($this->table, 'form');
        } else {
            $post = $this->request->post();
            $insert = array();
            $insert['title'] = $post['title'];
            $insert['content'] = $post['content'];
            $insert['from_user'] = $this->user['employee']['id'];
            $insert['created_at'] = time();
            $id = Db::name('saas_approval')->insertGetId($insert);
            $insertall = array();
            foreach ($post['to_user'] as $key => $value) {
                $toinsert['s_id'] = $id;
                $toinsert['content'] = '';
                $toinsert['to_user'] = $value;
                $toinsert['type'] = 2;
                $insertall[] = $toinsert;
            }
            Db::name('saas_replay')->insertAll($insertall);
            list($base, $spm, $url) = [url('@admin'), $this->request->get('spm'), url('oa/office/approval')];
            $this->success('申请成功', "{$base}#{$url}?spm={$spm}");
        }
    }

    /**
     * @return array|string
     * 审批预览及处理
     */
    public function approvalview()
    {
        if ($this->request->isGet()) {
            $id = $this->request->get('id');
            $content = Db::name('saas_replay')
                ->where('s_id', '=', $id)
                ->field('*')
                ->where('type', '=', 2)
                ->select();
            $this->assign('content', $content);
            return $this->_form('saas_approval', 'approvalview');
        } else {
            $post = $this->request->post();
            $update = array();
            $update['is_pass'] = $post['is_pass'];
            $update['content'] = $post['content'];
            $update['created_at'] = time();
            Db::name('saas_replay')
                ->where('s_id', '=', $post['s_id'])
                ->where('to_user', '=', $post['to_userid'])
                ->update($update);
            $is_pass = Db::name('saas_replay')
                ->where('s_id', '=', $post['s_id'])
                ->column('is_pass');

            $flag = false;
            foreach ($is_pass as $item) {
                if ($item == 0) {
                    $flag = 0;
                    break;
                } elseif ($item == 1) {
                    $flag = 1;
                    continue;
                } elseif($item == 2){
                    $flag = 2;
                    break;
                }
            }
            if ($flag == 1) {
                Db::name('saas_approval')->where('id', '=', $post['s_id'])->update(['is_pass' => 1]);
            } elseif ($flag == 2) {
                Db::name('saas_approval')->where('id', '=', $post['s_id'])->update(['is_pass' => 2]);
            }
            $this->success('审核完成', '');
        }
    }

    /**
     * 审批删除
     */
    public function approvaldel()
    {
        $db = 'saas_approval';
        if (DataService::update($db)) {
            $this->success("审批删除成功!", '');
        }
        $this->error("审批删除失败, 请稍候再试!");
    }


    /**
     * 班级审批
     */
    public function classApproval()
    {
        $this->title = '班级审批';
        $db = Db::name('saas_class c')
            ->where('c.status', '<>', 3)
            ->where('c.audit_status', 'in', [-95,-97,-1])
            ->order('c.id desc');
        //  校区
        if ($this->user['id'] != 10000 || $this->user['authorize'] != 3) {
            if (in_array($this->user['authorize'], [9])) {
                $branch = $this->user['employee']['department'];
                $db->where('c.branch', '=', $branch);
                $db->where('c.audit_status', 'in', [-95]);
            } elseif (in_array($this->user['authorize'], [4])) {
                $db->where('c.audit_status', 'in', [-95,-97]);
            }
        }
        $get = $this->request->get();
        (isset($get['branch']) && $get['branch'] !== '') && $db->where('c.branch', '=', "{$get['branch']}");
        return parent::_list($db, true);
    }

    protected function _classapproval_data_filter(&$data)
    {
        foreach ($data as $key => &$value) {
            if (in_array($this->user['authorize'], [4])) {
                if (count(get_class_students($value['id'])) < 6) {
                    $data[$key] = $value;
                } else {
                    unset($data[$key]);
                }
            }
        }
    }


    public function adopt()
    {
        $table = 'saas_class';
        if (DataService::update($table)) {
            $get = $this->request->get();
            LogService::write('班级管理', '操作者：'.get_employee_name($this->user['id']).'审核通过了班级id：'.$get['class_id'].'的开班申请');
            $this->success("审批通过成功!", '');
        }
        $this->error("班级审核通过失败");
    }

    public function refuse()
    {
        $table = 'saas_class';
        if (DataService::update($table)) {
            $get = $this->request->get();
            LogService::write('班级管理', '操作者：'.get_employee_name($this->user['id']).'拒绝了班级id：'.$get['class_id'].'的开班申请');
            $this->success("拒绝开班申请成功!", '');
        }
        $this->error("拒绝开班申请失败");
    }

    public function renew()
    {
        $table = 'saas_class';
        if (DataService::update($table)) {
            $get = $this->request->get();
            LogService::write('班级管理', '操作者：'.get_employee_name($this->user['id']).'还原班级id：'.$get['class_id']);
            $this->success("还原班级申请成功!", '');
        }
        $this->error("班级审核通过失败");
    }

    /**
     * 班级审批
     */
    public function orderApproval()
    {
        $this->title = '订单课时审批';
        $db = Db::name('saas_order')->where('o.status', '<>', 3)->alias('o')
            ->join('saas_order_log l', 'l.order_id=o.id', 'left')
            ->join('saas_class c', 'o.class_id=c.id', 'left')
            ->join('saas_customer s', 'o.student_id=s.id', 'left')
            ->field('o.*,s.branch,c.title,sum(l.old_price) as oldprice')
            ->where('o.audit_status', 'in', [-1,-99,-95,-97]);
        //  校区
        if ($this->user['id'] != 10000 || $this->user['authorize'] != 3) {
//            if (in_array($this->user['authorize'], [9])) {
//                $branch = $this->user['employee']['department'];
//                $db->where('s.branch', '=', $branch);
//                $db->where('o.audit_status', 'in', [-99,-95,-97]);
//            } elseif (in_array($this->user['authorize'], [4])) {
//                $db->where('o.audit_status', 'in', [-95,-97]);
//            } elseif (in_array($this->user['authorize'], [19])) {
//                $db->where('o.audit_status', 'in', [-95,-97]);
//            }

            if (in_array($this->user['authorize'], [9])) {
                $branch = $this->user['employee']['department'];
                $db->where('s.branch', '=', $branch);
                $db->where('o.audit_status', 'in', [-99]);
            } elseif (in_array($this->user['authorize'], [4])) {
                $db->where('o.audit_status', 'in', [-95]);
            } elseif (in_array($this->user['authorize'], [21])) {
                $db->where('o.audit_status', 'in', [-99,-95,-97]);
            }
        }
        $get = $this->request->get();
        if (isset($get['name']) && $get['name'] != '') {
            $name = $get['name'];
            $db->where('s.name', '=', $name);
        }
        if (isset($get['branch']) && $get['branch'] != '') {
            $branch = $get['branch'];
            $db->where('c.branch', '=', $branch);
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
//        if (isset($get['time_range']) && $get['time_range'] != '') {
//            $get['time_range'] = str_replace('+~+', ' ~ ', $get['time_range']);
//            list($start, $end) = explode(' ~ ', $get['time_range']);
//            $_start = strtotime($start);
//            $_end = strtotime($end) + 86400;
//            $db->whereBetween('o.created_at', [$_start, $_end]);
//        }
        $db->group('l.order_id');
        $db->order('o.id desc');
        return parent::_list($db, true);
    }

    protected function _orderapproval_data_filter(&$data)
    {
        foreach ($data as $key => &$value) {
            if (in_array($this->user['authorize'], [9])) {
                if (($value['oldprice'] - $value['price']) > 1000) {
                    $data[$key] = $value;
                } else {
                    unset($data[$key]);
                }
            } elseif (in_array($this->user['authorize'], [4])) {
                if (($value['oldprice'] - $value['price']) > 2000) {
                    $data[$key] = $value;
                } else {
                    unset($data[$key]);
                }
            } elseif (in_array($this->user['authorize'], [21])) {
                if ((($value['oldprice'] - $value['price']) < 1000 && $value['audit_status'] == -99) || ($value['audit_status'] == -97 && ($value['oldprice'] - $value['price']) > 1000 && ($value['oldprice'] - $value['price']) < 2000) || $value['audit_status'] == -97) {
                    $data[$key] = $value;
                } else {
                    unset($data[$key]);
                }
            }
        }
    }


    public function orderConfirm()
    {
        $table = 'saas_order';
        $oid = $this->request->get('oid');
        if (DataService::update($table)) {
            $res = Db::name('saas_order')
                ->where('id', $oid)
                ->update(['status'=>5]);
            if ($res) {
                LogService::write('订单状态', '操作者：'.get_employee_name($this->user['id']).'确认支付了订单id：'.$oid);
                $this->success("订单确认支付成功", '');
            }
            $this->error("订单确认支付失败");
        }
    }

    public function priceEdit()
    {
        $request = $this->request;
        if ($request->isGet()) {
            $id = $request->get('oid');
            $status = Db::name('saas_order')->where('id', $id)->field('status')->find();
            return $this->fetch('', ['id' => $id, 'status' => $status]);
        }
        if ($request->isPost()) {
            $data = $request->post();
            $data['audit_status'] = 99;
            // 插入跟进状态
            if ($data['status'] == 5) {
                $order_info = Db::name('saas_order')->field('student_id,class_id,orderno,price,status')->where('id', '=', $data['id'])->find();
                if ($order_info['status'] == 5) {
                    $this->error('订单已支付，请勿重复支付');
                }
                $userinfo = Db::name('saas_customer')->field('id,sales_id,collect_id')->where('id', '=', $order_info['student_id'])->find();
                $follow = array();
                $content = [
                    [
                        'content' => '订单付款添加跟进状态',
                        'created_at' => time()
                    ]
                ];
                $follow['customer_id'] = $userinfo['id'];
                $follow['type'] = 2;
                $follow['follow_status'] = 10;
                $follow['keyword'] = '订单付款';
                $follow['content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
                $follow['user_id'] = empty($userinfo['sales_id']) ? (empty($userinfo['collect_id']) ? '10000' : $userinfo['collect_id']) : $userinfo['sales_id'];
                $follow['created_at'] = time();

                Db::name('saas_customer_follow')->insert($follow);
                if ($data['pay_type'] == 1) {
                    cash_flow($order_info['orderno'], $order_info['student_id'], $order_info['price'], 1, $order_info['class_id'], "报名");//给资金流动表加一条数据
                }
            }
            $res = Db::name('saas_order')->update($data);
            if ($res) {
                LogService::write('订单状态', '操作者：'.get_employee_name($this->user['id']).'变更订单id为【' . $data['id'] . '】的支付状态为【' . strip_tags(convert_category($data['status'], 35)) . '】支付方式为【' . convert_category($data['pay_type'], 36) . '】');
                $this->success('确认状态成功', '');
            } else {
                $this->error('确认状态失败');
            }
        }
    }

    public function orderRefuse()
    {
        $table = 'saas_order';
        $oid = $this->request->get('oid');
        if (DataService::update($table)) {
            LogService::write('订单状态', '操作者：'.get_employee_name($this->user['id']).'拒绝了订单id：'.$oid.'的审批');
            $this->success("订单审批成功", '');
        }
        $this->error("订单审批失败");
    }

    public function orderAdopt()
    {
        $table = 'saas_order';
        $oid = $this->request->get('oid');
        if (DataService::update($table)) {
            LogService::write('订单状态', '操作者：'.get_employee_name($this->user['id']).'通过了订单id：'.$oid.'的审批');
            $this->success("订单审批成功", '');
        }
        $this->error("订单审批失败");
    }

    public function orderDelete()
    {
        $table = 'saas_order';
        $oid = $this->request->get('oid');
        if (DataService::update($table)) {
            LogService::write('订单状态', '操作者：'.get_employee_name($this->user['id']).'删除了订单id：'.$oid.'的审批');
            $this->success("订单删除成功", '');
        }
        $this->error("订单删除失败");
    }
}