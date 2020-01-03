<?php


namespace app\api_xcx\controller;


use app\common\model\Customer;
use controller\BasicXcx;
use service\HttpService;
use think\Db;
use think\Exception;
use think\facade\Cache;
use think\facade\Log;
use think\facade\Session;

/**
 * Class User
 * @package app\api_xcx\controller
 * @method \WeChat\Card card() static 卡券管理
 */

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
                $customer = Db::name('saas_customer')
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
                $wallet = Db::name('saas_wallet')
                    ->where('customer_id', '=', $customer['id'])
                    ->find();
                if (!$wallet) {
                    Db::name('saas_wallet')->insert(['customer_id' => $customer['id'], 'integration' => 0, 'amount' => 0, 'created_at' => time()]);
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
        $query_user = Db::name('saas_customer c')
            ->join('saas_wallet w', 'w.customer_id = c.id', 'left')
            ->where('c.status', '<>', 3)
            ->where('c.parent_tel', '=', $post['phone'])
            ->find();
        return $this->success('用户信息', $query_user);
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
     * user center
     */
    public function userWallet()
    {
        $user = $this->getUser();
        $current_time = time();
        $userinfo = Db::name('saas_customer c')
            ->join('saas_wallet w', 'w.customer_id = c.id', 'left')
            ->where('c.status', '<>', 3)
            ->where('c.id', '=', $user->id)
            ->find();
        $activity = Db::name('saas_xcx_award')
            ->where('beg_time', '<', $current_time)
            ->where('end_time', '>', $current_time-86400)
            ->where('deleted', '=', 0)
            ->select();
        return $this->success('用户信息', ['userinfo' => $userinfo, 'awards' => $activity]);
    }

    /**
     * 个人优惠券
     */
    public function getUserCoupon()
    {
        $user = $this->getUser();
        $status = $this->request->post('status');
        $time = time() - 86400;
        $coupons = Db::name('saas_xcx_coupon_detail sxad')
            ->join('saas_xcx_coupon sxa', 'sxa.id = sxad.aid');
        $coupons = $coupons->where('sxad.cid', '=', $user->id)
            ->where('sxa.deleted', '=', 0)
            ->where('sxad.del', '=', 0)
            ->where('sxad.is_can_use', '=', 1)
            ->field('sxad.id, sxa.title, sxa.beg_time, sxa.end_time, sxa.rule_detail, sxa.activity_theme_img, sxad.amount, sxad.status, sxa.activity_rule')
            ->select();
        $data1 = [];
        $data2 = [];
        $data3 = [];
        foreach ($coupons as $key => $value) {
            $value['beg_time'] = date('Y-m-d', $value['beg_time']);
            $value['end_time'] = date('Y-m-d', $value['end_time']);
            if ($status == 0 && $value['status'] == 0) {
                array_push($data1, $value);
            } elseif ($status == 1 && $value['status'] == 1) {
                array_push($data2, $value);
            } else {
                $rule = json_decode($value['activity_rule'], 1);
                if ($rule['term_of_validity'] == 2 && strtotime($value['end_time']) < $time) {
                    array_push($data3, $value);
                }
            }
        }
        if ($status == 0) {
            return $this->success('未使用优惠券', $data1);
        } elseif ($status == 1) {
            return $this->success('已使用优惠券', $data2);
        } else {
            return $this->success('过期优惠券', $data3);
        }

    }

    /**
     * 用户分享结果反
     */
    public function userShareeRecode()
    {
        $user = $this->getUser();
        $share_recode = Db::name('saas_xcx_award_detail')
            ->where('cid', '=', $user->id)
            ->where('del', '=', 0)
            ->where('type', '=', 2)
            ->select();
        return $this->success('分享记录', $share_recode);
    }

    /**
     * 用户领取红包
     */
    public function getReward()
    {
        $user = $this->getUser();
        $aid = $this->request->post('activity_id');
        $user_share_detail = Db::name('saas_xcx_award_detail')
            ->where('cid', '=', $user->id)
            ->where('aid', '=', $aid)
            ->find();
        if ($user_share_detail['is_can_use'] == 0) {
            return $this->error('该奖励暂不能领取');
        }
        if ($user_share_detail['status'] == 1) {
            return $this->error('该奖励已领取，勿重复操作');
        }
        //领取操作
        Db::startTrans();
        try {
            $data = [
                'customer_id' => $user->id,
                'integration' => 0,
                'amount' => $user_share_detail['amount'],
                'remark' => '参加活动-'.$user_share_detail['a_titile'],
                'created_at' => time(),
                'period_date' => 1
            ];
            Db::name('saas_wallet_log')->insert($data);
            $wallet = Db::name('saas_wallet')->where('customer_id', '=', $user->id)->find();
            Db::name('saas_wallet')->where('customer_id', '=', $user->id)->update(['amount' => $wallet['amount']]);
            Db::commit();
            return $this->success('领取成功');
        } catch (Exception $e) {
            Db::rollback();
            return $this->error('领取失败');
        }
    }



}