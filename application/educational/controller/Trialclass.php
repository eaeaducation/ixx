<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/14
 * Time: 10:21
 */

namespace app\educational\controller;

use controller\BasicAdmin;
use think\Db;
use think\Exception;
use think\facade\Log;
use service\DataService;
use service\LogService;


class Trialclass extends BasicAdmin
{
    public $table = 'saas_course_teacher_log';
    public $table2 = 'saas_trial_class';

    /**
     * @return array|string
     */
    public function index()
    {
        $this->title = '体验课列表';
        $branch = $this->request->get('branch', '');
        $time_range = $this->request->get('time_range', '');
        $teacher_id = $this->request->get('teacher_id', '');
        $db = Db::name($this->table)->alias('t')
            ->join('saas_courses c', 't.course_id = c.id', 'left')
            ->field('t.*')
            ->where('t.is_deleted', '=', '0')
            ->where('t.status', '=', '3')
            ->order('t.id desc');
        if (!in_array($this->user['authorize'], [1, 3, 4, 22])) {
            $db->where('c.branch', '=', $this->user['employee']['department']);
        }
        empty($branch) || $db->where('c.branch', '=', $branch);
        empty($teacher_id) || $db->where('t.teacher_id', '=', $teacher_id);
        if (!empty($time_range)) {
            list($_start, $_end) = explode(' ~ ', $time_range);
            $month_start = strtotime($_start);
            $month_end = strtotime($_end) + 86400;
            $db->whereBetween('t.created_at', [$month_start, $month_end]);
        }
        return parent::_list($db, true);
    }

