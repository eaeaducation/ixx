<?php
/**
 * User: Jasmine2
 * Date: 2018/5/21 16:42
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\admin\controller;


use controller\BasicAdmin;
use hook\Site;
use service\FileService;
use think\App;
use think\exception\HttpException;
use think\facade\Cache;
use think\facade\Env;
use think\Request;
use think\Db;

class Test extends BasicAdmin
{
    /**
     * @author Jasmine2
     * TODO 功能测试方法， 使用完请置空
     */
    public function index()
    {
    }
}