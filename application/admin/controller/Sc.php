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
use think\facade\Cache;

/**
 * Class Tc
 * @package app\admin\controller
 * @author Jasmine2
 * 二级类目
 */
class Sc extends BasicAdmin
{
    public $table = 'saas_category';

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
        $this->title = '二级类目';
        $db = Db::name($this->table)->alias('c')
            ->field('c.*, t.title as t_title');
        $db->join('saas_top_category t', 'c.top_id=t.id', 'LEFT')
            ->order('c.top_id asc,c.created_at desc')
            ->where('c.status', '=', 1);
        $top_categories = Db::name('saas_top_category')->where(['status' => '1'])->column('title', 'id');
        $this->assign(['top_categories' => $top_categories]);

        $get = $this->request->get();
        foreach (['top_id', 'key'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', $get[$key]);
        }
        Cache::clear();
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

    protected function _add_form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            $max_key = Db::name($this->table)
                ->where('top_id', '=', $vo['top_id'])
                ->max('key');
            $vo['key'] = $max_key > 0 ? $max_key + 1 : 1;
        }
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

    /**
     * @param $vo
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    protected function _form_filter(&$vo)
    {
        if ($this->request->isGet()) {
            $top_categories = Db::name('saas_top_category')->where(['status' => '1'])->column('title', 'id');
            $this->assign(['top_categories' => $top_categories]);
        }
    }
}