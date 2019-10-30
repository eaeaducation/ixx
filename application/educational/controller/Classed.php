<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/4
 * Time: 14:26
 */

namespace app\educational\controller;

use app\common\model\Customer;
use controller\BasicAdmin;
use Naixiaoxin\ThinkWechat\Facade;
use think\Db;
use service\DataService;
use think\Exception;
use think\facade\Log;
use QRCode;
use service\LogService;
use think\facade\Cache;

/**
 * 班级管理
 * Class Classed
 * @package app\educational\controller
 * @author mei
 */
class Classed extends BasicAdmin
{
    /**
     * 绑定操作模型
     * @var string
     */
    public $table = 'saas_class';

    /**
     * 班级列表
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function index()
    {
        $this->title = '班级管理';
        $db = Db::name($this->table)
            ->where('status', '<>', 3)
            ->order('id desc');
        //  校区
        if ($this->user['id'] != 10000 && !in_array($this->user['authorize'], [1, 3, 4, 22])) {
            $branch = $this->user['employee']['department'];
            $db->where('branch', '=', $branch);
        }
        $get = $this->request->get();
        (isset($get['title']) && $get['title'] !== '') && $db->where('title', 'like', "%{$get['title']}%");
        foreach (['branch'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', $get[$key]);
        }
        //停课班分类
        if (isset($get['status']) && $get['status'] == 4) {
            $db->where('status', '=', 4);
        } elseif (isset($get['status']) && $get['status'] == 1) {
            $db->where('status', '=', 1);
        } elseif (isset($get['status']) && $get['status'] == 2) {
            $db->where('status', '=', 2);
        } elseif (isset($get['status']) && $get['status'] == 5) {
//            $db->where('1=0');
        } else {
            $db->where('status', '<>', 4);
        }
        return parent::_list($db, true);
    }

    /**
     * 添加
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function add()
    {
        $this->title = '新建班级';
        return $this->_form($this->table, 'form');
    }

    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (isset($post['begin_time'])) {
                $vo['begin_time'] = strtotime($post['begin_time'] . " 00:00:00");
            }
            if (isset($vo['id'])) {
                $this->saveDetail($vo['id'], $this->request->post('params'));
            }
        } else {
            if (isset($vo['id'])) {
                $data = Db::name('saas_courses_detail')
                    ->alias('d')
                    ->join('saas_courses c', 'd.courses_id=c.id')
                    ->join("saas_courses_time t", 'd.begin_time_each=t.start_time and d.end_time_each=t.end_time')
                    ->field('d.is_ta_hour,d.class_course_no,d.courses_id,d.class_id,d.room_id,d.teacher_id,d.ta_id,d.week,d.cycle,d.school_hour_student,d.school_hour_teacher,d.begin_time_each,d.end_time_each,c.title as courses_name,t.id as class_time')
                    ->where('class_id', '=', $vo['id'])
                    ->where('c.status', '<>', 3)
                    ->where('t.status', '<>', 3)
                    ->where('d.status', '<>', 3)
                    ->select();
                foreach ($data as &$item) {
                    $item['courses_id'] = (string)$item['courses_id'];
                    $item['room_id'] = (string)$item['room_id'];
                    $item['teacher_id'] = (string)$item['teacher_id'];
                    $item['ta_id'] = (string)$item['ta_id'];
                    $item['cycle'] = (string)$item['cycle'];
                    $item['class_time'] = (string)$item['class_time'];
                    $item['week'] = explode(',', $item['week']);
                    $list = [];
                    foreach ($item['week'] as $v) {
                        switch ($v) {
                            case "1":
                                $list['mon'] = true;
                                break;
                            case "2":
                                $list ["tues"] = true;
                                break;
                            case "3":
                                $list["wed"] = true;
                                break;
                            case "4":
                                $list["thur"] = true;
                                break;
                            case "5":
                                $list ["fri"] = true;
                                break;
                            case "6":
                                $list ["sat"] = true;
                                break;
                            case "0":
                                $list ["sun"] = true;
                                break;
                        }
                    }
                    $item['week'] = $list;
                }
                $vo['params'] = json_encode($data);
            } else {
                $vo['params'] = '[]';
            }
        }
    }

    private function saveDetail($id, $params)
    {
        Db::name('saas_courses_detail')->where('class_id', '=', $id)->delete();
        $data = json_decode($params, 1);
        foreach ($data as &$vo) {
            $class_time = Db::name('saas_courses_time')->where('id', '=', $vo['class_time'])->field('start_time,end_time')->find();
            $vo['begin_time_each'] = $class_time['start_time'];
            $vo['end_time_each'] = $class_time['end_time'];
            if (empty($vo['class_course_no'])) {
                $vo['class_course_no'] = generate_class_courses_no();
            }
            $week_num = [];
            foreach ($vo['week'] as $k => $v) {
                if ($v) {
                    switch ($k) {
                        case "mon":
                            $k = 1;
                            break;
                        case "tues":
                            $k = 2;
                            break;
                        case "wed":
                            $k = 3;
                            break;
                        case "thur":
                            $k = 4;
                            break;
                        case "fri":
                            $k = 5;
                            break;
                        case "sat":
                            $k = 6;
                            break;
                        case "sun":
                            $k = 0;
                            break;
                    }
                    $week_num[] = $k;
                }
            }
            $vo['week'] = implode(',', $week_num);
            unset($vo['courses_name']);
            unset($vo['class_time']);
            $vo['class_id'] = $id;
            $vo['created_at'] = time();
        }
        //让data 的各项排序一致
        $courses_sort = [];
        foreach ($data as $k => $v) {
            $courses_sort[$k]['courses_id'] = $v['courses_id'];
            $courses_sort[$k]['class_id'] = $v['class_id'];
            $courses_sort[$k]['room_id'] = $v['room_id'];
            $courses_sort[$k]['teacher_id'] = $v['teacher_id'];
            $courses_sort[$k]['ta_id'] = $v['ta_id'];
            $courses_sort[$k]['week'] = $v['week'];
            $courses_sort[$k]['cycle'] = $v['cycle'];
            $courses_sort[$k]['is_ta_hour'] = $v['is_ta_hour'];
            $courses_sort[$k]['school_hour_student'] = $v['school_hour_student'];
            $courses_sort[$k]['school_hour_teacher'] = $v['school_hour_teacher'];
            $courses_sort[$k]['begin_time_each'] = $v['begin_time_each'];
            $courses_sort[$k]['end_time_each'] = $v['end_time_each'];
            $courses_sort[$k]['created_at'] = $v['created_at'];
            $courses_sort[$k]['class_course_no'] = $v['class_course_no'];
        }
        Log::error($courses_sort);
        Db::name('saas_courses_detail')->insertAll($courses_sort);
        $branch = $this->user['employee']['department'];
        if (Cache::has('class')) {
            Cache::rm('class');
        }
        if (Cache::has('class_' . $branch)) {
            Cache::rm('class_' . $branch);
        }
        LogService::write('班级管理', '添加/编辑了一个班级【id：' . $courses_sort[0]['class_id'] . '】和该班下的' . count($courses_sort) . '门课程');
        //给班级课程表插入不重复的课程信息
        set_class_courses($courses_sort[0]['class_id']);
    }

    protected function _form_result($success)
    {
        if ($success !== true) {
            $this->saveDetail($success, $this->request->post('params'));
        }
        list($base, $spm, $url) = [url('@admin'), $this->request->get('spm'), url('educational/classed/index')];
        $this->success('操作成功！', "{$base}#{$url}?spm={$spm}");
    }

    /**
     * 编辑
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function edit()
    {
        $this->title = '编辑班级';
        return $this->_form($this->table, 'form');
    }

    /**
     * 删除
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            LogService::write('班级管理', '删除了班级');
            $this->success("班级删除成功!", '');
        }
        $this->error("班级删除失败, 请稍候再试!");
    }

    /**
     * 停课班设置
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function stop()
    {
        $get = $this->request->get();
        $res = Db::name($this->table)->where('id', $get['id'])->update(['status' => 4]);
        if ($res) {
            $this->success('停课班级设置成功', '');
        } else {
            $this->error("停课班级设置失败, 请稍候再试!");
        }
    }

    /**
     * 还原停课班级
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function renew()
    {
        $get = $this->request->get();
        $res = Db::name($this->table)->where('id', $get['id'])->update(['status' => 1]);
        if ($res) {
            $this->success('停课班级还原成功', '');
        } else {
            $this->error("停课班级还原失败, 请稍候再试!");
        }
    }

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     * 选择课程
     */
    public function chooseCourse()
    {
        $db = Db::name('saas_courses')
            ->where('status', '<>', 3)
            ->order('id desc');
        $get = $this->request->get();
        (isset($get['title']) && $get['title'] !== '') && $db->where('title', 'like', "%{$get['title']}%");
        foreach (['branch', 'subject', 'type', 'category'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', $get[$key]);
        }
        return $this->_list($db, true, true);
    }

    /**
     * @return \think\response\View
     * 班级详情
     */
    public function class_detail()
    {
        $this->title = '班级详情';
        $db = Db::name($this->table)
            ->where('status', '<>', 3)
            ->order('id desc');
        $get = $this->request->get();
        (isset($get['id']) && $get['id'] !== '') && $db->where('id', '=', $get['id']);
        $class_detail = $db->find();
        $student = $this->get_students($get['id'], false);
        $dk_data = $this->get_dk_log($get['id']);
        $home_work = $this->get_work($get['id']);
        $stopCourseStudents = $this->get_students($get['id'], true);
        $courses_info = Db::name('saas_class_course')->where('class_id', $get['id'])->select();
        return view('', [
            'detail' => $class_detail,
            'student' => $student,
            'stop_student' => $stopCourseStudents,
            'dk_data' => $dk_data,
            'work' => $home_work,
            'title' => $this->title,
            'courses_info' => $courses_info
        ]);
    }

    /**
     * @return \think\response\View
     * 爱学学二维码
     */
    public function qr_ewm()
    {
        $id = $this->request->request('id');
        $app = Facade::officialAccount();
        $tempqr = $app->qrcode->temporary($id, 15 * 3600);
        Log::error($tempqr['url']);
        $code = QrCode::createQRCodeString($tempqr['url'], 150);
        $this->assign('code', $code);
        return view();
    }

    /**
     * @return array|string
     * @throws
     * 添加学员
     */
    public function add_student()
    {
        if ($this->request->isGet()) {
            $class_id = $this->request->request('id');
            //获取班级下的课
            $course_arr = Db::name('saas_class_course')->where('class_id', '=', $class_id)
                ->field('course_id')->group('course_id')->select();
            $course_str = implode(',', array_column($course_arr, 'course_id'));

            //获取包含该班级课程的所有订单信息
            $db = Db::name('saas_order_log')->alias('l')
                ->join('saas_order o', 'l.order_id = o.id', 'left')
                ->join('saas_customer c', 'o.student_id=c.id', 'left')
                ->join('saas_courses s', 'l.goods_id=s.id', 'left')
                ->field('o.student_id,c.gender,c.parent_name,c.parent_tel,l.goods_num,l.goods_type,l.goods_id,l.price,l.order_id,c.name,s.title')
                ->where('c.is_student', '=', 1)
                ->where('o.status', '<>', 3)
                ->where('l.class_id', '=', 0)
                ->where('c.branch=s.branch')
                ->where('l.goods_type', '=', 1)
                ->where('l.goods_id', 'in', $course_str)
                ->where('c.status', '<>', 3);
            $this->assign("classid", $class_id);
            if (!in_array($this->user['authorize'], [1, 3, 4])) {
                $db->where('c.branch', '=', $this->user['employee']['department']);
            }
            $order_log = $db->select();
            $student_right = [];
            if ($order_log) {
                $student_all = array_unique(array_column($order_log, 'student_id'));
                foreach ($student_all as $k0 => $v0) {
                    $student_right[$k0]['num'] = 0;
                    foreach ($order_log as $k => $v) {
                        if ($v['student_id'] == $v0) {
                            foreach (array_column($course_arr, 'course_id') as $k1 => $v1) {
                                if ($v['goods_id'] == $v1) {
                                    $student_right[$k0]['id'] = $v0;
                                    $student_right[$k0]['name'] = $v['name'];
                                    $student_right[$k0]['order_id'] = $v['order_id'];
                                    $student_right[$k0]['gender'] = $v['gender'];
                                    $student_right[$k0]['parent_name'] = $v['parent_name'];
                                    $student_right[$k0]['parent_tel'] = $v['parent_tel'];
                                    $student_right[$k0]['num'] = $student_right[$k0]['num'] + 1;
                                }
                            }
                        }
                    }
                }
                foreach ($student_right as $k2 => $v2) {
                    if ($v2['num'] != count(array_column($course_arr, 'course_id'))) {
                        unset($student_right[$k2]);
                    }
                }
            }
            return $this->fetch('', ['list' => $student_right]);
        } elseif ($this->request->isPost()) {
            $class_id = $this->request->request('class_id');
            $id = $this->request->post('id');
            //获取班级下的课
            $course_row = Db::name('saas_class_course')->where('class_id', '=', $class_id)->field('course_id')->find();
            $check = $this->check_class_student($class_id, $id);
            if (empty($check)) {
                $this->error('包含已有学员');
            } else {
                $data = [];
                foreach ($check as $k => $v) {
                    $res = Db::name('saas_order_log')->alias('l')
                        ->join('saas_order o', 'l.order_id = o.id', 'left')
                        ->where('o.student_id', '=', $v)
//                        ->where('o.class_id', '=', 0)
                        ->where('o.status', '<>', 3)
                        ->where('l.goods_id', '=', $course_row['course_id'])
                        ->field('l.order_id,l.id')
                        ->find();
                    if ($res) {
                        $prame['class_id'] = $class_id;
                        Db::name('saas_order')->where('id', '=', $res['order_id'])->update($prame);
                        Db::name('saas_order_log')->where('id', '=', $res['id'])->update($prame);
                    }
                    $data[$k]['customer_id'] = $v;
                    $data[$k]['class_id'] = $class_id;
                    $data[$k]['created_at'] = time();
                }
                $success = Db::name("saas_class_student")->insertAll($data);
                if ($success) {
                    LogService::write('班级添加学员', '给id【' . $data[0]['class_id'] . '】的班级增加了' . count($data) . '名学员
                    ');
                    $this->success('添加学员成功!', '', ['close_fragment' => true]);
                } else {
                    $this->error('添加学员失败!');
                }
            }
        }
    }

    /**
     * @param $class
     * @param $id
     * @return array
     * 检测学员是否在班级中
     * @throws
     */
    private function check_class_student($class, $id)
    {
        $student_ids = Db::name('saas_class_student')->where('class_id', '=', $class)->column('customer_id');
        $customer_id = explode(',', $id);
        return array_diff($customer_id, $student_ids);
    }

    /**
     * @param $id
     * @param $stop true: 停课学生  false: 正常上课学生
     * @return array|\PDOStatement|string|\think\Collection
     * 获取该班级的学员
     */
    public function get_students($id, $stop)
    {
        $db = Db::name('saas_class_student')->alias('c')
            ->join('saas_customer s', 's.id = c.customer_id', 'left')
            ->join('saas_class_course k', 'c.class_id = k.class_id', 'left')
            ->field('c.id as csid, s.name, c.customer_id as id, s.parent_tel, s.parent_name, s.gender, s.birthday, s.wxopenid, k.course_id')
            ->where('c.class_id', '=', $id)
            ->where('s.is_student', '=', 1)
            ->where('s.status', '<>', 3);

        if ($stop) {
            $db->where('s.status', '=', 0);
        } else {
            $db->where('s.status', '<>', 0);
        }
        $students = $db->select();
        return $students;
    }

    /**
     * @return bool
     * 学生成绩
     */
    public function student_score()
    {
        $class_id = $this->request->get('class_id', '');
        $sid = $this->request->get('sid', '');
        if ($this->request->isGet()) {
            $student = Db::name('saas_customer')->field('id,name,parent_tel,wxopenid')->where('id', '=', $sid)->find();
            $courses = Db::name('saas_class_course')
                ->alias('cc')
                ->join('saas_courses sc', 'cc.course_id=sc.id')
                ->field('*')
                ->where('cc.class_id', '=', $class_id)->select();

            $this->assign(['courses' => $courses]);
            $this->assign(['student' => $student]);
            return $this->fetch('student_score');
        } elseif ($this->request->isPost()) {
            $post = $this->request->post();
            $student_id = $post['student']['id'];
            $is_push = isset($post['push']) ? $post['push'] : '';
            unset($post['student']);
            unset($post['push']);
            $insert = [];
            $ids = [];
            foreach ($post as $item) {
                if (!empty($item['score'])) {
                    $item['student_id'] = $student_id;
                    $item['class_id'] = $class_id;
                    $item['created_at'] = time();
                    $insert[] = $item;
                    if ($is_push) {
                        $item['is_pushed'] = 1;
                        $ids[] = Db::name('saas_course_score')->insertGetId($item);
                    }
                }
            }
            if (!empty($ids) && $is_push) {
                $cus = Db::name('saas_customer')->where('id', '=', $student_id)->field('*')->find();
                $class = Db::name('saas_class')->field('title')->where('id', '=', $class_id)->find();
                $tpl_id =sysconf('tpl_id_grade');//tpl_id
                $app = Facade::officialAccount();
                if ($cus['wxopenid']) {
                    $app->template_message->send([
                        'touser' => $cus['wxopenid'],
                        'template_id' => $tpl_id,
                        'url' => $this->request->domain() . url('educational/classed/student_score_list') . '?id=' . implode(',', $ids),
                        'data' => [
                            'first' => [
                                'value' => '请查看【' . $cus["name"] . '】同学的成绩!',
                                'color' => '#040404'
                            ],
                            'keyword1' => [
                                'value' => $cus['name'],
                                'color' => '#173177'
                            ],
                            'keyword2' => [
                                'value' => $class['title'],
                                'color' => '#173177'
                            ],
                            'keyword3' => [
                                'value' => '点击查看详情',
                                'color' => '#173177'
                            ],
                            'keyword4' => [
                                'value' => '点击查看详情',
                                'color' => '#173177'
                            ],
                            'remark' => [
                                'value' => '欲穷千里目，更上一层楼。',
                                'color' => '#173177'
                            ],
                        ],
                    ]);
                }
                $this->success('成绩录入推送成功', '');
            } elseif (!empty($insert)) {
                Db::name('saas_course_score')->insertAll($insert);
                $this->success('成绩录入成功', '');
            } else {
                $this->error('未录入成绩');
            }
        }
    }

    /**
     * @return array|string
     * 成绩展示及推送
     */
    public function score_list()
    {
        if ($this->request->isGet()) {
            $class_id = $this->request->get('class_id', '');
            $db = Db::name('saas_course_score')
                ->alias('cc')
                ->field('cc.*,sc.name,sc.gender,sc.parent_name,sc.parent_tel,sc.id as student_id,sc.wxopenid')
                ->join('saas_customer sc', 'cc.student_id=sc.id')
                ->where('cc.is_pushed', '=', 0)
                ->where('cc.class_id', $class_id);
            return $this->_list($db, true);
        } elseif ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post['id'])) $this->error('请选择要操作的数据');
            $tpl_id = "ElQRUMiiMqyc7Qv5RvzS0iA_cqyeiaMQL2aR6bPXHYA"; // 官网
//            $tpl_id = "inBKKILqpe2NEXcCkay2aONFW_amzP8_jaLI3Sh8JjY";   // 测试
            $app = Facade::officialAccount();
            $res = Db::name('saas_course_score')
                ->alias('cs')
                ->field('cs.*,c.name,c.wxopenid')
                ->join('saas_customer c', 'cs.student_id=c.id')
                ->whereIn('cs.id', $post['id'])
                ->select();
            $class = Db::name('saas_class')->where('id', '=', $post['class_id'])->find();
            Db::name('saas_course_score')->whereIn('id', $post['id'])->update(['is_pushed' => 1]);
            foreach ($res as $k => $v) {
                if ($v['wxopenid']) {
                    $app->template_message->send([
                        'touser' => $v['wxopenid'],
                        'template_id' => $tpl_id,
                        'url' => $this->request->domain() . url('educational/classed/student_score_view') . '?id=' . $v['id'],
                        'data' => [
                            'first' => [
                                'value' => '请查看【' . $v["name"] . '】同学的成绩!',
                                'color' => '#040404'
                            ],
                            'keyword1' => [
                                'value' => $v['name'],
                                'color' => '#173177'
                            ],
                            'keyword2' => [
                                'value' => $class['title'],
                                'color' => '#173177'
                            ],
                            'keyword3' => [
                                'value' => $v['score'],
                                'color' => '#173177'
                            ],
                            'keyword4' => [
                                'value' => isset($v['remark']) ? $v['remark'] : '',
                                'color' => '#173177'
                            ],
                            'remark' => [
                                'value' => '欲穷千里目，更上一层楼。',
                                'color' => '#173177'
                            ],
                        ],
                    ]);
                }
            }
            $this->success('推送成功', '');
        }
    }

    /**
     * @param $id
     * @return array|\PDOStatement|string|\think\Collection
     * 获取学生打卡记录
     */
    public function get_dk_log1($id)
    {
        $time = strtotime(date('Y-m-d 23:59:59'));
        $start = $time - 604800;
        $data = Db::name('saas_class_student')->alias('s')
            ->join('saas_class_course c', 'c.class_id =' . $id, 'left')
            ->join('saas_course_log l', 'l.course_id = c.course_id and l.student_id = s.customer_id', 'left')
            ->join('saas_customer m', 'm.id = l.student_id', 'left')
            ->field('l.student_id, m.name, l.created_at, l.course_id, l.status')
            ->order('l.id desc')
            ->whereBetween('l.created_at', [$start, $time])
            ->where('l.student_id', '<>', null);
        return $data;
    }


    public function get_dk_log($id)
    {
        $course_id = Db::name('saas_class_course')
            ->where('class_id', '=', $id)
            ->column('course_id');
        $student_id = Db::name('saas_class_student')
            ->where('class_id', '=', $id)
            ->column('customer_id');
        $data = Db::name('saas_course_log')
            ->field('*')
            ->where('student_id', 'in', $student_id)
            ->where('course_id', 'in', $course_id)
            ->order('created_at desc')
            ->select();
        return $data;
    }

    /**
     * @param $id
     * @return array|\PDOStatement|string|\think\Collection
     * 获取班级班级作业
     */
    public function get_work($id)
    {
        $data = Db::name('saas_course_homework')
            ->where('class_id', '=', $id)
            ->order('created_at desc')
            ->field('id, title, content')
            ->select();
        if ($data) {
            foreach ($data as $k => $v) {
                $data[$k]['content'] = strip_tags($v['content']);
                if (mb_strlen($data[$k]['content'], 'utf-8') > 15) {
                    $data[$k]['content'] = mb_substr($data[$k]['content'], 0, 15, 'utf-8') . '......';
                }
            }
        }
        return $data;
    }


    /**
     * 删除班级作业
     */
    public function work_delete()
    {
        $id = $this->request->request('id');
        $res = Db::name('saas_course_homework')->where('id', '=', $id)->delete();
        if ($res) {
            $this->success('作业删除成功', '');
        } else {
            $this->success('作业删除失败');
        }
    }

    /**
     * 请假/旷课
     * @param $class_id 班级id
     * @param $id 学员id
     * @return mixed
     * @author mei
     */
    public function vacate_truancy()
    {
        $get = $this->request->get();
        $courses = Db::name('saas_class_course')
            ->field('course_id')
            ->where('class_id', '=', $get['class_id'])
            ->select();
        if ($this->request->isPost()) {
            $t = date('Y-m-d');
            $student_id = $this->request->post('student_id');
            $status = $this->request->post('status');
            $course = $this->request->post('course');
            $created_at = $this->request->post('created_at');
            $time1 = $this->request->post('time1');
            $time2 = $this->request->post('time2');
            $remark = $this->request->post('remark');
//            list($begin, $end) = explode('-', $time);
            $hour = intval(trim($this->request->post('hour')));
            $batch_code = $course . '~' . strtotime(date('Y-m-d', time())) . "~" . $status;
            if (($status == 1 || $status == 2) && $hour == 0) {
                $this->error('添加上课/补课记录消耗课时不能为0');
            }
            if (in_array($status , [3,4])) {
                if (!$time1) {
                    $this->error('请选择时间');
                }
                list($begin, $end) = explode('-', $time1);
                //请假旷课消耗课时为0
                $hour = 0;
            } elseif (in_array($status, [1,2])) {
                if (!$time2) {
                    $this->error('请选择时间');
                }
                $t = $this->request->post('date');
                list($begin, $end) = explode('-', $time2);
                //更新用户课程课时
                $order = Db::name('saas_order')
                    ->where('student_id', '=', $student_id)
                    ->where('class_id', '=', $get['class_id'])
                    ->field('id')
                    ->find();
                $row = Db::name('saas_order_log')
                    ->where('order_id', '=', $order['id'])
                    ->where('goods_id', '=', $course)
                    ->where('goods_type', '=', 1)
                    ->find();
                if (($row['consume_num'] + $hour) > $row['goods_num']) {
                    $this->error('学员课时不足,请购买课程!');
                }
                Db::name('saas_order_log')
                    ->where('order_id', '=', $order['id'])
                    ->where('goods_id', '=', $course)
                    ->setInc('consume_num', $hour);

                //同步course_teacher_log表给老师添加课时记录
//                $this->teacher_course_log($batch_code, $status, $course);
            }
            $class_course_no = Db::name('saas_courses_detail')
                ->where('courses_id', '=', $course)
                ->where('class_id', '=', $get['class_id'])
                ->where('status', '<>', 3)
                ->column('class_course_no');
            $data = [
                'student_id' => $student_id,
                'status' => $status,
                'course_id' => $course,
                'created_at' => $created_at,
                'course_hour' => $hour,
                'remark' => $remark,
                'begin_time' => strtotime($t . $begin),
                'end_time' => strtotime($t . $end),
                'class_course_no' => $class_course_no[0],
                'batch_code' => $class_course_no[0] . '~3'
            ];
            $res = Db::name('saas_course_log')
                ->insert($data);
            if ($res) {
                $this->success('添加成功！', '');
            } else {
                $this->error('添加失败', '');
            }
        }
        $student = Customer::get(['id' => $get['id']]);
        $this->assign('student', $student);
        $this->assign(['student_id' => $get['id']]);
        $this->assign(['class_id' => $get['class_id']]);
        $this->assign(['courses' => $courses]);
        return $this->fetch();
    }

    /**
     * 班级批量手动打卡
     * @return mixed
     */
    public function batchPunchCard()
    {
        $get = $this->request->get();
        $student_ids = explode(',', $get['id']);
        //查询该班级所有课程
        $class = Db::name('saas_class')->where('id', $get['class_id'])->find();
        $courses = Db::name('saas_class_course')
            ->field('course_id')
            ->where('class_id', '=', $get['class_id'])
            ->select();
        $student = Db::name('saas_customer')->where('id', 'in', $student_ids)->select();
        $students = '';
        foreach ($student as $value) {
            $students .= $value['name'] . ', ';
        }
        if ($this->request->isPost()) {
            $student_ids = explode(',', $this->request->get('id'));
            $status = $this->request->post('status');
            $course_id = $this->request->post('course');
            $created_at = strtotime(date('Y-m-d H:i:s'));
            $date = $this->request->post('date');
            $time = $this->request->post('time');
            $remark = $this->request->post('remark');
            $teacher_hour = intval(trim($this->request->post('teacher_hour')));
            $student_hour = intval(trim($this->request->post('student_hour')));
            $ta_teacher_hour = intval(trim($this->request->post('ta_teacher_hour')));
            if (in_array($status , [3,4])) {
                if (!$date || !$time) {
                    $this->error('请选择时间');
                }
                list($begin, $end) = explode('-', $time);
                //请假旷课消耗课时为0
                $teacher_hour = 0;
                $student_hour = 0;
                $ta_teacher_hour = 0;
            } elseif (in_array($status, [1,2])) {
                if ($student_hour == 0 || $teacher_hour == 0) {
                    $this->error('上课/补课课时值不能为0');
                }
                if (!$date || !$time) {
                    $this->error('请选择时间');
                }
                list($begin, $end) = explode('-', $time);
                Db::startTrans();
                try {
                    //更新用户课程课时
                    foreach ($student_ids as $item) {
                        $order = Db::name('saas_order')
                            ->where('student_id', '=', $item)
                            ->where('class_id', '=', $get['class_id'])
                            ->field('id')
                            ->find();
                        $row = Db::name('saas_order_log')
                            ->where('order_id', '=', $order['id'])
                            ->where('goods_id', '=', $course_id)
                            ->where('goods_type', '=', 1)
                            ->find();
                        if (!isset($row['goods_num']) || $row['goods_num'] == 0){
                            $user = Customer::get(['id'=>$item]);
                            Log::error('异常订单信息:客户id-'.$item.';订单id-'.$order['id'].';当前班级课程id-'.$course_id.';当前班级id-'.$get['class_id']);
                            throw new Exception("学员 ".$user->name." 订单异常,打卡失败");
                        } elseif (($row['consume_num'] + $student_hour) > $row['goods_num']) {
                            $user = Customer::get(['id'=>$item]);
                            throw new Exception("学员 ".$user->name." 课时不足,批量打卡失败");
                        }
                        Db::name('saas_order_log')
                            ->where('order_id', '=', $order['id'])
                            ->where('goods_id', '=', $course_id)
                            ->setInc('consume_num', $student_hour);
                    }
                    $class_course_no = Db::name('saas_courses_detail')
                        ->where('courses_id', '=', $course_id)
                        ->where('class_id', '=', $get['class_id'])
                        ->where('status', '<>', 3)
                        ->find();
                    //同步course_teacher_log表给老师添加课时记录
                    $batch_code = $class_course_no['class_course_no'] . '~' . strtotime(date('Y-m-d', time())) . "~" . $status;
                    $this->teacher_course_log($batch_code, $status, $class_course_no['class_course_no'], $teacher_hour, $ta_teacher_hour, $date);
                    Db::commit();
                } catch (Exception $exception) {
                    Db::rollback();
                    $this->error($exception->getMessage());
                }
            } elseif ($status == 5) {
                $status = 1;
                if (!$date || !$time) {
                    $this->error('请选择时间');
                }
                list($begin, $end) = explode('-', $time);
                //请假旷课消耗课时为0
                $teacher_hour = 0;
                $ta_teacher_hour = 0;
                foreach ($student_ids as $item) {
                    $order = Db::name('saas_order')
                        ->where('student_id', '=', $item)
                        ->where('class_id', '=', $get['class_id'])
                        ->field('id')
                        ->find();
                    Db::name('saas_order_log')
                        ->where('order_id', '=', $order['id'])
                        ->where('goods_id', '=', $course_id)
                        ->setInc('consume_num', $student_hour);
                }
            }
            $class_course_no = Db::name('saas_courses_detail')
                ->where('courses_id', '=', $course_id)
                ->where('class_id', '=', $get['class_id'])
                ->where('status', '<>', 3)
                ->find();
            $data = [];
            foreach ($student_ids as $item) {
                $data[] = [
                    'student_id' => $item,
                    'status' => $status,
                    'course_id' => $course_id,
                    'created_at' => strtotime($date . date('H:i:s')),
                    'course_hour' => $student_hour,
                    'remark' => $remark,
                    'begin_time' => strtotime($date . $begin),
                    'end_time' => strtotime($date . $end),
                    'class_course_no' => $class_course_no['class_course_no'],
                    'batch_code' => $class_course_no['class_course_no'] . '~'.$status
                ];
            }
            $res = Db::name('saas_course_log')
                ->insertAll($data);
            if ($res) {
                $this->success('添加成功！', '');
            } else {
                $this->error('添加失败', '');
            }
        }
        $this->assign([
            'class' => $class,
            'courses' => $courses,
            'class_id' => $get['class_id'],
            'students' => $students,
        ]);
        return $this->fetch();
    }

    /**
     * @param $batch_code
     * @param $status
     * @param $did
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function teacher_course_log($batch_code, $status, $did, $teacher_hour, $ta_teacher_hour, $date)
    {
        $course_sub_info = Db::name('saas_courses_detail')->where('class_course_no', '=', $did)->find();
        if ($course_sub_info) {
            if ($ta_teacher_hour == '0') {
                $data = [
                    ['course_sub_id' => $course_sub_info['id'],
                        'course_id' => $course_sub_info['courses_id'],
                        'teacher_id' => $course_sub_info['teacher_id'],
                        'status' => $status,
                        'created_at' => strtotime($date . date('H:i:s')),
                        'class_course_no' => $did,
                        'batch_code' => $batch_code]
                ];
                if ($status == 1) {
                    $data[0]['course_hour'] = $teacher_hour;
                } elseif ($status == 2) {
                    $data[0]['course_hour'] = $teacher_hour;
                } else {
                    $data[0]['course_hour'] = 1;
                }
            } else {
                $data = [
                    ['course_sub_id' => $course_sub_info['id'],
                        'course_id' => $course_sub_info['courses_id'],
                        'teacher_id' => $course_sub_info['teacher_id'],
                        'status' => $status,
                        'created_at' => strtotime($date . date('H:i:s')),
                        'class_course_no' => $did,
                        'batch_code' => $batch_code
                    ],
                    ['course_sub_id' => $course_sub_info['id'],
                        'course_id' => $course_sub_info['courses_id'],
                        'teacher_id' => $course_sub_info['ta_id'],
                        'status' => $status,
                        'created_at' => strtotime($date . date('H:i:s')),
                        'class_course_no' => $did,
                        'batch_code' => $batch_code
                    ]
                ];
                if ($status == 1) {
                    $data[0]['course_hour'] = $teacher_hour;
                    $data[1]['course_hour'] = $ta_teacher_hour;
                } elseif ($status == 2) {
                    $data[0]['course_hour'] = $teacher_hour;
                    $data[1]['course_hour'] = $ta_teacher_hour;
                } else {
                    $data[0]['course_hour'] = 1;
                    $data[1]['course_hour'] = 1;
                }
            }
            Db::name('saas_course_teacher_log')->insertAll($data);
            return true;
        } else {
            Log::error('未查询到课程详情');
            return false;
        }
    }

    /**
     * 删除班级学员
     * @param $id 学生id
     * @param $class_id 班级id
     * @author mei
     */
    public function del_student()
    {
        $id = $this->request->request('id');
        $cid = $this->request->request('cid');
        $class_id = $this->request->request('class_id');
        $oid = Db::name('saas_order')
            ->where('student_id', '=', $cid)
            ->where('class_id', '=', $class_id)
            ->where('status', '<>', 3)
            ->column('id');
        Db::name('saas_order_log')->where('order_id', '=', $oid[0])
            ->where('goods_type', '=', 1)
            ->update(['class_id' => 0]);
        $res = Db::name('saas_class_student')->where('id', '=', $id)->delete();
        if ($res) {
            $this->success('学员删除成功', '');
        } else {
            $this->error('学员删除失败');
        }
    }

    /**
     * 编辑打卡记录备注
     * @return mixed
     * @author mei
     */
    public function edit_remark()
    {
        $id = $this->request->get('id');
        $vo = Db::name('saas_course_log')
            ->field('*')
            ->where('id', '=', $id)
            ->find();
        if ($this->request->isPost()) {
            $remark = $this->request->post('remark');
            $res = Db::name('saas_course_log')
                ->where('id', '=', $id)
                ->update(['remark' => $remark]);
            if ($res) {
                $this->success('编辑成功', '');
            } else {
                $this->error('编辑失败');
            }
        }
        return $this->fetch('edit_remark', ['vo' => $vo]);
    }

    /**
     * 学员转班
     */
    public function replace_class()
    {
        if ($this->request->isPost()) {
            $student = $this->request->post('student_id'); //学生id
            $old_class_id = $this->request->post('old_class'); //转出班的id
            $course_id = $this->request->post('cu_course_id'); //转出班的课程id
            $branch_id = $this->request->post('cu_branch_id'); //转入的校区
            $class = $this->request->post('class');  //要转入班的ID
            if (empty($class)) $this->error('请选择要转入的班级');
            if (empty($course_id)) $this->error('参数错误');
            if (empty($branch_id)) $this->error('参数错误');

            $order_id = Db::name('saas_order')->alias('o')
                ->join('saas_order_log l', 'o.id = l.order_id')
                ->where('o.status', '<>', 3)
                ->where('o.student_id', '=', $student)
                ->where('l.class_id', '=', $old_class_id)
                ->value('o.id');

            // 启动事务
            Db::startTrans();
            try {
                //更改用户校区
                Db::name('saas_customer')->where('id', $student)->update(['branch' => $branch_id]);
                //查询班级课程id
                $new_class_course_id = Db::name('saas_class_course')->where('class_id', $class)->value('course_id');
                //订单详情表   order
                Db::name('saas_order_log')->where('order_id', $order_id)->where('goods_id', '=', $course_id)->where('goods_type', '=', 1)->update(['class_id' => $class, 'goods_id' => $new_class_course_id]);
                //订单表
                Db::name('saas_order')->where('id', $order_id)->where('student_id', '=', $student)->update(['class_id' => $class]);
                //  学生班级   class_student
                Db::name('saas_class_student')->where('customer_id', $student)->where('class_id', $old_class_id)->update(['class_id' => $class]);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $this->error('转班失败,请重新尝试');
            }
            LogService::write('学员转班', '学员【' . get_customer_tel($student) . '】从(id)为【' . $old_class_id . '】的班转入(id)为【' . $class . '】的班');
            $this->success('转班成功', '');
        } else {
            $get = $this->request->get();
            $student = $get['id'];
            $class_id = $get['class_id'];
//            $class_courses_array = Db::name('saas_class_course')->field('course_id')->where('class_id', $class_id)->select();
//            //取该班级下的课程
//            $class_courses = [];//班级的课程
//            foreach ($class_courses_array as $v) {
//                $class_courses[] = $v['course_id'];
//            }
            $class_courses_ids = Db::name('saas_class_course')->where('class_id', $class_id)->column('course_id');
            //查询该班级所有课程所属分类
            $course_detail = Db::name('saas_courses')
                ->field('id,title,type,category,subject')
                ->where('id', 'in', $class_courses_ids)
                ->select();
            $ok_courses_ids = [];
            //筛选符合条件的课程
            foreach ($course_detail as $item) {
                $ok_courses = Db::name('saas_courses')
                    ->where('type', $item['type'])
                    ->whereIn('category', $item['category'])
                    ->whereIn('subject', $item['subject'])
                    ->column('id');
                $ok_courses_ids = array_merge($ok_courses_ids, $ok_courses);
            }
            //当前班级所在校区
            $current_branch = Db::name('saas_class')->field('branch')->where('id', $class_id)->value('branch');
            //查询当前校区适合该生转班的班级
            $ok_class_arr = Db::name('saas_class_course')->whereIn('course_id', $ok_courses_ids)->group('class_id')->column('class_id');
            $key = array_search($class_id ,$ok_class_arr);
            array_splice($ok_class_arr,$key,1);
            //查询具体班级信息
            $ok_class_details = Db::name('saas_class')
                ->where('branch', '=', $current_branch)
                ->whereIn('id', $ok_class_arr)
                ->select();
//            //查询合适的班
//            $ok_class_array = Db::name('saas_class_course')->field('class_id')->whereIn('course_id', $class_courses)->group('class_id')->select();
//            $ok_class = [];
//            foreach ($ok_class_array as $v) {
//                $ok_class[] = $v['class_id'];
//            }
//            //查适合的班中的课程
//            $ok_class_courses = Db::name('saas_class_course')->whereIn('class_id', $ok_class)->select();
//            $data = [];
//            foreach ($ok_class_courses as &$v) {
//                foreach ($ok_class as $v1) {
//                    if ($v['class_id'] == $v1) {
//                        $data[$v1][] = $v['course_id'];
//                    }
//                }
//            }
//            unset($data[$class_id]);//去除掉自己
//            $SatisfyClass = [];
//            foreach ($data as $key => $item) {
//                if (empty(array_diff($class_courses, $item)) && empty(array_diff($item, $class_courses))) {
//                    $SatisfyClass[] = $key;
//                }
//            }
//            empty($SatisfyClass) && $this->error('没有课程一样的班可以转');
//            $branch_arr = Db::name('saas_class')->field('branch')->where('id', $class_id)->find();
//            $branch = $branch_arr['branch'];
//            $SatisfyClassInfo = Db::name('saas_class')->where('branch', $branch)->where('status', '<>', 3)->whereIn('id', $SatisfyClass)->select();
//            empty($SatisfyClassInfo) && $this->error('本校区下没有课程一样的班可以转');
            return $this->fetch('', [
                'student_id' => $student,
                'old_class_id' => $class_id,
                'course_detail' => $course_detail,//课程详情
                'current_branch' => $current_branch,
                'ok_class_details' => $ok_class_details
            ]);
        }
    }

    /**
     * @return \think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * 学生恢复上课
     */
    public function course_status()
    {
        $get = $this->request->get();
        $student = Db::name('saas_customer')->where('id', '=', $get['sid'])->find('status');
        $this->assign('student', $student);
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $res = Db::name('saas_customer')->where('id', '=', $get['sid'])->update($post);
            if ($res) {
                $this->success('数据修改成功！', '');
            } else {
                $this->error('数据修改失败，请稍后再试。。。');
            }
        }
        return view();
    }

    /**
     * 学生成绩一条
     */
    public function student_score_view()
    {
        $id = $this->request->get('id');
        $info = Db::name('saas_course_score')->where('id', '=', $id)->find();
        if (empty($info)) $this->error('暂无成绩');
        $class = Db::name('saas_class')->field('title')->where('id', $info['class_id'])->find();
        $info['class'] = $class['title'];
        return $this->fetch('', ['info' => $info]);
    }

    /**
     * 学生成多条
     */
    public function student_score_list()
    {
        $id = $this->request->get('id');
        $ids = explode(',', $id);
        $info = Db::name('saas_course_score')->whereIn('id', $ids)->select();
        if (empty($info)) $this->error('暂无成绩');
        $class = Db::name('saas_class')->column('title', 'id');
        foreach ($info as &$v) {
            if (isset($class[$v['class_id']])) {
                $v['class'] = $class[$v['class_id']];
            }
        }
        return $this->fetch('', ['info' => $info]);
    }

    public function find_leave_record()
    {
        $student_id = $this->request->post('student_id');
        $data = Db::name('saas_course_log')
            ->field("sum(IF(status=2,1,0)) as bk_num, sum(IF(status=3,1,0)) as qj_num, sum(IF(status=4,1,0)) as kk_num")
            ->where('status', 'in', [2, 3, 4])
            ->where('student_id', '=', $student_id)
            ->order('created_at desc')
            ->find();
        if ($data['bk_num'] < $data['qj_num'] + $data['kk_num']) {
            return true;
        } else {
            return false;
        }
    }

    public function get_change_class()
    {
        $course_id = $this->request->post('course_id');
        $branch = $this->request->post('branch');
        $class = $this->request->post('class');
        $course_arr = explode('-', $course_id);
        $ok_courses = Db::name('saas_courses')
            ->where('type', $course_arr[1])
            ->whereIn('category', $course_arr[2])
            ->whereIn('subject', $course_arr[3])
            ->column('id');
        $ok_class_arr = Db::name('saas_class_course')
            ->whereIn('course_id', $ok_courses)
            ->group('class_id')
            ->column('class_id');
        $key = array_search($class ,$ok_class_arr);
        array_splice($ok_class_arr,$key,1);
        //查询具体班级信息
        $ok_class_details = Db::name('saas_class')
            ->where('branch', '=', $branch)
            ->whereIn('id', $ok_class_arr)
            ->select();
        foreach ($ok_class_details as &$value) {
            $value['begin_time'] = date("Y-m-d", $value['begin_time']);
            $value['teacher_id'] = get_employee_name($value['teacher_id']);
        }
        if ($ok_class_details) {
            return json([
                'code' => 1,
                'data' => $ok_class_details,
                'msg' => '获取成功'
            ]);
        } else {
            return json([
                'code' => 0,
                'data' => [],
                'msg' => '无数据'
            ]);
        }
    }
}