<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/16
 * Time: 18:54
 */

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\App;
use think\Db;
use think\facade\Cache;


class Password extends BasicAdmin
{
    public $table = 'system_user';

    /**
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author Jasmine2
     */
    public function getPassword()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $mobile = $post['mobile'];
            $password = $post['password'];
            $code = $post['code'];
            if (strlen($password) < 6 || strlen($password) > 16) {
                $this->error("登录密码长度不能少于6位或者大于16位有效字符!", '');
            }
            //检查验证码
            $check = checkCode($mobile, $code);
            if ($check === false) {
                $this->error("短信验证码不正确！");
            }
            //更新密码
            $res = Db::name($this->table)
                ->where('phone', '=', $mobile)
                ->update(['password' => password_hash($password, PASSWORD_DEFAULT),
                    'password_encode' => encode($password)]);
            if ($res) {
                $this->success('');
            } else {
                $this->error('密码修改失败，请稍后再试！');
            }
        } else {
            return $this->fetch('');
        }
    }

    /**
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author Jasmine2
     * 发送验证码，并检测客户是否存在
     */
    public function checkUser()
    {
        $mobile = input('post.mobile', '');
        $captcha = input('post.captcha', '');
        if (!captcha_check($captcha)) {
            $this->error('图形验证码错误');
        }
        $user = Db::name($this->table)
            ->field('id')
            ->where('phone', '=', $mobile)
            ->find();
        if ($user) {
            $code = rand(1000, 9999);
            $msg = sms_lock($mobile);
            if ($msg !== false) {
                $this->error($msg);
            }
            $ret = send_sms($this->request->post('mobile'), '您正在申请重置密码,验证码为' . $code . ',请勿转发他人！');
            if ($ret) {
                Cache::set('captcha_' . $mobile, $code, 600);
                $this->success('发送成功');
            } else {
                $this->error('验证码发送失败，请稍后再试！');
            }
        } else {
            $this->error('用户不存在', '');
        }
    }
}