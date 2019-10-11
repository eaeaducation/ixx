<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/4
 * Time: 14:26
 */

namespace app\educational\controller;

use controller\BasicAdmin;
use think\Db;
use service\DataService;
use think\facade\Cache;
use service\LogService;

class Courses extends BasicAdmin
{
    public $table = "saas_courses_detail";

    public function classList()
    {
        $this->title = "课程表";
        $this->assign('title', $this->title);
        return $this->fetch();
    }

    public function classApi()
    {
        $this->title = "课程表";
        $this->assign('title', $this->title);
        return $this->fetch();
    }


    public function addClass()
    {
        $this->title = '排课';
        return $this->_form($this->table, 'form');
    }

    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    public function del()
    {
        if (DataService::update($this->table)) {
            LogService::write('排课', '删除了排课信息');
            $this->success("排课删除成功!", '');
        }
        $this->error("排课删除成功, 请稍候再试!");
    }

    /**
     * 课表数据
     */
    public function classdata()
    {
        $get = $this->request->get();
//        //查询起始时间
//        $start_time = $get['start'];
//        //查询截止时间
//        $end_time = $get['end'];
        $db = Db::name($this->table)
            ->alias('s')
            ->join('saas_courses c', 'c.id=s.courses_id', 'right')
            ->join('saas_class r', 's.class_id=r.id')
            ->field('s.*,c.branch,r.begin_time')
            ->where('s.status', '<>', '3')
            ->where('c.status', '<>', '3')
            ->where('r.status', '<>', '3');
        //校区
        if ($this->user['id'] != 10000 && !in_array($this->user['authorize'], [1, 3, 4, 22])) {
            $branch = $this->user['employee']['department'];
            $db->where('c.branch', '=', $branch);
        }
        if (!empty($get['branch'])) {
            $db->where('c.branch', '=', $get['branch']);
        }
        if (!empty($get['cycle'])) {
            $db->where('s.cycle', '=', $get['cycle']);
        }
        if (!empty($get['class_id'])) {
            $db->where('s.class_id', '=', $get['class_id']);
        }
        if (!empty($get['teacher'])) {
            $db->where('s.teacher_id', '=', $get['teacher']);
        }
        $classAll = $db->select();
        $courses_data = [];
        foreach ($classAll as $k => $v) {
            //开课日期为开班日期
            //查开班日期
            $courses_start_time = $v['begin_time'];//开班日期
            switch ($v['cycle']) {
                case 5:     //单次课程,从开课日期开始 一天
                    $courses_data [] = [
                        'id' => $v['id'],
                        "allDay" => false,
                        'title' => get_employee_name($v['teacher_id']) . '-' . get_courses_title($v['courses_id']),
                        'start' => date('Y-m-d ', $courses_start_time) . trim($v['begin_time_each']),
                        'end' => date('Y-m-d ', $courses_start_time) . trim($v['end_time_each']),
                        "color" => "red",
                        'course' => get_courses_title($v['courses_id']),
                        'teacher' => get_employee_name($v['teacher_id']),
                        'class' => convert_class($v['class_id']),
                        'room' => get_room_title($v['room_id']),
                        'ta' => get_employee_name($v['ta_id']),
                    ];
                    break;
                case 1:
                    //每天都要上的课程  从当前日期起  往后先排2个月
                    $title = get_courses_title($v['courses_id']);//课程名称
                    $teacher = get_employee_name($v['teacher_id']);//老师名称
                    $class = convert_class($v['class_id']);//班级
                    $room = get_room_title($v['room_id']);//教室
                    $ta = get_employee_name($v['ta_id']);//助教
                    if ($courses_start_time > time()) {
                        for ($i = 0; $i < 60; $i++) {   //如果起始日期大于当前日期,从起始日期排 60天
                            $start_time = $courses_start_time + $i * 86400;
                            $week = date("w", $start_time);
                            $dbweek = explode(',', $v['week']);
                            if (in_array($week, $dbweek)) {
                                $courses_data [] = [
                                    'id' => $v['id'],
                                    "allDay" => false,
                                    'title' => $teacher . '-' . $title,
                                    'start' => date('Y-m-d ', $start_time) . trim($v['begin_time_each']),
                                    'end' => date('Y-m-d ', $start_time) . trim($v['end_time_each']),
                                    "color" => "purple",
                                    'course' => $title,
                                    'teacher' => $teacher,
                                    'class' => $class,
                                    'room' => $room,
                                    'ta' => $ta
                                ];
                            };
                        }
                    } else {
                        for ($i = 0; $i < 60; $i++) {  //如果起始日期小于当前日期,从当前日期起排60天
                            $start_time = time() + $i * 86400;
                            $week = date("w", $start_time);
                            $dbweek = explode(',', $v['week']);
                            if (in_array($week, $dbweek)) {
                                $courses_data [] = [
                                    'id' => $v['id'],
                                    "allDay" => false,
                                    'title' => $teacher . '-' . $title,
                                    'start' => date('Y-m-d ', $start_time) . trim($v['begin_time_each']),
                                    'end' => date('Y-m-d ', $start_time) . trim($v['end_time_each']),
                                    "color" => "purple",
                                    'course' => $title,
                                    'teacher' => $teacher,
                                    'class' => $class,
                                    'room' => $room,
                                    'ta' => $ta
                                ];
                            }
                        };
                    }
                    break;
                case 2:
                    $title = get_courses_title($v['courses_id']);
                    $teacher = get_employee_name($v['teacher_id']);
                    $class = convert_class($v['class_id']);//班级
                    $room = get_room_title($v['room_id']);//教室
                    $ta = get_employee_name($v['ta_id']);//助教
                    for ($i = 0; $i < 7; $i++) {  //周课 起始如期起往后排7天
                        $start_time = $courses_start_time + $i * 86400;
                        $week = date("w", $start_time);
                        $dbweek = explode(',', $v['week']);
                        if (in_array($week, $dbweek)) {
                            $courses_data [] = [
                                'id' => $v['id'],
                                "allDay" => false,
                                'title' => $teacher . '-' . $title,
                                'start' => date('Y-m-d ', $start_time) . trim($v['begin_time_each']),
                                'end' => date('Y-m-d ', $start_time) . trim($v['end_time_each']),
                                "color" => "blue",
                                'course' => $title,
                                'teacher' => $teacher,
                                'class' => $class,
                                'room' => $room,
                                'ta' => $ta
                            ];
                        }
                    };
                    break;
                case 3:
                    $title = get_courses_title($v['courses_id']);
                    $teacher = get_employee_name($v['teacher_id']);
                    $class = convert_class($v['class_id']);//班级
                    $room = get_room_title($v['room_id']);//教室
                    $ta = get_employee_name($v['ta_id']);//助教
                    for ($i = 0; $i < 30; $i++) {  //月课 起始如期起往后排30天
                        $start_time = $courses_start_time + $i * 86400;
                        $week = date("w", $start_time);
                        $dbweek = explode(',', $v['week']);
                        if (in_array($week, $dbweek)) {
                            $courses_data [] = [
                                'id' => $v['id'],
                                "allDay" => false,
                                'title' => $teacher . '-' . $title,
                                'start' => date('Y-m-d ', $start_time) . trim($v['begin_time_each']),
                                'end' => date('Y-m-d ', $start_time) . trim($v['end_time_each']),
                                "color" => "green",
                                'course' => $title,
                                'teacher' => $teacher,
                                'class' => $class,
                                'room' => $room,
                                'ta' => $ta
                            ];
                        }
                    };
                    break;
                case 4:
                    $title = get_courses_title($v['courses_id']);
                    $teacher = get_employee_name($v['teacher_id']);
                    $class = convert_class($v['class_id']);//班级
                    $room = get_room_title($v['room_id']);//教室
                    $ta = get_employee_name($v['ta_id']);//助教
                    for ($i = 0; $i < 14; $i++) {  //两周课 起始如期起往后排14天
                        $start_time = $courses_start_time + $i * 86400;
                        $week = date("w", $start_time);
                        $dbweek = explode(',', $v['week']);
                        if (in_array($week, $dbweek)) {
                            $courses_data [] = [
                                'id' => $v['id'],
                                "allDay" => false,
                                'title' => $teacher . '-' . $title,
                                'start' => date('Y-m-d ', $start_time) . trim($v['begin_time_each']),
                                'end' => date('Y-m-d ', $start_time) . trim($v['end_time_each']),
                                "color" => "orange",
                                'course' => $title,
                                'teacher' => $teacher,
                                'class' => $class,
                                'room' => $room,
                                'ta' => $ta
                            ];
                        }
                    };
                    break;
            }
        }
        return json($courses_data);
    }


