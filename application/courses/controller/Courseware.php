<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/4
 * Time: 16:08
 */

namespace app\courses\controller;

use controller\BasicAdmin;
use think\Db;
use service\DataService;
use service\LogService;

/**
 * 课件管理
 * Class Courseware
 * @package app\courses\controller
 */
class Courseware extends BasicAdmin
{
    public $table = 'saas_courseware';

    /**
     * 展示
     * @return array|string
     */
    public function index()
    {
        $this->title = '课件管理';
        $db = Db::name($this->table)
            ->order('id desc');
        $get = $this->request->get();
        (isset($get['title']) && $get['title'] !== '') && $db->where('title', 'like', "%{$get['title']}%");
        foreach (['course_id'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', $get[$key]);
        }
        return parent::_list($db, true);
    }

    /**
     * 添加
     * @return array|string
     */
    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑
     * @return array|string
     */
    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 删除
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            LogService::write('课程管理', '删除了一项课件');
            $this->success("课件删除成功!", '');
        }
        $this->error("课件删除失败, 请稍候再试!");
    }

    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            LogService::write('课程管理', '修改了【' . $vo['title'] . '】课件');
        }
    }
}