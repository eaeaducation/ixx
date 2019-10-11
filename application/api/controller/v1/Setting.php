<?php
/**
 * User: Jasmine2
 * Date: 2018/6/26 10:31
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\api\controller\v1;


use app\api\controller\AuthApi;

class Setting extends AuthApi
{
    /**
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author Jasmine2
     */
    public function branch_set()
    {
        $data = $this->request->except('access_token');
        foreach ($data as $key => $value) {
            if (!is_array($value) && $value != '') {
                sysconf($key, $value);
            }
        }
        $this->success('update success');
    }
}