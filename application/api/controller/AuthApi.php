<?php
/**
 * User: Jasmine2
 * Date: 2018/6/26 10:39
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\api\controller;


use controller\BasicApi;
use jwt\JwtHelper;

class AuthApi extends BasicApi
{
    public function __construct()
    {
        parent::__construct();
        $this->check_token();
    }

    protected function check_token()
    {
        if ($this->request->has('access_token')) {
            $token = $this->request->request('access_token', '');
            $check = JwtHelper::checkTokenBool($token);
            if (!$check) {
                $this->error('access_token error');
            }
        } else {
            $this->error('access_token can\'t be null');
        }
    }
}