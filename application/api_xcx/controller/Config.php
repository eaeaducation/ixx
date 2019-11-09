<?php


namespace app\api_xcx\controller;


use controller\BasicXcx;
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
}