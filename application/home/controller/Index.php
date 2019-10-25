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
 * 官网（pc端）
 * Class Index
 * @package app\home\controller
 */
class Index extends controller
{
    public $table = "saas_content";

    /**
     * 空操作
     * @return mixed
     */
    public function _empty()
    {
        return $this->error_404();
    }

    /**
     * 构造方法,先查询头部,底部,并加载
     * Index constructor.
     * @param App|null $app
     */
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        //左侧导航栏
        $left_pic = Db::name($this->table)
            ->where('catid', '=', '18')
            ->where('status', '=', '99')
            ->column('id,thumb');
        //头部课程
        $header_course = Db::name($this->table)
            ->field('title,thumb,link')
            ->where('catid', '=', '39')
            ->where('status', '=', '99')
            ->select();
        //头部留学
        $header_study = Db::name($this->table)
            ->field('title,thumb,link')
            ->where('catid', '=', '40')
            ->where('status', '=', '99')
            ->select();
        //头部公馆简介
        $header_residence = Db::name($this->table)
            ->field('title,thumb,link')
            ->where('catid', '=', '43')
            ->where('status', '=', '99')
            ->select();
        //底部
        $ours = Db::name($this->table)
            ->field('content,title')
            ->where('id', '=', '27')
            ->find();
        $this->assign('header_residence', $header_residence);
        $this->assign('header_study', $header_study);
        $this->assign('header_course', $header_course);
        $this->assign('ours', $ours['content']);
        $this->assign('nav', $ours['title']);
        $this->assign('left_pic', $left_pic);
    }


    public function index()
    {
        $title = 'EA国际教育-一站式少儿英语教育-企业官网';
        //首页轮播图
        $banner = Db::name($this->table)
            ->field('thumb')
            ->where('catid', '=', '8')
            ->select();
        //首页课程展示
        $content = Db::name($this->table)
            ->field('content,title,thumb,link')
            ->where('catid', '=', '12')
            ->select();
        //老师列表
        $teachers = Db::name($this->table)
            ->field('thumb,content')
            ->where('catid', '=', '4')
            ->select();
        //首页轮播图导航栏文字
        $nav_text = Db::name($this->table)
            ->field('content')
            ->where('id', '=', '45')
            ->find();
        $this->assign('nav_text', $nav_text['content']);
        $this->assign('teachers', $teachers);
        $this->assign('content', $content);
        $this->assign('title', $title);
        $this->assign('banner1', $banner);
        return $this->fetch();
    }



    /**
     * esl 单页模式
     * @return mixed
     */
    public function course()
    {
        return $this->fetch('');
    }

    /**
     * esl 单页模式
     * @return mixed
     */
    public function esl()
    {
        return $this->page(44);
    }

    /**
     * 课程 ltip 单页模式
     * @return mixed
     */
    public function ltip()
    {
        return $this->page(66);
    }

    /**
     * 课程 PBL 单页
     * @return mixed
     */
    public function PBL()
    {
        return $this->page(67);
    }

    /**
     * 案例  列表模式
     * @return mixed
     */
    public function cases()
    {
        return $this->listModel(32, 34);
    }

    /**
     * 低龄留学 一站式服务 单页
     * @return mixed
     */
    public function studyAbroad()
    {
        return $this->page(41);
    }

    /**
     * 低龄留学 国际名校直通车 单页
     * @return mixed
     */
    public function internationalSchool()
    {
        return $this->page(68);
    }

    /**
     * 低龄留学 短期游学 单页
     * @return mixed
     */
    public function shortTerm()
    {
        return $this->page(69);
    }

    /**
     * 低龄留学 出国语言培训 单页
     * @return mixed
     */
    public function languageTraining()
    {
        return $this->page(70);
    }


    /**
     * 公馆简介
     * @return mixed
     */
    public function about()
    {
        return $this->page(42);
    }

    /**
     * 老师列表 列表
     * @return mixed
     */
    public function teacherList()
    {
        return $this->listModel(47, 46);
    }

    /**
     * 联系我们 单页
     * @return mixed
     */
    public function usMe()
    {
        return $this->page(71);
    }

    /**
     * 招商加盟 单页
     * @return mixed
     */
    public function merchantsJoin()
    {
        return $this->page(47);
    }

    /**
     * 最新资讯 列表
     * @return mixed
     */
    public function latestNews()
    {
        return $this->listModel(35, 36);
    }

    /**
     * 公共列表模式
     * param $banner_catid  列表banner catid
     * param $content_catid  内容catid
     * @return mixed
     */
    public function listModel($banner_catid, $content_catid)
    {
        $banner_catid = intval($banner_catid);
        $content_catid = intval($content_catid);
        if (empty($banner_catid) || empty($content_catid)) {
            return $this->error_404();
        }
        //查banner图
        $banner = Db::name($this->table)->where('catid', $banner_catid)->field('thumb')->find();
        //查内容 列表模式
        $content = Db::name($this->table)->where('catid', $content_catid)->field('id,title,thumb')->select();
        //dump($content);die;
        return $this->fetch('page_list', ['banner' => $banner['thumb'], 'content' => $content]);
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
        $banner = Db::name($this->table)->where('catid', 35)->field('thumb')->find();
        $content = Db::name($this->table)->where('id', $id)->field('title,content')->find();
        if (empty($content['content'])) {
            return $this->error_404();
        }
        return $this->fetch('', ['banner' => $banner['thumb'], 'content' => $content]);
    }

    /**
     * 单页面
     * @param $id
     * @return mixed
     */
    public function page($id)
    {
        //查banner图
        $banner = Db::name($this->table)->where('id', $id)->field('thumb')->find();
        //查内容
        $content = Db::name($this->table)->where('id', $id)->field('content')->find();
        if (empty($content['content'])) {
            return $this->error_404();
        }
        return $this->fetch('page_content', ['banner' => $banner['thumb'], 'content' => $content['content']]);
    }

    /**
     * 错误页面
     * @return mixed
     */
    public function error_404()
    {
        return $this->fetch('error_404');
    }

}
