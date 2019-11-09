<?php


namespace app\api_xcx\controller;


use controller\BasicXcx;
use think\Db;

class Show extends BasicXcx
{
    /**
     * 精英秀
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $data = Db::name('store_goods_brand')
            ->where('status', '=', 1)
            ->where('is_deleted', '=', 0)
            ->field('id,brand_logo,brand_title')
            ->select();
        return $this->success('数据获取成功', $data);
    }

    /**
     * 菁英秀详情
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail()
    {
        $get = $this->request->get();
        $row = Db::name('store_goods_brand')
            ->where('id', '=', $get['id'])
            ->where('status', '=', 1)
            ->where('is_deleted', '=', 0)
            ->field('id,brand_detail,brand_cover')
            ->find();
        if (strpos($row['brand_cover'], '|') !== false) {
            $row['brand_cover'] = explode('|', $row['brand_cover']);
        } else {
            $url = $row['brand_cover'];
            $row['brand_cover'] = [];
            $row['brand_cover'][0] = $url;
        }
        $row['brand_detail'] = base64_encode($row['brand_detail']);
        return $this->success('数据获取成功', $row);
    }
}