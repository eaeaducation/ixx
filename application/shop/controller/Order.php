<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/17 0017
 * Time: 11:12
 */

namespace app\shop\controller;

use think\Controller;
use think\Db;

class Order extends Controller
{
    public function list()
    {
        $customer = session('customer');
        if (!$customer) {
            $url = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/shop/index/index';
            cookie('url', $url, 120);
            $this->redirect('/shop/login/index');
        }
        $img = Db::name('saas_content')->where('catid', '=', 75)->field('thumb')->find();

        $order = Db::name('saas_order')->alias('o')
            ->join('saas_order_log l', 'o.id = l.order_id', 'right')
            ->join('store_goods g', 'l.goods_id = g.id', 'left')
            ->where('o.status', '<>', 3)
            ->where('l.goods_type', '=', 1)
            ->where('o.student_id', '=', $customer['id'])
            ->order('o.id desc')
            ->field('o.status, l.id, l.goods_id, l.price, o.id as oid, o.orderno, g.goods_title')
            ->select();
        $this->assign('code', session('code_openid'));
        return view('', ['img' => $img, 'list' => $order]);
    }

    public function del()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $res = Db::name('saas_order')->where('id', $post['oid'])->update(['status' => 3]);
            if ($res) {
                $this->success('订单删除成功', '');
            } else {
                $this->error('订单删除失败，请稍候再试');
            }
        }
    }
}