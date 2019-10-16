<?php


namespace app\api\controller\v2;


use app\api\controller\AppBasicApi;
use app\common\model\Customer;
use jwt\JwtHelper;
use think\Cookie;
use think\Db;
use think\facade\Cache;

class User extends AppBasicApi
{
    /**
     * 注册
     * "支持手机号+验证码注册",
     */
    public function register()
    {
        $post = $this->request_data;
        if (!isset($post['mobile']) || !isset($post['password']) || !isset($post['code'])) {
            return $this->error('参数格式不正确');
        }
        $res = Db::name('saas_customer')
            ->where('status', '<>', 3)
            ->where('parent_tel', '=', $post['mobile'])
            ->column('id');
        if ($res) {
            $this->error('账号已存在，请登录');
        }
        $verify_code = Cache::get('customer_' . $post['mobile']. '_code');
        if (!$verify_code) {
            return $this->error('验证码已过期，请重新获取', '', '0');
        } elseif ($verify_code != $post['code']) {
            return $this->error('验证码不正确', '', '1');
        }
        $data['parent_tel'] = $post['mobile'];
        $data['source'] = 37;
        $data['created_at'] = time();
        $data['password'] = password_hash($post['password'], PASSWORD_DEFAULT);
        $row = Db::name('saas_customer')->insert($data);
        if ($row) {
            return $this->success('注册成功, 请登录');
        } else {
            return $this->error('注册失败，请稍后再试');
        }
    }

    /**
     * 登录
     */
    public function login()
    {
        //手机号+密码
        //手机号+验证码
        //微信
        $post = $this->request_data;
//        switch ($post['login_type']){
//            case 'mobile_pwd':
//
//                break;
//            case 'mobile_code':
//
//
//                break;
//            case 'weixin':
//                break;
//        }
        if (!isset($post['mobile']) || !isset($post['password'])) {
            return $this->error('参数不能为空');
        }
        $customer = Customer::where('status', '<>', 3)->where('parent_tel', '=', $post['mobile'])->find();
        if (empty($customer)) return $this->error('用户不存在');
        if(!password_verify($post['password'], $customer['password'])) return $this->error('密码错误');
        $access_token = JwtHelper::fromUser($customer, 21600);
//        \cookie('ea_user', $customer);
        $return_data = api_encrypt(['access_token' => $access_token, 'user' => $customer]);
        return $this->success('登录成功', $return_data);

    }

    /**
     * 登录状态
     */
    public function islogin()
    {
        $user = $this->getUser();

    }

    /**
     * 修改密码
     * 支持手机号+验证码
     */
    public function update_password()
    {
        $post = $this->request_data;
        if (!$post['code']) {
            return $this->error('验证码不能为空');
        }
        if (!$post['new_pwd']) {
            return $this->error('请设置新的登录密码');
        }
        $verify_code = Cache::get('customer_' . $post['mobile']. '_code');
        if (!$verify_code) {
            return $this->error('验证码已过期，请重新获取', '', '0');
        } elseif ($verify_code != $post['code']) {
            return $this->error('验证码不正确', '', '1');
        }
//        $new_pwd = password_hash($post['new_pwd'], PASSWORD_DEFAULT);
        //更新密码
        $res = Db::name($this->table)
            ->where('parent', '=', strval($post['mobile']))
            ->update(['password' => password_hash($post['new_pwd'], PASSWORD_DEFAULT)]);
        if ($res) {
            return $this->success('密码修改成功');
        } else {
            return $this->error('密码修改失败，请稍后再试！');
        }
    }

    /**
     * 忘记密码
     */
    public function forget_password()
    {

    }

    /**
     * 退出登录
     */
    public function outlogin()
    {
//        \cookie(null);
        return $this->success('退出登录成功');
    }
}