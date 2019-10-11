<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/26
 * Time: 17:02
 */

namespace app\oa\controller;

use think\Db;
use controller\BasicAdmin;

class staffAttend extends BasicAdmin
{
    public function clockCard()
    {
        $title = '员工打卡';
        return view('', ['title' =>$title]);
    }

    public function card()
    {
        if ($this->request->isPost()) {

            $post = $this->request->post();
            $card = $post['card'];
            $type = $post['type'];

            $employee = Db::name('saas_employee')
                ->where('status', '<>', 3)
                ->where('icard', '=', $card)
                ->find();

            if (!$employee) {
                $this->error('此卡未绑定员工!');
            }

            if ($type == 1) {
                $data = [];
                $data['icard'] = $card;
                $data['created_at'] = time();
                $res = Db::name('saas_employee_log')->insert($data);
                if ($res) {
                    $this->success('打卡成功！', '');
                } else {
                    $this->error('打卡失败，请重新打卡！');
                }
            } elseif ($type == 2) {
                $row = [];
                $row['name'] = $employee['name'];
                $row['mobile'] = $employee['mobile'];
                $row['personnel'] = convert_category($employee['personnel_status'], 17);
                $row['contract'] = convert_category($employee['contract_status'], 21);
                $row['department'] = convert_branch($employee['department']);
                $this->success('', '', $row);
            }

        }
    }

    public function recordDetail()
    {
        $this->title = '员工打卡记录';

        $db = Db::name('saas_employee_log')->alias('l')
            ->join('saas_employee e', 'l.icard = e.icard', 'left')
            ->join('system_user u', 'e.userid = u.id', 'left')
            ->join('system_auth a', 'u.authorize = a.id', 'left')
            ->field('e.name, e.mobile, e.department, l.created_at, a.title')
            ->where('e.status', '<>', 3)
            ->order('l.created_at desc');

        $get = $this->request->get();

        if (isset($get['branch']) && $get['branch'] != '') {
            $db->where('e.department', '=', $get['branch']);
        }

        if (isset($get['keyword']) && $get['keyword'] != '') {
            if (preg_match('/^\d{11}$/', $get['keyword'])) {
                $db->where('e.mobile', '=', "{$get['keyword']}");
            } elseif (preg_match('/^[\x{4e00}-\x{9fa5}]{2,10}(?:·[\x{4e00}-\x{9fa5}]{2,10})*$/u', $get['keyword'])) {
                $db->where('e.name', 'like', '%'.$get['keyword'].'%');
            }
        }

        if (isset($get['time_range']) && $get['time_range'] != '') {
            $get['time_range'] = str_replace('+~+', ' ~ ', $get['time_range']);
            list($start, $end) = explode(' ~ ', $get['time_range']);
            $_start = strtotime($start);
            $_end = strtotime($end) + 86400;
            $db->whereBetween('l.created_at', [$_start, $_end]);
        }

        return $this->_list($db);
    }

    public function set()
    {
        if($this->request->isPost()){
            $post = $this->request->post();
            sysconf('start_work_time', $post['start']);
            sysconf('end_work_time', $post['end']);
            $this->success('参数修改成功', '');
        };
        return view('form');
    }
}