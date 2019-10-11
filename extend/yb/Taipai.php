<?php

namespace yb;

/**
 * User: Youjingqiang
 * Date: 2019/3/14 17:39
 * Email: youjingqiang@gmail.com
 * Copyright (c) Youjingqiang <youjingqiang@gmail.com>
 */

use think\facade\Log;
use yb\lib\Util\YopSignUtils;
use yb\lib\YopClient;
use yb\lib\YopClient3;
use yb\lib\YopRequest;
use yb\lib\YopResponse;

/**
 * Class Taipai
 * @author Jasmine2
 * 台牌扫码
 */
class Taipai
{
    private $yop_public_key;
    private $parentMerchantNo;
    private $server_root = 'https://openapi.yeepay.com/yop-center';
    private $yop_private_key;
    private $merchantNo;
    private $hmacKey;

    public function __construct()
    {
        $this->parentMerchantNo = sysconf('parent_merchant_no');
        $this->server_root = 'https://openapi.yeepay.com/yop-center';
        $this->yop_public_key = sysconf('yop_public_key');
        $this->yop_private_key = sysconf('yop_private_key');
        $this->merchantNo = sysconf('merchant_no');
        $this->hmacKey = sysconf('hmac_key');
    }

    public function createOrder($orderNo, $amount, $redirectUrl = '', $notifyUrl = false)
    {
        if (!$notifyUrl) {
            $notifyUrl = url('notify/notify/ybpay', [], '', true);
        }
        $hmacData = [
            'parentMerchantNo' => $this->parentMerchantNo,
            'merchantNo' => $this->merchantNo,
            'orderId' => $orderNo,
            'orderAmount' => number_format($amount, 2, '.', ''),
            'notifyUrl' => $notifyUrl,
        ];
        $hmacstr = hash_hmac('sha256', $this->toString($hmacData), $this->hmacKey, true);
        $hmac = bin2hex($hmacstr);
        $data = [
            'parentMerchantNo' => $this->parentMerchantNo,
            'merchantNo' => $this->merchantNo,
            'orderId' => $orderNo,
            'orderAmount' => number_format($amount, 2, '.', ''),
            'redirectUrl' => $redirectUrl,
            'notifyUrl' => $notifyUrl,
            'goodsParamExt' => '{"goodsName":"教育培训","goodsDesc":"课程购买"}',
            'fundProcessType' => 'REAL_TIME',
            'hmac' => $hmac
        ];
        $request = new YopRequest("OPR:" . $this->parentMerchantNo, $this->yop_private_key, $this->server_root, $this->yop_public_key);
        foreach ($data as $key => $value) {
            $request->addParam($key, $value);
        }
        $response = YopClient3::post("/rest/v1.0/sys/trade/order", $request);
        return YopResponse::objectArray($response);
    }

    /**
     * @param $token
     * @param $payTool
     * @param $payType
     * @param $userNo
     * @param $openid
     * @param $appId
     * @param string $userType
     * @author Jasmine2
     * 聚合支付
     */
    public function unionApi($token, $payTool, $payType, $userNo, $openid, $appId, $userType = 'PHONE')
    {
        $request = new YopRequest("OPR:" . $this->parentMerchantNo, $this->yop_private_key, $this->server_root, $this->yop_public_key);
        $request->addParam("parentMerchantNo", $this->parentMerchantNo);
        $request->addParam("merchantNo", $this->merchantNo);
        $request->addParam('token', $token);
        $request->addParam('payTool', $payTool);
        $request->addParam('payType', $payType);
        $request->addParam('userNo', $userNo);
        $request->addParam('userType', $userType);
        $appId === '' || $request->addParam('appId', $appId);
        $request->addParam('openId', $openid);
        $request->addParam('userIp', request()->ip());
        $request->addParam('version', '1.0');
        $request->addParam('extParamMap', json_encode(['reportFee' => 'XIANXIA']));
        Log::error('========支付参数============' . json_encode($request));
        $response = YopClient3::post("/rest/v1.0/nccashierapi/api/pay", $request);
        return YopResponse::objectArray($response);
    }

    #将参数转换成k=v拼接的形式
    function toString($arraydata)
    {
        $Str = "";
        foreach ($arraydata as $k => $v) {
            $Str .= strlen($Str) == 0 ? "" : "&";
            $Str .= $k . "=" . $v;
        }
        return $Str;
    }

    public function refund($orderId, $refundRequestId, $uniqueOrderNo, $refundAmount, $description = '')
    {
        $url = '/rest/v1.0/sys/trade/refund';
        $request = new YopRequest("OPR:" . $this->parentMerchantNo, $this->yop_private_key, $this->server_root, $this->yop_public_key);
        $request->addParam("parentMerchantNo", $this->parentMerchantNo);
        $request->addParam("merchantNo", $this->merchantNo);
        $request->addParam("orderId", $orderId);
        $request->addParam("refundRequestId", $refundRequestId);
        $request->addParam("uniqueOrderNo", $uniqueOrderNo);
        $request->addParam("refundAmount", $refundAmount);
        $request->addParam("description", $description);
        $request->addParam("notifyUrl", url('/notify/notify/refundYibao', [], '', true));
        //$request->addParam("notifyUrl", 'http://zf.dev.zhishensoft.com:9000/notify/notify/refundYibao');
        $hmacData = [
            'parentMerchantNo' => $this->parentMerchantNo,
            'merchantNo' => $this->merchantNo,
            'orderId' => $orderId,
            'uniqueOrderNo' => $uniqueOrderNo,
            'refundRequestId' => $refundRequestId,
            'refundAmount' => $refundAmount
        ];
        $hmacstr = hash_hmac('sha256', $this->toString($hmacData), $this->hmacKey, true);
        $hmac = bin2hex($hmacstr);
        $request->addParam("hmac", $hmac);
        $response = YopClient3::post($url, $request);
        return YopResponse::objectArray($response);
    }


    public function verifyNotify($response)
    {
        return YopSignUtils::decrypt($response, $this->yop_private_key, $this->yop_public_key);
    }
}