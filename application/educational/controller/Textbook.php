<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/4
 * Time: 9:20
 */

namespace app\educational\controller;


use controller\BasicAdmin;
use think\Db;
use service\DataService;
use service\LogService;


/**
 * 教材管理
 * Class Textbook
 * @package app\educational\controller
 */
class Textbook extends BasicAdmin
{
    public $table = 'saas_textbook';
    public $table1 = 'saas_incidentals';

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 梅晨
     */
    public function index()
    {
        $this->title = '教材杂费';
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

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 梅晨
     */
    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 梅晨
     */
    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * @return array|string
     * 杂费修改
     */
    public function edit_in()
    {
        return $this->_form($this->table1, 'form');
    }

    /**
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author 梅晨
     */
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

}
