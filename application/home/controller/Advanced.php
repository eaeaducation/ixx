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


namespace app\home\controller;

use service\DataService;
use think\App;
use think\Controller;
use think\Db;
use think\db\Query;
use think\facade\Config;
use think\facade\View;


/**
 * 高级客户入驻
 * Class Index
 * @package app\home\controller
 */
class Advanced extends controller
{
    public function index()
    {
        $exist = Db::query('show tables like "saas_advanced"');
        if (empty($exist)) {
            $url = url('index/index');
            header("Location: $url");
        } else {
            return $this->fetch();
        }
    }

    /**
     * 前台注册
     */
    public function register()
    {
        $post = $this->request->post();
        $tel = $post['tel'];
        //验证手机号是否已经存在
        $count = Db::name('saas_advanced')->where('tel', $tel)->count(1);
        if ($count > 0) {
            return json(['title' => "抱歉", 'msg' => '未获得5折机会', 'txt' => "您的这个手机号已经领取或5折机会了"]);
        }
        //入库
        $post['created_at'] = time();
        $res = Db::name('saas_advanced')->insert($post);
        if ($res) {
            return json(['title' => "恭喜您", 'msg' => '成功获得5折机会', 'txt' => "我们将会在24小时内联系您,请您稍后"]);
        } else {
            return json(['title' => "抱歉", 'msg' => '未获得5折机会', 'txt' => "请稍后重新再试"]);
        }


    }
}