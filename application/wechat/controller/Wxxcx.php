<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17
 * Time: 10:29
 */

namespace app\wechat\controller;

use think\Controller;
use think\facade\Cache;
use think\facade\Log;
use service\HttpService;
use think\Db;

class Wxxcx extends Controller
{
    public function mine()
    {
        $nickname = $this->request->get('name', '');
        Log::error($nickname);
        Log::error(Cache::has('wxuser' . $nickname));
        if (Cache::has('wxuser' . $nickname)) {
            $userinfo = Cache::get('wxuser' . $nickname);
            Log::error($userinfo);
        } else {
            $wechat_userinfo = $this->request->request('userinfo', '');
            Log::error($wechat_userinfo);
            $userinfo = json_decode($wechat_userinfo, 1);
            Cache::set('wxuser' . $nickname, $userinfo, 3600);
        }
        Log::error($userinfo);
        $this->assign('user', $userinfo);
        return $this->fetch();
    }

    public function adlist()
    {
        return $this->fetch();
    }

    public function adpage()
    {
        return $this->fetch();
    }

    public function adpagetwo()
    {
        return $this->fetch();
    }

    public function adpagethree()
    {
        return $this->fetch();
    }

    public function adpagefour()
    {
        return $this->fetch();
    }

    /**
     * @return mixed
     * 已更换为下面的方法，等小程序审核过了删除
     */
    public function rollpage()
    {
        $cus = Db::name('saas_customer')->field('parent_tel')->where('source', '=', '3')->limit(20)->order('created_at desc')->select();
        $this->assign('cus', $cus);
        return $this->fetch();
    }

    public function jump_page()
    {
        return $this->fetch();
    }
}