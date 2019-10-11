<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/29
 * Time: 10:00
 */

namespace app\home\controller;

use think\App;
use think\Controller;
use think\Db;

/**
 * EA官网移动端
 * Class Mobile
 * @package app\home\controller
 */
class Mobile extends Controller
{
    public $table = 'saas_content';

    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $logo = Db::name($this->table)
            ->field('thumb')
            ->where('id', '=', '74')
            ->where('status', '=', '99')
            ->find();
        $title = Db::name($this->table)
            ->field('content')
            ->where('id', '=', '75')
            ->where('status', '=', '99')
            ->find();
        $this->assign('logo', $logo['thumb']);
        $this->assign('title', $title['content']);
    }

    public function index()
    {
        $banner = Db::name($this->table)
            ->where('catid', '=', '51')
            ->where('status', '=', '99')
            ->select();
        $this->assign('banner', $banner);
        return $this->fetch();
    }

    /**
     * 关于我们
     * @return mixed
     */
    public function about_us()
    {
        return $this->page(79);
    }

    /**
     * 授课环境
     * @return mixed
     */
    public function teaching_environment()
    {
        return $this->page(80);
    }

    /**
     * 课程介绍
     * @return mixed
     */
    public function course_introduce()
    {
        return $this->page(81);
    }

    /**
     * 招商加盟
     * @return mixed
     */
    public function investment()
    {
        return $this->page(82);
    }

    /**
     * 经典案例
     * @return mixed
     */
    public function classic_case()
    {
        return $this->listModel(56);
    }

    /**
     * 资讯动态
     * @return mixed
     */
    public function news()
    {
        return $this->listModel(57);
    }

    public function contact_us()
    {
        return $this->fetch();
    }

    /**
     * 单页面
     * @param $id
     * @return mixed
     */
    public function page($id)
    {
        //查banner图
        $title = Db::name($this->table)->where('id', $id)->field('title')->find();
        //查内容
        $content = Db::name($this->table)->where('id', $id)->field('content')->find();
        if (empty($content['content'])) {
            return $this->error_404();
        }
        return $this->fetch('page_content', ['title' => $title['title'], 'content' => $content['content']]);
    }

    /**
     * 公共,内页详情页
     * get 传内容页的id
     * @return mixed
     */
    public function innerPage()
    {
        $id = $this->request->get('id');
        if (empty($id)) {
            return $this->error_404();
        }
        $content = Db::name($this->table)->where('id', $id)->field('title,content')->find();
        if (empty($content['content'])) {
            return $this->error_404();
        }
        return $this->fetch('', ['content' => $content]);
    }

    /**
     * 公共列表模式
     * param $banner_catid  列表banner catid
     * param $content_catid  内容catid
     * @return mixed
     */
    public function listModel($content_catid)
    {
        $content_catid = intval($content_catid);
        if (empty($content_catid)) {
            return $this->error_404();
        }
        //查内容 列表模式
        $content = Db::name($this->table)->where('catid', $content_catid)->field('id,title,thumb')->select();
        //dump($content);die;
        return $this->fetch('page_list', ['content' => $content]);
    }

}

