<?php

namespace call_center;

/**
 * Filename: Link.php
 * User: Jasmine2
 * Date: 2018-5-14 16:05
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 * 沃.云总机开放平台
 * Link+ Call Center API 文档
 */
//版本号：           20180319
//总机号码：         02968289712
//MainAccountId:     837a4441faf308f5aa2f9d1d724d25fb         //主账户Id
//MainAccountToken:  6f301b0864547bc68cbc22f0f1d0147c        //主账户令牌
//SubAccountId:      24e0b5479e9e0cd7b19fff8d69f3c88e       //子账户Id
//SubAccountToken:   89cc65325246c7b9873421304d783bde      //子账户时间令牌
//Appid:             a6744762d1e8a16e545b00d1ffceb3f6    //应用Id


use service\HttpService;
use think\facade\Log;

class Woyun
{
    public $host;
    public $SoftVersion;
    public $MainAccountId;
    public $MainAccountToken;
    public $SubAccountId;
    public $SubAccountToken;
    public $Appid;
    public $time_param;

    public function __construct()
    {
        $this->host = 'http://apiusertest.emic.com.cn';
        $this->SoftVersion = '20180319';
        $this->MainAccountId = '837a4441faf308f5aa2f9d1d724d25fb';
        $this->MainAccountToken = '6f301b0864547bc68cbc22f0f1d0147c';
        $this->SubAccountId = '24e0b5479e9e0cd7b19fff8d69f3c88e';
        $this->SubAccountToken = 'cce2d5d6d213946458b06ea3f26f2c58';
        $this->Appid = 'a6744762d1e8a16e545b00d1ffceb3f6';
        $this->time_param = date("YmdHis",time());
    }


    /*
     *业务--  获取主账户信息
     * */
    public function AccountInfo(){
        $sig = $this->mksig($this->MainAccountId,$this->MainAccountToken);
        $api = "/".$this->SoftVersion."/Accounts/".$this->MainAccountId."/AccountInfo?sig=".$sig;
        $authorization = $this->authorization($this->MainAccountId);
        $res = $this->http_request($api,$authorization,[],'post');
        return $res;
    }

    /*
     *业务--  创建子账号
     * */
    public function createSubAccount($nickName,$mobile,$email){
        $sig = $this->mksig($this->MainAccountId,$this->MainAccountToken);
        $api = "/".$this->SoftVersion."/Accounts/".$this->MainAccountId."/Applications/createSubAccount?sig=".$sig;
        $authorization = $this->authorization($this->MainAccountId);
        $data['createSubAccount'] = [
            'appId' => $this->Appid,
            'nickName' => $nickName,
            'mobile' => $mobile,
            'email' => $email
        ];
        $res = $this->http_request($api,$authorization,$data,'json');
        return $res;
    }

    /*
     *业务--  查询子账户列表
     * */
    public function subAccountList(){
        $sig = $this->mksig($this->MainAccountId,$this->MainAccountToken);
        $api = "/".$this->SoftVersion."/Accounts/".$this->MainAccountId."/Applications/subAccountList?sig=".$sig;
        $authorization = $this->authorization($this->MainAccountId);
        $data['subAccountList'] = [
            'appId' => $this->Appid
        ];
        $res = $this->http_request($api,$authorization,$data,'json');
        return $res;
    }

    /*
     *业务--  查询子账户
     * */
    public function subAccount($appid,$subAccountSid){
        $sig = $this->mksig($this->MainAccountId,$this->MainAccountToken);
        $api = "/".$this->SoftVersion."/Accounts/".$this->MainAccountId."/Applications/subAccount?sig=".$sig;
        $authorization = $this->authorization($this->MainAccountId);
        $data['subAccount'] = [
            'appId' => $appid,
            'subAccountSid' => $subAccountSid
        ];
        $res = $this->http_request($api,$authorization,$data,'json');
        return $res;
    }

    /*
     *业务--  更新子账户
     * */
    public function updateSubAccount($mobile,$email,$nickName){
        $sig = $this->mksig($this->MainAccountId,$this->MainAccountToken);
        $api = "/".$this->SoftVersion."/Accounts/".$this->MainAccountId."/Applications/updateSubAccount?sig=".$sig;
        $authorization = $this->authorization($this->MainAccountId);
        $data['updateSubAccount'] = [
            'appId' => $this->Appid,
            'subAccountSid' => $this->SubAccountId,
            'mobile' => $mobile,
            'email' => $email,
            'nickName' => $nickName
        ];
        $res = $this->http_request($api,$authorization,$data,'json');
        return $res;
    }

    /*
     *业务--  创建企业用户
     * */
    public function createUser($phone){
        $sig = $this->mksig($this->SubAccountId,$this->SubAccountToken);
        $api = "/".$this->SoftVersion."/SubAccounts/".$this->SubAccountId."/Enterprises/createUser?sig=".$sig;
        $authorization = $this->authorization($this->SubAccountId);
        $data['createUser'] = [
          'appId' => $this->Appid,
          'phone' =>$phone
        ];
        $res = $this->http_request($api,$authorization,$data,'json');
        return $res;
    }

    /*
     *业务--  获取用户信息
     * */
    public function userInfo($phone){
        $sig = $this->mksig($this->SubAccountId,$this->SubAccountToken);
        $api = "/".$this->SoftVersion."/SubAccounts/".$this->SubAccountId."/Enterprises/userInfo?sig=".$sig;
        $authorization = $this->authorization($this->SubAccountId);
        $data['userInfo'] = [
            'appId' => $this->Appid,
            'phone' =>$phone
        ];
        $res = $this->http_request($api,$authorization,$data,'json');
        return $res;
    }

