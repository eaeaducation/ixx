<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/12
 * Time: 10:17
 */

namespace app\educational\controller;

use app\common\model\CourseLog;
use controller\BasicAdmin;
use think\Facade;
use think\facade\Cache;
use app\common\model\OrderLog;
use think\Db;
use app\common;
use Naixiaoxin\ThinkWechat\Facade as wechat;

class Punch extends BasicAdmin
{
    public function index()
    {
        if ($this->request->get()) {
            $title = '忘带磁卡';
            $class = $this->request->get('class_id');
            $course = $this->request->get('course');
            $hour = $this->request->get('hour');
            $status = $this->request->get('status');
            return view('', ['class' => $class, 'course' => $course, 'hour' => $hour, 'status' => $status, 'title' => $title]);
        }
    }

    public function search()
    {
        $user = $this->user;

        $name = $this->request->post('name');

        $mobile = $this->request->post('mobile');

        $class_id = $this->request->post('class_id');

        $course = $this->request->post('course');

        $time = $this->request->post('time');

        list($did, $hour) = explode('~', $time);

        $db = Db::name('saas_customer')->alias('c')
            ->join('saas_order o', 'c.id = o.student_id', 'left')
            ->field('c.name,c.id,l.goods_num,l.consume_num,d.school_hour_student,o.id as oid, c.icard')
            ->join('saas_order_log l', 'l.order_id = o.id', 'left')
            ->join('saas_courses_detail d', 'l.goods_id = d.courses_id', 'left')
            ->join('saas_class_student s', 's.customer_id = c.id', 'left')
            ->where('c.name', 'like', '%' . $name . '%')
            ->where('c.is_student', '=', 1)
            ->where('l.class_id|o.class_id', '=', $class_id)
            ->where('o.status', '<>', 3)
            ->where('l.goods_id', '=', $course)
            ->where('d.class_id', '=', $class_id)
            ->where('s.class_id', '=', $class_id)
            ->where('d.class_course_no', '=', $did);

        if (!empty($mobile)) {
            $db->where('c.parent_tel', '=', $mobile);
        }

        if (!in_array($user['authorize'], [1, 3, 4])) {
            $db->where('c.branch', '=', $user['employee']['department']);
        }

        $data = $db->select();

        $this->success('', '', $data);
    }

    /**
     * 学生忘带磁卡打卡
     */
    public function daKa()
    {
        $student_id = $this->request->request('id');
        $course_id = $this->request->request('course_id');
        $oid = $this->request->request('oid');
        $status = $this->request->request('status');
        $icard = $this->request->request('icard');
        $time = $this->request->request('time');
        list($did, $hour) = explode('~', $time);
        $time = strtotime(date('Y-m-d'));
        $batch = $did . '~' . $time . '~' . $status;
        $batch_res = Db::name('saas_course_log')->where('batch_code', '=', $batch)->where('student_id', '=', $student_id)->where('status', '<>', 5)->find();
        if ($batch_res) {
            $this->error('该次课已打卡，不要重复打卡');
        }
        $row = Db::name('saas_order_log')
            ->where('order_id', '=', $oid)
            ->where('goods_id', '=', $course_id)
            ->where('goods_type', '=', 1)
            ->find();
        if (($row['consume_num'] + $hour) > $row['goods_num']) {
            $this->error('学员课时不足,请购买课程!');
        }
        $data = [
            'course_id' => $course_id,
            'student_id' => $student_id,
            'created_at' => time(),
            'class_course_no' => $did,
            'course_hour' => $hour,
            'status' => $status,
            'icard' => $icard,
            'batch_code' => $batch
        ];
        $res = Db::name('saas_course_log')->insert($data);
        if ($res) {
            Db::name('saas_order_log')
                ->where('order_id', '=', $oid)
                ->where('goods_id', '=', $course_id)
                ->where('goods_type', '=', 1)
                ->update(['consume_num' => $row['consume_num'] + $hour]);
            $this->teacher_course_log($data['batch_code'], $status, $did);
            $this->success('打卡成功!');
        } else {
            $this->error('打卡失败!');
        }
    }


