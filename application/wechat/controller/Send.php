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
            ->field('c.title as class_name,h.*, w.*')
            ->join('saas_class c', 'h.class_id=c.id', 'left')
            ->join('saas_courseware w', 'w.id=h.courseware_id', 'left')
            ->where('h.id', '=', $id)
            ->find();
        $homework['courseware_words'] = json_decode($homework['courseware_words'], 1);
        $homework['courseware_sentence'] = json_decode($homework['courseware_sentence'], 1);
        return $this->fetch('homework', ['homework' => $homework]);
    }
}