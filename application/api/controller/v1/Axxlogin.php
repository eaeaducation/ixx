<?php
/**
 * User: Jasmine2
 * Date: 2018/6/26 10:39
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\api\controller\v1;


use controller\BasicApi;
use Naixiaoxin\ThinkWechat\Facade;
use think\facade\Cache;
use think\Db;
use think\facade\Log;
use service\NodeService;

class Axxlogin extends BasicApi
{

    private $appid;
    private $qrkey = 'c9b2533069ebfa67';

    /**
     *
     * 爱学学app扫码登陆
     */
    public function index()
    {
        $lsign_code = $this->request->post('lsign');
        $mobile = $this->request->post('mobile');

        if(strpos($lsign_code,$this->qrkey) == false){
            $this->error('验签失败',[],'0001');
        }else{
            $sign = $this->lsign_decode($lsign_code);
        }

        if(empty($mobile)){
            $this->error('手机不能为空',[],'0002');
        }
        if(!preg_match("/^1[345789]\d{9}$/", $mobile)){
            $this->error('手机格式错误',[],'0003');
        }

        $check = $this->checkMobile($mobile);
        if ($check) {
            Cache::rm($sign);
            $data['status'] = '1';
            $data['phone'] = $mobile;
            Cache::set($sign, $data,5);
            $this->success('success', ['status' => true,'phone' => $mobile],'0000');
        }else{
            Cache::rm($sign);
            $data['status'] = '0';
            $data['phone'] = $mobile;
            Cache::set($sign, $data,5);
            $this->error('未入住saas无法登陆', [],'0004');
        }

    }

    /*
     * lsign_decode解码
     * */
    private function lsign_decode($lsign_code){
        $num = strpos($lsign_code,$this->qrkey);
        $lsign = substr($lsign_code,0,$num);
        return base64_decode($lsign);
    }

    /**
     * @param $mobile
     * @return mixed
     * 手机账号检测是否存在
     */
    private function checkMobile($mobile)
    {
        $user = DB::table('system_user')->where('phone', '=',$mobile)->
        where('is_deleted','<>','1')->field('id')->find();
        if (!$user) {
            return false;
        } else {
            return true;
        }
    }


}