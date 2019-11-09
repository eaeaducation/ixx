<?php


namespace app\api_xcx\controller;


use controller\BasicXcx;
use think\Db;

class Home extends BasicXcx
{
    public $table = "saas_content";

    /**
     * 小程序首页展示信息
     */
    public function index()
    {
        //首页banner轮播图
        $banner = Db::name($this->table)->field('title,thumb,link')->where('status', '=', 99)->where('catid', '=', '81')->select();
        //导航
        $nav = Db::name($this->table)->field('title, id, link')->where('status', '=', 99)->where('catid', '=', '82')->select();
        //体系课程图片
        $course_system = Db::name($this->table)->field('title, thumb')->where('status', '=', 99)->where('catid', '=', '83')->select();
        //精品课图
        $boutique = Db::name($this->table)->field('title, thumb, link')->where('status', '=', 99)->where('catid', '=', '84')->select();
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
            'cooperativer' => $cooperative_colleges,
            'activity' => $act
        ];
        return $this->success('数据获取成功', $data);
    }


    /**
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function introduce()
    {
        $introduce = Db::name('saas_content')->field('title, content')->where('catid', '=', '86')->find();
        $introduce['content'] = base64_encode($introduce['content']);
        return $this->success('', $introduce);
    }

    /**
     * 低龄留学
     */
    public function youngAbroad()
    {
        $abroad = Db::name('saas_content')->field('title, content')->where('catid', '=', '90')->find();
        $abroad['content'] = base64_encode($abroad['content']);
        return $this->success('', $abroad);
    }

    public function joinEa()
    {
        $join = Db::name('saas_content')->field('title, content')->where('catid', '=', '91')->find();
        $join['content'] = base64_encode($join['content']);
        return $this->success('', $join);
    }
}