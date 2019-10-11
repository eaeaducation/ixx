<?php
/**
 * User: Jasmine2
 * Date: 2018/6/20 10:56
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;


use controller\BasicAdmin;
use think\Db;
use service\DataService;

/**
 * Class Tc
 * @package app\admin\controller
 * @author Jasmine2
 * 一级类目
 */
class Tc extends BasicAdmin
{
    public $table = 'saas_top_category';

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function index()
    {
        $this->title = '一级类目';
        $db = Db::name($this->table)
            ->order('created_at desc')
            ->where('status', '=', 1);
        return parent::_list($db, true);
    }

    /**
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("类目删除成功!", '');
        }
        $this->error("类目删除失败, 请稍候再试!");
    }
}