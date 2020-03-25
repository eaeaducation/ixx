<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/23
 * Time: 10:56
 */

namespace app\oa\controller;

use controller\BasicAdmin;
use think\DB;
use service\DataService;
use service\LogService;
use think\facade\Log;

class Goods extends BasicAdmin
{
    public $table = 'saas_textbook';
    public function index()
    {
        $this->title = '库存管理';
        $db = Db::name($this->table)
            ->where('status', '<>', 3)
            ->order('id desc');
        $get = $this->request->get();
        (isset($get['title']) && $get['title'] !== '') && $db->where('title', 'like', "%{$get['title']}%");
        foreach (['type', 'cost_type'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', $get[$key]);
        }
        return parent::_list($db, true);
    }

    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    public function edit_in()
    {
        return $this->_form($this->table1, 'form');
    }

    public function del()
    {
        if (DataService::update($this->table)) {
            LogService::write('教材杂费', '删除了教材杂费信息');
            $this->success("教材删除成功!", '');
        }
        $this->error("教材删除失败, 请稍候再试!");
    }

    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            if (isset($vo['id'])) {
                LogService::write('教材杂费', '编辑教材杂费【' . $vo['title'] . '】');
            } else {
                LogService::write('教材杂费', '添加了一条教材杂费信息【' . $vo['title'] . '】');
            }
        }
    }

    /**
     * @return array|string
     * 出库记录
     */
    public function outRecord()
    {
        $this->title = '出库记录';

        $db = Db::name('saas_goods_record')->alias('r')
            ->join('saas_textbook g', 'r.textbook_id = g.id', 'left')
            ->where('r.status', '<>', 3)
            ->field('r.id, r.number, r.user_id, r.remark, r.created_at, g.title')
            ->order('r.created_at desc');

        $get= $this->request->get();

        if (isset($get['title']) && $get['title'] != '') {
            $db->where('g.title', 'like', '%' . $get['title'] . '%');
        }

        if (isset($get['user_id']) && $get['user_id'] != '') {
            $db->where('r.user_id', '=', $get['user_id']);
        }

        if (isset($get['time_range']) && $get['time_range'] != '') {
            list($_start, $_end) = explode(' ~ ', $get['time_range']);
            $start = strtotime($_start);
            $end = strtotime($_end) + 86400;
            $db->whereBetween('r.created_at', [$start, $end]);
        }

        return $this->_list($db);
    }

    /**
     * @return \think\response\View
     * 物品出库
     */
    public function addRecord()
    {
        $id = $this->request->get('id');

        $office_goods = Db::name('saas_textbook')->where('id', $id)->find();
        if ($this->request->isPost()) {

            $post = $this->request->post();

            if ($post['number'] > $office_goods['residue']) {
                $this->error('物品库存不够！');
            }

            $post['textbook_id'] = $id;

            $post['status'] = 1;

            Db::name('saas_goods_record')->insert($post);

            $lastNum = intval($office_goods['residue']) - intval($post['number']);//剩余库存
            $res = Db::name('saas_textbook')->where('id', '=', $id)->update(['residue' => $lastNum]);

            if ($res) {
                $this->success('出库成功！','');
            } else {
                $this->error('出库失败，请稍后再试！');
            }
        }

        return view();
    }
}