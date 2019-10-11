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

namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;
use think\App;
use think\facade\Cache;

/**
 * 后台参数配置控制器
 * Class Config
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:05
 */
class Config extends BasicAdmin
{

    /**
     * 当前默认数据模型
     * @var string
     */
    public $table = 'SystemConfig';

    /**
     * 当前页面标题
     * @var string
     */
    public $title = '系统参数配置';

    /**
     * 显示系统常规配置
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        if ($this->request->isGet()) {
            return $this->fetch('', ['title' => $this->title]);
        }
        if ($this->request->isPost()) {
            foreach ($this->request->post() as $key => $vo) {
                sysconf($key, $vo);
            }
            LogService::write('系统管理', '系统参数配置成功');
            $this->success('系统参数配置成功！', '');
        }
    }

    /**
     * 文件存储配置
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function file()
    {
        $this->title = '文件存储配置';
        return $this->index();
    }

    /**
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author Jasmine2
     */
    public function sms()
    {
        $this->title = '短信接口配置';
        return $this->index();
    }

    /**
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author Jasmine2
     */
    public function sms_test()
    {
        $ret = send_sms($this->request->post('mobile'), '您的验证码为123456，打死也不要告诉其他人哦！');
        if ($ret == 1) {
            $this->success("短信发送成功!", '');
        } else {
            $this->error("短信发送失败");
        }
    }

    /**
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author Jasmine2
     * Ip 白名单设置
     */
    public function ip_white_list()
    {
        $this->title = 'IP白名单设置';
        return $this->index();
    }

    /**
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author Jasmine2
     */
    public function wechat_pay()
    {
        $this->title = '微信支付配置';
        return $this->index();
    }

    /**
     * @return string
     */
    public function wxxcx()
    {
        $this->title = '微信小程序配置';
        return $this->index();
    }

    public function clearCache()
    {
        Cache::clear();
        $this->success('删除缓存成功!', '');
    }

    /**
     * 话务中心配置
     */
    public function call_center()
    {
        $this->title = '话务中心配置';
        return $this->index();
    }

    public function send_mail()
    {
        $this->title = '邮件发送配置';
        return $this->index();
    }

    public function mail_test()
    {
        $res = sendmail($this->request->post('touser'),sysconf('site_name'),'测试邮件','你好，欢迎使用邮件发送系统');
        if ($res){
             $this->success('发送成功，请注意查收');
        }else{
             $this->error('发送失败');
        }
    }

    public function kufenqi()
    {
        $this->title = '库分期配置';
        return $this->index();
    }

    public function yibao()
    {
        $this->title = '易宝配置';
        return $this->index();
    }
}
