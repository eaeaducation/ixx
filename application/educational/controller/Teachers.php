<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/6
 * Time: 11:25
 */

namespace app\educational\controller;

use controller\BasicAdmin;
use think\Db;
use think\Exception;
use think\facade\Log;
use service\DataService;
use service\LogService;

class Teachers extends BasicAdmin
{
    public $table = 'saas_employee';
    public $table2 = 'saas_course_teacher_log';

    /**
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Hzb
     */
    public function index()
    {
        $this->title = '教师列表';
        $branch = $this->request->get('branch', $this->user['employee']['department']);
        $keyword = $this->request->get('keyword', '');
        $time_range = $this->request->get('time_range', '');

        if (isset($time_range) && !empty($time_range)) {
            list($_start, $_end) = explode(' ~ ', $time_range);
            $month_start = strtotime($_start);
            $month_end = strtotime($_end) + 86400;
        } else {
            $month_start = mktime(0, 0, 0, date("m"), 1, date("Y"));
            $month_end = time() + 86400;
        }
        $this->assign('start', $month_start);
        $this->assign('end', $month_end);
        $this->assign('branch', $branch);
//        $db = Db::name($this->table)->alias('e')
//            ->join('(select sum(course_hour)as total_hour ,teacher_id, course_id, created_at from saas_course_teacher_log where is_deleted = 0 and is_ok = 1 and created_at between ' . $month_start . ' and ' . $month_end . '  GROUP BY teacher_id) c', 'e.id=c.teacher_id', 'left')
//            ->join('saas_courses s', 'c.course_id = s.id', 'left')
//            ->where('e.status', '=', 1)
//            ->where('e.is_teacher', '=', 1)
////            ->whereOr('s.branch', '=', $this->user['employee']['department'])
//            ->order('e.created_at desc')
//            ->field('e.name, e.english_name, e.id, e.mobile, c.total_hour, e.department');
//        empty($source) || $db->where('e.source', '=', $source);
//        empty($branch) || $db->where('e.department', '=', $branch);

        $db = Db::name('saas_course_teacher_log')->alias('tl')
            ->join('saas_courses s', 's.id = tl.course_id', 'left')
            ->join('saas_employee e', 'e.id = tl.teacher_id', 'left')
            ->where("tl.is_deleted = 0 and tl.is_ok = 1 and tl.created_at between $month_start and $month_end")
            ->field('sum(tl.course_hour)as total_hour, s.branch, tl.teacher_id, e.name, e.english_name,e.id, e.mobile,e.department')
            ->where('e.status', '=', 1)
            ->where('e.is_teacher', '=', 1);
        if (isset($branch) && !empty($branch)) {
            $db->where('s.branch', '=', $branch);
        }
        if (isset($keyword) && !empty($keyword)) {
            $db->where(['e.name|e.english_name' => $keyword]);
        }
        if (!in_array($this->user['authorize'], [1, 3, 4, 22])) {
//            $db->where('e.department', '=', $this->user['employee']['department']);
            $db->where('s.branch', '=', $this->user['employee']['department']);
        }
        $db->group('tl.teacher_id');
        return parent::_list($db, true);
    }

