<?php


namespace app\api_xcx\controller;


use app\common\model\Customer;
use controller\BasicXcx;
use service\HttpService;
use think\Db;
use think\facade\Cache;
use think\facade\Log;
use think\facade\Session;

class User extends BasicXcx
{
    /**
     * 获取临时登录open_id
     */
    public function getOpenid()
    {
        $code = $this->request->post('code');
        if (!$code) return $this->error('登录认证code错误');
        $request_url = $this->wxapi_url.'sns/jscode2session?appid='.$this->app_id.'&secret='.$this->app_secret.'&js_code='.$code.'&grant_type=authorization_code';
        $res = HttpService::get($request_url,[]);
        $row = json_decode($res, 1);
        if ($row['openid']) {
            //登录成功（随机生成登录token返回给小程序，用以验证身份）
            $value['open_id'] = $row['openid'];
            $value['session_key'] = $row['session_key'];
            $sessionid = md5($row['openid']);
            \cache($sessionid, $value, 2592000);
            $data = [
                'open_id' => $row['openid'],
                'sessionid' => $sessionid
            ];
            return $this->success('open_id获取成功', $data);
        }
        return $this->error('获取失败');
    }

    /**
     * 保存用户xinx
     */
    public function userInfo()
    {
        $post = $this->request->post();
        $query_user = Db::name('saas_customer')
            ->where('status', '<>', 3)
            ->where('xcxopenid', '=', $post['open_id'])
            ->find();
        if (!$query_user) {
            $data['xcxopenid'] = $post['open_id'];
            $data['nickname'] = $post['nickName'];
            $data['parnet_name'] = $post['nickName'];
            $data['name'] = $post['nickName'];
            $data['created_at'] = time();
            $data['avatar_url'] = $post['avatarUrl'];
            $data['source'] = 22;
            Db::name('saas_customer')->insert($data);
        }
        return $this->success('用户信息保存成功', $query_user);
    }

    /**
     * 绑定手机号
     */
    public function addMobile()
    {
        $user = $this->getUser();
        $post = $this->request->post();
        $verify_code = Cache::get('customer_' . $post['mobile']. '_code');
        if (!$verify_code) {
            return $this->error('验证码已过期，请重新获取', '', '0');
        } elseif ($verify_code != $post['code']) {
            return $this->error('验证码不正确', '', '1');
        }
//        $sessionid = $this->request->request('sessionid');
//        $sessioninfo = session($sessionid);
//        $decrypt = $this->decryptData($post['encryptedData'], $post['iv'], $sessioninfo['session_key']);
        $user->parent_tel = trim($post['mobile']);
        $user->save();
        return $this->success('手机号绑定成功', []);
    }

}