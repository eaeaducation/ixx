<?php


namespace app\admin\controller;


use controller\BasicAdmin;
use think\Db;

class Recruitment extends BasicAdmin
{

    public $recruitTable = 'company_recruit';

    public function index()
    {
        $this->title = '应聘信息';
        $get = $this->request->get();
        $db = Db::name($this->recruitTable)
            ->order('id desc');
        if (isset($get['sex']) && $get['sex'] != '') {
            $db->where('gender', $get['sex']);
        }
        if (isset($get['education']) && $get['education'] != '') {
            $db->where('education', 'like', "%{$get['education']}%");
        }

        if (isset($get['is_read']) && $get['is_read'] != '') {
            $db->where('is_read', $get['is_read']);
        }
        if (isset($get['resume_status']) && $get['resume_status'] != '') {
            $db->where('status', $get['resume_status']);
        }

        if (isset($get['keyword']) && $get['keyword'] != '') {
            $get['keyword'] = trim($get['keyword']);
            if (preg_match('/^\d{11}$/', $get['keyword'])) {
                $db->where('mobile', '=', "{$get['keyword']}");
            } elseif (preg_match('/[a-zA-Z\x{4e00}-\x{9fa5}]/u', $get['keyword'])) {
                $db->where('name', 'like', "%{$get['keyword']}%");
            } else {
                $db->where('1=0');
            }
        }

        if (isset($get['time_range']) && $get['time_range'] != '') {
            $get['time_range'] = str_replace('+~+', ' ~ ', $get['time_range']);
            list($start, $end) = explode(' ~ ', $get['time_range']);
            $_start = strtotime($start);
            $_end = strtotime($end) + 86400;
            $db->whereBetween('created_time', [$_start, $_end]);
        }
        return parent::_list($db, true);
    }


    public function edit()
    {
        return $this->_form($this->recruitTable, 'form');
    }
}