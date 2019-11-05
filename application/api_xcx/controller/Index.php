<?php


namespace app\api_xcx\controller;


use controller\BasicXcx;
use think\Db;

class Index extends BasicXcx
{
    public $table = "saas_content";

    /**
     * 小程序首页展示信息
     */
    public function index()
    {
        //首页banner轮播图
        $banner = Db::name($this->table)->field('title,thumb')->where('status', '=', 99)->where('catid', '=', '81')->select();
        //导航
        $nav = Db::name($this->table)->field('title, id')->where('status', '=', 99)->where('catid', '=', '82')->select();
        //体系课程图片
        $course_system = Db::name($this->table)->field('title, thumb')->where('status', '=', 99)->where('catid', '=', '83')->select();
        //精品课图
        $boutique = Db::name($this->table)->field('title, thumb')->where('status', '=', 99)->where('catid', '=', '84')->select();
        //合作院校
        $cooperative_colleges = Db::name($this->table)->field('title, thumb')->where('status', '=', 99)->where('catid', '=', '85')->select();
        //首页弹出图片
        $act = Db::name($this->table)->field('title, thumb')->where('status', '=', 99)->where('catid', '=', '77')->order('id', 'desc')->find();
        //活动弹出（优惠券，领红包系别入口）
        $data = [
            'banner' => $banner,
            'navigate' => $nav,
            'course_system' => $course_system,
            'boutique' => $boutique,
            'cooperativer' => $cooperative_colleges
        ];
        return $this->success('数据获取成功', $data);
    }
}