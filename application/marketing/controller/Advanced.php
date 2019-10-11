<?php
/**
 * User: Jasmine2
 * Date: 2018/6/20 14:41
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\marketing\controller;

use app\admin\controller\Log;
use app\common\model\Order;
use app\common\model\Customer;
use app\common\model\CustomerFollow;
use controller\BasicAdmin;
use think\App;
use think\Db;
use service\DataService;
use service\LogService;

/**
 * Class PreMarketing
 * @package app\marketing\controller
 * @author Jasmine2
 * 高级
 */
class Advanced extends BasicAdmin
{
    public $table = "saas_advanced";

    public function index()
    {
        $this->title = '高级客户管理';
        $keyword = trim($this->request->get('keyword', ''));
        $created_at = $this->request->get('created_at', '');
        $db = Db::name($this->table)->where('status', '<>', 3);
        if (!empty($keyword)) {
            if (preg_match('/^\d{4}$/', $keyword)) {
                $db->where('tel', 'like', "%{$keyword}%");
            } elseif (preg_match('/^\d{11}$/', $keyword)) {
                $db->where('tel', '=', "{$keyword}");
            } elseif (preg_match('/[a-zA-Z\x{4e00}-\x{9fa5}]/u', $keyword)) {
                $db->where('name', 'like', "%{$keyword}%");
            } else {
                $db->where('1=0');
            }
        }
        if (!empty($created_at)) {
            list($start_time, $end_time) = explode(' - ', $created_at);
            $start_time = strtotime(trim($start_time) . " 00:00:00");
            $end_time = strtotime(trim($end_time) . " 23:59:59");
            $db->whereBetween('created_at', [$start_time, $end_time]);
        }
        $db->order('created_at desc');
        return $this->_list($db);
    }

    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("通知删除成功!", '');
        }
        $this->error("通知删除失败, 请稍候再试!");
    }

    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            $tel = $vo['tel'];
            $id = isset($vo['id']) ? $vo['id'] : '';
            if (empty($id)) {
                $count = Db::name($this->table)->where('tel', $tel)->count(1);
                if ($count > 0) {
                    $this->error('这个手机号已经存在');
                }
            } else {
                $tel_arr = Db::name($this->table)->where('id', $id)->field('tel')->find();
                $check_tel = $tel_arr['tel'];
                if ($vo['tel'] != $check_tel) {
                    $count = Db::name($this->table)->where('tel', $tel)->count(1);
                    if ($count > 0) {
                        $this->error('这个手机号已经存在');
                    }

                }
            }

        }
    }

    public function resource()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $data = [];
            $data['tel'] = $post['mobile'];
            $data['company'] = $post['title'];
            $data['userid'] = $post['userid'];
            $data['created_at'] = time();
            $res = Db::name($this->table)->insert($data);
            if ($res) {
                $this->success('领取成功', '');
            } else {
                $this->error('数据保存失败，请稍后再试...');
            }
        }
    }
}