    private function teacher_course_log($batch_code, $status, $did)
    {
        $res = Db::name('saas_course_teacher_log')->where('batch_code', '=', $batch_code)->find();
        if (!$res) {
            $course_sub_info = Db::name('saas_courses_detail')->where('class_course_no', '=', $did)->find();
            if ($course_sub_info) {
                if ($course_sub_info['is_ta_hour'] == '0') {
                    $data = [
                        ['course_sub_id' => $course_sub_info['id'],
                            'course_id' => $course_sub_info['courses_id'],
                            'teacher_id' => $course_sub_info['teacher_id'],
                            'status' => $status,
                            'created_at' => time(),
                            'class_course_no' => $did,
                            'batch_code' => $batch_code]
                    ];
                    if ($status == 1) {
                        $data[0]['course_hour'] = $course_sub_info['school_hour_teacher'];
                    } else {
                        $data[0]['course_hour'] = 1;
                    }
                } else {
                    $data = [
                        ['course_sub_id' => $course_sub_info['id'],
                            'course_id' => $course_sub_info['courses_id'],
                            'teacher_id' => $course_sub_info['teacher_id'],
                            'status' => $status,
                            'created_at' => time(),
                            'class_course_no' => $did,
                            'batch_code' => $batch_code
                        ],
                        ['course_sub_id' => $course_sub_info['id'],
                            'course_id' => $course_sub_info['courses_id'],
                            'teacher_id' => $course_sub_info['ta_id'],
                            'status' => $status,
                            'created_at' => time(),
                            'class_course_no' => $did,
                            'batch_code' => $batch_code
                        ]
                    ];
                    if ($status == 1) {
                        $data[0]['course_hour'] = $course_sub_info['school_hour_teacher'];
                        $data[1]['course_hour'] = $course_sub_info['is_ta_hour'];
                    } else {
                        $data[0]['course_hour'] = 1;
                        $data[1]['course_hour'] = 1;
                    }
                }
                Db::name('saas_course_teacher_log')->insertAll($data);
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 批量打卡
     */
    public function more()
    {
        //加载页面
        if ($this->request->isGet()) {
            $get = $this->request->get();
            $class_id = $get['class_id'];
            $goods_id = $get['course'];
            $status = $get['status'];
            list($course_detail_id, $hour) = explode('~', $get['hour']);
            $student = Db::name('saas_order')->alias('o')
                ->leftJoin('saas_order_log l ', 'o.id=l.order_id')
                ->where('l.goods_type', 1)
                ->where('l.class_id', $class_id)
                ->where('o.status', '<>', 3)
                ->where('l.goods_id', $goods_id)
                ->field('o.student_id,l.id,l.goods_num,l.consume_num')
                ->select();
            return $this->fetch('',
                [
                    'title' => '批量打卡',
                    'student' => $student,
                    'hour' => $hour,
                    'course_id' => $goods_id,
                    'course_detail_id' => $course_detail_id,
                    'status' => $status
                ]);
        }
        //处理数据
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $ids = [];
            $students = [];
            $consume_num = [];
            $goods_num = [];
            foreach ($post['id'] as $k => $v) {
                list($ids[$k], $students[$k], $consume_num[$k], $goods_num[$k]) = explode('-', $v);
            }
            //防止重复打卡
            $batch_code = trim($post['course_detail_id']) . '~' . strtotime(date('Y-m-d', time())) . "~" . $post['status'];
            $student_ids = Db::name('saas_course_log')
                ->where('batch_code', $batch_code)
                ->where('student_id', 'in', $students)
                ->field('student_id')->select();
            if (!empty($student_ids)) {   //有学生已经打过卡的时候
                $name = [];
                foreach ($student_ids as $k => $v) {
                    $name [$k] = get_customer_name($v['student_id']);
                }
                $name = implode(',', $name);
                return json(['code' => -1, 'msg' => $name . "今天已经打过卡了"]);
            }
            $data = [];
            foreach ($ids as $k => $v) {
                $data[$k] = ["id" => $v, "consume_num" => $consume_num[$k]];
            }
            //  order_log   给学员订单详情增加课时,修改
            $order_log_model = new OrderLog();
            $res = $order_log_model->saveAll($data);
            if (!$res) {
                return json(['code' => -1, 'msg' => '打卡失败:err-1']);
            }
            $rescus = Db::name('saas_customer')->whereIn('id', $students)->field('wxopenid, name, id')->select();
            //把student_id 再装进data
            foreach ($students as $k => $v) {
                $data[$k]['student_id'] = $v;
                $data[$k]['course_id'] = $post['course_id'];
                $data[$k]['goods_num'] = $goods_num[$k];
                $data[$k]['last_num'] = $goods_num[$k] - $data[$k]['consume_num'];
                foreach ($rescus as $key => $val) {
                    if (in_array($v, $val)) {
                        $data[$k]['name'] = $val['name'];
                        $data[$k]['openid'] = $val['wxopenid'];
                    }
                }
            }
            //course_log  表增加一条记录  插入  有几个学生打卡插入几条
            $course_log_data = [];

            $class_course_no = $post['course_detail_id'];
            $batch_code = trim($post['course_detail_id']) . '~' . strtotime(date('Y-m-d', time())) . "~" . $post['status'];
            foreach ($data as $k => $v) {
                $course_log_data[$k] = [
                    'course_id' => $post['course_id'],
                    'student_id' => $v['student_id'],
                    'created_at' => time(),
                    'course_hour' => $post['hour'],
                    'status' => $post['status'],
                    //class_couse_no-当天时间戳-状态
                    'batch_code' => $batch_code,
                    'class_course_no' => $class_course_no
                ];
            }
            $res2 = Db::name('saas_course_log')->insertAll($course_log_data);
            if (!$res2) {
                return json(['code' => -1, 'msg' => '打卡失败:err-2']);
            }
            //course_teacher_log表  插入  只插入一条
            $res3 = $this->teacher_course_log($batch_code, $post['status'], $class_course_no);
            $this->send_tpl_msg($data);
            if ($res3) {
                return json(['code' => 99, 'msg' => "批量打卡成功"]);
            } else {
                return json(['code' => -1, 'msg' => '打卡失败:err-3']);
            }
        }
    }


    /**
     * 快速打卡
     */
    public function speed_da_ka()
    {
        $id = $this->request->request('id');
        $id = explode(',', $id);
        $res = Db::name('saas_class_student')->alias('s')
            ->join('saas_courses_detail d', 'd.class_id = s.class_id', 'left')
            ->whereIn('s.customer_id', $id)
            ->field('s.customer_id as student_id, d.id as course_sub_id, d.courses_id as course_id, d.begin_time_each, d.end_time_each')
            ->select();
        foreach ($res as $k => $v) {
            if (get_da_ka($v['student_id'], $v['course_id'], $v['course_sub_id'])) {
                $this->error('有学生已打卡，请重新选择');
            }
            $res[$k]['begin_time'] = strtotime(date('Y-m-d' . $v['begin_time_each']));
            $res[$k]['end_time'] = strtotime(date('Y-m-d' . $v['end_time_each']));
            $res[$k]['created_at'] = strtotime(date('Y-m-d' . $v['begin_time_each']));
            unset($res[$k]['begin_time_each']);
            unset($res[$k]['end_time_each']);
        }
        $course_log = new CourseLog();
        $row = $course_log->saveAll($res);
        if ($row) {
            $this->success('打卡成功', '');
        } else {
            $this->error('打卡失败');
        }
    }

    /**
     * @return \think\response\Json
     * 获取课程
     */
    public function get_courses()
    {
        if ($this->request->isPost()) {
            $id = $this->request->request('id');
            $courses = Db::name('saas_class_course')->alias('c')
                ->join('saas_courses k', 'k.id = c.course_id')
                ->where('c.class_id', '=', $id)
                ->field('k.title,k.id')
                ->select();
            if ($courses) {
                $this->success('', '', $courses);
            } else {
                $this->error();
            }
        }
    }

    /**
     * @return \think\response\View
     * 磁卡考勤
     */
    public function card_kq()
    {
        $title = '磁卡考勤';
        $time = time();
        $this->assign('date', $time);
        $types = get_category(30);
        $today_start = strtotime(date('Y-m-d'));
        $today_end = strtotime(date('Y-m-d 23:59:59'));
        $db = Db::name('saas_course_log')->alias('l');
        $db->join('saas_customer c', 'c.id = l.student_id', 'left')
            ->join('saas_courses k', 'k.id = l.course_id', 'left')
            ->join('saas_employee e', 'e.id = l.employee_id', 'left')
            ->whereBetween('l.created_at', [$today_start, $today_end])
            ->field('l.student_id, c.name as c_name, c.gender as c_gender, k.title, l.created_at, l.icard, l.employee_id, e.name as e_name, e.gender as e_gender');
        $this->assign('title', $title);
        $this->assign('types', $types);
        return $this->_list($db);
    }

    /**
     * @return \think\response\Json
     * 刷卡获取用户数据
     */
    public function read_card()
    {
        if ($this->request->isPost()) {
            $course_log = new CourseLog();
            $today_start = strtotime(date('Y-m-d'));
            $today_end = strtotime(date('Y-m-d 23:59:59'));
            $card = $this->request->request('card');
            $type = $this->request->request('type');
            $course = $this->request->request('course');
            $class_id = $this->request->request('class_id');
            $status = $this->request->request('status');
            if ($type == 1) {
                if ($this->request->request('hour_id') != null) {
                    $hour_id = $this->request->request('hour_id');
                    list($course_detail_id, $hour) = explode('~', $hour_id);
                } else {
                    $this->error('请选择上课时间!');
                }
                $row = $this->get_student_msg($card);
                if (empty($row)) {
                    $this->error('此卡未绑定学员!');
                }
                $id = $row['id'];
                $name = $row['name'];
                $order = Db::name('saas_order')->alias('o')
                    ->join('saas_order_log l', 'o.id = l.order_id', 'left')
                    ->where('o.student_id', '=', $id)
                    ->where('o.class_id|l.class_id', '=', $class_id)
                    ->where('o.status', '<>', 3)
                    ->where('l.goods_id', '=', $course)
                    ->where('l.goods_type', '=', 1)
                    ->field('l.goods_num, l.consume_num, l.id')
                    ->order('l.created_at desc')
                    ->find();
                if (null === $order) {
                    $this->error('打卡失败,' . $name . '学员没有购买此课程!');
                }
                $course_detail = [];
                $course_detail['name'] = $name;
                $course_detail['now_num'] = 0;
                $course_detail['goods_num'] = $order['goods_num'];
                $course_detail['consume_num'] = $order['consume_num'];
                $course_detail['remain_num'] = $order['goods_num'] - $order['consume_num'];
                if ($hour > $course_detail['remain_num']) {
                    $this->error($name . '学员课时不足,请购买课程!', '', $course_detail);
                }
                $batch = $course_detail_id . '~' . $today_start . '~' . $status;
                $batch_res = Db::name('saas_course_log')->where('batch_code', '=', $batch)->where('student_id', '=', $id)->where('status', '<>', 5)->find();
                if ($batch_res) {
                    $order_arr = $this->get_order_arr($card, $class_id, $course, $hour);
                    $this->error('该次课已打卡，不要重复打卡', '', $order_arr);
                }
                $data = [];
                $data['course_id'] = $course;
                $data['class_course_no'] = $course_detail_id;
                $data['student_id'] = $id;
                $data['created_at'] = time();
                $data['icard'] = $card;
                $data['course_hour'] = $hour;
                $data['batch_code'] = $batch;
                $data['status'] = $status;
                $res = $course_log->save($data);
                if ($res) {
                    $course_time = $order['consume_num'] + $hour;
                    $order_log = Db::name('saas_order_log')->where('id', '=', $order['id'])->update(['consume_num' => $course_time]);
                    if ($order_log) {
                        $order_ok = $this->get_order_arr($card, $class_id, $course, $hour);
                        $this->teacher_course_log($data['batch_code'], $status, $course_detail_id);
                        $this->success($name . '学员打卡成功', '', $order_ok);
                    } else {
                        $this->error('打卡失败,请重新打卡!');
                    }
                } else {
                    $this->error('打卡失败,请重新打卡!');
                }
            } elseif ($type == 2) {
                $data = $this->get_student_msg($card);
                if ($data) {
                    $data['branch'] = convert_branch($data['branch']);
                    $data['collect_id'] = get_employeeId($data['collect_id'], '');
                    $data['userid'] = get_employeeId($data['userid'], '');
                    $data['source'] = convert_channel($data['source']);
                    $this->success('', '', $data);
                } else {
                    $this->error('此卡未绑定学员!');
                }
            } elseif ($type == 4) {
                $is_log = $course_log->where('icard', '=', $card)
                    ->where('employee_id', '<>', null)
                    ->whereBetween('created_at', [$today_start, $today_end])
                    ->find();
                if ($is_log) {
                    $this->error('该员工今天已打卡，不要重复打卡!');
                }
                $row = $this->get_employee_msg($card);
                if (empty($row)) {
                    $this->error('此卡未绑定员工!');
                }
                $name = $row['name'];
                $data['icard'] = $card;
                $data['employee_id'] = $row['id'];
                $data['created_at'] = time();
                $work_time = strtotime(date('Y-m-d 09:00:00'));
                $res = $course_log->save($data);
                if ($res && $data['created_at'] <= $work_time) {
                    $this->success($name . '上班打卡成功', '');
                } elseif ($res && $data['created_at'] > $work_time) {
                    $this->error($name . '上班打卡迟到!');
                } else {
                    $this->error('打卡失败,请重新打卡!');
                }
            } elseif ($type == 6) {
                $data = $this->get_employee_msg($card);
                if ($data) {
                    $this->success('', '', $data);
                } else {
                    $this->error('此卡未绑定员工!');
                }
            }
        }
    }

    /**
     * @param $batch
     * @param $class_id
     * @param $course
     * @return array|\PDOStatement|string|\think\Collection
     * 打卡学员订单详情
     */
    private function get_order_arr($card, $class_id, $course, $hour)
    {
        //$sid = Db::name('saas_customer')->where('icard', '=', $card)->field('id')->find();
        $data = Db::name('saas_order')->alias('o')
            ->join('saas_order_log l', 'o.id = l.order_id', 'left')
            ->join('saas_customer c', 'c.id = o.student_id', 'left')
            ->where('c.icard', '=', $card)
            ->where('o.class_id|l.class_id', '=', $class_id)
            ->where('o.status', '<>', 3)
            ->where('l.goods_id', '=', $course)
            ->where('l.goods_type', '=', 1)
            ->field('l.goods_num, l.consume_num, l.id, c.name')
            ->order('l.created_at desc')
            ->select();
        $row = [];
        $row['goods_num'] = array_sum(array_column($data, 'goods_num'));
        $row['consume_num'] = array_sum(array_column($data, 'consume_num'));
        $row['remain_num'] = $row['goods_num'] - $row['consume_num'];
        $row['name'] = $data[0]['name'];
        $row['id'] = $data[0]['id'];
        $row['now_num'] = $hour;
        return $row;
    }

    /**
     * @param $card
     * @return array|null|\PDOStatement|string|\think\Model
     * 获取学生信息
     */
    private function get_student_msg($card)
    {
        $data = Db::name('saas_customer')
            ->where('status', '<>', 3)
            ->where('icard', '=', $card)
            ->find();
        return $data;
    }

    /**
     * @param $card
     * @return array|null|\PDOStatement|string|\think\Model
     * 获取员工信息
     */
    private function get_employee_msg($card)
    {
        $data = Db::name('saas_employee')
            ->where('status', '<>', 3)
            ->where('icard', '=', $card)
            ->find();
        if ($data) {
            $data['personnel'] = convert_category($data['personnel_status'], 17);
            $data['contract'] = convert_category($data['contract_status'], 21);
            $data['department'] = convert_branch($data['department']);
            return $data;
        } else {
            return $data;
        }
    }

    /**
     * @return \think\response\Json
     * 查询校区下的班级
     */
    public function get_class()
    {
        if ($this->request->isPost()) {
            $branch = $this->request->post('branch');
            $data = [];
            $data['class'] = Db::name('saas_class')
                ->field('title,id')
                ->where('branch', '=', $branch)
                ->where('status', '<>', 3)
                ->select();
            return json($data);
        }
    }

    /**
     * @return \think\response\Json
     * 查询班级下的班级
     */
    public function get_class_course()
    {
        if ($this->request->isPost()) {
            $branch = $this->request->post('branch');
            $class = $this->request->post('class');
            $data = [];
            $data['course'] = Db::name('saas_courses')->alias('c')
                ->join('saas_class_course b', 'b.course_id = c.id', 'left')
                ->field('c.title,c.id')
                ->where('c.branch', '=', $branch)
                ->where('b.class_id', '=', $class)
                ->where('c.status', '<>', 3)
                ->select();
            return json($data);
        }
    }

    /**
     * 查询课时消耗
     */
    public function get_course_time()
    {
        if ($this->request->isPost()) {
            $class_id = $this->request->post('class_id');
            $course = $this->request->post('course');
            $result = Db::name('saas_courses_detail')
                ->where('status', '<>', 3)
                ->where('class_id', '=', $class_id)
                ->where('courses_id', '=', $course)
                ->field('begin_time_each as begin, end_time_each as end, school_hour_student as hour, class_course_no as id, school_hour_teacher as thour, is_ta_hour as tahour')
                ->select();
            if ($result) {
                $this->success('', '', $result);
            } else {
                $this->error();
            }
        }
    }

    /**
     * 微信推送
     * @param $data
     * @return bool
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private function send_tpl_msg($data)
    {
        $tpl_id = sysconf('tpl_id_daka');
        $app = wechat::officialAccount();
        foreach ($data as $k => $v) {
            if (isset($v['openid']) && !empty($v['openid'])) {
                $app->template_message->send([
                    'touser' => $v['openid'],
                    'template_id' => $tpl_id,
                    'data' => [
                        'first' => [
                            'value' => '【' . $v['name'] . '同学家长， 您好】',
                            'color' => '#040404'
                        ],
                        'name' => [
                            'value' => $v['name'],
                            'color' => '#173177'
                        ],
                        'couse' => [
                            'value' => get_courses_title($v['course_id']),
                            'color' => '#173177'
                        ],
                        'goods_num' => [
                            'value' => $v['goods_num'] . '课时',
                            'color' => '#173177'
                        ],
                        'consume_num' => [
                            'value' => $v['last_num'] . '课时',
                            'color' => '#173177'
                        ],
                        'time' => [
                            'value' => date('Y-m-d H:i:s', time()) . '已打卡',
                            'color' => '#173177'
                        ],
                        'remark' => [
                            'value' => '感谢您的关注',
                            'color' => '#040404'
                        ]
                    ]
                ]);
            }
        }
    }
}