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
}