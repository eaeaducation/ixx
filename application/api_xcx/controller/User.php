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
     * 微信登录
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
     * 微信手机号登录
     */
    public function wxMobileLogin()
    {
        $post = $this->request->post();
        if ($post['sessionid']) {
            $sessioninfo = \cache($post['sessionid']);
            //解密获取 手机号信息
            $decrypt = $this->decryptData($post['encrypt_data'], $post['iv'], $sessioninfo['session_key']);
            if ($decrypt->phoneNumber) {
                $customer = $query_user = Db::name('saas_customer')
                    ->where('status', '<>', 3)
                    ->where('parent_tel', '=', $decrypt->phoneNumber)
                    ->find();
                if (!$customer) {
                    $data['xcxopenid'] = $post['open_id'];
                    $data['parent_tel'] = $decrypt->phoneNumber;
                    $data['created_at'] = time();
                    $data['source'] = 22;
                    $customer = Db::name('saas_customer')->insert($data);
                }
                return $this->success('用户信息保存成功', $customer);
            }
        } else {
            return $this->error('参数异常');
        }
    }

    /**
     * 保存用户xinx
     */
    public function userInfo()
    {
        $post = $this->request->post();
        if (!$post['phone']) {
            return $this->error('参数错误');
        }
        $query_user = Db::name('saas_customer')
            ->where('status', '<>', 3)
            ->where('parent_tel', '=', $post['phone'])
            ->find();
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

    /**
     * 个人优惠券
     */
    public function getUserCoupon()
    {
        $user = $this->getUser();
        $status = $this->request->post('status');
        $time = time();
        $coupons = Db::name('saas_xcx_activity_detail sxad')
            ->join('saas_xcx_activity sxa', 'sxa.id = sxad.aid');
        if (isset($status) && $status == 0) {
            $coupons->where('sxad.status', '=', 0);//未使用
        } elseif (isset($status) && $status == 1) {
            $coupons->where('sxad.status', '=', 1);//已使用
        } else {
            $coupons->where("sxa.end_time < $time");//已过期
        }
        $coupons = $coupons->where('sxad.cid', '=', $user->id)
            ->where('sxa.deleted', '=', 0)
            ->where('sxad.del', '=', 0)
            ->where('sxad.is_can_use', '=', 1)
            ->field('sxad.id, sxa.title, sxa.beg_time, sxa.end_time, sxa.rule_detail, sxa.activity_theme_img, sxad.amount, sxad.status')
            ->select();
        foreach ($coupons as $key => $value) {
            $coupons[$key]['beg_time'] = date('Y-m-d', $coupons[$key]['beg_time']);
            $coupons[$key]['end_time'] = date('Y-m-d', $coupons[$key]['end_time']);
        }
        return $this->success('用户在活动中获取的所有优惠', $coupons);
    }

}