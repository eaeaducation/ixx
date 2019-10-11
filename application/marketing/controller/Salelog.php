<?php
/**
 * User: Jasmine2
 * Date: 2018/6/20 14:41
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\marketing\controller;

use app\common\model\Order;
use app\common\model\Customer;
use app\common\model\CustomerFollow;
use controller\BasicAdmin;
use think\App;
use think\Db;
use service\DataService;

/**
 * Class PreMarketing
 * @package app\marketing\controller
 * @author Jasmine2
 * 售前营销
 */
class Salelog extends BasicAdmin
{
    public $table = 'saas_customer';
    public $table2 = 'system_fast_word';


    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function customerViewlog()
    {
        $this->title = '客户详情';
        $id = $this->request->get('id', false);
        $customer = Customer::findOrFail($id);
        $order = Db::name('saas_order')->alias('o')
            ->join('saas_order_log l', 'o.id = l.order_id', 'left')
            ->where('o.student_id', '=', $id)
            ->field('o.class_id, l.goods_id, l.price, o.status, l.goods_num, l.goods_type')
            ->select();
        return view('', [
            'customer' => $customer,
            'title' => $this->title,
            'order' => $order
        ]);
    }

}