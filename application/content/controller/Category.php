<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19
 * Time: 9:48
 */

namespace app\content\controller;

use controller\BasicAdmin;
use think\App;
use think\Db;
use service\DataService;
use app\common\model\Course;
use service\LogService;
use service\ToolsService;

/**
 * 栏目管理
 * Class Category
 * @package app\content\controller
 */
class Category extends BasicAdmin
{
    public $table = 'saas_content_category';

    public function index()
    {
        $this->title = '栏目管理';
        $get = $this->request->get();
        $db = Db::name($this->table)->where('status', '<>', '3')->order('sort asc,id asc');
        if (isset($get['type']) && $get['type'] !== '') {
            $db->where('type', '=', "{$get['type']}");
        }
        if (isset($get['id']) && $get['id'] !== '' && $get['id'] !== '0') {
            $db->where('id', '=', "{$get['id']}");
        }
        return parent::_list($db, false);
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index_data_filter(&$data)
    {
        foreach ($data as &$vo) {
            $vo['ids'] = join(',', ToolsService::getArrSubIds($data, $vo['id']));
        }
        $data = ToolsService::arr2table($data);
        $_menus = Db::name('saas_content_category')->where('status', '<>', '3')->order('sort desc,id desc')->select();
        foreach ($_menus as &$vo) {
            $vo['ids'] = join(',', ToolsService::getArrSubIds($_menus, $vo['id']));
        }
        $menus = ToolsService::arr2table($_menus);
        foreach ($menus as $key => &$menu) {
            if (substr_count($menu['path'], '-') > 3) {
                unset($menus[$key]);
                continue;
            }
            if (isset($vo['pid'])) {
                $current_path = "-{$vo['pid']}-{$vo['id']}";
                if ($vo['pid'] !== '' && (stripos("{$menu['path']}-", "{$current_path}-") !== false || $menu['path'] === $current_path)) {
                    unset($menus[$key]);
                }
            }
        }
        $this->assign('menus', $menus);
    }

    /**
     * 添加菜单
     */
    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑菜单
     */
    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 表单数据前缀方法
     *
     * @param array $vo
     */
    protected function _form_filter(&$vo)
    {
        if ($this->request->isGet()) {
            // 上级菜单处理
            $_menus = Db::name($this->table)->where('status', '<>', '3')->order('sort desc,id desc')->select();
            $_menus[] = ['name' => '一级栏目', 'id' => '0', 'pid' => '-1'];
            $menus = ToolsService::arr2table($_menus);
            foreach ($menus as $key => &$menu) {
                if (substr_count($menu['path'], '-') > 3) {
                    unset($menus[$key]);
                    continue;
                }
//                if (isset($vo['pid'])) {
//                    $current_path = "-{$vo['pid']}-{$vo['id']}";
//                    if ($vo['pid'] !== '' && (stripos("{$menu['path']}-", "{$current_path}-") !== false || $menu['path'] === $current_path)) {
//                        unset($menus[$key]);
//                    }
//                }
            }
            isset($vo['pid']) ? '' : $vo['pid'] = 0;
            $this->assign('menus', $menus);
        }
    }

    /**
     * 删除菜单
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }

    /**
     * 菜单禁用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            $this->success("菜单禁用成功!", '');
        }
        $this->error("菜单禁用失败, 请稍候再试!");
    }

    /**
     * 菜单启用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            $this->success("菜单启用成功!", '');
        }
        $this->error("菜单启用失败, 请稍候再试!");
    }

}