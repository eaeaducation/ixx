<?php

namespace app\marketing\controller;

/**
 * Filename: Call.php
 * User: Jasmine2
 * Date: 2018-5-17 15:51
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

use call_center\Link;
use controller\BasicAdmin;
use think\App;
use think\Db;
use think\Exception;
use think\Facade\Log;

/**
 * Class Call
 * @package app\marketing\controller
 * @property Link $instance
 */
class Call extends BasicAdmin
{
    public $checkAuth = false;
    public $checkLogin = false;

    public $instance;

    public $agent_no;
    public $password;

    public function __construct(App $app)
    {
        parent::__construct($app);
        if (sysconf('call_center_type') != 'no') {
            $class = "\call_center\\" . ucfirst(sysconf('call_center_type'));
            $this->instance = new $class;
            $this->agent_no = session('agent_no');
            $this->password = session('password');
        } else {
            throw new Exception('没有该类型');
        }
    }

    /**
     * @return \think\response\View
     * 签入/签出
     */
    public function checkin()
    {
        if (session('checkin')) {
            $agent_no = $this->agent_no;
            $password = $this->password;
            $res = $this->instance->check($agent_no, $password, 2);
            if ($res['status'] == 1) {
                session('checkin', null);
                session('agent_no', null);
                session('password', null);
                Db::name('link_check')->insert([
                    'agent_no' => $agent_no,
                    'userid' => session('user.id'),
                    'type' => 'checkout'
                ]);
                $this->success('签退成功', '');
            } else {
                $this->error($res['desc']);
            }
        }
        if ($this->request->isPost()) {
            $agent_no = input('post.agent_no');
            $password = input('post.password');
            $res = $this->instance->check($agent_no, $password);
            if ($res['status'] == 1) {
                session('checkin', true);
                cookie('agent_no', $agent_no);
                cookie('password', $password);
                session('agent_no', $agent_no);
                session('password', $password);
                Db::name('link_check')->insert([
                    'agent_no' => $agent_no,
                    'userid' => session('user.id'),
                    'type' => 'checkin'
                ]);
                $this->success('签入成功', '');
            } else {
                $this->error($res['desc']);
            }
        }
        return view('');
    }

    /**
     * 拨打电话主要方法
     */
    public function make_call()
    {
        $mobile = input('post.id', false, 'trim');
        if (session('checkin') == false) {
            $this->error('坐席未签入, 请先签入!');
        }
        if ($mobile) {
            $agent_no = $this->agent_no;
            $password = $this->password;
            try {
                $uuid = uniqid('link-');
                $res = $this->instance->make_call($agent_no, $password, $mobile, $uuid);
                Db::name('link_call_log')->insert([
                    'agent_no' => $agent_no,
                    'uuid' => $uuid,
                    'mobile' => $mobile,
                    'call_time' => time(),
                    'userid' => session('user.id')
                ]);
                if ($res['status'] == 1) {
                    $this->success('调用成功', '');
                } else {
                    $this->error($res['msg'] . $res['desc']);
                }
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                $this->error('服务异常, 请联系 it@zhishensoft.com !');
            }
        }
    }

    /**
     * 挂断
     */
    public function hangup_call()
    {
        if (session('checkin') == false) {
            $this->error('坐席未签入, 请先签入!');
        }
        try {
            $res = $this->instance->hangup_call($this->agent_no, $this->password);
            if ($res['status'] == 1) {
                $this->success('挂断成功', '');
            } else {
                $this->error($res['msg'] . $res['desc']);
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            $this->error('服务异常, 请联系 it@zhishensoft.com !');
        }
    }

    /**
     * 示闲/示忙
     */
    public function agent_status_setup()
    {
        if (session('checkin') == false) {
            $this->error('坐席未签入, 请先签入!');
        }
        $id = $this->request->request('id');
        try {
            $res = $this->instance->agent_status_setup($this->agent_no, $this->password, $id, 'other', 'yes');
            if ($res['status'] == 1) {
                $this->success('接口调用成功', '');
            } else {
                $this->error($res['msg'] . $res['desc']);
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            $this->error('服务异常, 请联系 it@zhishensoft.com !');
        }
    }

    /**
     * 拨打电话过程中的回调
     */
    public function notify()
    {
        if ($this->request->isPost()) {
            // 记录日志
            $post = $this->request->post();
            Db::name('link_logs')->insert([
                'uuid' => $post['userdata'],
                'msg' => json_encode($post)
            ]);
            // 做状态更新
            Db::name('link_call_log')->where('uuid', '=', $post['userdata'])->findOrFail();
            $_call_log['event'] = $post['event'];
            if ($post['source'] == 'CONVERSATION' && $post['event'] == 'hangup') {
                $_call_log['hangup_time'] = time();
                $record = $this->instance->get_record($post['sessionid'], date('Y-m-d'));
                if ($record['status'] == 1) {
                    $_call_log['record_url'] = $record['data']['url'];
                }
                if (sysconf('link_download') == 99) {
                    $_call_log['local_record_url'] = download_file($_call_log['record_url'], 'wav');
                }
                Log::error(sysconf('link_download'));
                Log::error($_call_log);
            }
            Db::name('link_call_log')
                ->where('uuid', '=', $post['userdata'])
                ->where('event', '=', 'hangup')
                ->update($_call_log);
            return json(['success' => true, 'msg' => 'SUCCESS']);
        }
    }
}
