<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/16 0016
 * Time: 16:38
 */

namespace app\shop\controller;

use think\Controller;
use think\Db;

class Show extends Controller
{
    public function index()
    {
        $res = Db::name('store_goods_brand')
            ->where('status', '<>', 3)
            ->field('id,brand_logo,brand_title')
            ->select();
        return view('', ['list' => $res]);
    }

    public function detail()
    {
        $get = $this->request->get();
        if (!isset($get['id']) && empty($get['id'])) {
            $this->redirect('/shop/show/index');
        }
        $row = Db::name('store_goods_brand')
            ->where('id', '=', $get['id'])
            ->where('status', '<>', 3)
            ->field('id,brand_detail,brand_cover')
            ->find();
        if (strpos($row['brand_cover'], '|') !== false) {
            $row['brand_cover'] = explode('|', $row['brand_cover']);
        } else {
            $url = $row['brand_cover'];
            $row['brand_cover'] = [];
            $row['brand_cover'][0] = $url;
        }
        return view('', ['row' => $row]);
    }
}