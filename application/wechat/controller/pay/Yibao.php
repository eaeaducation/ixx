<?php
/**
 * User: Jasmine2
 * Date: 2018/7/20 15:32
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\wechat\controller\pay;

use think\Controller;
use EasyWeChat\Factory;
use think\Db;
use think\exception\PDOException;
use think\facade\Log;

/**
 * Class Show
 * @package app\wechat\controller\pay
 * @author Jasmine2
 * 易宝微信收款
 */
class Yibao extends Controller
{

    public function notify()
    {
        $app = Factory::payment($this->config);
        $response = $app->handlePaidNotify(function ($message, $fail) {
            Log::error($message);
            if (isset($message['result_code']) && $message['result_code'] == 'SUCCESS') {
                Db::name('saas_pay_order')->where('order_no', $message['out_trade_no'])->update(['status' => 99]);
                return true;
            }
        });
        return $response->send();
    }

    /**
     * @param $order_no
     * @throws PDOException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function queryOrder($order_no)
    {
        $app = Factory::payment($this->config);
        $res = $app->order->queryByOutTradeNumber($order_no);
        if (isset($res['trade_state']) && $res['trade_state'] == 'SUCCESS') {
            Db::name('saas_pay_order')->where('order_no', $order_no)
                ->where('status', '=', 0)->update(['status' => 1]);
            return json([
                'success' => true,
                'data' => $res
            ]);
        }
        return json([
            'success' => false,
            'data' => ''
        ]);
    }

    /**
     * @return \think\response\View
     * @throws PDOException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function success_page()
    {
//        $app = Factory::payment($this->config);
        $order_no = $this->request->get('orderno');
//        $res = $app->order->queryByOutTradeNumber($order_no);
        $success = false;
        if (isset($res['trade_state']) && $res['trade_state'] == 'SUCCESS') {
            $order = Db::name('saas_pay_order')
                ->where('order_no', $order_no)->find();
            Db::name('saas_pay_order')->where('order_no', $order_no)->update(['status' => 99]);
            $success = true;
        }
        return view('', [
            'success' => $success,
            'order' => isset($order) ? $order : null
        ]);
    }

}