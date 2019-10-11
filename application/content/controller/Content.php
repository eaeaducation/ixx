<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19
 * Time: 16:05
 */

namespace app\content\controller;

use controller\BasicAdmin;
use think\App;
use think\Db;
use service\DataService;
use app\common\model\Course;
use service\LogService;
use think\facade\Cache;
use service\ToolsService;

class Content extends BasicAdmin
{
    public $table = 'saas_content';

    public function index()
    {
        $this->title = '官网内容管理';
        $db = Db::name($this->table)->order('sort asc,id asc');
        $get = $this->request->get();
        if (isset($get['catid']) && $get['catid'] !== '' && $get['catid'] !== '0') {
            $db->where('catid', 'in', "{$get['catid']}");
        }
        if (isset($get['title']) && $get['title'] !== '') {
            $db->where('title', 'like', "%{$get['title']}%");
        }
        return parent::_list($db, true);
    }

    protected function _index_data_filter(&$vo)
    {
        $_menus = Db::name('saas_content_category')->where('status','<>','3')->order('sort desc,id desc')->select();
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
        $this->title = '添加';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑菜单
     */
    public function edit()
    {
        $this->title = '编辑';
        return $this->_form($this->table, 'form');
    }

    protected function _form_result(&$data)
    {
        if ($data) {
            $this->success('保存成功!', '');
        }
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
            $_menus = Db::name('saas_content_category')->where('status','<>','3')->order('sort desc,id desc')->select();
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
            isset($vo['catid']) ? '' : $vo['catid'] = 0;
            isset($vo['status']) ? '' : $vo['status'] = 0;
            isset($vo['thumb']) ? '' : $vo['thumb'] = '';
            isset($vo['app_id']) ? '' : $vo['app_id'] = 0;
            $app = Cache::get('app_list', []);
            $this->assign('apps', $app);
            $this->assign('menus', $menus);
        }
        if ($this->request->isPost()) {
            if (isset($vo['status']) && $vo['status'] == 'on') {
                $vo['status'] = 99;
            } else {
                $vo['status'] = 0;
            }
//            if (mb_strlen($vo['content']) < 10) {
//                $this->error('内容至少为10个字符');
//            }
        }
    }

    /**
     * 删除菜单
     */
    public function del()
    {
        if ($this->request->isGet()) {
            $get = $this->request->get();
            $res = Db::name($this->table)
                ->where('id', '=', $get['id'])
                ->delete();
        }
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $res = Db::name($this->table)
                ->where('id', 'in', $post['id'])
                ->delete();
        }
        if ($res) {
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }

    /**
     * 禁用
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            $this->success("禁用成功！", '');
        }
        $this->error("禁用失败，请稍候再试！");
    }

    /**
     * 启用
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            $this->success("启用成功！", '');
        }
        $this->error("启用失败，请稍候再试！");
    }
}