<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/4
 * Time: 11:09
 */

namespace app\educational\controller;

use controller\BasicAdmin;
use think\Db;
use service\DataService;
use service\LogService;


/**
 * 教室管理
 * Class Room
 * @package app\room\controller
 */
class Room extends BasicAdmin
{
    public $table = 'saas_room';

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
        $this->title = '教室管理';
        $userid = $this->user['authorize'];
        $db = Db::name($this->table)
            ->where('status', '<>', 3)
            ->order('id desc');
        if (!in_array($userid, [1, 3, 4, 22])) {
            $department = $this->user['employee']['department'];
        }
        if (!empty($department)) {
            $db->where('branch', '=', $department);
        }
        $get = $this->request->get();
        (isset($get['code']) && $get['code'] !== '') && $db->where('code', 'like', "%{$get['code']}%");
        foreach (['branch', 'type'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', $get[$key]);
        }
        $this->assign('userid', $userid);
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
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author 梅晨
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            LogService::write('教师管理', '删除了教室信息');
            $this->success("教室删除成功!", '');
        }
        $this->error("教室删除失败, 请稍候再试!");
    }

    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            if (isset($vo['id'])) {
                LogService::write('教室管理', '编辑了教室【' . $vo['code'].'】');
            } else {
                LogService::write('教室管理', '添加了一条教室信息【' . $vo['code'].'】');
            }
        }

    }
}