<?php
/**
 * User: Jasmine2
 * Date: 2018/7/20 15:32
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\wechat\controller\pay;


use Detection\MobileDetect;
use think\Controller;
use EasyWeChat\Factory;
use think\Db;
use think\exception\PDOException;
use think\facade\Log;

/**
 * Class Show
 * @package app\wechat\controller\pay
 * @author Jasmine2
 * 活动秀收款
 */
class Show extends Controller
{
    private $config = [];

    /**
     * @throws PDOException
     * @throws \think\Exception
     * @author Jasmine2
     */
    public function initialize()
    {
        $this->config = [
            'app_id' => sysconf('wechat_app_id'),
            'secret' => sysconf('wechat_secret'),
            'mch_id' => sysconf('wechat_mch_id'),
            'key' => sysconf('wechat_mch_key'),
            'oauth' => [
                'scopes' => ['snsapi_base'],
                'callback' => url("wechat/pay.show/jump", [], true, true),
            ],
        ];
    }

    public function pre_index()
    {
        $this->config['oauth']['callback'] = url("wechat/pay.show/jump", ['id' => urlencode($this->request->get('id'))], true, true);
        $app = Factory::officialAccount($this->config);
        $response = $app->oauth
            ->scopes(['snsapi_base'])
            ->redirect();
        return $response->send();
    }

    public function jump($id)
    {
        $app = Factory::officialAccount($this->config);
        $openid = $app->oauth->user()->getId();
        $url = url('wechat/pay.show/index') . '?id=' . $id;
        Log::error($url);
        if (stripos($url, '?') > 0) {
            $url .= '&openid=' . $openid;
        } else {
            $url .= '?openid=' . $openid;
        }
        return redirect($url);
    }

    /**
     * @return \think\response\View
     * @throws
     * @author Jasmine2
     */
    public function index()
    {
        $detect = new MobileDetect();
        $is_wechat = $detect->is('WeChat');
        if ($is_wechat) {
            $openid = $this->request->get('openid', false);
            if ($openid == false) {
                // 接受订单信息
                $data = $this->request->get();
                unset($data['/wechat/pay_show/index_html']);
                $res = $this->validate($data, [
                    'title' => 'require',
                    'body' => 'require',
                    'price' => 'require',
                    'cid' => 'require',
                    'aid' => 'require',
                ]);
                if ($res !== true) {
                    return json([
                        'success' => false,
                        'msg' => $res
                    ]);
                }
                $order = array_merge($data, [
                    'type' => 1,
                    'trade_type' => 'JSAPI',
                    'created_at' => time(),
                    'status' => 0,
                    'orderno' => generate_order_no()
                ]);
                /*$order_num = Db::name('wechat_order')
                    ->where('cus_num', '=', 1)
                    ->select();
                if (count($order_num) < 10) {
                    $order_id = Db::name('wechat_order')->insertGetId($order);
                    return redirect(url('wechat/pay.show/pre_index') . '?id=' . $order_id);
                } else {
                    return redirect(url('home/introduction/fail'));
                }*/
                $order_id = Db::name('wechat_order')->insertGetId($order);
                return redirect(url('wechat/pay.show/pre_index') . '?id=' . $order_id);
            } else {
                $id = $this->request->get('id');
                $order = Db::name('wechat_order')
                    ->where('id', '=', $id)
                    ->where('status', '=', 0)
                    ->find();
                /*$order_num = Db::name('wechat_order')
                    ->where('cus_num', '=', 1)
                    ->select();
                if (count($order_num) >= 10) {
                    return redirect(url('home/introduction/fail'));
                }*/
                if ($order) {
                    $app = Factory::payment($this->config);
                    $res = $app->order->unify([
                        'body' => $order['body'],
                        'out_trade_no' => $order['orderno'],
                        'total_fee' => round($order['price'] * 100, 0),
                        'notify_url' => url('wechat/pay.show/notify', [], true, true),
                        'trade_type' => 'JSAPI',
                        'openid' => $openid,
                    ]);
                    Log::error($res);
                    $jssdk = $app->jssdk;
                    return view('', [
                        'config' => $jssdk->bridgeConfig($res['prepay_id'], true),
                        'order' => $order,
                        'is_wechat' => true
                    ]);
                } else {
                    die('订单不存在或已支付');
                }
            }
        } else {
            return $this->h5pay();
        }
    }

