<?php
/**
 * User: Jasmine2
 * Date: 2018/7/11 18:13
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\common\model;


use app\courses\controller\Courseware;
use think\Db;
use think\Model;

/**
 * Class Course
 * @package app\common\model
 * @author Jasmine2
 * @property Courseware $coursewares
 */
class Course extends Model
{
    public $table = 'saas_courses';

    /**
     * @var Courseware
     * @return \think\model\relation\HasMany
     * @author Jasmine2
     * 课件
     */
    public function coursewares()
    {
        return $this->hasMany(SaasCourseware::class, 'course_id', 'id');
    }
    public function test()
    {
        $db = Db::name('saas_courseware')
            ->field('*')
            ->where('course_id', '=', $this->id)
            ->order('created_at desc');
        return $db->select();
    }

    /**
     * @author Jasmine2
     * @throws
     * 教材
     */
    public function textbooks()
    {
        $db = Db::name('saas_course_textbook')->alias('ct')
            ->join('saas_textbook t', 'ct.textbook_id=t.id', 'left')
            ->field('t.*')
            ->where('ct.course_id', '=', $this->id)
            ->where('t.status', '=', 1)
            ->order('ct.created_at desc');
        return $db->select();
    }

    /**
     * @return array|\PDOStatement|string|\think\Collection
     * 作业
     */
    public function homework()
    {
        $db = Db::name('saas_course_homework')
            ->where('course_id','=',$this->id)
            ->order('created_at desc');
        return $db->select();
    }

}