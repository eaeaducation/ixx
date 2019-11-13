<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace controller;

use app\admin\controller\Log;
use app\common\model\Customer;
use service\HttpService;
use service\ToolsService;
use think\exception\HttpResponseException;
use think\facade\Response;
use think\facade\Session;
use think\facade\Config;
use think\facade\View;

/**
 * 基础接口类
 * Class BasicApi
 * @package controller
 */
class BasicXcx
{

    /**
     * 当前请求对象
     * @var \think\Request
     */
    protected $request;

    /**
     * @var
     * @author Jasmine2
     * 站点名称
     */
    public $site_name;

    /**
     * appid
     */
     public $app_id;

    /**
     * app_secret
     */
    public $app_secret;

    /**
     * app_mch
     */
    public $app_mch_id;

    /**
     * app_key
     */
    public $app_mch_key;

    /**
     * 微信api接口地址
     * @var
     */
    protected $wxapi_url;

    private $aeskey = 'AaOb0iLGzk8O2lRq';
    private $aesiv = '1t31keXoH1EIvYCX';

    /**
     * 构造方法
     * BasicApi constructor.
     */
    public function __construct()
    {
        // Cors跨域Options请求处理
        Session::init(config('session.'));
//        ToolsService::corsOptionsHandler();
        // Cors跨域会话切换及初始化
        $this->request = app('request');
        $this->site_name = Config::get('site_name');
        $this->app_id = sysconf('wxxcx_app_id');
        $this->app_secret =  sysconf('wxxcx_secret');
        $this->app_mch_id = sysconf('wechat_mch_id');
        $this->app_mch_key = sysconf('wechat_mch_key');
        $this->wxapi_url = "https://api.weixin.qq.com/";
    }

    /**
     * 返回成功的操作
     * @param string $msg 消息内容
     * @param array $data 返回数据
     * @param int $code 错误码
     */
    protected function success($msg, $data = [], $code = 200)
    {
        $result = ['success' => true, 'code' => $code, 'msg' => $msg, 'data' => $data];
        throw new HttpResponseException(Response::create($result, 'json', 200, ToolsService::corsRequestHander()));
    }

    /**
     * 返回失败的请求
     * @param string $msg 消息内容
     * @param array $data 返回数据
     * @param int $code 错误码
     */
    protected function error($msg, $data = [], $code = 400)
    {
        $result = ['success' => false, 'code' => $code, 'msg' => $msg, 'data' => $data];
        throw new HttpResponseException(Response::create($result, 'json', 200, ToolsService::corsRequestHander()));
    }

    /**
     * 请求微信接口调用凭证
     */
    protected function getAccessToken()
    {
        $url = $this->wxapi_url . 'cgi-bin/token?grant_type=client_credential&appid='.$this->app_id.'&secret='.$this->app_secret;
        $res = HttpService::get($url,[]);
        $row = json_decode($res, 1);
        if ($row['errcode'] != 0) {
            return $this->error($row['errmsg']);
        }
        return $this->success('token获取成功', ['access_token' => $row['access_token']]);
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     */
    public function decryptData($encryptedData, $iv, $sessionkey)
    {
        if (strlen($sessionkey) != 24) {
            return $this->error('encodingAesKey 非法');
        }
        $aesKey = base64_decode($sessionkey);
        if (strlen($iv) != 24) {
            return $this->error('初始向量非法');
        }
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj = json_decode($result);
        if($dataObj == NULL ) {
            \think\facade\Log::error('aes 解密失败');
            return $this->error('数据异常');
        }
        if($dataObj['watermark']['appid'] != $this->app_id ) {
            \think\facade\Log::error('解密后得到的buffer非法');
            return $this->error('数据异常');
        }
        return $dataObj;
    }


    /**
     * 判断用户登录状态
     */
    public function getUser()
    {
        $user = null;
        $sessionid = $this->request->header('sessionid');
        if ($sessionid) {
            $sessioninfo = cache($sessionid);
            if ($sessioninfo) $user = Customer::get(['xcxopenid' => $sessioninfo['open_id']]);
        }
        if ($user !== null) {
            return $user;
        }
        return $this->error('登录已过期');

    }

    protected function aesEncrypt($data)
    {
        $encryptd = openssl_encrypt( $data, "AES-128-CBC", $this->aeskey, 1, $this->aesiv);
        return $encryptd;
    }

    protected function aesDecrypt($data)
    {
        $decrypted = openssl_decrypt($data, 'aes-128-cbc', $this->aeskey, false, $this->aesiv);
        return $decrypted;
    }

    protected function arrayToxml($data)
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

    protected function xmlToarray($xml, $isfile = false)
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