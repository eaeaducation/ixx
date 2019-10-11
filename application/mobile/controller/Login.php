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

namespace app\mobile\controller;

use controller\BasicAdmin;
use app\common\model\SystemUser;
use service\LogService;
use service\NodeService;
use think\Db;

class Login extends BasicAdmin
{
    public function initialize()
    {
        if (session('user.id') && $this->request->action() !== 'out') {
            $this->redirect('@mobile');
        }
    }

    public function index()
    {
        if ($this->request->isGet()) {
            return $this->fetch('', ['title' => '用户登录']);
        }
        $username = $this->request->post('username', '', 'trim');
        $password = $this->request->post('password', '', 'trim');
        strlen($username) < 4 && $this->error('登录账号长度不能少于4位有效字符!');
        strlen($password) < 4 && $this->error('登录密码长度不能少于4位有效字符!');
        // 用户信息验证
        $user = SystemUser::where('phone', '=', $username)->where('is_deleted', '=', 0)->find();
        empty($user) && $this->error('登录账号不存在，请重新输入!');

        !password_verify($password, $user['password']) && $this->error('登录密码与账号不匹配，请重新输入!');
        empty($user['status']) && $this->error('账号已经被禁用，请联系管理员!');
        if ($user['id'] != 10000 && (except_group($user) || !checkIpFromConfig())) {
            LogService::write('异常登录', "用户[{$username}]尝试从[{$this->request->ip()}]登录!!!");
            $this->error('ip not in the white list');
        }
        // 更新登录信息
        $data = ['login_at' => Db::raw('now()'), 'login_num' => Db::raw('login_num+1')];
        $user->save($data);
        session('user', $user);
        !empty($user['authorize']) && NodeService::applyAuthNode();
        LogService::write('系统管理', '用户登录系统成功');
        $this->success('登录成功...', '@mobile');
    }

    /**
     * 退出登录
     */
    public function out()
    {
        session('user') && LogService::write('系统管理', '用户退出系统成功');
        !empty($_SESSION) && $_SESSION = [];
        [session_unset(), session_destroy()];
        header("location:/mobile/index");
    }
}