    /*
     *业务--  删除企业用户
     * */
    public function dropUser($phone,$workNumber){
        $sig = $this->mksig($this->SubAccountId,$this->SubAccountToken);
        $api = "/".$this->SoftVersion."/SubAccounts/".$this->SubAccountId."/Enterprises/dropUser?sig=".$sig;
        $authorization = $this->authorization($this->SubAccountId);
        $data['dropUser'] = [
            'appId' => $this->Appid,
            'workNumber' => $workNumber,
            'phone' =>$phone
        ];
        $res = $this->http_request($api,$authorization,$data,'json');
        return $res;
    }

    /*
     *业务--  坐席迁入
     * */
    public function signIn($workNumber){
        $sig = $this->mksig($this->SubAccountId,$this->SubAccountToken);
        $api = "/".$this->SoftVersion."/SubAccounts/".$this->SubAccountId."/CallCenter/signIn?sig=".$sig;
        $authorization = $this->authorization($this->SubAccountId);
        $data['signIn'] = [
            'appId' => $this->Appid,
            'workNumber' => $workNumber,
        ];
        $res = $this->http_request($api,$authorization,$data,'json');
        return $res;
    }

    /*
     *业务--  坐席迁出
     * */
    public function signOff($workNumber){
        $sig = $this->mksig($this->SubAccountId,$this->SubAccountToken);
        $api = "/".$this->SoftVersion."/SubAccounts/".$this->SubAccountId."/CallCenter/signOff?sig=".$sig;
        $authorization = $this->authorization($this->SubAccountId);
        $data['signOff'] = [
            'appId' => $this->Appid,
            'workNumber' => $workNumber,
        ];
        $res = $this->http_request($api,$authorization,$data,'json');
        return $res;
    }

    /*
     *业务--  改变坐席模式
     * mode 0-固定座席（默认值），1-值班座席
     * */
    public function changeMode($workNumber,$mode='0'){
        $sig = $this->mksig($this->SubAccountId,$this->SubAccountToken);
        $api = "/".$this->SoftVersion."/SubAccounts/".$this->SubAccountId."/CallCenter/changeMode?sig=".$sig;
        $authorization = $this->authorization($this->SubAccountId);
        $data['changeMode'] = [
            'appId' => $this->Appid,
            'workNumber' => $workNumber,
            'mode' => $mode,
        ];
        $res = $this->http_request($api,$authorization,$data,'json');
        return $res;
    }

    /*
     * 业务-- 改变坐席状态
     * status 0-示闲（默认）；1-示忙；2-整理
     * */
    public function changeStatus($workNumber,$status='0'){
        $sig = $this->mksig($this->SubAccountId,$this->SubAccountToken);
        $api = "/".$this->SoftVersion."/SubAccounts/".$this->SubAccountId."/CallCenter/changeStatus?sig=".$sig;
        $authorization = $this->authorization($this->SubAccountId);
        $data['changeStatus'] = [
            'appId' => $this->Appid,
            'workNumber' => $workNumber,
            'status' => $status
        ];
        $res = $this->http_request($api,$authorization,$data,'json');
        return $res;
    }

    /*
     *业务--  坐席呼出
     * */
    public function callOut($workNumber,$userphone){
        $sig = $this->mksig($this->SubAccountId,$this->SubAccountToken);
        $api = "/".$this->SoftVersion."/SubAccounts/".$this->SubAccountId."/CallCenter/callOut?sig=".$sig;
        $authorization = $this->authorization($this->SubAccountId);
        $data['callOut'] = [
            'appId' => $this->Appid,
            'workNumber' => $workNumber,
            'to' => $userphone
        ];
        $res = $this->http_request($api,$authorization,$data,'json');
        return $res;
    }

    /*
     *业务-- 挂断电话
     * */
    public function callCancel($workNumber,$callId){
        $sig = $this->mksig($this->SubAccountId,$this->SubAccountToken);
        $api = "/".$this->SoftVersion."/SubAccounts/".$this->SubAccountId."/CallCenter/callCancel?sig=".$sig;
        $authorization = $this->authorization($this->SubAccountId);
        $data['callCancel'] = [
            'appId' => $this->Appid,
            'workNumber' => $workNumber,
            'callId' => $callId
        ];
        $res = $this->http_request($api,$authorization,$data,'json');
        return $res;
    }


    /*
     *工具-- 生成API验证参数SigParameter
     * */
    private function mksig($accountId,$accountToken){
        $sig_str = $accountId.$accountToken.$this->time_param;
        return strtoupper(MD5($sig_str));
    }

    /*
     *工具-- 生成鉴权表头验证信息Authorization
     * */
    private function authorization($accountId){
        $Authorization_str = $accountId.':'.$this->time_param;
        return base64_encode($Authorization_str);
    }

    private function http_request($api,$authorization,$data=[],$type){
        $url = 'http://apiusertest.emic.com.cn'.$api;
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => $authorization,
            'Content-Type' => 'application/json;charset=utf-8',
        ];
       $res = json_decode(HttpService::$type($url, $data,30,$headers), 1);
        if(isset($res['resp']['respCode']) && $res['resp']['respCode']!= '0'){
            $res['resp']['code_msg'] = WoyunBackMsg::getMsg($res['resp']['respCode']);
        }
        return $res;
    }

}
