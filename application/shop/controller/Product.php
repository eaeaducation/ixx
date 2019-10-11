<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20 0020
 * Time: 11:42
 */

namespace app\shop\controller;


use think\Controller;
use think\Db;

class Product extends Controller
{
    /**
     * 商品列表
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $get = $this->request->get();
        $get['cate'] = isset($get['cate']) ? $get['cate'] : 1;
        $cate = Db::name('store_goods_cate')->field('id,cate_title')->where('is_deleted', '=', 0)->select();
        if ($this->request->isPost()) {
            $post = $this->request->post();
        }
        $db = Db::name('store_goods')
            ->where('is_deleted', '=', 0)
            ->where('status', '<>', 3)
            ->field('id,goods_title,goods_subtitle,goods_logo');
        if (isset($get['cate'])) {
            $db->where('cate_id', '=', $get['cate']);
        }
        if (isset($get['keyword'])) {
            $db->where('goods_title', 'like', '%' . $get['keyword'] . '%');
        }
        $product = $db->select();
        $this->assign('list', $product);
        $this->assign('cate', $cate);
        $this->assign('on', $get['cate']);
        return view();
    }

    /**
     * 商品详情
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail()
    {
        $get = $this->request->get();
        if (!isset($get['id']) && empty($get['id'])) {
            $this->redirect('/shop/product/index');
        }
        $product = Db::name('store_goods')
            ->where('id', '=', $get['id'])
            ->where('is_deleted', '=', 0)
            ->where('status', '<>', 3)
            ->field('id,goods_title,goods_desc,goods_content,goods_image,spec_id,goods_subtitle')
            ->find();
        if (strpos($product['goods_image'], '|') !== false) {
            $product['goods_image'] = explode('|', $product['goods_image']);
        } else {
            $url = $product['goods_image'];
            $product['goods_image'] = [];
            $product['goods_image'][0] = $url;
        }
        $money = Db::name('store_goods_spec')
            ->where('id', '=', $product['spec_id'])
            ->column('spec_param');
        $spec = json_decode($money[0], true);
        $len = count($spec);
        $low_price = $spec[0]['value'];
        $high_price = $spec[$len - 1]['value'];
        $this->assign('product', $product);
        $this->assign('spec', $spec);
        $this->assign('low', $low_price);
        $this->assign('high', $high_price);
        $this->assign('code', session('code_openid'));
        return view();
    }

    /**
     * 购物车页面
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cart()
    {
        $customer = session('customer');
        if (!$customer) {
            $url = $_SERVER['HTTP_REFERER'];
            cookie('url', $url, 3600);
            $this->redirect('/shop/login/index');
        }
        $product = Db::name('store_cart')->alias('c')
            ->join('store_goods g', 'c.gid = g.id', 'left')
            ->where('c.status', '<>', 3)
            ->where('c.cid', '=', $customer['id'])
            ->order('c.created_at desc')
            ->field('c.id as cart_id, c.price, g.goods_title, g.goods_image, c.number, c.param, g.id')
            ->select();
        $this->assign('list', $product);
        $this->assign('code', session('code_openid'));
        return view();
    }

    /**
     * 添加购物车
     */
    public function add()
    {
        $customer = session('customer');
        if (!$customer) {
            $this->error('用户未登录');
        }
        if ($this->request->isPost()) {
            $post = $this->request->post();
            //检查库里是否又该商品
            $is_exist = Db::name('store_cart')
                ->where('gid', '=', $post['pro_id'])
                ->where('cid', '=', $customer['id'])
                ->where('param', '=', $post['param'])
                ->field('id, number, price')
                ->find();
            $data = [];
            if (!$is_exist) {
                $data['cid'] = $customer['id'];
                $data['gid'] = $post['pro_id'];
                $data['price'] = $post['price'];
                $data['status'] = 1;
                $data['number'] = $post['num'];
                $data['param'] = $post['param'];
                $data['created_at'] = time();
                $res = Db::name('store_cart')->insert($data);
            } else {
                $data['number'] = $post['num'] + $is_exist['number'];
                $data['price'] = $post['price'] + $is_exist['price'];
                $res = Db::name('store_cart')->where('id', '=', $is_exist['id'])->update($data);
            }
            if ($res) {
                $this->success('添加成功', '');
            } else {
                $this->error('添加失败，请稍后再试');
            }
        }
    }

    /**
     * 购物车删除
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $res = Db::name('store_cart')->where('id', '=', $post['id'])->delete();
            if ($res) {
                $this->success('删除成功！', '');
            } else {
                $this->error('删除失败，请稍后再试。。。');
            }
        }
    }

    public function buy()
    {
        $customer = session('customer');
        if (!$customer) {
            $url = $_SERVER['HTTP_REFERER'];
            cookie('url', $url, 120);
            $data['url'] = '/shop/login/index';
            $this->error('', '', $data);
        }
        $this->success('', '', '');
    }
}