<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\wechat\controller;

use think\Controller;
use app\wechat\service\FansService;
use app\wechat\service\TagsService;
use controller\BasicAdmin;
use service\LogService;
use service\ToolsService;
use service\WechatService;
use think\Db;


/**
 * User wbj726@gmail.com
 * Date wangbaojie
 * 微信公共方法,将客户微信openid和客户绑定一起,一次授权,永久免登录
 */
class CustomerRegister extends Controller
{
    /**
     * 参数1 wxopenid
     * 参数2 回调页面  绑定完成后返回的页面
     * 验证这个登录的微信号在客户表中有无openid
     * 如果有,则返回客户信息
     * 如果没有,跳转绑定页面
     */
    public static function checkRegister($openId, $call_back)
    {
        // $cphone = session('introducer_customer');
        session('openid', $openId);
        $customer = Db::name('saas_customer')->where('wxopenid', $openId)->find();
        if (empty($customer)) {
            if (!empty(session('introducer_customer'))) {
                $parent_info = session('introducer_customer');
                $parent_tel = $parent_info['parent_tel'];
                Db::name('saas_customer')->where('parent_tel', $parent_tel)->update(['wxopenid' => $openId]);
                return true;
            } else {
                $url = url('/wechat/CustomerRegister/register') . "?call_back=" . $call_back;
                header("location:$url");
            }
        }
        return $customer;
    }

    /**
     * @param $openId
     * 客户微信注册
     */
    public function register()
    {
        if ($this->request->isPost()) {
            $openid = $this->request->post('openid');
            $parent_tel = $this->request->post('parent_tel');
            if (empty($openid)) {
                return json(['code' => -1, 'msg' => '非法操作']);
            }
            if (empty($parent_tel)) {
                return json(['code' => -1, 'msg' => '请输入电话号']);
            }
            $customer = Db::name('saas_customer')->where('parent_tel', $parent_tel)->find();
            if (empty($customer)) {
                $data['parent_name'] = '微信双十一';
                $data['wxopenid'] = $openid;
                $data['parent_tel'] = $parent_tel;
                $data['branch'] = 2;
                $data['first_branch'] = 2;
                $data['source'] = 1;
                $data['created_at'] = time();
                Db::name('saas_customer')->insert($data);
                return json(['code' => 1, 'msg' => '参与成功']);
            } elseif (!empty($customer['wxopenid'])) {
                return json(['code' => -1, 'msg' => '该客户已经绑定了微信']);
            } else {
                $res = Db::name('saas_customer')->where('parent_tel', $parent_tel)->update(['wxopenid' => $openid]);
                if ($res) {
                    return json(['code' => 1, 'msg' => '绑定成功']);
                } else {
                    return json(['code' => -1, 'msg' => '绑定失败,请稍后重试']);
                }
            }
        }
        $openId = session('openid');
        $call_back = $this->request->get('call_back');
        return $this->fetch('', ['openid' => $openId, 'call_back' => $call_back]);
    }

    public function wechatBindMobile()
    {
        $url = "http://" . $_SERVER['HTTP_HOST'] . "/wechat/CustomerRegister/wechatBindMobile";
        $userInfo = WechatService::webOauth($url);
        $openId = $userInfo['openid'];
        //查询用户信息
        $call_back = url('wechatBindMobile');
        if ($this->request->isPost()) {
            $openid = $this->request->post('openid');
            $parent_tel = $this->request->post('parent_tel');
            if (empty($openid)) {
                return json(['code' => -1, 'msg' => '非法操作']);
            }
            if (empty($parent_tel)) {
                return json(['code' => -1, 'msg' => '请输入电话号']);
            }
            $customer = Db::name('saas_customer')->where('parent_tel', trim($parent_tel))->find();
            if (empty($customer)) {
                $data['wxopenid'] = $openid;
                $data['parent_tel'] = $parent_tel;
                $data['source'] = 1;
                $data['created_at'] = time();
                Db::name('saas_customer')->insert($data);
                return json(['code' => 1, 'msg' => '您已成为了EA新成员']);
            } elseif (!empty($customer['wxopenid'])) {
                return json(['code' => -1, 'msg' => '该账号已经关联了微信']);
            } else {
                $res = Db::name('saas_customer')->where('parent_tel', $parent_tel)->update(['wxopenid' => $openid]);
                if ($res) {
                    return json(['code' => 1, 'msg' => '账号与微信关联成功']);
                } else {
                    return json(['code' => -1, 'msg' => '关联失败,请稍后重试']);
                }
            }
        }
        return $this->fetch('register', ['openid' => $openId, 'call_back' => $call_back]);
    }

}