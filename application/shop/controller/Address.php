<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3 0003
 * Time: 9:06
 */

namespace app\shop\controller;


use think\Controller;
use think\Db;

class Address extends Controller
{
    public function index()
    {
        $list = Db::name('store_goods_address')->select();
        $this->assign('list', $list);
        return view();
    }
}