    /**
     * 添加
     * @return array|string
     */
    public function add()
    {
        $this->title = '添加体验课';
        return $this->_form($this->table, 'form');
    }

    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (isset($post['date']) && !empty($post['date'])) {
                $date = $post['date'];
            }
            if (isset($post['time']) && !empty($post['time'])) {
                $time = $post['time'];
                list($start, $end) = explode('~', $time);
            }
            if ($date != '' && $start != '' && $end != '') {
                $vo['created_at'] = strtotime($date . $start);
                $vo['begin_time'] = strtotime($date . $start);
                $vo['end_time'] = strtotime($date . $end);
            }
            $vo['userid'] = $this->user['id'];
        }
    }

    /**
     * 编辑
     * @return mixed
     */
    public function edit()
    {
        $id = $this->request->get('id');
        if ($this->request->isPost()) {
            $post = $this->request->post();

            if (isset($post['date']) && !empty($post['date'])) {
                $date = $post['date'];
            }
            if (isset($post['time']) && !empty($post['time'])) {
                $time = $post['time'];
                list($start, $end) = explode('~', $time);
            }
            if ($date != '' && $start != '' && $end != '') {
                $start_time = strtotime($date . $start);
                $end_time = strtotime($date . $end);
            }
            $data = [
                'begin_time' => $start_time,
                'end_time' => $end_time,
                'course_hour' => $post['course_hour'],
                'teacher_id' => $post['teacher_id'],
                'is_ok' => $post['is_ok']
            ];
            $res = Db::name($this->table)
                ->where('id', '=', $id)
                ->update($data);
            if ($res) {
                $this->success('编辑成功', '');
            } else {
                $this->error('编辑失败，请稍候再试！', '');
            }
        } else {
            if (isset($id) && $id != '') {
                $data = Db::name($this->table)
                    ->field('begin_time,end_time,teacher_id,course_hour,is_ok')
                    ->where('id', '=', $id)
                    ->where('is_deleted', '=', '0')
                    ->find();
            }
            return $this->fetch('', ['vo' => $data]);
        }
    }

    /**
     * 删除体验课
     */
    public function del()
    {
        $id = $this->request->get('id');
        if (isset($id) && !empty($id)) {
            $res = Db::name($this->table)
                ->where('id', '=', $id)
                ->update(['is_deleted' => 1]);
            if ($res) {
                LogService::write('体验课', '删除了体验课');
                $this->success("体验课删除成功!", '');
            }
        }
        if ($this->request->post()) {
            $ids = $this->request->post('id');
            $res = Db::name($this->table)
                ->where('id', 'in', $ids)
                ->update(['is_deleted' => '1']);
            if ($res) {
                LogService::write('体验课', '删除了体验课');
                $this->success("体验课删除成功!", '');
            }
        }
        $this->error("体验课删除失败, 请稍候再试!");

    }

    /**
     * 确认上课（老师加课时）
     */
    public function is_ok()
    {
        $id = $this->request->get('id');
        if (isset($id) && $id != '') {
            $res = Db::name($this->table)
                ->where('id', '=', $id)
                ->update(['is_ok' => 1]);
            if ($res) {
                $this->success('已确认上课！', '');
            }
        }
    }

    /**
     * 参与详情
     * @return array|string
     */
    public function details()
    {
        $this->title = '体验课参与详情';
        $id = $this->request->get('id');
        $db = Db::name('saas_trial_class')->alias('t')
            ->join('saas_customer c', 't.student_id = c.id', 'left')
            ->field('t.*,c.parent_name')
            ->where('t.top_id', '=', $id)
            ->order('t.id desc');
        return parent::_list($db, true);
    }

    /**
     * 添加学生
     * @return array|string
     */
    public function add_student()
    {
        $get = $this->request->get();
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $created_at = time();
            $s_ids = $post['id'];
            $ids = explode(',', $s_ids);
            $data = [];
            foreach ($ids as $k => $v) {
                $data[$k]['student_id'] = $v;
                $data[$k]['top_id'] = $get['top_id'];
                $data[$k]['created_at'] = $created_at;
            }
            $res = Db::name('saas_trial_class')
                ->insertAll($data);
            if ($res) {
                $this->success('添加学生成功！', '', ['close_fragment' => true]);
            } else {
                $this->error('添加失败，请稍候再试！', '');
            }
        } else {
            $cus_id = Db::name('saas_trial_class')
                ->column('student_id');
            $db = Db::name('saas_customer')
                ->where('status', '<>', '3')
                ->where('id', 'not in', $cus_id)
                ->order('id desc');
            if (!in_array($this->user['authorize'], [1, 3, 4])) {
                $db->where('branch', '=', $this->user['employee']['department']);
            }
            if (isset($get['keyword']) && $get['keyword'] != "") {
                if (isset($get['keyword']) && $get['keyword'] != "") {
                    if (preg_match('/^\d{11}$/', trim($get['keyword']))) {
                        $db->where('parent_tel', '=', "{$get['keyword']}");
                    } else {
                        $db->where('name|nickname', 'like', "{$get['keyword']}%");
                    }
                }
            }
            if (!empty($get['branch'])) $db->where('branch', '=', $get['branch']);
            $this->assign(['top_id' => $get['top_id']]);
            return parent::_list($db, true);
        }
    }

    /**
     * 删除体验课中的学员
     */
    public function del_student()
    {
        $id = $this->request->post('id');
        $res = Db::name('saas_trial_class')
            ->where('id', '=', $id)
            ->delete();
        if ($res) {
            $this->success("学员删除成功!", '');
        } else {
            $this->error("学员删除失败, 请稍候再试!");
        }
    }

    /**
     * 体验课快捷报名
     * @return mixed
     */
    public function fast_add()
    {
        $this->title = '体验课报名';
        $id = $this->request->get('id');
        if ($this->request->isPost()) {
            $top_id = $this->request->post('course_id');
            $created_at = $this->request->post('created_at');
            $data = [
                'student_id' => $id,
                'top_id' => $top_id,
                'created_at' => $created_at
            ];
            $res = Db::name($this->table2)
                ->insertGetId($data);
            if ($res) {
                $cus = Db::name('saas_customer')
                    ->where('id', '=', $id)
                    ->update(['trial_class_id' => $res]);
                if ($cus) {
                    $this->success('报名成功！', '');
                }
            }
        } else {
            $db = Db::name('saas_course_teacher_log')->alias('t')
                ->join('saas_courses c', 't.course_id = c.id', 'left')
                ->where('t.is_deleted', '=', '0')
                ->where('t.status', '=', '3')
                ->where('t.is_ok', '=', '2')
                ->order('t.id desc');
            if (!in_array(session('authorize'), [1, 3, 4])) {
                $branch = session('user.employee.department');
                $db->where('c.branch', '=', $branch);
            }
            $course = $db->column('c.title,t.teacher_id,t.begin_time', 't.id');
            return $this->fetch('', ['course' => $course]);
        }

    }

    /**
     * 根据校区获取课程
     * @return \think\response\Json
     */
    public function get_branch_courses()
    {
        if ($this->request->isPost()) {
            $branch = $this->request->post('branch');
            $data = [];
            $data['course'] = Db::name('saas_courses')->alias('c')
                ->join('saas_class_course b', 'b.course_id = c.id', 'left')
                ->field('c.title,c.id')
                ->where('c.branch', '=', $branch)
                ->where('c.status', '<>', 3)
                ->group('c.id')
                ->select();
            return json($data);
        }
    }

}