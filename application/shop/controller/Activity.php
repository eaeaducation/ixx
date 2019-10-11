<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/4 0004
 * Time: 10:35
 */

namespace app\shop\controller;

use think\Controller;
use think\facade\Cache;

class Activity extends Controller
{
    public function h5()
    {
        return view();
    }

    public function sendCode()
    {
        $mobile = $this->request->post('mobile');
        $msg = sms_lock($mobile);
        if ($msg !== false) {
            $this->error($msg);
        }
        $code = random_int(100000, 999999);
        Cache::set('cushongbao_' . $mobile . '_code', $code, 60);
        $res = send_sms($mobile, '您的验证码为' . $code);
        if ($res == 1) {
            $this->success("验证码发送成功,请注意查收", '');
        } else {
            $this->error("验证码发送失败");
        }
    }

    public function sendMsg()
    {
        $mobile = $this->request->post('mobile');
        if (Cache::get('cushongbao_' . $mobile . '_code')) {
            $res = send_sms($mobile, '恭喜您成功领取EA国际教育88元代金红包，凭此短信即可到EA任意线下门店进行购买课程兑换消费。详情咨询：4006692620', 'EA国际教育', 3);
            if ($res == 1) {
                $this->success("短信发送成功,请注意查收", '');
            } else {
                $this->error("短信发送失败");
            }
        }
    }
}