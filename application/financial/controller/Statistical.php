<?php
/**
 * User: Hzb
 * Date: 2018/9/26 15:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\financial\controller;


use controller\BasicAdmin;
use think\Db;
use think\facade\Log;

/**
 * Class Statistical
 * @package app\survey\controller
 * @author Hzb
 * 销售员业绩统计
 */
class Statistical extends BasicAdmin
{

    public function saler_count()
    {
        return $this->fetch('');
    }

    public function get_saler_data()
    {
        $db = Db::name('saas_cash_flow')
            ->alias('f')
            ->join('saas_customer c','f.cid=c.id','left')
            ->join('saas_employee e','c.sales_id=e.userid','left')
            ->field('c.branch,c.sales_id,f.price,sum(f.price) as price,count(f.id) as total_num,e.name')
            ->group('c.sales_id');
        $get = $this->request->get();
        $_start = strtotime(date('Y-m-01', strtotime(date("Y-m-d"))));
        $_end = time();
        if (!empty($get['time_range'])){
            list($_start, $_end) = explode(' ~ ', $get['time_range']);
            $_start = strtotime($_start);
            $_end = strtotime($_end);
        }
        $db->whereBetween('f.created_at',[$_start,$_end]);
        if (!empty($get['branch'])){
            $db->where('e.department','=',$get['branch']);
        }
        $order= $db->select();
        $saler = [];
        $price = [];
        $total_num = [];
        foreach ($order as $key=>$value){
            if (empty($value['name'])) {
                $saler[] = '系统';
            } else {
                $saler[] = $value['name'];
            }
            $price[] = round($value['price'],2);
            $total_num[] = $value['total_num'];
        }
        $time = !empty($get['time_range'])?$get['time_range'].'日':date('Y-m',$_start).'月';
        $res = ['name'=>$saler,'total_price'=>$price,'total_num'=>$total_num,'time'=>$time];
        return json($res);
    }

}