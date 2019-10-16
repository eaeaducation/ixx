<?php


namespace app\api\controller;

use app\common\model\Customer;
use jwt\JwtHelper;
use service\ToolsService;
use think\exception\HttpResponseException;
use think\facade\Log;
use think\facade\Response;
use think\facade\Session;
use think\facade\Config;
use think\facade\View;


class AppBasicApi
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
     * @var
     * 请求数据
     */
    public $request_data;

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
        $this->request_data = api_decrypt($this->request->request('data'), config('api_key'), config('api_iv'));
        View::assign('site_name', $this->site_name);
    }

    /**
     * @return Customer|null
     * @throws \think\exception\DbException
     */
    final protected function getUser()
    {
        $user = null;
        if ($this->request->has('access_tokan', 'param', true)) {
            $payload = JwtHelper::checkToken($this->request->request('access_token'));
            $user = Customer::get($payload->sub);
        }
        if ($user !== null) {
            return $user;
        }
        $result = [
            'success' => false,
            'code' => -400,
            'msg' => '未登录',
        ];
        throw new HttpResponseException(Response::create($result, 'json', 200, ToolsService::corsRequestHander()));
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