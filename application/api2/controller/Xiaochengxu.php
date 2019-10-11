<?php
/**
 * Created by PhpStorm.
 * User: King
 * Date: 2018/8/13
 * Time: 11:33
 * Email: jackying009@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\api2\controller;

use service\HttpService;
use think\Controller;
use think\facade\Log;
use think\facade\Request;
use think\Db;

class Xiaochengxu extends Controller
{

    private $appid ;
    private $secret;
    private $mch_id;
    private $key;
    private $url = "https://api.weixin.qq.com/";

    public function __construct()
    {
        $this->appid =  sysconf('wxxcx_app_id');
        $this->secret = sysconf('wxxcx_secret');
        $this->mch_id = sysconf('wechat_mch_id');
        $this->key = sysconf('wechat_mch_key');
    }

    public function getSessionkey()
    {
        $code = Request::post('code','');
        $request_url = $this->url.'sns/jscode2session?appid='.$this->appid.'&secret='.$this->secret.'&js_code='.$code.'&grant_type=authorization_code';
        $res = HttpService::get($request_url,[]);
        Log::error($res);
        $row = json_decode($res, 1);
        if (session('openid') == '') {
            session('openid', $row['openid']);
        }
        return $res;
        /*$res = $this->getuser_info($res);
        if(!empty($res)){
            return json_encode($res);
        }*/
    }


    //获取用户信息并返回
        public function getuser_info($res)
    {
        $openid = json_decode($res,1);
        $res = json_decode($res,1);

        $introduce_id = Db::name('saas_customer')->field('id, parent_tel')->where('xcxopenid', '=', $openid['openid'])->find();
        $num = Db::name('saas_customer_introducer')
            ->where('introducer_id', '=', $introduce_id['id'])
            ->select();
        /*$payEnd = Db::name('wechat_order')
            ->where('cid', '=', $introduce_id['id'])
            ->where('status', '=', 1)
            ->where('aid', '=', '1111')
            ->field('id')
            ->find();*/
        if (count($num) >= 5) {
            $res['have_ticket'] = 1; // 有资格进入秒杀
        } else {
            $res['have_ticket'] = 0; // 无资格进入秒杀
        }
        $res['parent_tel'] = $introduce_id['parent_tel'];
        return $res;
    }

    public function xcxShopPay()
    {
        $post = Request::post();
        //统一支付签名
        $param = [
            'appid' => $this->appid,//appid
            'body' => !empty($post['title'])?$post['title']:'在线选课',
            'openid' => session('openid'),
            'mch_id' => $this->mch_id,
            'nonce_str' => $this->getRandom(32),
            'notify_url' => 'http://saas.eaeducation.net/notify/notify/xcxShopNotify',
            //'notify_url' => 'http://guess.liuqiang2275qqcom.yxnat.softdev.top/notify/notify/xcxShopNotify',
            'spbill_create_ip' => Request::ip(),
            'out_trade_no' => isset($post['orderno']) ? $post['orderno'] : generate_order_no(),
            'total_fee' => intval($post['price'] * 100),
            'trade_type' => "JSAPI",
        ];
        ksort($param);
        $params = post_str($param, false);
        $sign = strtoupper(md5($params . "&key=" . $this->key));
        //封装统一支付xml参数
        $param['sign'] = $sign;
        $form_data = $this->arrayToxml($param);
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $res = Httpservice::raw($url, $form_data);
        $res = $this->xmlToarray($res);
        if ($res['result_code'] == "SUCCESS") {
            if (!isset($post['orderno']) && empty($post['orderno'])) {
                $this->shopOrder($post, $param['out_trade_no']);
            }
            //发起支付
            $data = [
                'appId' => $this->appid,
                'nonceStr' => $this->getRandom(32),
                'package' => 'prepay_id=' . $res['prepay_id'],
                'signType' => 'MD5',
                'timeStamp' => '"' . time() . '"',
            ];
            $param = post_str($data, false);
            $sign = strtoupper(md5($param . "&key=" . $this->key));
            unset($data['appId']);
            $data['paySign'] = $sign;
            return json_encode(['success' => true, 'data' => $data]);
        } else {
            return json_encode(['success' => false, 'data' => $res]);
        }

    }

    /**
     * 生成订单
     * @param $post
     * @param $orderno
     * @return bool
     */
    public function shopOrder($post, $orderno)
    {
        $order = [
            'student_id' => session('customer')['id'],
            'class_id' => 0,
            'price' => $post['price'],
            'status' => 4,
            'created_at' => time(),
            'pay_type' => 2,
            'orderno' => $orderno
        ];
        $order_id = Db::name('saas_order')->insertGetId($order);
        $order_log = [];
        foreach ($post['data'] as $key => $v) {
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
        return true;
    }

    public function wechatPay()
    {
        $total_fee = Request::post('total_fee', '');
        $openid = Request::post('openid', '');
        $mobile = Request::post('mobile', '');
        $body = Request::post('body', '');
        $actid = Request::post('actid','');
        //统一支付签名
        $param = [
            'appid' => $this->appid,//appid
            'body' => !empty($body)?$body:'EA国际教育',
            'openid' => $openid,
            'mch_id' => $this->mch_id,
            'nonce_str' => $this->getRandom(32),
            'notify_url' => 'https://saas.eaeducation.net/api/v1/shows/xcx_order_after',
            'spbill_create_ip' => Request::ip(),
            'out_trade_no' => generate_order_no(),
            'total_fee' => intval($total_fee * 100),
            'trade_type' => "JSAPI",
        ];
        ksort($param);
        $params = post_str($param, false);
        $sign = strtoupper(md5($params . "&key=" . $this->key));
        //封装统一支付xml参数
        $param['sign'] = $sign;
        $form_data = $this->arrayToxml($param);
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $res = Httpservice::raw($url, $form_data);
        $res = $this->xmlToarray($res);
        if ($res['result_code'] == "SUCCESS") {
            $this->xcx_order($param['out_trade_no'], $param['body'], $param['total_fee'], $param['openid'], $mobile,$actid);
            //发起支付
            $data = [
                'appId' => $this->appid,
                'nonceStr' => $this->getRandom(32),
                'package' => 'prepay_id=' . $res['prepay_id'],
                'signType' => 'MD5',
                'timeStamp' => '"' . time() . '"',
            ];
            $param = post_str($data, false);
            $sign = strtoupper(md5($param . "&key=" . $this->key));
            unset($data['appId']);
            $data['paySign'] = $sign;
            return json_encode(['success' => true, 'data' => $data]);
        } else {
            return json_encode(['success' => false, 'data' => $res]);
        }

    }

    //小程序订单_下单时
    private function xcx_order($order, $title, $price, $openid,$mobile,$actid)
    {
        if (empty($actid) || round($price) == '58') {
            $actid = 5;
        }
        $customer_id = Db::name('saas_customer')->field('id')->where('parent_tel', '=', $mobile)->where('status','<>',3)->find();
        $param['orderno'] = $order;
        $param['title'] = $title;
        $param['trade_type'] = 'JSAPI';
        $param['type'] = 1;
        $param['price'] = ($price/100);
        $param['body'] = $title;
        $param['status'] = 0;
        $param['isxcx'] = 1;
        $param['mobile'] = $mobile;
        $param['created_at'] = time();
        $param['openid'] = $openid;
        $param['cid'] = !empty($customer_id['id'])?$customer_id['id']:'';
        $param['aid'] = $actid;
        Db::name("wechat_order")->insert($param);
        return true;
    }

    private function getRandom($param)
    {
        $str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $key = "";
        for ($i = 0; $i < $param; $i++) {
            $key .= $str{mt_rand(0, 32)};
        }
        return $key;
    }

    private function arrayToxml($data)
    {
        if (!is_array($data) || count($data) <= 0) {
            return false;
        }
        $xml = "<xml>";
        foreach ($data as $key => $val) {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    private function xmlToarray($xml, $isfile = false)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        if ($isfile) {
            if (!file_exists($xml)) return false;
            $xmlstr = file_get_contents($xml);
        } else {
            $xmlstr = $xml;
        }
        $result = json_decode(json_encode(simplexml_load_string($xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }
}