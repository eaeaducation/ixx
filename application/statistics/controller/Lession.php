<?php


namespace app\statistics\controller;


use app\admin\controller\Log;
use controller\BasicAdmin;
use think\Db;

class Lession extends BasicAdmin
{

    /**
     * 体验课课时数据统计列表
     */
    public function experience()
    {
        $this->assign('title',  '体验课课时转化统计');
        $get = $this->request->get();
        $foreign_teacher_data = Db::name('saas_trial_class l')
            ->join('saas_customer c', 'c.id = l.student_id', 'left')
            ->join('saas_experience_lession e', 'e.id = l.top_id', 'left')
            ->join('saas_courses co', 'co.id = e.course_id', 'left')
            ->field('count(l.student_id) as student_num, sum(if(l.is_deal=1,1,0)) as volume_num, count(distinct(e.id)) as lession_num, e.bishop as teacher')
            ->where('e.status', '=', 1)
            ->where('e.type', '=', 1)
            ->where('e.is_deleted', '=', 0)
            ->where('c.status', '<>', 3);
        $chinese_teacher_data = Db::name('saas_trial_class l')
            ->join('saas_customer c', 'c.id = l.student_id', 'left')
            ->join('saas_experience_lession e', 'e.id = l.top_id', 'left')
            ->join('saas_courses co', 'co.id = e.course_id', 'left')
            ->field('count(l.student_id) as student_num, sum(if(l.is_deal=1,1,0)) as volume_num, count(distinct(e.id)) as lession_num, e.assistant as teacher')
            ->where('e.status', '=', 1)
            ->where('e.is_deleted', '=', 0)
            ->where('c.status', '<>', 3);

        $school_data = Db::name('saas_trial_class l')
            ->join('saas_customer c', 'c.id = l.student_id', 'left')
            ->join('saas_experience_lession e', 'e.id = l.top_id', 'left')
            ->join('saas_courses co', 'co.id = e.course_id', 'left')
            ->field('count(l.student_id) as student_num, sum(if(l.is_deal=1,1,0)) as volume_num, count(distinct(e.id)) as lession_num, co.branch as school')
            ->where('e.status', '=', 1)
            ->where('e.is_deleted', '=', 0)
            ->where('c.status', '<>', 3);

        if (isset($get['time_range']) && $get['time_range'] != '') {
            $get['time_range'] = str_replace('+~+', ' ~ ', $get['time_range']);
            list($start, $end) = explode(' ~ ', $get['time_range']);
            $_start = strtotime($start);
            $_end = strtotime($end) + 86400;
            $foreign_teacher_data->whereBetween('e.updated_at', [$_start, $_end]);
            $chinese_teacher_data->whereBetween('e.updated_at', [$_start, $_end]);
            $school_data->whereBetween('e.updated_at', [$_start, $_end]);
        } else {
            $_start = strtotime(date('Y-m-01 00:00:00',strtotime('-1 month')));
            $_end = strtotime(date('Y-m-01 00:00:00'));
            $foreign_teacher_data->whereBetween('e.updated_at', [$_start, $_end]);
            $chinese_teacher_data->whereBetween('e.updated_at', [$_start, $_end]);
            $school_data->whereBetween('e.updated_at', [$_start, $_end]);
            $default_date = date('Y-m-01',strtotime('-1 month')) .' ~ '.date('Y-m-01');
            $this->assign('default_date', $default_date);
        }

        if (isset($get['branch']) && $get['branch'] != '') {
            $foreign_teacher_data->where('co.branch', $get['branch']);
            $chinese_teacher_data->where('co.branch', $get['branch']);
            $school_data->where('co.branch', $get['branch']);
        }
        $foreign_teacher_data = $foreign_teacher_data->group('e.bishop')
            ->select();
        $chinese_teacher_data = $chinese_teacher_data->group('e.assistant')
            ->select();
        $school_data = $school_data->group('co.branch')
            ->select();
        return $this->fetch('', ['foreign_teacher_data' => $foreign_teacher_data, 'chinese_teacher_data' => $chinese_teacher_data, 'school_data' => $school_data]);
    }

    /**
     * 正常课时统计
     */
    public function classhour()
    {
        $this->assign('title',  '课程课时统计');
        $get = $this->request->get();
        $branchs = get_branches();
        $course_data = Db::name('saas_order_log')->alias('ol')
            ->field('sum(ol.goods_num) as lession_num, c.branch, c.category')
            ->join('saas_order o', 'o.id = ol.order_id', 'left')
            ->join('saas_courses c', 'c.id = ol.goods_id', 'left')
            ->where('o.status', 'in', [5, 6]);

        $consume_course_data = Db::name('saas_course_log cl')
            ->field('sum(cl.course_hour) as consume_course, c.branch, c.category')
            ->join('saas_courses c', 'c.id = cl.course_id', 'left')
            ->where('cl.status', '<>', 5);

        if (isset($get['time_range']) && $get['time_range'] != '') {
            $_start = strtotime($get['time_range'] . '-01 00:00:00');
            $_end = strtotime(date('Y-m-01 00:00:00',strtotime('1 month')));
            $course_data->whereBetween('o.created_at', [$_start, $_end]);
            $consume_course_data->whereBetween('cl.created_at', [$_start, $_end]);
        } else {
            $_start = strtotime(date('Y-m-01 00:00:00'));
            $_end = time();
            $course_data->whereBetween('o.created_at', [$_start, $_end]);
            $consume_course_data->whereBetween('cl.created_at', [$_start, $_end]);
        }
        $course_data = $course_data->group('c.category,c.branch')->select();
        $consume_course_data = $consume_course_data->group('c.category,c.branch')->select();
        $cate_data = [];
        foreach ($course_data as $item) {
            if (!$item['category']) {
                isset($item);
                continue;
            }
            $cate_data[$item['category']][] = [
                'branch' => $item['branch'],
                'course_hour' => $item['lession_num']
            ];
        }
        $courses_data = [];
        foreach ($cate_data as $key => $item) {
            foreach ($item as $value) {
                foreach ($branchs as $k => $v) {
                    if ($value['branch'] == $k) {
                        $courses_data[$key][$k] = $value['course_hour'];
                    }
                    if (!isset($courses_data[$key][$k])) {
                        $courses_data[$key][$k] = 0;
                    }
                }
            }
        }
        $cate_data1 = [];
        foreach ($consume_course_data as $item) {
            if (!$item['category']) {
                isset($item);
                continue;
            }
            $cate_data1[$item['category']][] = [
                'branch' => $item['branch'],
                'course_hour' => $item['consume_course']
            ];
        }
        $consume_data = [];
        foreach ($cate_data1 as $key => $item) {
            foreach ($item as $value) {
                foreach ($branchs as $k => $v) {
                    if ($value['branch'] == $k) {
                        $consume_data[$key][$k] = $value['course_hour'];
                    }
                    if (!isset($consume_data[$key][$k])) {
                        $consume_data[$key][$k] = 0;
                    }
                }
            }
        }
        return $this->fetch('', ['courses_data' => $courses_data, 'branch' => $branchs, 'consume_data' => $consume_data]);
    }
}