<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/21 0021
 * Time: 16:37
 */

namespace app\order\controller;

use Alipay\AopClient;
use Alipay\Key\AlipayKeyPair;
use Alipay\Request\AlipaySystemOauthTokenRequest;
use app\common\model\Customer;
use service\HttpService;
use think\App;
use think\Controller;
use EasyWeChat\Factory;
use think\Db;
use think\facade\Log;
use yb\Taipai;

class Pay extends Controller
{
    public $wxapp;

    public $aliapp;

    public $yeepay;

    private $alipayConfig;

    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $wxconfig = [
            'app_id' => sysconf('wx_appid'),
            'secret' => sysconf('wx_secret'),
            'oauth' => [
                'scopes' => ['snsapi_base'],
                'callback' => url("order/pay/callbackWechat", [], true, true),
            ]
        ];

        $this->wxapp = Factory::officialAccount($wxconfig);

        $this->alipayConfig = [
            'pid' => sysconf('zfb_pid'),
            'app_id' => sysconf('zfb_appid'),
            'public_key' => sysconf('zfb_public_key'),
            'private_key' => sysconf('zfb_private_key')
        ];
        $this->wrapAlipay();
        $alipayKeyPair = AlipayKeyPair::create($this->alipayConfig['private_key'], $this->alipayConfig['public_key']);

        $this->aliapp = new AopClient($this->alipayConfig['app_id'], $alipayKeyPair);

        $this->yeepay = new Taipai();
    }


    private function wrapAlipay()
    {
        $this->alipayConfig['private_key'] = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->alipayConfig['private_key'], 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        $this->alipayConfig['public_key'] = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($this->alipayConfig['public_key'], 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
    }

    public function callbackWechat()
    {
        $openID = $this->wxapp->oauth->user()->getId();
        $openID = encode($openID);
        return redirect('/order/pay/index?' . http_build_query(['type' => 'WECHAT', 'openid' => $openID]));
    }

    public function index()
    {
        $type = $this->request->get('type');
        $openid = $this->request->get('openid');
        return view('pay', [
            'type' => $type,
            'openid' => $openid
        ]);
    }

    public function createOrder($type, $openid)
    {
        if ($type == 'WECHAT') {
            $payTool = 'WECHAT_OPENID';
            Log::error("====================weixinpeizhi====================");
            Log::error($this->wxapp->getConfig());
            $app_id =  'wx3ea13ba727680856';
        }
        if ($type == 'ALIPAY') {
            $payTool = 'ZFB_SHH';
            $app_id = '';
        }
        $post = $this->request->post();
        $openid = decode($openid);
        // 检验参数

        if (!preg_match('/^1[3-9]\d{9}$/', $post['mobile'])) {
            $this->error('手机号码不正确');
        }

        // 先创建用户
        $customer = Customer::where('parent_tel', '=', $post['mobile'])->find();
        if (!$customer) {
            $customer = Customer::create([
                'parent_tel' => $post['mobile'],
                'parent_name' => $post['name'],
                'name' => $post['name']
            ]);
        }

        // 创建订单
        $orderNo = generate_order_no();

        $order_data = [];
        $order_data['uid'] = $customer->id;
        $order_data['mobile'] = $customer->parent_tel;
        $order_data['money'] = $post['total_money'];
        $order_data['name'] = $customer->parent_name;
        $order_data['type'] = 2;
        $order_data['status'] = 2;
        $order_data['order_no'] = $orderNo;
        $order_data['created_at'] = time();
        Db::name('saas_pay_order')->insert($order_data);

        $token = $this->yeepay->createOrder($orderNo, $order_data['money']);
        if (isset($token['result']) && $token['result']['code'] == 'OPR00000') {
            $token = $token['result']['token'];
            $res = $this->yeepay->unionApi($token, $payTool, $type, $customer->parent_tel, $openid, $app_id);
            if ($res['result']['code'] == 'CAS00000') {
                $json = json_decode($res['result']['resultData'], 1);
                $json = array_merge($json, ['appId' => $app_id]);
                $this->success('success', '', [
                    'json' => $json,
                    'oid' => $orderNo
                ]);
            } else {
                $this->error($res['result']['message']);
            }
        }
        $this->error('创建订单失败, 请重新扫码');
    }

    public function yibao()
    {
        $saas_id = $this->request->get('saas_id');
        // 判断支付宝还是微信
        if (is_wechat()) {
            if (sysconf('wx_appid') && sysconf('wx_secret')) {
                return $this->wxapp->oauth->redirect()->send();
            } else {
                return redirect('http://jy.chengxf.com/api/third/get_wechat_id?' . http_build_query(['saas_id' => $saas_id]));
            }
        }
        if (is_alipay()) {
            return redirect('http://jy.chengxf.com/api/third/getAlipayRedirect?' . http_build_query(['saas_id' => $saas_id]));
//            return $this->getAlipayRedirect();
        }
        return redirect('errorPage');
    }

    private function getAlipayRedirect()
    {
        $callBack = url('callbackAlipay', [], null, true);
        $url = "https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id={$this->alipayConfig['app_id']}&scope=auth_base&redirect_uri={$callBack}";
        return redirect($url);
    }

    public function callbackAlipay()
    {
        $auth_code = $this->request->get('auth_code');
        $request = new AlipaySystemOauthTokenRequest();
        $request->setCode($auth_code);
        $request->setGrantType('authorization_code');

        try {
            $data = $this->aliapp->execute($request)->getData();
            $openID = encode($data['user_id']);
            return redirect('/order/pay/index?' . http_build_query(['type' => 'ALIPAY', 'openid' => $openID]));
        } catch (\Exception $ex) {
            return '请重新扫码';
        }
    }

    public function errorPage($msg = '暂不支持该扫码方式')
    {
        return $msg;
    }

    public function paySuccess()
    {
        $oid = $this->request->get('oid');
        $row = Db::name('saas_pay_order')
            ->where('order_no', '=', $oid)
            ->field('money, status, type, created_at')
            ->find();
        return view('', ['row' => $row, 'oid' => $oid]);
    }

    public function payFail()
    {
        $get = $this->request->get();
        $this->assign('msg', $get['msg']);
        return view();
    }

    public function payType()
    {
        return view();
    }

// =====================================================================================================================
// 库分期相关
// =====================================================================================================================
    public function kufenqi()
    {
        $oid = $this->request->request('oid');
        $uid = $this->request->request('uid');
        $this->assign('oid', $oid);
        $this->assign('uid', $uid);
        return view();
    }

    public function userMsg()
    {
        return view();
    }

    public function tieCard()
    {
        $get = $this->request->get();
        $oid = $get['oid'];
        $uid = $get['uid'];
        $price = $get['price'];
        $rate = $get['rate'];
        $stageNum = $get['stageNum'];

        $html_select = html_select(get_kfq_bankcode(), 'bankCode', '', 'userInput');
        $customer = Db::name('saas_customer')->where('id', '=', $uid)->field('parent_name, parent_tel')->find();
        return view('', [
            'oid' => $oid,
            'uid' => $uid,
            'price' => $price,
            'rate' => $rate,
            'stageNum' => $stageNum,
            'name' => $customer['parent_name'],
            'mobile' => $customer['parent_tel'],
            'html' => $html_select
        ]);
    }

    public function verif()
    {
        $get = $this->request->get();
        return view('', [
            'oid' => $get['oid'],
            'uid' => $get['uid'],
            'price' => $get['price'],
            'payId' => $get['payid'],
            'applyId' => $get['applyid'],
            'bankno' => $get['bankno']
        ]);
    }
}