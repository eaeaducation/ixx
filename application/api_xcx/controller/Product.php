<?php


namespace app\api_xcx\controller;


use controller\BasicXcx;
use think\Db;

class Product extends BasicXcx
{
    /**
     * 产品分类
     */
    public function product_cate()
    {
        $cate = Db::name('store_goods_cate')->field('id,cate_title')->where('is_deleted', '=', 0)->select();
        if (!$cate) {
            return $this->error('数据获取失败');
        }
        return $this->success('数据获取成功', $cate);
    }
    /**
     * 在线选课
     * 产品列表
     */
    public function index()
    {
        $get = $this->request->get();
        $get['cate_id'] = isset($get['cate_id']) ? $get['cate_id'] : 1;
        $data = Db::name('store_goods')
            ->where('is_deleted', '=', 0)
            ->where('status', '=', 1)
            ->field('id,goods_title,goods_subtitle,goods_logo');
        if (isset($get['cate']) && !empty($get['cate'])) {
            $data->where('cate_id', '=', $get['cate_id']);
        }
        if (isset($get['keyword']) && !empty($get['keyword'])) {
            $data->where('goods_title', 'like', '%' . $get['keyword'] . '%');
        }
        $product = $data->select();
        if (!$data) {
            return $this->error('数据获取失败');
        }
        return $this->success('数据获取成功', $product);
    }

    public function detail()
    {
        $get = $this->request->get();
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
            ->value('spec_param');
        $spec = json_decode($money, true);
        $data = [
            'detail' => $product,
            'spec' => $spec
        ];
        return $this->success('数据获取成功', $data);
    }
}