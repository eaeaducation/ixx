<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/9
 * Time: 10:24
 */

namespace app\educational\controller;

use controller\BasicAdmin;
use think\Db;
use service\DataService;

/**
 * 学年管理
 * Class Schoolyear
 * @package app\educational\controller
 * @author mei
 */
class Schoolyear extends BasicAdmin
{
    /**
     * 绑定操作模型
     * @var string
     */
    public $table = 'saas_school_year';

    /**
     * 学年列表
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function index()
    {
        $this->title = '学年管理';
        $db = Db::name($this->table)
            ->where('status', '<>', 3)
            ->order('sort asc,id desc');
        return parent::_list($db, true);
    }

    /**
     * 添加
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
     * 编辑
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
     * 删除
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("学年删除成功!", '');
        }
        $this->error("学年删除失败, 请稍候再试!");
    }
}