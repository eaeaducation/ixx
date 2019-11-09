<?php


namespace app\api_xcx\controller;


use app\common\model\Customer;
use controller\BasicXcx;
use service\HttpService;
use think\facade\Log;

class User extends BasicXcx
{
    /**
     * 获取临时登录open_id
     */
    public function getOpenid()
    {
        $code = $this->request->request('code');
        Log::error($code);
        if (!$code) return $this->error('登录认证code错误');
        $request_url = $this->wxapi_url.'sns/jscode2session?appid='.$this->app_id.'&secret='.$this->app_secret.'&js_code='.$code.'&grant_type=authorization_code';
        $res = HttpService::get($request_url,[]);
        $row = json_decode($res, 1);
        if ($row['openid']) {
            //登录成功（随机生成登录token返回给小程序，用以验证身份）
            $value['open_id'] = $row['openid'];
            $value['session_key'] = $row['session_key'];
            $sessionid = session_id($row['openid']);
            session($sessionid, $value);
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
        $query_user = Customer::get(['xcxopenid' => $post['open_id']]);
        if (!$query_user) {
            $user = new Customer();
            $user->xcxopenid = $post['open_id'];
            $user->nickname = $post['nickName'];
            $user->created_at = time();
            $user->avatar_url = $post['avatarUrl'];
            $user->source = 22;
            $user->save();
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
        $sessionid = $this->request->request('sessionid');
        $sessioninfo = session($sessionid);
        $decrypt = $this->decryptData($post['encryptedData'], $post['iv'], $sessioninfo['session_key']);
        $user->parent_tel = $decrypt['phoneNumber'];
        $user->save();
        return $this->success('手机号绑定成功', []);
    }

}