    /**
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @author Jasmine2
     */
    protected function h5pay()
    {
        $data = $this->request->get();
        unset($data['/wechat/pay_show/index_html']);
        $res = $this->validate($data, [
            'title' => 'require',
            'body' => 'require',
            'price' => 'require',
            'cid' => 'require',
            'aid' => 'require',
        ]);
        if ($res !== true) {
            return json([
                'success' => false,
                'msg' => $res
            ]);
        }
        $order = array_merge($data, [
            'type' => 1,
            'trade_type' => 'MWEB',
            'created_at' => time(),
            'status' => 0,
            'orderno' => generate_order_no()
        ]);
        $order_id = Db::name('wechat_order')->insertGetId($order);
        /*$order_num = Db::name('wechat_order')
            ->where('cus_num', '=', 1)
            ->select();
        if (count($order_num) > 10){
            return redirect(url('home/introduction/fail'));
        }*/
        $app = Factory::payment($this->config);
        $request = [
            'body' => $order['body'],
            'out_trade_no' => $order['orderno'],
            'total_fee' => round($order['price'] * 100, 0),
            'notify_url' => url('wechat/pay.show/notify', [], true, true),
            'trade_type' => 'MWEB',
            'spbill_create_ip' => $this->request->ip(),
            'scene_info' => json_encode([
                'h5_info' => [
                    'type' => 'h5_info',
                    'wap_url' => $this->request->domain(),
                    'wap_name' => sysconf('app_name')
                ]
            ])
        ];
        Log::error('微信支付:' . json_encode($request));
        $res = $app->order->unify($request);
        return view('', [
            'res' => $res,
            'order' => $order,
            'is_wechat' => false,
            'config' => '[]'
        ]);
    }

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     * @author Jasmine2
     */
    public function notify()
    {
        $app = Factory::payment($this->config);
        $response = $app->handlePaidNotify(function ($message, $fail) {
            Log::error($message);
            if (isset($message['result_code']) && $message['result_code'] == 'SUCCESS') {
                Db::name('wechat_order')->where('orderno', $message['out_trade_no'])->update(['status' => 1,
                    'openid' => $message['openid']]);
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
            Db::name('wechat_order')->where('orderno', $order_no)
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
        $app = Factory::payment($this->config);
        $order_no = $this->request->get('orderno');
        $res = $app->order->queryByOutTradeNumber($order_no);
        $success = false;
        if (isset($res['trade_state']) && $res['trade_state'] == 'SUCCESS') {
            $order = Db::name('wechat_order')
                ->where('orderno', $order_no)->find();
            Db::name('wechat_order')->where('orderno', $order_no)->update(['status' => 1]);
            $success = true;
        } else {
            Db::name('wechat_order')->where('orderno', $order_no)->update(['cus_num' => 0]);
        }
        return view('', [
            'success' => $success,
            'order' => isset($order) ? $order : null
        ]);
    }


    public function shop_pre_index()
    {
        $this->config['oauth']['callback'] = url("wechat/pay.show/shop_jump", ['id' => urlencode($this->request->get('id'))], true, true);
        $app = Factory::officialAccount($this->config);
        $response = $app->oauth
            ->scopes(['snsapi_base'])
            ->redirect();
        return $response->send();
    }

    public function shop_jump($id)
    {
        $app = Factory::officialAccount($this->config);
        $openid = $app->oauth->user()->getId();
        $url = url('wechat/pay.show/shopPay') . '?id=' . $id;
        Log::error($url);
        if (stripos($url, '?') > 0) {
            $url .= '&openid=' . $openid;
        } else {
            $url .= '?openid=' . $openid;
        }
        return redirect($url);
    }

    public function shopPay()
    {
        $detect = new MobileDetect();
        $is_wchat = $detect->is('WeChat');
        if ($is_wchat) {
            $openid = $this->request->get('openid', false);
            if ($openid == false) {
                $data = $this->request->get();
                if (isset($data['orderno']) && !empty($data['orderno'])) {
                    $order_id = Db::name('saas_order')->where('orderno', $data['orderno'])->value('id');
                } else {
                    $row = json_decode($data['order'],true);
                    $order = [
                        'student_id' => session('customer')['id'],
                        'class_id' => 0,
                        'price' => $data['money'],
                        'status' => 4,
                        'created_at' => time(),
                        'pay_type' => 2,
                        'orderno' => generate_order_no()
                    ];
                    $order_id = Db::name('saas_order')->insertGetId($order);
                    $order_log = [];
                    foreach ($row as $key => $v) {
                        $order_log[$key]['order_id'] = $order_id;
                        $order_log[$key]['goods_id'] = $v['gid'];
                        $order_log[$key]['goods_type'] = 1;
                        $order_log[$key]['old_price'] = $v['price'];
                        $order_log[$key]['price'] = $v['price'];
                        $order_log[$key]['consume_num'] = 0;
                        $order_log[$key]['goods_num'] = $v['num'] * $v['number'];
                        $order_log[$key]['created_at'] = time();
                        $order_log[$key]['class_id'] = 0;
                    }
                    Db::name('saas_order_log')->insertAll($order_log);
                }
                return redirect(url('wechat/pay.show/shop_pre_index') . '?id=' . $order_id);
            } else {
                $id = $this->request->get('id');
                $order = Db::name('saas_order')
                    ->where('id', '=', $id)
                    ->where('status', '=', 4)
                    ->find();
                if ($order) {
                    $app = Factory::payment($this->config);
                    $res = $app->order->unify([
                        'body' => '在线选课',
                        'out_trade_no' => $order['orderno'],
                        'total_fee' => round($order['price'] * 100, 0),
                        'notify_url' => url('wechat/pay.show/shop_notify', [], true, true),
                        'trade_type' => 'JSAPI',
                        'openid' => $openid,
                    ]);
                    $order['title'] = '在线选课';
                    $order['body'] = '在线选课';
                    $jssdk = $app->jssdk;
                    Log::error($jssdk->bridgeConfig($res['prepay_id'], true));
                    return view('index', [
                        'config' => $jssdk->bridgeConfig($res['prepay_id'], true),
                        'order' => $order,
                        'is_wechat' => true
                    ]);
                } else {
                    die('订单不存在或已支付');
                }
            }
        } else {
            return $this->shopH5pay();
        }
    }

    protected function shopH5pay()
    {
        $data = $this->request->get();
        if (isset($data['orderno']) && !empty($data['orderno'])) {
            $order = Db::name('saas_order')->where('order', $data['orderno'])->find();
        } else {
            $res = json_decode($data['order'],true);

            /*$res = $this->validate($data, [
                'price' => 'require',
                'cid' => 'require',
                'goods_id' => 'require',
                'num' => 'require',
                'title' => 'require',
                'number' => 'require'
            ]);
            if ($res !== true) {
                return json(['success' => false, 'msg' => $res]);
            }*/
            $order = [
                'student_id' => session('customer')['id'],
                'class_id' => 0,
                'price' => $data['money'],
                'status' => 4,
                'created_at' => time(),
                'pay_type' => 2,
                'orderno' => generate_order_no()
            ];
            $order_id = Db::name('saas_order')->insertGetId($order);
            $order_log = [];
            foreach ($res as $key => $v) {
                $order_log[$key]['order_id'] = $order_id;
                $order_log[$key]['goods_id'] = $v['gid'];
                $order_log[$key]['goods_type'] = 1;
                $order_log[$key]['old_price'] = $v['price'];
                $order_log[$key]['price'] = $v['price'];
                $order_log[$key]['consume_num'] = 0;
                $order_log[$key]['goods_num'] = $v['num'] * $v['number'];
                $order_log[$key]['created_at'] = time();
                $order_log[$key]['class_id'] = 0;
            }
            Db::name('saas_order_log')->insertAll($order_log);
        }
        $app = Factory::payment($this->config);
        $request = [
            'body' => '在线选课',
            'out_trade_no' => $order['orderno'],
            'total_fee' => round($order['price'] * 100, 0),
            'notify_url' => url('wechat/pay.show/shop_notify', [], true, true),
            'trade_type' => 'MWEB',
            'spbill_create_ip' => $this->request->ip(),
            'scene_info' => json_encode([
                'h5_info' => [
                    'type' => 'h5_info',
                    'wap_url' => $this->request->domain(),
                    'wap_name' => sysconf('app_name')
                ]
            ])
        ];
        Log::error('微信支付:' . json_encode($request));
        $order['title'] = $data['title'];
        $order['body'] = $data['title'];
        $res = $app->order->unify($request);
        return view('index', [
            'res' => $res,
            'order' => $order,
            'is_wechat' => false,
            'config' => '[]'
        ]);
    }

    public function shop_notify()
    {
        $app = Factory::payment($this->config);
        $response = $app->handlePaidNotify(function ($message, $fail) {
            Log::error($message);
            if (isset($message['result_code']) && $message['result_code'] == 'SUCCESS') {
                Db::name('saas_order')->where('orderno', $message['out_trade_no'])->update(['status' => 5]);
                return true;
            }
        });
        return $response->send();
    }
}