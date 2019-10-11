<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/16 0016
 * Time: 17:51
 */

namespace app\shop\controller;

use think\Controller;
use think\Db;

class Join extends Controller
{
    public function index()
    {
        $img = Db::name('saas_content')->where('catid', '=', '74')->field('thumb')->find();
        $this->assign('image', $img);
        return view();
    }
}