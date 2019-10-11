<?php
/**
 * User: Jasmine2
 * Date: 2018/6/21 10:28
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\marketing\controller;


use controller\BasicAdmin;
use think\Db;
use service\DataService;

class Channel extends BasicAdmin
{
    public $table = 'saas_channel';

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
        $this->title = '渠道管理';
        $db = Db::name($this->table)
            ->where('status', '=', 1)
            ->order('id desc');
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
            $this->success("渠道删除成功!", '');
        }
        $this->error("渠道删除失败, 请稍候再试!");
    }
}