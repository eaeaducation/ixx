<?php


namespace app\api_xcx\controller;


use app\admin\controller\Log;
use controller\BasicXcx;
use service\HttpService;
use think\Db;

class Config extends BasicXcx
{
    /**
     * 小程序信息
     */
    public function index()
    {
        $data = [
            'xcx_name' => sysconf('app_name'),
            'xcx_version' => '1.0.0',
            'xcx_appid' => sysconf('wxxcx_app_id'),
            'xcx_secret' => sysconf('wxxcx_secret'),
            'site_copy' => sysconf('site_copy'),
            'miitbeian' => sysconf('miitbeian'),
        ];
        return $this->success('数据获取成功', $data);
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function schoolinfo()
    {
        $res = Db::name('saas_school_branch')
            ->order('id desc')
            ->select();
        if ($res) {
            return $this->success('获取成功', $res);
        }
        return $this->error('', '');
    }



    /**
     * 发送验证码
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function sendcode()
    {
        $mobile = $this->request->post('mobile');
        if (!$mobile) {
            return $this->error("手机号格式不正确");
        }
        $code = random_int(100000, 999999);
        \think\facade\Cache::set('customer_' . $mobile . '_code', $code, 120);
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

    /**
     * 系统消息
     */
    public function sysMessage()
    {
        $res = Db::name('saas_xcx_message')
            ->where('deleted', '=', 0)
            ->order('id desc')
            ->select();
        foreach ($res as $key=>$item) {
            $res[$key]['content'] = base64_encode($item['content']);
        }
        return $this->success("成功!", $res);
    }

    /**
     * 获取小程序二维码
     */
    public function getQr()
    {
        $mobile = $this->request->post('phone');
        $aid = $this->request->post('activity_id');
        $access_token = $this->getAccessToken();
        if ($access_token['access_token']) {
            $qr_url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$access_token['access_token'];
            $data = [
                'scene' => "phone=$mobile&aid=$aid",
            ];
            $header = [
                'content-type' => 'image/png'
            ];
            $res = HttpService::json($qr_url, $data, 30, $header);
            //分享页头部效果图
            $share = Db::name('saas_content')->field('title, thumb, content')->where('status', '=', 99)->where('catid', '=', '94')->order('id', 'desc')->find();
            return $this->success('二维码生成', ['qrcode_url' => base64_encode($res), 'share_picture' => $share['thumb'], 'content' => strip_tags($share['content'])]);
        }
    }
}