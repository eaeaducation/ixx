<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/4
 * Time: 11:09
 */

namespace app\educational\controller;

use controller\BasicAdmin;
use think\Db;
use service\DataService;
use Naixiaoxin\ThinkWechat\Facade;

/**
 * 布置作业
 * Class Room
 * @package app\educational\controller
 */
class Homework extends BasicAdmin
{
    public $table = 'saas_homework_log';


    public function index()
    {
        $this->title = '布置作业';
        $class_id = $this->request->get('class_id');
        $id = $this->request->get('id');
        $class_row = Db::name("saas_class")->where("id", "=", $class_id)->find();
        $students = $this->get_class_students($class_id);
        if (empty($students)) {
            $this->error("该班级未分配学生！");
        }
        $this->assign('class_id', $class_id);
        $this->assign('id', $id);
        $this->assign('class', $class_row['title']);
        $this->assign('students', $students);
        if ($id) {
            $va = Db::name("saas_course_homework")->where("id", "=", $id)->find();
            $checks = Db::name("saas_homework_log")->field("customer_id")->where("h_id", "=", $id)->select();
            if ($checks) {
                $checks = array_unique(array_column($checks, 'customer_id'));
                $this->assign("checks", $checks);
            }
            $this->assign("va", $va);
        }
        //保存学员作业
        if ($this->request->isPost() && empty($this->request->post('operation'))) {
            $post = $this->request->post();
            $home_work = [
                'class_id' => $class_id,
                'title' => $post['title'],
                'content' => $post['content'],
                'created_at' => time(),
                'userid' => session('user.id'),
                'homework_pic' => $post['homework_pic']
            ];
            if (isset($post['id']) && !empty($post['id'])) {
                $res = Db::name('saas_course_homework')->where('id', '=', $post['id'])->update($home_work);
            } else {
                $res = Db::name('saas_course_homework')->insert($home_work);
            }
            if ($res) {
                $this->success('作业保存成功', '/admin.html#/educational/classed/class_detail.html?id=' . $class_id);
            } else {
                $this->success('作业保存失败');
            }
        } elseif ($this->request->isPost() && !empty($this->request->post('operation'))) {
            $data = $this->request->post();
            $ids = $data['ids'];
            if (isset($data['id']) && !empty($data['id'])) {
                $h_id = $data['id'];
                $home_work = [
                    'class_id' => $data['class_id'],
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'created_at' => time(),
                    'userid' => session('user.id'),
                    'homework_pic' => $data['homework_pic']
                ];

                $res = Db::name('saas_course_homework')->where('id', '=', $h_id)->update($home_work);
                if ($res) {
                    $customer = Db::name('saas_homework_log')->where('h_id', '=', $h_id)->column('customer_id');
                    $ids = array_diff($ids, $customer);
                    $data = [];
                    foreach ($ids as $k => $v) {
                        $data[$k]['h_id'] = $h_id;
                        $data[$k]['customer_id'] = $v;
                        $data[$k]['created_at'] = time();
                    }
                    $row = Db::name('saas_homework_log')->insertAll($data);
                    if ($row) {
                        $result = $this->send_tpl_news($ids, $h_id);
                        if ($result) {
                            $this->success('作业布置完成!', '');
                        } else {
                            $this->error('作业发送失败!');
                        }
                    } else {
                        $this->error('作业发送失败!');
                    }
                } else {
                    $this->error('作业修改失败！');
                }
            } else {
                $home_work = [
                    'class_id' => $data['class_id'],
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'created_at' => time(),
                    'homework_pic' => $data['homework_pic'],
                    'userid' => session('user.id')
                ];
                $homework_id = Db::name('saas_course_homework')->insertGetId($home_work);
                if ($homework_id) {
                    $data = [];
                    foreach ($ids as $k => $v) {
                        $data[$k]['h_id'] = $homework_id;
                        $data[$k]['customer_id'] = $v;
                        $data[$k]['created_at'] = time();
                    }
                    $log = Db::name('saas_homework_log')->insertAll($data);
                    if ($log) {
                        $result = $this->send_tpl_news($ids, $homework_id);
                        if ($result) {
                            $this->success('作业布置完成!', '');
                        } else {
                            $this->error('作业发送失败!');
                        }
                    } else {
                        $this->error('作业发送失败!');
                    }
                } else {
                    $this->error('作业保存失败！');
                }
            }
        }
        return $this->_form($this->table, 'form');
    }