    /**
     * 获取班级的课程,校区,校区老师等
     */
    public function getCourse()
    {
        if ($this->request->isPost()) {
            $class_id = $this->request->post('class_id');
            //获取班级信息
            $branch = Db::name('saas_class')
                ->where('status', '<>', 3)
                ->field('branch')
                ->where('id', '=', $class_id)
                ->find();
            $branch = $branch['branch'];//班级所属的校区
            $info = $this->getBranchInfo($branch);
            return json($info);
        }
    }

    public function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            if (empty($vo['class_id'])) {
                $this->error('请选择班级');
            }
            if (empty($vo['courses_id'])) {
                $this->error('请选择课程');
            }
            $class_time = Db::name('saas_courses_time')
                ->where('status', '<>', 3)
                ->where('id', '=', $vo['class_time'])
                ->field('start_time,end_time')
                ->find();
            $vo['begin_time_each'] = $class_time['start_time'];
            $vo['end_time_each'] = $class_time['end_time'];
            if ($vo['cycle'] == '5') {  //一次性课程的星期为开班当天的星期
                $courses_start_time = DB::name('saas_class')
                    ->field('begin_time')
                    ->where('status', '<>', 3)
                    ->where('id', '=', $vo['class_id'])
                    ->find();
                $courses_start_time = $courses_start_time['begin_time'];
                $vo['week'] = date('w', $courses_start_time);
            } elseif (empty($vo['week'])) {
                $this->error('请选择星期!');
            } else {
                $vo['week'] = implode(',', $vo['week']);
            }
            $vo['class_course_no'] = generate_class_courses_no();
            $vo['created_at'] = time();
            if (isset($vo['id'])) {
                LogService::write('排课', '编辑【' . convert_class($vo['class_id']) . '】班排【' . get_courses_title($vo['courses_id']) . '】课');
            } else {
                LogService::write('排课', '给【' . convert_class($vo['class_id']) . '】班排【' . get_courses_title($vo['courses_id']) . '】课');
            }
        }
    }

    public function _form_result($res, $data)
    {
        if ($res) {
            //给班级课程表插入不重复的课程信息
            set_class_courses($data['class_id']);
            $this->success('恭喜, 排课成功!', '');
        } else {
            $this->error("排课失败");
        }

    }

    /**
     * @return array|string
     * js修改排课时候的数据
     */
    public function getEditData()
    {
        //传过来排课ID
        $id = $this->request->post('id');
        //排课表 和课程表联合查 校区 取这个校区的助教等等信息
        $editCourses = Db::name('saas_courses_detail')->alias('s')
            ->join('saas_courses c', 'c.id=s.courses_id', 'left')
            ->field('s.*,c.branch')
            ->where('s.id', '=', $id)
            ->where('c.status', '<>', 3)
            ->where('s.status', '<>', 3)
            ->find();
        $time_id = Db::name('saas_courses_time')
            ->field('id')
            ->where('start_time', '=', $editCourses['begin_time_each'])
            ->where('end_time', '=', $editCourses['end_time_each'])
            ->where('status', '<>', 3)
            ->find();
        $editCourses['time_id'] = $time_id['id'];
        $editCourses['class_name'] = convert_class($editCourses['class_id']);
        $editCourses['course_name'] = get_courses_title($editCourses['courses_id']);

        //获取 这个校区下的班级 教室
        $branchid = $editCourses['branch'];
        $branch = $this->getBranchInfo($branchid);
        unset($branch['courses']);
        unset($editCourses['begin_time_each']);
        unset($editCourses['end_time_each']);
        $data = array_merge($branch, $editCourses);
        return json($data);

    }

    /**
     * @param $branch
     * @return array
     * 根据校区ID获取校区全部教师等数据
     */
    private function getBranchInfo($branch)
    {
        //根据班级所属校区获取该校区的教室
        $room = Db::name('saas_room')->field('id,code')->where('status', '<>', 3)->where('branch', '=', $branch)->select();
        //根据班级所属校区获取该校区的老师
        $employee = Db::name('system_user')->alias('u')
            ->join('saas_employee e', 'e.userid=u.id')
            ->field('e.name,e.id,u.authorize,e.is_teacher')
            ->where('e.status', '<>', '3')
            ->where('u.status', '<>', '3')
            ->whereOr('u.authorize', '=', 22)
            ->where('e.is_teacher', '=', 1)
            ->where('e.department', '=', $branch)
            ->select();
        $teacher = [];
        $ta = [];
        foreach ($employee as $v) {
            if ($v['is_teacher'] == 1) {
                $teacher[] = [
                    'name' => $v['name'],
                    'id' => $v['id']
                ];
            }
            //助教为 权限为15  暂时搁置
//            if ($v['authorize'] == 15) {
//                $ta[] = [
//                    'name' => $v['name'],
//                    'id' => $v['id']
//                ];
//            }

        }
        //助教为校区下的全部员工
        $ta = $teacher;
        //获取该班级所对应校区的全部课程
        $courses = Db::name('saas_courses')
            ->where('status', '<>', 3)
            ->where('branch', '=', $branch)
            ->select();
//            $course = Db::name('saas_class_course')->alias('s')
//                ->join("saas_courses c", 's.course_id=c.id')
//                ->field('c.*,s.class_id')
//                ->where('s.class_id', '=', $class_id)
//                ->select();
        $info = [
            'room' => $room,
            'teacher' => $teacher,
            'ta' => $ta,
            'courses' => $courses
        ];
        return $info;
    }

    /**
     * @return \think\response\Json
     * 通过校区ID获取 校区下的教室 老师 助教
     */
    public function getBranchData()
    {
        if ($this->request->isPost()) {
            $branch = $this->request->post('branch');
            $branchData = $this->getBranchInfo($branch);
            unset($branchData['courses']);
            return json($branchData);
        }
    }
}