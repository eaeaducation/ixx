<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/9 0009
 * Time: 11:11
 */

namespace app\shop\controller;


use think\Controller;
use think\Db;

class Study extends Controller
{
    /*
     * 低龄留学
     */
    public function index()
    {
        $img = Db::name('saas_content')->where('catid', '=', '67')->field('thumb')->find();
        $this->assign('image', $img);
        return view();
    }

    public function find()
    {
        $ban = Db::name('saas_content')->where('catid', '=', '70')->field('thumb')->select();
        $content = Db::name('saas_content')
            ->where('catid', '=', '71')
            ->field('thumb, title, link, id')
            ->select();
        $this->assign('banner', $ban);
        $this->assign('content', $content);
        return view();
    }

    public function detail()
    {
        $get = $this->request->get();
        $row = Db::name('saas_content')->where('id', '=', $get['id'])->value('content');
        return view('', ['row' => $row]);
    }
}

