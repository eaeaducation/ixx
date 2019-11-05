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
     * 构造方法
     * BasicApi constructor.
     */
    public function __construct()
    {
        // Cors跨域Options请求处理
        Session::init(config('session.'));
        ToolsService::corsOptionsHandler();
        // Cors跨域会话切换及初始化
        $this->request = app('request');
        $sessionName = $this->request->header(session_name());
        empty($sessionName) || session_id($sessionName);
        $this->site_name = Config::get('site_name');
        View::assign('site_name', $this->site_name);
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
}