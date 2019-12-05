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
        $branch = $this->request->get('branch', '');
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
        $db = Db::name($this->table)->alias('e')
            ->join('(select sum(course_hour)as total_hour ,teacher_id, course_id, created_at from saas_course_teacher_log where is_deleted = 0 and is_ok = 1 and created_at between ' . $month_start . ' and ' . $month_end . '  GROUP BY teacher_id) c', 'e.id=c.teacher_id', 'left')
            ->join('saas_courses s', 'c.course_id = s.id', 'left')
            ->where('e.status', '=', 1)
            ->where('e.is_teacher', '=', 1)
//            ->whereOr('s.branch', '=', $this->user['employee']['department'])
            ->order('e.created_at desc')
            ->field('e.name, e.english_name, e.id, e.mobile, c.total_hour, e.department');
//        empty($source) || $db->where('e.source', '=', $source);
//        empty($branch) || $db->where('e.department', '=', $branch);
        if (isset($branch) && !empty($branch)) {
            $db->where('s.department', '=', $branch);
        }
        if (isset($keyword) && !empty($keyword)) {
            $db->where(['e.name|e.english_name' => $keyword]);
        }
        if (!in_array($this->user['authorize'], [1, 3, 4, 22])) {
//            $db->where('e.department', '=', $this->user['employee']['department']);
            $db->where('s.branch', '=', $this->user['employee']['department']);
        }
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
            $db = Db::name('saas_course_teacher_log')->alias('c')
                ->join('(select d.class_course_no,d.begin_time_each,d.end_time_each,c.title,u.title as ctitle from saas_courses_detail as d LEFT JOIN saas_class c on d.class_id = c.id LEFT JOIN saas_courses u on d.courses_id = u.id) n', 'c.class_course_no = n.class_course_no', 'left')
                ->where('c.is_ok', '=', '1')
                ->where('c.teacher_id', '=', $teacher_id)
                ->where('c.is_deleted', '=', '0')
                ->whereBetween('c.created_at', [$get['start'], $get['end']])
                ->field('c.*,n.begin_time_each,n.end_time_each,n.title,n.ctitle')
                ->order('c.created_at desc');
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
}