<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/11 0011
 * Time: 16:58
 */

namespace app\wechat\controller;


use think\Controller;
use think\Db;

class Send extends Controller
{
    /**
     * @return mixed
     * 作业微信页面
     */
    public function wechat_homework()
    {
        $id = $this->request->request('id');
        $homework = Db::name('saas_course_homework')
            ->alias('h')
            ->field('c.title as class_name,h.*')
            ->join('saas_class c', 'h.class_id=c.id', 'left')
            ->where('h.id', '=', $id)
            ->find();
        return $this->fetch('homework', ['homework' => $homework]);
    }
}