<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13 0013
 * Time: 13:58
 */

namespace app\shop\controller;


use think\Controller;
use think\Db;

class Index extends Controller
{
    public $table = "saas_content";

    public function index()
    {
        $get = $this->request->get();
        session('code_openid', isset($get['code'])?$get['code']:'');
        //banner图
        $banner = Db::name($this->table)->field('thumb,link')->where('catid', '=', '61')->select();
        //导航
        $nav = Db::name($this->table)->field('content, link, id')->where('catid', '=', '62')->select();
        //体系课程图片
        $system = Db::name($this->table)->field('thumb')->where('catid', '=', '63')->select();
        //课程第一行
        $course1 = Db::name($this->table)->field('thumb, link')->where('catid', '=', '64')->select();
        //课程第一行
        $course2 = Db::name($this->table)->field('thumb, link')->where('catid', '=', '65')->select();
        //合作院校
        $school = Db::name($this->table)->field('thumb')->where('catid', '=', '68')->find();
        //精品课图
        $boutique = Db::name($this->table)->field('thumb, link')->where('catid', '=', '72')->select();
        //首页弹出图片
        $act = Db::name($this->table)->field('thumb')->where('catid', '=', '77')->find();
        $this->assign('banner', $banner);
        $this->assign('nav', $nav);
        $this->assign('sys', $system);
        $this->assign('course1', $course1);
        $this->assign('course2', $course2);
        $this->assign('school', $school);
        $this->assign('boutique', $boutique);
        $this->assign('act', $act);
        return view();
    }

    public function introduce()
    {
        $img = Db::name('saas_content')->where('catid', '=', '79')->field('thumb')->find();
        $this->assign('image', $img);
        return view();
    }
}