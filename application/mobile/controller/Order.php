<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/19 0019
 * Time: 11:39
 */

namespace app\mobile\controller;

use think\Db;
use app\common\model\Customer;

class Order extends MobileBase
{
    public function index()
    {
        return view();
    }
}