    /**
     * 课时明细
     * @return array|string
     */
    public function hour_detail()
    {
        if ($this->request->isGet()) {
            $get = $this->request->get();
            $this->title = '教师-【' . $get['name'] . '】 课时明细';
            $teacher_id = !empty($get['id']) ? $get['id'] : '';
//            $db = Db::name('saas_course_teacher_log')->alias('c')
//                ->join('(select d.class_course_no,d.begin_time_each,d.end_time_each,c.title,u.title as ctitle from saas_courses_detail as d LEFT JOIN saas_class c on d.class_id = c.id LEFT JOIN saas_courses u on d.courses_id = u.id) n', 'c.class_course_no = n.class_course_no', 'left')
//                ->where('c.is_ok', '=', '1')
//                ->where('c.teacher_id', '=', $teacher_id)
//                ->where('c.is_deleted', '=', '0')
//                ->whereBetween('c.created_at', [$get['start'], $get['end']]);
            $db = Db::name('saas_course_teacher_log')->alias('c')
                ->join('saas_courses s', 's.id = c.course_id', 'left')
                ->join('(select d.class_course_no,d.begin_time_each,d.end_time_each,c.title,u.title as ctitle from saas_courses_detail as d LEFT JOIN saas_class c on d.class_id = c.id LEFT JOIN saas_courses u on d.courses_id = u.id) n', 'c.class_course_no = n.class_course_no', 'left')
                ->where('c.is_ok', '=', '1')
                ->where('c.teacher_id', '=', $teacher_id)
                ->where('c.is_deleted', '=', '0')
                ->whereBetween('c.created_at', [$get['start'], $get['end']]);
            $course_num = Db::name('saas_course_teacher_log')->alias('c')
                ->join('saas_courses s', 's.id = c.course_id', 'left')
                ->join('(select d.class_course_no,d.begin_time_each,d.end_time_each,c.title,u.title as ctitle from saas_courses_detail as d LEFT JOIN saas_class c on d.class_id = c.id LEFT JOIN saas_courses u on d.courses_id = u.id) n', 'c.class_course_no = n.class_course_no', 'left')
                ->where('c.is_ok', '=', '1')
                ->where('c.teacher_id', '=', $teacher_id)
                ->where('c.is_deleted', '=', '0')
                ->whereBetween('c.created_at', [$get['start'], $get['end']]);
            if (isset($get['type']) && !empty($get['type'])) {
                $db->where('c.status', '=', $get['type']);
                $course_num->where('c.status', '=', $get['type']);
            }
            if ($get['branch']) {
                $course_num->where('s.branch', '=', $get['branch']);
                $db->where('s.branch', '=', $get['branch']);
            }
            $db->field('c.*,n.begin_time_each,n.end_time_each,n.title,n.ctitle')
                ->order('c.created_at desc');
            $course_num = $course_num->sum("c.course_hour");
            $this->assign('course_num', $course_num);
            return parent::_list($db, true);
        }
    }

    /**
     * 课时明细编辑
     * @return array|string
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $status = $this->request->post('status');
            if ($status == '') {
                $this->error('请选择类型！');
            }
        }
        return $this->_form('saas_course_teacher_log', 'form');
    }

    /**
     * 课时明细删除（软删除）
     */
    public function del()
    {
        $id = $this->request->get('id');
        if (isset($id) && !empty($id)) {
            $res = Db::name('saas_course_teacher_log')
                ->where('id', '=', $id)
                ->update(['is_deleted' => 1]);
            if ($res) {
                LogService::write('教师课时', '删除了课时明细');
                $this->success("课时明细删除成功!", '');
            }
        }
        $this->error("课时明细删除失败, 请稍候再试!");
    }

    /**
     * 上课学员信息
     */
    public function courseStudent()
    {
        $id = $this->request->get('id');
        $batch_code = Db::name('saas_course_teacher_log')->where('id', $id)->field('batch_code')->find();
        if (empty($batch_code)) {
            $this->error('本节课未记录学员上课信息');
        }
        $courseStudent = Db::name('saas_course_log')->where('batch_code', $batch_code['batch_code'])->select();
        return $this->fetch('', ['courseStudent' => $courseStudent]);

    }

    /**
     * 老师课时添加
     * @return mixed
     */
    public function addTeacherHours()
    {
        $this->title = '老师补录课时';
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post['course']) || empty($post['teacher_id']) || empty($post['teacher_hour'])) {
                $this->error('数据不正确');
            }
            $data = [
                'course_sub_id' => '',
                'course_id' => $post['course'],
                'teacher_id' => $post['teacher_id'],
                'status' => $post['type'],
                'created_at' => strtotime($post['date'] . date('H:i:s')),
                'class_course_no' => '',
                'batch_code' => '',
                'course_hour' => trim($post['teacher_hour']),
                'remark' => $post['remark']
            ];
            $res = Db::name('saas_course_teacher_log')->insert($data);
            LogService::write('教师课时', '老师【' . get_employee_name($post['teacher_id']) . '】课程【' . get_courses_title($post['course']) . '】新增' . $post['teacher_hour'] . '课时');
            if ($res) {
                $this->success('课时补录成功！', '');
            } else {
                $this->error('课时补录失败', '');
            }
        }
        return $this->fetch('addteacherhours');
    }

    /**
     * 获取科目
     */
    public function get_course()
    {
        $category_id = $this->request->post('category');
        $subject_id = $this->request->post('subject');
        $branch_id = $this->request->post('branch');
        $data = Db::name('saas_courses')
            ->field('id,title')
            ->where('category','=',$category_id)
            ->where('subject', '=', $subject_id)
            ->where('branch', '=', $branch_id)
            ->where('status','=','1')
            ->select();
        if ($data){
            $this->success('', '', $data);
        } else {
            $this->error();
        }
    }
}