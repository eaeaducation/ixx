<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\show\controller;

use app\store\service\GoodsService;
use controller\BasicAdmin;
use service\DataService;
use service\ToolsService;
use think\Db;
use think\exception\HttpResponseException;

/**
 * 商店商品管理
 * Class Goods
 * @package app\store\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Introduction extends BasicAdmin
{
    public $table = "saas_show_introduction";

    public function index()
    {
        $get = $this->request->get();
        $this->title = "转介绍活动";
        $db = Db::name($this->table)->where('status', '<>', 3);
        if (!empty($get['title'])) $db->where('title', $get['title']);
        return $this->_list($db);

    }

    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    public function edit()
    {
        return $this->_form($this->table, 'form');
    }


    /**
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author Jasmine2
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("活动删除成功!", '');
        }
        $this->error("活动删除失败, 请稍候再试!");
    }

    /**
     * 介绍详情
     */
    public function introductionList()
    {
        $this->title = "转介绍详情";
        $db = Db::name('saas_customer_introducer')
            ->field('i.id,i.introducer_id,i.created_at,c.id as cid,c.parent_name,c.parent_tel,c.branch,s.title')
            ->alias('i')
            ->where('i.status', '<>', 3)
            ->join('saas_customer c', 'i.customer_id=c.id')
            ->join('saas_show_introduction s', 'i.actid=s.id')
            ->order('i.created_at desc');
        $get = $this->request->get();

        if (isset($get['customer_tel']) && $get['customer_tel'] != '') {
            $db->where('c.parent_tel', $get['customer_tel']);
        }
        if (isset($get['introducer_tel']) && $get['introducer_tel'] != '') {
            $introducer_id = get_customer_id($get['introducer_tel']);
            $db->where('i.introducer_id', $introducer_id);
        }
        $data = $db->select();
        $this->assign('title', $this->title);
        return $this->fetch('', ['data' => $data]);
    }

    /**
     * 完成名单
     */
    public function prize()
    {
        $get = $this->request->get();
        $id = $get['id'];
        $db = DB::name('saas_introducer_prize');
        if (isset($get['customer_tel']) && $get['customer_tel'] != '') {
            $cid = get_customer_id($get['customer_tel']);
            $db->where('customer_id', $cid);
        }
        $introduce = $db->where('act_id', $id)->select();
        return $this->fetch('', ['introduce' => $introduce]);
    }

    /**
     * 发放奖励后 给客户记录
     */
    public function addPrize()
    {
        $cid = $this->request->get('cid');
        $act_id = $this->request->get('act_id');
        $res = Db::name('saas_introducer_prize')->where('act_id', $act_id)->where('customer_id', $cid)->update(['status' => 3]);
        if ($res) {
            $this->success('记录成功');
        } else {
            $this->error('记录失败');
        }
    }
}