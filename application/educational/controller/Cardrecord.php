<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/9
 * Time: 9:18
 */

namespace app\educational\controller;

use controller\BasicAdmin;
use think\Db;
use service\LogService;

class Cardrecord extends BasicAdmin
{
    public function student()
    {
        $title = '学员打卡记录';
        $this->assign('title', $title);
        $start = strtotime(date('Y-m-d'));
        $end = $start + 86400;
        $db = Db::name('saas_course_log')->alias('l');
        $db->join('saas_customer c', 'c.id = l.student_id', 'left')
            ->join('saas_courses_detail d', 'l.class_course_no = d.class_course_no', 'left')
            ->join('saas_courses co', 'l.course_id = co.id', 'left')
            ->where('l.student_id', '<>', null)
            ->where('l.course_id', '<>', null)
            ->where('c.status', '<>', 3)
            ->where('l.status', '<>', 5);
        $countDb = Db::name('saas_course_log')->alias('l')
            ->join('saas_customer c', 'c.id = l.student_id', 'left')
            ->join('saas_courses_detail d', 'l.class_course_no = d.class_course_no', 'left')
            ->join('saas_courses co', 'l.course_id = co.id', 'left')
            ->where('l.student_id', '<>', null)
            ->where('l.course_id', '<>', null)
            ->where('c.status', '<>', 3)
            ->where('l.status', '<>', 5);

        $get = $this->request->get();
        if (isset($get['time_range']) && $get['time_range'] != '') {
            list($s_time, $e_time) = explode(' ~ ', $get['time_range']);
            $s_time = strtotime($s_time);
            $e_time = strtotime($e_time) + 86400;
            $db->whereBetween('l.created_at', [$s_time, $e_time]);
            $countDb->whereBetween('l.created_at', [$s_time, $e_time]);
        } else {
            $db->whereBetween('l.created_at', [$start, $end]);
            $countDb->whereBetween('l.created_at', [$start, $end]);
        }
        if ($this->user['id'] != 10000 && !in_array($this->user['authorize'], [1, 3, 4, 22])) {
            $branch = $this->user['employee']['department'];
            $db->where('c.branch', '=', $branch);
            $countDb->where('c.branch', '=', $branch);
        }
        if (isset($get['name']) && $get['name'] != '') {
            $db->where('c.name', 'like', '%' . $get['name'] . '%');
            $countDb->where('c.name', 'like', '%' . $get['name'] . '%');
        }

        if (isset($get['branch']) && $get['branch'] != '') {
            $db->where('c.branch', '=', $get['branch']);
            $countDb->where('c.branch', '=', $get['branch']);
        }

        if (isset($get['class_id']) && $get['class_id'] != '') {
            $db->where('d.class_id', '=', $get['class_id']);
            $countDb->where('d.class_id', '=', $get['class_id']);
        }
        if (isset($get['course']) && $get['course'] != '') {
            $db->where('co.title', 'like', '%' . $get['course'] . '%');
            $countDb->where('co.title', 'like', '%' . $get['name'] . '%');
        }
        $count = $countDb->sum('l.course_hour');
        $this->assign('count', $count);
        $db->field('l.course_id, l.created_at, l.icard, c.name, c.gender, l.course_hour, l.status, d.class_id, l.id, co.title')
            ->order('l.id desc');
        return $this->_list($db);
    }

    /**
     * @return array|string
     * 班级打卡记录
     */
    public function class_record()
    {
        $get = $this->request->get();

        $this->assign('class_id', $get['class_id']);

        $db = Db::name('saas_course_log')->alias('l');
        $db->join('saas_customer c', 'c.id = l.student_id', 'left')
            ->join('saas_courses_detail d', 'l.class_course_no = d.class_course_no', 'left')
            ->where('l.student_id', '<>', null)
            ->where('l.course_id', '<>', null)
            ->where('c.status', '<>', 3)
            ->where('l.status', '<>', 5)
            ->where('d.class_id', '=', $get['class_id'])
            ->field('l.course_id, l.created_at, l.icard, c.name, c.gender, l.course_hour, l.status, l.id')
            ->order('l.id desc');

        if (isset($get['time_range']) && $get['time_range'] != '') {
            list($s_time, $e_time) = explode(' ~ ', $get['time_range']);
            $s_time = strtotime($s_time);
            $e_time = strtotime($e_time) + 86400;
            $db->whereBetween('l.created_at', [$s_time, $e_time]);
        }

        if (isset($get['name']) && $get['name'] != '') {
            $db->where('c.name', 'like', '%' . $get['name'] . '%');
        }

        return $this->_list($db);
    }

    /**
     * 删除记录
     */
    public function del()
    {
        $id = $this->request->get('id');
        if (isset($id) && !empty($id)) {
            $res = Db::name('saas_course_log')->where('id', '=', $id)->update(['status' => 5]);
            if ($res) {
                LogService::write('打卡记录', '删除了课时明细');
                $this->success("删除成功!", '');
            } else {
                $this->error("打卡记录删除失败, 请稍候再试!");
            }
        }
    }
}