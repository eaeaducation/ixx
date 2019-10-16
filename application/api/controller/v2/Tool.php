<?php


namespace app\api\controller\v2;


use app\api\controller\AppBasicApi;
use think\facade\Cache;

class Tool extends AppBasicApi
{

    /**
     * 发送验证码
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function send_code()
    {
        $mobile = $this->request->post('mobile');
        if (!$mobile) {
            return $this->error("手机号格式不正确");
        }
        $code = random_int(100000, 999999);
        Cache::set('customer_' . $mobile . '_code', $code, 120);
        $msg = sms_lock($mobile);
        if ($msg !== false) {
            return $this->error($msg);
        }
        $ret = send_sms($mobile, '您的验证码为' . $code);
        if ($ret == 1) {
            return $this->success("验证码发送成功!", '');
        } else {
            return $this->error("验证码发送失败");
        }
    }

}