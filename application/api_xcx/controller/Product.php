<?php


namespace app\api_xcx\controller;


use controller\BasicXcx;
use think\Db;
use think\facade\Log;

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
        $cate = Db::name('store_goods_cate')->field('id,cate_title')->where('is_deleted', '=', 0)->select();
        $get['cate_id'] = isset($get['cate_id']) ? $get['cate_id'] : 1;
        $res = Db::name('store_goods g')
            ->join('store_goods_spec gs', 'g.spec_id = gs.id')
            ->where('g.is_deleted', '=', 0)
            ->where('g.status', '=', 1)
            ->where('g.cate_id', '=', $get['cate_id'])
            ->field('g.id,g.goods_title,g.goods_desc,g.goods_subtitle,g.goods_logo,gs.spec_param');
        if (isset($get['keyword']) && !empty($get['keyword'])) {
            $res->where('g.goods_title', 'like', '%' . $get['keyword'] . '%');
        }
        $product = $res->select();
        if (!$product) {
            return $this->error('数据获取失败');
        }
        foreach ($product as &$item) {
            foreach (json_decode($item['spec_param'], 1) as $value) {
                $item['spec_type'][] = $value['name'];
            }
        }
        $data = [
            'product_list' => $product,
            'product_type' => $cate
        ];
        return $this->success('数据获取成功', $data);
    }

    public function detail()
    {
        $get = $this->request->get();
        if (!isset($get['id'])) return $this->error('参数错误');
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
        $len = count($spec);
        $low_price = $spec[0]['value'];
        $high_price = $spec[$len - 1]['value'];
        $product['goods_content'] = base64_encode($product['goods_content']);
        $data = [
            'detail' => $product,
            'spec' => $spec,
            'lowe_price' => $low_price,
            'high_price' => $high_price
        ];
        return $this->success('数据获取成功', $data);
    }
}