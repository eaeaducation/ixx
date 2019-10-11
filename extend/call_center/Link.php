<?php

namespace call_center;

/**
 * Filename: Link.php
 * User: Jasmine2
 * Date: 2018-5-14 16:05
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 * 上海九霄祥云网络科技股份有限公司
 * Link+ Call Center API 文档
 */

use service\HttpService;
use think\facade\Log;

class Link
{
    public $host;
    public $api_key;
    public $api_secret;
    public $group_id;

    public function __construct()
    {
        $this->host = sysconf('link_host');
        $this->api_key = sysconf('link_api_key');
        $this->api_secret = sysconf('link_api_secret');
        $this->group_id = sysconf('link_group_id');
    }

    /**
     * @param $agent_no
     * @param $password
     * @param int $type 签入: 1 签出: 2
     * @return mixed
     * 坐席（签入/签出）
     * 调用接口，使指定坐席的状态变更为“签入”或“签出”
     */
    public function check($agent_no, $password, $type = 1)
    {
        $api = 'api/uccc/agent_checkinout';
        return $this->_request($api, [
            'agent_no' => $agent_no,
            'password' => $password,
            'type' => $type,
            'group_id' => $this->group_id
        ]);
    }

    /**
     * @param $agent_no
     * @param $password
     * @param $type
     * @param $reason // training=培训，meeting=会议，leave=请假，lunch=午休，rest=小憩，other=其他
     * @param string $phone_sync // yes or null
     * @return mixed
     */
    public function agent_status_setup($agent_no, $password, $type, $reason, $phone_sync = '')
    {
        $api = 'api/uccc/agent_status_setup';
        return $this->_request($api, [
            'agent_no' => $agent_no,
            'password' => $password,
            'type' => $type,
            'reason' => $reason,
            'phone_sync' => $phone_sync
        ]);
    }

    /**
     * @param $agent_no
     * @param $password
     * @return mixed
     * 坐席结束话后
     * 如果坐席组设定了自动话后，那么呼叫应答时会自动将坐席状态设置为话后（线路繁忙的一
     * 种，这种状态下队列请求将不会再轮询到此坐席，坐席可以进行通话记录及其他行动）。
     * 然后，可以调用接口，使指定坐席的状态变更为“结束话后”（也可理解为示闲）
     */
    public function agent_acw_off($agent_no, $password)
    {
        $api = 'api/uccc/agent_acw_off';
        return $this->_request($api, [
            'agent_no' => $agent_no,
            'password' => $password,
        ]);
    }

    public function get_record($sessionid, $calldate, $mp3 = 'no')
    {
        $api = 'api/uccc/get_record';
        return $this->_request($api, [
            'sessionid' => $sessionid,
            'calldate' => $calldate,
            'mp3' => $mp3
        ]);
    }

    /**
     * @param $password
     * @param $callee
     * @param $caller
     * @return mixed
     * 外呼接口
     */
    public function make_call($caller, $password, $callee, $userdata)
    {
        $api = 'api/uccc/make_call';
        return $this->_request($api, [
            'password' => $password,
            'group_id' => $this->group_id,
            'callee' => $callee,
            'caller' => $caller,
            'userdata' => $userdata
        ]);
    }

    /**
     * @param $agent_no
     * @param $password
     * @return mixed
     * 挂断当前通话
     */
    public
    function hangup_call($agent_no, $password)
    {
        $api = 'api/uccc/hangup_call';
        return $this->_request($api, [
            'agent_no' => $agent_no,
            'password' => $password
        ]);
    }

    /**
     * @param $agent_no
     * @param $password
     * @return mixed
     * 获取单一坐席状态统计数据
     */
    public
    function get_agent_status_analysis($agent_no, $password)
    {
        $api = 'api/uccc/get_agent_status_analysis';
        return $this->_request($api, [
            'agent_no' => $agent_no,
            'password' => $password
        ]);
    }

    public
    function get_agent_call_analysis($agent_no, $password)
    {
        $api = 'api/uccc/get_agent_call_analysis';
        return $this->_request($api, [
            'agent_no' => $agent_no,
            'password' => $password
        ]);
    }

    /**
     * @param $agent_no
     * @param $password
     * @param $status
     * @return mixed
     * all 获取组内所有坐席的状态
     * idle 获取组内空闲坐席
     * busy 获取组内通话坐席
     * ring 获取组内振铃坐席
     * pause 获取组内暂停坐席
     * acw 获取组内话后坐席
     * login 获取组内签入坐席
     * logout 获取组内签出坐席
     */
    public
    function get_all_agents_status($agent_no, $password, $status)
    {
        $api = 'api/uccc/get_all_agents_status';
        return $this->_request($api, [
            'agent_no' => $agent_no,
            'password' => $password,
            'status' => $status
        ]);
    }

    /**
     * @param string $id
     * @param string $type
     * @return mixed
    //     * @deprecated 调用显示接口不存在
     */
    public function get_call_log($id = '', $type = 'all')
    {
        $api = 'api/uccc/get_call_log';
        return $this->_request($api, [
            'id' => $id,
            'type' => $type
        ]);
    }

    public
    function web_call($switch, $caller, $callee, $account, $password)
    {
        $api = 'api/uccc/web_call';
        return $this->_request($api, [
            'switch' => $switch,
            'caller' => $caller,
            'callee' => $callee,
            'account' => $account,
            'password' => $password
        ]);
    }

    /**
     * @param $api
     * @param $data
     * @param string $method
     * @return mixed
     */
    private
    function _request($api, $data, $method = 'post')
    {
        $url = $this->host . '?r=' . $api;
        $requestData = array_merge([
            'api_key' => $this->api_key,
            'api_secret' => $this->api_secret
        ], $data);
        if (array_key_exists('password', $requestData)) {
            $requestData['password'] = md5($requestData['password']);
        }
        if (env('app_debug') == true) {
            Log::error($requestData);
        }
        $res = json_decode(HttpService::$method($url, $requestData), 1);
        if ($res['status'] == 0) {
            if (preg_match('/\[(.*)\]/', $res['msg'], $match)) {
                $res['desc'] = LinkBackmsg::getMsg($match[1]);
            }
        }
        if (env('app_debug') == true) {
            Log::error($res);
        }
        return $res;
    }
}