    private function get_class_students($class_id)
    {
        $students = Db::name("saas_class_student")->alias("s")->join("saas_class c", "s.class_id = c.id", "left")
            ->join("saas_customer u", "s.customer_id = u.id", "left")->where("s.class_id", "=", $class_id)
            ->field("s.id,s.class_id,s.customer_id,u.name,c.title,u.wxopenid")->select();
        return $students;
    }

    public function _form_filter(&$vo)
    {
        $id = $this->request->get('id');
        $class_id = $this->request->get('class_id');
        $course_id = $this->request->get('course_id');
        $userid = isset($this->user['id']) ? $this->user['id'] : '10000';

        if ($this->request->isPost()) {
            $form_data = $this->request->post();
            $data['title'] = $form_data['title'];
            $data['class_id'] = $class_id;
            $data['course_id'] = $course_id;
            $data['content'] = $form_data['content'];
            $data['homework_pic'] = $form_data['homework_pic'];
            $data['userid'] = $userid;
            $data['created_at'] = time();
            if (!$id) {
                $hid = Db::name("saas_course_homework")->insertGetId($data);
                if (isset($form_data['customer_id']) && is_array($form_data['customer_id'])) {
                    $res = [];
                    foreach ($form_data['customer_id'] as $k => $v) {
                        $setdata['h_id'] = $hid;
                        $setdata['customer_id'] = $v;
                        $setdata['created_at'] = time();
                        $res[] = $setdata;
                    }
                    Db::name($this->table)->insertAll($res);
                }
            } else {
                Db::name('saas_course_homework')->where("id", "=", $id)->update($data);
                Db::name($this->table)->where("h_id", "=", $id)->delete();
                if (isset($form_data['customer_id']) && is_array($form_data['customer_id'])) {
                    foreach ($form_data['customer_id'] as $k => $v) {
                        $setdata['h_id'] = $id;
                        $setdata['customer_id'] = $v;
                        $setdata['created_at'] = time();
                        Db::name($this->table)->insert($setdata);
                    }
                }
            }
        }
    }


    /**
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("教室删除成功!", '');
        }
        $this->error("教室删除失败, 请稍候再试!");
    }

    /*
   * 发送模板消息 之 布置作业
   * */
    public function send_tpl_news($ids = [], $hid)
    {
        $tpl_id = sysconf('tpl_id_homework');//tpl_id
        if (is_array($ids) && !empty($hid)) {
            $app = Facade::officialAccount();
            $id_str = implode(",", $ids);
            $res_cus = Db::name('saas_customer')->where("id", "in", $id_str)->field('wxopenid,name')->select();
            $res_homework = Db::name('saas_course_homework')->alias('h')->join('saas_class c', 'h.class_id=c.id', 'left')
                ->where('h.id', '=', $hid)
                ->field('h.title,c.title as cname')->find();
            foreach ($res_cus as $k => $v) {
                $app->template_message->send([
                    'touser' => $v['wxopenid'],
                    'template_id' => $tpl_id,
                    'url' => $this->request->domain() . url('wechat/send/wechat_homework') . '?id=' . $hid,
                    'data' => [
                        'first' => [
                            'value' => '【' . $v["name"] . '】您有新的作业已发布，请查收！',
                            'color' => '#040404'
                        ],
                        'keyword1' => [
                            'value' => date("Y-m-d", time()),
                            'color' => '#173177'
                        ],
                        'keyword2' => [
                            'value' => $res_homework['cname'],
                            'color' => '#173177'
                        ],
                        'keyword3' => [
                            'value' => $res_homework['title'],
                            'color' => '#173177'
                        ],
                        'keyword4' => [
                            'value' => '请大家认真完成作业',
                            'color' => '#173177'
                        ],
                        'remark' => [
                            'value' => '感谢您的查阅，请认真对待，按时提交。',
                            'color' => '#173177'
                        ],
                    ],
                ]);
            }
            return true;
        } else {
            return false;
        }
    }

}