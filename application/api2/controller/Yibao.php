<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/27 0027
 * Time: 13:37
 */

namespace app\api2\controller;

use app\admin\controller\Log;
use EasyWeChat\Factory;
use think\App;
use think\Controller;
use think\Db;
use think\Request;
use yb\lib\Util\YopSignUtils;
use yb\lib\YopClient3;
use yb\lib\YopRequest;

class Yibao extends Controller
{
    private $yop_public_key;
    private $parentMerchantNo;
    private $server_root = 'https://openapi.yeepay.com/yop-center';
    private $yop_private_key;
    private $merchantNo;
    private $hmacKey;
    private $config = [];


    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->parentMerchantNo = sysconf('parent_merchant_no');
        $this->server_root = 'https://openapi.yeepay.com/yop-center';
        $this->yop_public_key = sysconf('yop_public_key');
        $this->yop_private_key = sysconf('yop_private_key');
        $this->merchantNo = sysconf('merchant_no');
        $this->hmacKey = sysconf('hmac_key');
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

    public function cash()
    {
        $post = $this->request->post();
        $order_amount = $post['total_money'];
        $order_id = generate_order_no();

        $request = new YopRequest("OPR:" . $this->parentMerchantNo, $this->yop_private_key, $this->server_root, $this->yop_public_key);
        $request->addParam("parentMerchantNo", $this->parentMerchantNo);
        $request->addParam("merchantNo", $this->merchantNo);
        $request->addParam("orderId", $order_id);
        $request->addParam("orderAmount", $order_amount);
        $request->addParam("timeoutExpress", '');
        $request->addParam("requestDate", date("Y-m-d H:i:s", time()));
        $request->addParam("redirectUrl", '');
        $request->addParam("notifyUrl", $this->request->domain() . '/notify/notify/ybpay');
        $request->addParam("goodsParamExt", '{"goodsName":"教育培训","goodsDesc":"课程购买"}');
        $request->addParam("paymentParamExt", '{}');
        $request->addParam("industryParamExt", '');
        $request->addParam("memo", '');
        $request->addParam("riskParamExt", '');
        $request->addParam("csUrl", '');
        $request->addParam("fundProcessType", 'REAL_TIME');
        $request->addParam("divideDetail", '');
        $request->addParam("divideNotifyUrl", '');

        $data = array();
        $data['parentMerchantNo'] = $this->parentMerchantNo;
        $data['merchantNo'] = $this->merchantNo;
        $data['orderId'] = $order_id;
        $data['orderAmount'] = $order_amount;
        $data['notifyUrl'] = $this->request->domain() . '/notify/notify/ybpay';

        $hmacstr = hash_hmac('sha256', $this->toString($data), $this->hmacKey, true);
        $hmac = bin2hex($hmacstr);

        $request->addParam("hmac", $hmac);
        $response = YopClient3::post("/rest/v1.0/sys/trade/order", $request);

        //取得返回结果
        $data = $this->object_array($response);

        if ($data['result']['code'] != 'OPR00000') {
            $this->error('支付失败，请联系客服');
        }
        $uid = Db::name('saas_customer')->where('parent_tel', '=', $post['mobile'])->where('status', '<>', 3)->value('id');
        if ($uid == '') {
            $customer = [];
            $customer['parent_name'] = $post['name'];
            $customer['parent_tel'] = $post['mobile'];
            $uid = Db::name('saas_customer')->insertGetId($customer);
        }
        $order_data = [];
        $order_data['uid'] = $uid;
        $order_data['mobile'] = $post['mobile'];
        $order_data['money'] = $order_amount;
        $order_data['name'] = $post['name'];
        $order_data['type'] = 2;
        $order_data['status'] = 0;
        $order_data['order_no'] = $order_id;
        $order_data['created_at'] = time();
        Db::name('saas_pay_order')->insert($order_data);

        $token = $data['result']['token'];
        $res = $this->yibao_juhe($token, $post);
        return $res;
    }

    /**
     * 获取二代密钥接口
     * todo 更新到数据库中保存
     * @return mixed
     */
    public function hmackeyquery()
    {
        $request = new YopRequest("OPR:" . $this->parentMerchantNo, $this->yop_private_key, $this->server_root, $this->yop_public_key);
        $request->addParam("parentMerchantNo", $this->parentMerchantNo);
        $request->addParam("merchantNo", $this->merchantNo);

        $data = array();
        $data['parentMerchantNo'] = $this->parentMerchantNo;
        $data['merchantNo'] = $this->merchantNo;

        $response = YopClient3::post("/rest/v1.0/sys/merchant/hmackeyquery", $request);

        //取得返回结果
        $data = $this->object_array($response);
        return $data['result']['merHmacKey'];
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

    function object_array($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = $this->object_array($value);
            }
        }
        return $array;
    }

    function getUrl($response, $private_key)
    {
        $content = $this->toString($response);
        $sign = YopSignUtils::signRsa($content, $private_key);
        $url = $content . "&sign=" . $sign;
        return $url;
    }

    public function yibao_juhe($token, $post)
    {
        \think\facade\Log::error($post);
        $request = new YopRequest("OPR:" . $this->parentMerchantNo, $this->yop_private_key, $this->server_root, $this->yop_public_key);
        $request->addParam("parentMerchantNo", $this->parentMerchantNo);
        $request->addParam("merchantNo", $this->merchantNo);
        $request->addParam('token', $token);
        $request->addParam('payTool', json_decode($post['browser'],1)['payTool']);
        $request->addParam('payType', json_decode($post['browser'],1)['payType']);
        $request->addParam('userNo', $post['mobile']);
        $request->addParam('userType', 'ID_CARD');
        $request->addParam('appId', json_decode($post['browser'],1)['appId']);
        $request->addParam('openId', json_decode($post['browser'],1)['openId']);
        $request->addParam('payEmpowerNo', '');
        $request->addParam('merchantTerminalId', '');
        $request->addParam('merchantStoreNo', $token);
        $request->addParam("notifyUrl", $this->request->domain() . '/notify/notify/ybpay');
        $request->addParam('userIp', \think\facade\Request::instance()->ip());
        $request->addParam('version', '1.0');
        $response = YopClient3::post("/rest/v1.0/nccashierapi/api/pay", $request);
        return $response;
    }
}