<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/28 0028
 * Time: 10:09
 */
namespace app\notify\controller;

use service\HttpService;
use think\Controller;
use think\Db;
use think\facade\Log;
use Naixiaoxin\ThinkWechat\Facade;

class Notify extends Controller
{
    public function ybpay()
    {
        $post = $this->request->post();
        Log::error('易宝支付异步'.json_encode($post));
        $row = json_decode($post, true);
        $update_data = [];
        $update_data['paytime'] = time();
        $update_data['notify'] = json_encode($post);
        $update_data['status'] = 99;
        Db::name('saas_pay_order')->where('order_no', '=', $row['data']['orderId'])->update($update_data);
//        $order_data = Db::name('saas_pay_order')->where('order_no', '=', $row['data']['orderId'])->find();
//        $url = 'http://jy.chengxf.com/client/order/saveOrder';
//        HttpService::post($url, $order_data);
        echo 'SUCCESS';
    }

    public function xcxShopNotify()
    {
        $app = Facade::payment('wxxcx');
        $response = $app->handlePaidNotify(function ($message, $fail) {
            Log::error($message);
            if (isset($message['result_code']) && $message['result_code'] == 'SUCCESS') {
                $has_order = Db::name("saas_order")->where("orderno", "=", $message['out_trade_no'])->find();
                if ($has_order) {
                    $param['status'] = 5;
                    Db::name("saas_order")->where("orderno", "=", $message['out_trade_no'])->update($param);
                }
                return true;
            } else {
                return false;
            }
        });
        $response->send();
    }
}