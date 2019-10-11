<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/25 0025
 * Time: 17:11
 */

namespace app\shop\controller;

use think\facade\Cache;
use think\Controller;
use think\Db;

class Login extends Controller
{
    public function index()
    {
        if (!$this->request->isPost()) {
            return view();
        }
        $post = $this->request->post();
        $code = Cache::get('customer_' . $post['mobile']. '_code');
        if (!$code) {
            $this->error('验证码已过期，请重新获取', '', '0');
        } elseif ($code != $post['code']) {
            $this->error('验证码不正确', '', '1');
        }
        $user = Db::name('saas_customer')
            ->where('status', '<>', 3)
            ->where('parent_tel', '=', $post['mobile'])
            ->field('id, parent_tel, parent_name, created_at')
            ->find();

        if (!$user) {
            $data = [];
            $data['parent_tel'] = $post['mobile'];
            $data['source'] = 22;
            $data['created_at'] = time();
            $cid= Db::name('saas_customer')->insertGetId($data);
            $data['id'] = $cid;
            $data['parent_name'] = '';
            session('customer', $data);
        } else {
            session('customer', $user);
        }
        $url = !empty(cookie('url')) ? cookie('url') : '/shop/index';
        $this->success('登录成功', '', $url);
    }

    public function registered()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();

            $res = Db::name('saas_customer')
                ->where('status', '<>', 3)
                ->where('parent_tel', '=', $post['parent_tel'])
                ->column('id');
            if ($res) {
                $this->error('账号已存在，请登录');
            }
            $post['source'] = 22;
            $post['created_at'] = time();
            $row = Db::name('saas_customer')->insert($post);
            if ($row) {
                $this->success('注册成功, 请登录', '');
            } else {
                $this->error('注册失败，请稍后再试');
            }
        }
        return view();
    }

    /**
     * 发送验证码
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function send_code()
    {
        $mobile = $this->request->post('mobile');
        $code = random_int(100000, 999999);
        Cache::set('customer_' . $mobile . '_code', $code, 120);
        $msg = sms_lock($mobile);
        if ($msg !== false) {
            $this->error($msg);
        }
        $ret = send_sms($mobile, '您的验证码为' . $code);
        if ($ret == 1) {
            $this->success("短信发送成功!", '');
        } else {
            $this->error("短信发送失败");
        }
    }

    public function protocol()
    {
        $row = Db::name('saas_content')->where('catid', '=', 76)->field('content')->find();
        return view('', ['row' => $row]);
    }
}