<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/9
 * Time: 11:02
 */

namespace app\educational\controller;

use controller\BasicAdmin;
use think\Db;
use service\DataService;
use service\LogService;


/**
 * 科目管理
 * Class Subject
 * @package app\educational\controller
 * @author mei
 */
class Subject extends BasicAdmin
{
    /**
     * 绑定操作模型
     * @var string
     */
    public $table = 'saas_subject';

    /**
     * 科目列表
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function index()
    {
        $this->title = '科目管理';
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
            LogService::write('科目设置', '删除了科目信息');
            $this->success("科目删除成功!", '');
        }
        $this->error("科目删除失败, 请稍候再试!");
    }

    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            if (isset($vo['id'])) {
                LogService::write('科目设置', '编辑科目【' . $vo['title'] . '】');
            } else {
                LogService::write('科目设置', '添加了一条科目信息【' . $vo['title'] . '】');
            }
        }

    }

    /**
     * 获取科目
     */
    public function get_subject()
    {
        $category_id = $this->request->post('category');
        $data = Db::name('saas_subject')
            ->field('id,title')
            ->where('category_id','=',$category_id)
            ->where('status','=','1')
            ->select();
        if ($data){
            $this->success('', '', $data);
        } else {
            $this->error();
        }
    }

}