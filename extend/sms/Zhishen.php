<?php

namespace sms;

/**
 * Created by PhpStorm.
 * User: jasmine
 * Date: 2018/5/19
 * Time: 15:09
 */

use think\Db;
use think\facade\Cache;
use GuzzleHttp\Client;
use think\facade\Log;

class Zhishen implements SmsInterface
{
    public $gateway;
    public $account;
    public $password;
    public $client;
    public $_access_token;
    public $sign;

    private $type;

    /**
     * Zhishen constructor.
     * @param int $type
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function __construct($type = 1)
    {
        if ($type == 1) {
            $this->gateway = sysconf('sms_code_gateway');
            $this->account = sysconf('sms_code_account');
            $this->password = sysconf('sms_code_password');
            $this->sign = sysconf('sms_code_sign');
        } elseif ($type == 3) {
            $this->gateway = sysconf('sms_other_gateway');
            $this->account = sysconf('sms_other_account');
            $this->password = sysconf('sms_other_password');
            $this->sign = sysconf('sms_other_sign');
        }
        $this->type = $type;
        $this->client = new Client([
            'base_uri' => $this->gateway,
        ]);
        if (Cache::has('sms_access_token')) {
            $this->_access_token = Cache::get('sms_access_token');
        } else {
            $this->accessToken();
        }
    }

    private function accessToken()
    {
        $data = [
            'uid' => $this->account,
            'key' => $this->password
        ];
        $sign = $this->sms_encrypt($data);

        $response = $this->client->post('/api/v1/access_token', [
            'form_params' => [
                'data' => json_encode($sign)
            ]
        ]);

        if ($response->getStatusCode() == '200') {
            $res = json_decode($response->getBody()->getContents(), 1);
            if ($res['err_code'] == 200) {
                $this->_access_token = $res['data']['access_token'];
                Cache::set('sms_access_token', $this->_access_token, $res['data']['expires_in']);
            }
        }
    }

    /**
     * @param $mobiles string|array
     * @param $content
     * @param string $sign
     * @author Jasmine2
     * @return bool
     */
    public function send($mobiles, $content, $sign = '')
    {
        $sign == '' || $this->sign = $sign;
        $userid = session('user.id');
        session('user.id') != null || $userid = 0;
        if (is_array($mobiles)) {
            $mobiles = join(',', $mobiles);
        }
        $data = [
            'mobile' => $mobiles,
            'content' => $content,
            'sign' => $this->sign,
            'type' => $this->type
        ];
        Log::debug($data);
        $url = 'api/v1/send?access_token=' . $this->_access_token;
        $res = $this->client->post($url, [
            'form_params' => [
                'data' => json_encode($data)
            ]
        ]);
        $res = json_decode($res->getBody()->getContents(), 1);
        Log::debug($res);
        $count = explode(',',$mobiles);
        if($count>1){
            $mobile = explode(',',$mobiles);
            for($i=0;$i<count($mobile);$i++){
                Db::name('system_sms')->insert([
                    'ip' => request()->ip(),
                    'mobiles' => $mobile[$i],
                    'content' => "【{$this->sign}】{$content}",
                    'sign' => $this->sign,
                    'userid' => $userid,
                    'type' => $this->type,
                    'res' => $res['err_code'],
                    'created_at' => time()
                ]);
            }
        }else{
            Db::name('system_sms')->insert([
                'ip' => request()->ip(),
                'mobiles' => $mobiles,
                'content' => "【{$this->sign}】{$content}",
                'sign' => $this->sign,
                'userid' => $userid,
                'type' => $this->type,
                'res' => $res['err_code'],
                'created_at' => time()
            ]);
        }
        if ($res['err_code'] == 3) {
            // 短信欠费
            alert('短信欠费');
        }
        if ($res['err_code'] == 1) {
            return true;
        }
        return false;
    }

    private function sms_encrypt($in)
    {
        $json = \GuzzleHttp\json_encode($in);
        $un_encrypt = base64_encode($json);
        $key = 'saDmaTBxTiuFRDdd';
        $iv = 'CxGBndSgc7a7dA7T';
        if (!$key || !$iv) {
            return "未配置接口秘钥";
        }
        $encrypted = openssl_encrypt($un_encrypt, 'aes-128-cbc', $key, false, $iv);
        return base64_encode($encrypted);
    }
}