<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/7
 * Time: 17:48
 */
namespace app\common\model;

use think\Model;

/**
 * Class Order
 * @package app\common\model
 */
class Order extends Model
{
    public $table = 'saas_order';

    public static function orderList($id)
    {
        $order = Order::where('student_id', '=', $id)->select();
        return $order;
    }
}