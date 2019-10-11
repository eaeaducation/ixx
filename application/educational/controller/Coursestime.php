<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/4
 * Time: 14:26
 */

namespace app\educational\controller;

use controller\BasicAdmin;
use service\DataService;
use think\Db;
use service\LogService;

class Coursestime extends BasicAdmin
{
    public $table = "saas_courses_time";

    public function index()
    {
        $this->title = '课程时间管理';
        $db = Db::name($this->table)->where('status', '<>', 3)->order('id desc');
        return parent::_list($db);
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
            LogService::write('上课时间', '删除了上课时间');
            $this->success("时间删除成功!", '');
        }
        $this->error("时间删除失败, 请稍候再试!");
    }

    public function _form_filter(&$vo)
    {
        if ($this->request->isGet() && isset($vo['id'])) {
            $vo['courses_time'] = $vo['start_time'] . " ~ " . $vo['end_time'];
        }

        if ($this->request->isPost()) {

            if ($vo['courses_time'] == '') {
                $this->error("请选择开始和结束时间!");
            }
            list($start_time, $end_time) = explode('~', $vo['courses_time']);
            $start_time = trim($start_time);
            $end_time = trim($end_time);
            $db = Db::name($this->table);
            $time_count = $db
                ->where('status', '<>', 3)
                ->where('start_time', $start_time)
                ->where('end_time', $end_time)
                ->count();
            if ($time_count > 0) {
                $this->error('已存在这个时间段');
            }
            if (strtotime(trim($start_time)) >= strtotime(trim($end_time))) {
                $this->error("开始时间不能大于结束时间!");
            }
            $vo['start_time'] = trim($start_time);
            $vo['end_time'] = trim($end_time);
            if (isset($vo['id'])) {
                LogService::write('上课时间', '编辑上课时间【' . $vo['start_time'] . '-' . $vo['end_time'] . '】');
            } else {
                LogService::write('上课时间', '添加了一条上课时间信息【' . $vo['start_time'] . '-' . $vo['end_time'] . '】');
            }
        }
    }

}