<?php
/**
 * User: Jasmine2
 * Date: 2018/6/26 10:34
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\api\controller;


use app\api\model\ApiUser;
use controller\BasicApi;
use jwt\JwtHelper;

class Token extends BasicApi
{
    protected $user = [
        'admin' => '123456'
    ];

    public function get_token()
    {
        // 做一个简单的用户名和密码
        $username = $this->request->request('username');
        $password = $this->request->request('password');
        if (!array_key_exists($username, $this->user) || $password != $this->user[$username]) {
            $this->error('username or password error');
        }
        $user = new ApiUser();
        $user->id = 'admin';
        $token = JwtHelper::fromUser($user, 24);
        $this->success('success', ['access_token' => $token]);
    }
}