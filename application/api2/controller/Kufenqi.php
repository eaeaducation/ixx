<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/19 0019
 * Time: 15:48
 */

namespace app\api2\controller;


use service\HttpService;
use think\App;
use think\Controller;
use think\Db;
use think\facade\Log;

class Kufenqi extends Controller
{
    private $midPlatform = '';//商户平台号
    private $aesKey = "";//aes_key
    private $md5Key = "";//md5_key
    private $api_url = "";//api地址
    private $iv = '0102030405060708';
    private $discount = '';//1 商户贴息  0 用户贴息

    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->midPlatform = sysconf('kfq_midplatform');
        $this->aesKey = sysconf('kfq_aes_key');
        $this->md5Key = sysconf('kfq_md5_key');
        $this->api_url = sysconf('kfq_api');
        $this->discount = sysconf('kfq_discount');
    }

    /**
     * 银行卡查询接口
     * @return mixed
     */
    public function cardCx()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $uid = Db::name('saas_customer')
                ->where('parent_tel', '=', $post['mobile'])
                ->where('status', '<>', 3)
                ->value('id');
            if (!$uid) {
                $customer = [];
                $customer['parent_tel'] = $post['mobile'];
                $customer['parent_name'] = $post['name'];
                $uid = Db::name('saas_customer')->insertGetId($customer);
            }
            $orderno = generate_order_no();

            $data = [];
            $data['midTransaction'] = $this->midPlatform;
            $data['time'] = time() * 1000;
            $data['opType'] = 4004;
            $data['uid'] = $uid;

            $data['data'] = new \stdClass();

            $data['risk']['ip'] = $this->request->ip();
            $data['risk']['deviceType'] = 10;

            $res = $this->execute($data);

            $res['data'] = $this->decrypt($res['data']);
            $res['data'] = json_decode($res['data'], true);

            //$row = $this->cardCx($uid);
            if (isset($res['data']['data'])) {
                foreach ($res['data']['data'] as $k => $v) {
                    $res['data']['data'][$k]['codename'] = get_kfq_bankcode()[$res['data']['data'][$k]['code']];
                    $res['data']['data'][$k]['bankmask'] = bank_mask($res['data']['data'][$k]['bankcard']);
                }
            }

            $res['data']['uid'] = $uid;
            $res['data']['oid'] = $orderno;
            return $res['data'];
        }
    }

    /**
     * 费率查询
     * @return mixed
     */
    public function rateCx()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $data = [];
            $data['midTransaction'] = $this->midPlatform;
            $data['time'] = time() * 1000;
            $data['opType'] = 4007;

            $data['data']['amount'] = sprintf('%.2f', $post['price']);
            $data['data']['stageNum'] = $post['stageNum'];

            $data['risk']['ip'] = $this->request->ip();
            $data['risk']['deviceType'] = 10;

            $res = $this->execute($data);
            $res['data'] = $this->decrypt($res['data']);
            $res['data'] = json_decode($res['data'], true);

            if (isset($res['data']['data']['rates'])) {
                if ($this->discount == 1) {
                    $userfee = $post['price'] * $res['data']['data']['rates'][0]['rate'] / 100 * $post['stageNum'];
                    $supply = sprintf('%.2f', ($post['price'] + $userfee) / $post['stageNum']);
                } elseif ($this->discount == 2) {
                    $supply = sprintf('%.2f', $post['price'] / $post['stageNum']);
                }
                $res['data']['supply'] = $supply;
                return $res['data'];
            } else {
                return ['result' => 404];
            }
        }
    }

    /**
     * 消费
     * @return mixed
     */
    public function pay()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $data = [];
            $data['midTransaction'] = $this->midPlatform;
            $data['time'] = time() * 1000;
            $data['opType'] = 2001;
            $data['uid'] = $post['uid'];

            $data['data']['bankNo'] = $post['bankno'];
            $data['data']['orderId'] = $post['oid'];
            $data['data']['orderAmount'] = sprintf('%.2f', $post['price']);
            $data['data']['payAmount'] = sprintf('%.2f', $post['price']);
            if ($this->discount == 0) {
                $data['data']['chargeAmount'] = sprintf('%.2f', $post['price'] * $post['rate'] / 100 * $post['stageNum']);
            }
            $data['data']['stageNum'] = $post['stageNum'];
            $data['data']['productInfo'] = '用户支付';

            $data['risk']['ip'] = $this->request->ip();
            $data['risk']['deviceType'] = 10;

            $res = $this->execute($data);
            $res['data'] = $this->decrypt($res['data']);
            $res['data'] = json_decode($res['data'], true);
            return $res['data'];
        }
    }

    /**
     * 绑卡消费一体
     * @return mixed
     */
    public function integration()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();

            $data = [];
            $data['midTransaction'] = $this->midPlatform;
            $data['time'] = time() * 1000;
            $data['opType'] = 6001;
            $data['uid'] = $post['uid'];

            $data['data']['userName'] = $post['name'];
            $data['data']['idCardNo'] = $post['idCardNo'];
            $data['data']['bankNo'] = $post['bankNo'];
            $data['data']['bankTel'] = $post['bankTel'];
            $data['data']['bankCode'] = $post['bankCode'];
            $data['data']['bankExpireDate'] = $post['expireDate'];
            $data['data']['bankCvn2'] = $post['bankCvn2'];
            $data['data']['orderId'] = $post['oid'];
            $data['data']['userName'] = $post['name'];
            $data['data']['payAmount'] = sprintf('%.2f', $post['price']);
            $data['data']['orderAmount'] = sprintf('%.2f', $post['price']);
            $data['data']['productInfo'] = '用户支付';
            $data['data']['stageNum'] = $post['stageNum'];

            if ($this->discount == 0) {
                $data['data']['chargeAmount'] = sprintf('%.2f', $post['price'] * $post['rate'] / 100 * $post['stageNum']);
            }

            $data['risk']['ip'] = $this->request->ip();
            $data['risk']['deviceType'] = 10;
            $res = $this->execute($data);
            $res['data'] = $this->decrypt($res['data']);
            $res['data'] = json_decode($res['data'], true);
            return $res['data'];
        }
    }

    /**
     * 消费绑卡验证
     * @return mixed
     */
    public function verif()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $udata = Db::name('saas_customer')
                ->where('id', '=', $post['uid'])
                ->field('parent_tel, parent_name')->find();
            $order_data = [];
            $order_data['uid'] = $post['uid'];
            $order_data['mobile'] = $udata['parent_tel'];
            $order_data['money'] = $post['price'];
            $order_data['name'] = $udata['parent_name'];
            $order_data['type'] = 1;
            $order_data['status'] = 2;
            $order_data['order_no'] = $post['oid'];
            $order_data['created_at'] = time();
            $oid = $this->savaOrder($order_data);

            $data = [];
            $data['midTransaction'] = $this->midPlatform;
            $data['time'] = time() * 1000;
            $data['opType'] = 6002;
            $data['uid'] = $post['uid'];

            $data['data']['orderId'] = $post['oid'];
            $data['data']['payId'] = $post['payid'];
            $data['data']['applyId'] = $post['applyid'];
            $data['data']['orderAmount'] = sprintf('%.2f', $post['price']);
            $data['data']['payAmount'] = sprintf('%.2f', $post['price']);
            $data['data']['smsVerifyCode'] = $post['code'];
            $data['data']['callbackURL'] = $this->request->domain() . '/notify/notify/kfq';
            //$data['data']['callbackURL'] = 'http://guess.liuqiang2267qqcom.yxnat.softdev.top/notify/notify/kfq';

            $data['risk']['ip'] = $this->request->ip();
            $data['risk']['deviceType'] = 10;

            $res = $this->execute($data);
            $res['data'] = $this->decrypt($res['data']);
            $res['data'] = json_decode($res['data'], true);

            if ($res['data']['result'] == 200) {
                Db::name('saas_pay_order')->where('order_no', '=', $post['oid'])->update(['bankcard' => $post['bankno']]);
            }

            return $res['data'];
        }
    }

    /**
     * 退款
     * @return string
     */
    public function refundApply($post)
    {
        $data = [];
        $data['midTransaction'] = $this->midPlatform;
        $data['time'] = time() * 1000;
        $data['opType'] = 3001;
        $data['uid'] = $post['uid'];

        $data['data']['orderId'] = $post['order_no'];
        $data['data']['applyId'] = $post['order_no'];
        $data['data']['refundAmount'] = $post['money'];
        $data['data']['reson'] = $post['reason'];
        $data['data']['callbackURL'] = $this->request->domain() . '/notify/notify/refundKfq';
        //$data['data']['callbackURL'] = 'http://guess.liuqiang2263qqcom.yxnat.softdev.top/notify/notify/refundKfq';

        $data['risk']['ip'] = $this->request->ip();
        $data['risk']['deviceType'] = 10;

        $res = $this->execute($data);
        $res['data'] = $this->decrypt($res['data']);
        return json_decode($res['data'], 1);
    }

    public function untied()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $data = [];
            $data['midTransaction'] = $this->midPlatform;
            $data['time'] = time() * 1000;
            $data['opType'] = 1003;
            $data['uid'] = $post['uid'];

            $data['data']['bankCardId'] = $post['bankId'];

            $data['risk']['ip'] = $this->request->ip();
            $data['risk']['deviceType'] = 10;

            $res = $this->execute($data);
            $res['data'] = $this->decrypt($res['data']);
            return $res['data'];
        }
    }

    /**
     * 保存订单
     * @param $data
     * @return int|string
     */
    private function savaOrder($data)
    {
        $oid = Db::name('saas_pay_order')->insertGetId($data);
        return $oid;
    }

    /**
     * 签名
     * @param $data
     * @return mixed
     */
    private function execute($data)
    {
        $json_data = json_encode($data);
        $post_data = [];
        $post_data['sign'] = md5($json_data . $this->md5Key);
        $post_data['data'] = $this->encrypt($json_data);
        $post_data['midPlatform'] = $this->midPlatform;
        $post_data['version'] = 'V1.0';

        $res = $this->post($this->api_url, $post_data);
        $res = json_decode($res, true);
        return $res;
    }

    /**
     * AES加密
     * @param $input
     * @return string
     */
    private function encrypt($input)
    {
        $data = openssl_encrypt($input, 'AES-128-CBC', $this->aesKey, OPENSSL_RAW_DATA, $this->iv);
        $data = bin2hex($data);
        return $data;
    }

    /**
     * AES解密
     * @param $sStr
     * @return string
     */
    public function decrypt($sStr)
    {
        $decrypted = openssl_decrypt(hex2bin($sStr), 'AES-128-CBC', $this->aesKey, OPENSSL_RAW_DATA, $this->iv);
        return $decrypted;
    }

    /**
     * post请求
     * @param $url
     * @param array $post
     * @return mixed
     */
    private function post($url, $post = array())
    {
        $post_string = json_encode($post);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}