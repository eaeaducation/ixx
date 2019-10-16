<?php


namespace app\api\controller\v2;


use app\api\controller\AppBasicApi;
use think\Db;

class Product extends AppBasicApi
{

    public function product_attr()
    {
        //产品分类
        $goods_type = Db::name('store_goods_cate')->field('id,cate_title')->where('is_deleted', '=', 0)->select();
        $return_data = api_encrypt([
            'goods_type' => $goods_type,
        ]);
        return $this->success('', $return_data);
    }

    /**
     * 商品列表
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goods_list()
    {
        $post = $this->request_data;
        $post['cate_id'] = isset($post['cate_id']) ? $post['cate_id'] : 1;
        $post['per_page'] = isset($post['per_page']) ? $post['per_page'] : 20;
        $db = Db::name('store_goods')
            ->where('is_deleted', '=', 0)
            ->where('status', '<>', 3)
            ->field('id,goods_title,goods_subtitle,goods_logo');
        if (isset($post['cate_id'])) {
            $db->where('cate_id', '=', $post['cate_id']);
        }
        if (isset($post['keyword'])) {
            $db->where('goods_title', 'like', '%' . $post['keyword'] . '%');
        }
        $goods_list = $db->paginate($post['per_page']);
        $return_data = api_encrypt([
            'goods_list' => $goods_list,
        ]);
        return $this->success('', $return_data);
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
        $post = $this->request_data;
        $product = Db::name('store_goods')
            ->where('id', '=', $post['id'])
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
        $return_data = api_encrypt([
            'product' => $product,
            'spec_param' => $spec,
            'low_price' => $low_price,
            'high_price' => $high_price
        ]);
        return $this->success('', $return_data);
    }
}