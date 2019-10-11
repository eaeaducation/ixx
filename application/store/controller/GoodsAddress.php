<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3 0003
 * Time: 13:40
 */

namespace app\store\controller;


use controller\BasicAdmin;
use service\DataService;
use think\Db;

class GoodsAddress extends BasicAdmin
{
    public $table = 'StoreGoodsAddress';

    public function address()
    {
        $this->title = '咨询地址';
        $db = Db::name($this->table)->field('*')->order('id desc');
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
            $this->success('数据删除成功', '');
        }
        $this->error('删除失败，请稍后再试。。。');
    }
}