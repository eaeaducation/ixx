<?php

namespace exception;


/**
 * User: Jasmine2
 * Date: 2018/7/20 10:12
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

use Exception;
use think\db\exception\DataNotFoundException;
use think\facade\Request;

class Handle extends \think\exception\Handle
{
    public function render(Exception $e)
    {
        $request = Request::instance();
        if ($request->isAjax() && $e instanceof DataNotFoundException) {
            return json([
                'code' => 0,
                'msg' => '数据不存在',
                'data' => '',
                'url' => '',
                'wait' => 2,
            ]);
        }
        return parent::render($e);
    }
}