<?php
/**
 * User: Jasmine2
 * Date: 2018/6/20 14:41
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\marketing\controller;

use app\admin\controller\Log;
use app\common\model\Order;
use app\common\model\Customer;
use app\common\model\CustomerFollow;
use controller\BasicAdmin;
use think\App;
use think\Db;
use service\DataService;
use service\LogService;

/**
 * Class PreMarketing
 * @package app\marketing\controller
 * @author Jasmine2
 * 售前营销
 */
class Premarketing extends BasicAdmin
{
    public $table = 'saas_customer';
    public $table2 = 'system_fast_word';

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function createConsultation()
    {
        $this->title = '新建咨询';
        if ($this->request->isPost()) {
            $customer = array_filter($this->request->except(['follow', 'spm']));
            $customer['userid'] = session('user.id');
            if ($this->user['id'] != '10000') {
                $customer['first_branch'] = $this->user['employee']['department'];
            } else {
                $customer['first_branch'] = '';
            }
            //分配销售后才会改变状态为99 已分配   wangbaojie 2018.10.23改
            if (!empty($customer['collect_id'])) {
                $customer['status'] = 99;
            }
            !isset($customer['birthday']) || $customer['birthday'] = strtotime($customer['birthday']);
            $follow = array_filter($this->request->post('follow/a'));
            $follow['user_id'] = session('user.id');
            !isset($follow['remind_time']) || $follow['remind_time'] = strtotime($follow['remind_time']);
            $is_save = Customer::get(['parent_tel' => $customer['parent_tel'], 'status' => [0,1,99]]);
            if ($is_save) {
                $this->error('手机号码重复，客户 ' . $is_save['parent_name'] . ' 已录入！');
            }
            $customer = Customer::create($customer);
            CustomerFollow::create(array_merge($follow, ['customer_id' => $customer->id, 'follow_status' => 1]));

            //添加标签
            $keyword = $this->request->post('follow')['keyword'];
            if (!empty($keyword)) {
                $keyword_row = explode('，', trim($keyword, "，"));
                $keyword_row = array_unique($keyword_row);
                foreach ($keyword_row as $k => $v) {
                    $is_have = Db::name("saas_consult_tips")->where("tips", "=", $v)->field("id")->find();
                    if ($is_have) {
                        Db::name("saas_consult_tips")->where("id", "=", $is_have['id'])->setInc("nums");
                    } else {
                        $tips_data = [
                            'tips' => $v,
                            'nums' => '1',
                        ];
                        Db::name("saas_consult_tips")->insert($tips_data);
                    }
                }
            }
            LogService::write('新建客户', '新建了一个客户', $customer->id);
            $this->success('创建成功!', '');
        }
        $tips_list = Db::name("saas_consult_tips")->order("nums desc")->limit("0,9")->select();
        $tips_list = array_column($tips_list, 'tips');
        $this->assign("tips_list", get_tips_list($tips_list));
        return $this->_form(null, '');
    }

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function consultation()
    {
        $this->title = '咨询记录';
        $get = $this->request->get();
        $source = !empty($get['source']) ? $get['source'] : '';
        $branch = !empty($get['branch']) ? $get['branch'] : '';
        $interest = !empty($get['interest']) ? $get['interest'] : '';
        $class_type = !empty($get['class_type']) ? $get['class_type'] : '';
        $time_range = !empty($get['time_range']) ? $get['time_range'] : '';
        $db = Db::name($this->table)->alias('c')
            ->field('c.*, f.type, f.interest,f.follow_status,f.remind_time,f.keyword,f.content,f.user_id,
            f.created_at as follow_time, f.interest_course_1,f.interest_course_2,f.interest_course_3');
        $db->join('(select * from saas_customer_follow where id in ((select max(id) from saas_customer_follow  group by customer_id))) f', 'c.id=f.customer_id', 'left');
        $db->order('c.created_at desc');
//        $db->where('is_student', '<>', 1);   2018.11.7 开
        $db->where('c.status', '<>', 3);
        if (!empty($source)) $db->where('c.source', '=', $source);
        if (!empty($branch)) $db->where('c.branch', '=', $branch);
        if (!empty($interest)) $db->where('f.interest', '=', $interest);
        if (!empty($class_type)) $db->where(['f.interest_course_1|f.interest_course_2|f.interest_course_3' => $class_type]);
        if (isset($get['follow_status']) && !empty($get['follow_status'])) {
            $db->where('f.follow_status', '=', $get['follow_status']);
        }
        if (!empty($time_range)) {
            list($_start, $_end) = explode(' ~ ', $time_range);
            $start = strtotime($_start . " 00:00:00");
            $end = strtotime($_end . " 23:59:59");
            $db->whereBetween('c.created_at', [$start, $end]);
        }
        $user = session('user');
        if ($user['id'] != '10000') {
            $authorize = $user['authorize'];
            $department = $this->user['employee']['department'];
            if (in_array($authorize, [3, 4])) {
                $db->where('1', '<', '2');
            } elseif (in_array($authorize, [5, 9, 20])) {
                $db->where('c.branch', '=', $department);
            } elseif (in_array($authorize, [10, 11])) {
                $db->where('c.branch', '=', $department);
                $db->where(['c.collect_id|c.sales_id|c.userid' => $user['id']]);
            } else {
                $db->where('c.branch', '=', $department);
                $db->where('c.sales_id', '=', $user['id']);
            }
            $this->assign('myauth', $authorize);
        }
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $get['keyword'] = trim($get['keyword']);
            if (preg_match('/^\d{11}$/', $get['keyword'])) {
                $db->where('c.parent_tel', '=', "{$get['keyword']}");
            } elseif (preg_match('/^[\x{4e00}-\x{9fa5}]{1,10}(?:·[\x{4e00}-\x{9fa5}]{2,10})*$/u', $get['keyword'])) {
                $db->where('c.parent_name|c.name|c.tags', 'like', "%{$get['keyword']}%");
            } elseif (preg_match('/^[a-zA-Z0-9]+$/u', $get['keyword'])) {
                $keyword = strtolower($get['keyword']);
                $db->where('c.name_permalink|c.name_abbr|c.name', 'like', '%' . $keyword . '%');
            } else {
                $db->where('1=0');
            }
        }
        if (isset($get['follow_time']) && $get['follow_time'] != '') {
            list($_start, $_end) = explode(' ~ ', $get['follow_time']);
            $start = strtotime($_start . " 00:00:00");
            $end = strtotime($_end . " 23:59:59");
            $db->whereBetween('f.created_at', [$start, $end]);
        }
        $this->assign(['authorize' => $user['authorize']]);
        if (isset($get['action']) && $get['action'] == 'down') {
            $this->dataDown($db, $user['authorize']);//调用导出数据方法
            exit();
        }
        return $this->_list($db);
    }

    /**
     * 新增咨询记录
     */
    public function consultingRecord()
    {
        return $this->_form('saas_customer_follow', 'con_record');
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function customerView()
    {
        $this->title = '客户详情';
        $id = $this->request->get('id', false);
        $customer = Customer::findOrFail($id);
        $order = Db::name('saas_order')->where('status', '<>', 3)->where('student_id', '=', $id)->select();
        $course_record = Db::name('saas_course_log')->where('student_id', '=', $id)->where('status', '<>', 5)
            ->field('course_id,created_at,course_hour,status')->order('created_at desc')->select();
        $log = Db::name('SystemLog')->where('cid', '=', $id)->order('id', 'desc')->limit('20')->select();
        $introducer = Db::name('saas_customer_introducer')->where('customer_id', $id)->field('introducer_id')->find();
        $follow_status = Db::name('saas_customer_follow')->where('customer_id', '=', $id)->order('id desc')->find();
        return view('', [
            'customer' => $customer,
            'title' => $this->title,
            'order' => $order,
            'record' => $course_record,
            'log' => $log,
            'introducer' => $introducer,
            'follow_status' => $follow_status
        ]);
    }

    public function _form_filter(&$data)
    {
        //添加咨询记录之前判断有没有其他更高级的跟进状态,没有就传个参数给前台让其默认为跟进中
        if ($this->request->isGet()) {
            $id = $this->request->get('id');
            $follow_status = Db::name('saas_customer_follow')->where('customer_id', $id)
                ->field("max(follow_status) as follow_status")->find();
            if ($follow_status['follow_status'] <= 2) {      //2为跟进中
                $data['follow_status'] = 2;  //传跟进中的过去让显示在页面
            } else {
                $data['follow_status'] = '';
            }
        }
        if ($this->request->isPost() && isset($data['remind_time'])) {
            $data['remind_time'] = strtotime($data['remind_time']);
            LogService::write('新建咨询', '给【' . get_customer_tel($data['customer_id']) . '】客户新建了一条咨询记录', $data['customer_id']);
        }
    }

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     * 客户基本信息修改
     */
    public function basicedit()
    {
        return $this->_form($this->table, 'basicedit', '', [], [], 'basicedit_form_filter');
    }

    protected function _basicedit_form_filter(&$data)
    {
        if ($this->request->isPost()) {
            //修改的时候不能把电话号码改成已经存在的客户的手机号
            $check_id = $data['id'];
            $tel_arr = Db::name($this->table)->where('id', $check_id)->field('parent_tel')->find();
            $check_tel = $tel_arr['parent_tel'];
            if ($data['parent_tel'] != $check_tel) {
                $count = Db::name($this->table)->where('parent_tel', $data['parent_tel'])->count(1);
                if ($count > 0) {
                    $this->error('这个手机号已经存在');
                }
            }
            !isset($data['birthday']) || $data['birthday'] = strtotime($data['birthday']);
            $data['name_permalink'] = convert_pinyin_permalink($data['name']);
            $data['name_abbr'] = convert_pinyin_abbr($data['name']);

            $info = Db::name('saas_customer')->where('id', '=', $data['id'])->find();
            // 如果没有传值，则保持原来不变（校区及销售员采单员为二级联动，防止只改校区将原采单销售置空）
            foreach ($data as $k => $v) {
                if ($v == '') {
                    unset($data[$k]);
                }
            }
            unset($data['name_permalink']);
            unset($data['name_abbr']);
            $res = array_diff_assoc($data, $info);
            $record = '';
            foreach ($res as $key => $value) {
                switch ($key) {
                    case 'name':
                        $word = '姓名';
                        continue;
                    case 'nickname':
                        $word = '昵称';
                        continue;
                    case 'gender':
                        $word = '性别';
                        $res[$key] = convert_category($res[$key], 3);
                        continue;
                    case 'parent_name':
                        $word = '家长姓名';
                        continue;
                    case 'relation':
                        $word = '亲属关系';
                        $res[$key] = convert_category($res[$key], 9);
                        continue;
                    case 'parent_tel':
                        $word = '联系电话';
                        continue;
                    case 'wechat':
                        $word = '微信号码';
                        continue;
                    case 'birthday':
                        $word = '出生日期';
                        $res[$key] = format_date($res[$key]);
                        continue;
                    case 'address':
                        $word = '居住地址';
                        continue;
                    case 'branch':
                        $word = '校区';
                        $res[$key] = convert_branch($res[$key]);
                        continue;
                    case 'icard':
                        $word = '学员卡号';
                        continue;
                    case 'school':
                        $word = '公立学校';
                        continue;
                    case 'class':
                        $word = '公立班级';
                        continue;
                    case 'other_info':
                        $word = '其他信息';
                        continue;
                    case 'tags':
                        $word = '标签';
                        continue;
                    default :
                        $word = '';
                }
                $record .= $word . '修改为' . '【' . $res[$key] . '】';
            }
            LogService::write('编辑客户', '将客户id为【' . $data['id'] . '】客户的' . $record, $data['id']);


        }
    }

    protected function dataDown($db, $authorize)
    {
        $res = $db->select();
        //处理数据
        $data = [];
        foreach ($res as $k => $v) {
            switch ($v['gender']) {
                case 0:
                    $v['gender'] = "女";
                    break;
                case 1:
                    $v['gender'] = "男";
                    break;
                case 2:
                    $v['gender'] = "未知";
                    break;
            }
            if ($authorize != 1 && $authorize != 3 && $authorize != 4) {
                $v['parent_tel'] = mobile_mask($v['parent_tel']);
            }
            $v['sales_id'] = get_user_realname($v['sales_id']);
            $v['collect_id'] = get_user_realname($v['collect_id']);
            $v['interest_course_1'] = convert_category($v['interest_course_1'], 6);
            $v['interest_course_2'] = convert_category($v['interest_course_2'], 6);
            $v['interest_course_3'] = convert_category($v['interest_course_3'], 6);
            $follow_status = convert_category($v['follow_status'], 7);
            $t = preg_replace('/<(\/?span.*?)>/si', ' ', $follow_status);
            $v['follow_status'] = $t;
            $v['follow_time'] = date('Y-m-d H:i:s', $v['follow_time']);
            $v['remind_time'] = date('Y-m-d H:i:s', $v['remind_time']);
            $v['branch'] = convert_branch($v['branch']);
            $v['source'] = convert_channel($v['source']);
            $data[] = $v;
        }
        //不要的字段删除下面的配置即可
        $key = [
            'name' => '姓名',
            'gender' => '性别',
            'parent_tel' => '监护人电话',
            'sales_id' => '采单员',
            'collect_id' => '销售员(CC)',
            'interest' => '意向度',
            'interest_course_1' => '意向课程1',
            'interest_course_2' => '意向课程2',
            'interest_course_3' => '意向课程3',
            'content' => '沟通记录',
            'follow_status' => '跟进状态',
            'keyword' => '关键词',
            'follow_time' => '跟进时间',
            'remind_time' => '下次跟进时间',
            'branch' => '校区',
            'source' => '渠道',
        ];
        $title = "客户咨询记录";
        LogService::write('导出客户', '导出了客户信息');
        down_excel($data, $key, $title);
    }

    /**
     * 异步获取销售员
     */
    public function get_sales()
    {
        if ($this->request->isPost()) {
            $branch = $this->request->request('school');
            $res = Db::name('saas_employee')->alias('e')
                ->join('system_user u', 'e.userid = u.id', 'left')
                ->field('e.name,u.id,e.userid,u.authorize')
                ->where('e.department', '=', $branch)
                ->select();
            $this->success('', '', $res);
        }
    }

    /**
     * 短信发送
     * @return \think\response\View
     */
    public function sms()
    {
        if ($this->request->isPost()) {
            $ids = $this->request->get('id');
            $content = $this->request->post('content', false);
            if (!$content) {
                $this->error('短信内容不能为空!');
            }
            $userid = session('user')['id'];
            $mobiles = Db::name($this->table)->where('id', 'in', $ids)->column('parent_tel');
            $ret = send_sms_other($mobiles, $content, '爱学学', '', '3'); // 3智深营销短信
            if ($ret) {
                LogService::write('发送短信', '给' . count($mobiles) . '个客户成功发送短信');
                $this->success("短信发送成功!", '');
            } else {
                $this->error('短信发送失败');
            }
        } else {
            $fastowrd = getFastOptions(session('user.authorize'), true);
            return view('sms_content', [
                'fastword' => $fastowrd
            ]);
        }
    }

    /**
     * 发送全部短信
     * @return \think\response\View
     */
    public function all_sms()
    {
        if ($this->request->isPost()) {
            $content = $this->request->post('content', false);
            if (!$content) {
                $this->error('短信内容不能为空!');
            }
            $userid = session('user')['id'];
            $mobiles = Db::name($this->table)
                ->field('parent_tel')
                ->where('status', '<>', 3)
                ->where('is_student', '<>', 1)
                ->order('created_at desc')
                ->column('parent_tel');
            $ret = send_all_sms_other($mobiles, $content, '爱学学', '', '3'); // 3智深营销短信
            if ($ret) {
                LogService::write('发送短信', '给全部客户发送短信成功');
                $this->success("短信发送成功!", '');
            } else {
                $this->error('短信发送失败');
            }
        } else {
            $fastowrd = getFastOptions(session('user.authorize'), true);
            return view('sms_content', [
                'fastword' => $fastowrd
            ]);
        }
    }

    /**
     * 短信快捷回复列表
     * @return array|string
     */
    public function fastword_list()
    {
        $this->title = '短信模板';
        $db = Db::name('system_fast_word')
            ->order('id desc');
        $get = $this->request->get();
        foreach (['authorize_id'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', $get[$key]);
        }
        return parent::_list($db, true);
    }

    /**
     * 添加短信模板
     * @return mixed
     */
    public function add_fastword()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $data = [
                'authorize_id' => $post['authorize_id'],
                'content' => $post['content'],
                'addtime' => $post['addtime']
            ];
            $res = Db::name('system_fast_word')
                ->insert($data);
            if ($res) {
                LogService::write('短信模板', '添加了短信模板');
                $this->success("添加成功成功!", '');
            } else {
                $this->error('添加失败');
            }
        } else {
            return $this->fetch('add_fastword');
        }

    }

    /**
     * 编辑短信模板
     * @return array|string
     */
    public function edit_fastword()
    {
        $this->title = '编辑短信模板';
        return $this->_form($this->table2, 'add_fastword');
    }

    /**
     * 删除短信模板
     */
    public function del_fastword()
    {
        if (DataService::update($this->table2)) {
            LogService::write('短信模板', '删除了短信模板');
            $this->success("删除成功!", '');
        }
        $this->error("删除失败, 请稍候再试!");
    }

    /**
     * @return array|string
     * 维护记录
     */
    public function consultationlog()
    {
        $this->title = '客户列表';
        $user = $this->user;
        $db = Db::name($this->table)->alias('c')
            ->field('c.*, f.type, f.interest,f.follow_status,f.remind_time,f.keyword,f.content,f.user_id,
            f.created_at as follow_time, f.interest_course_1,f.interest_course_2,f.interest_course_3');
        $db->join('(select * from saas_customer_follow where id in ((select max(id) from saas_customer_follow  group by customer_id))) f', 'c.id=f.customer_id', 'right');
        $db->order('c.created_at desc');
        $get = $this->request->get();
        if (isset($get['follow_status']) && !empty($get['follow_status'])) {
            $db->where('f.follow_status', '=', $get['follow_status']);
        }
        if (isset($get['branch']) && !empty($get['branch'])) {
            $db->where('c.branch', '=', $get['branch']);
        }
        if (isset($get['dxy']) && !empty($get['dxy'])) {
            $db->where('c.sales_id', '=', $get['dxy']);
        }
        if (isset($get['cc']) && !empty($get['cc'])) {
            $db->where('c.collect_id', '=', $get['cc']);
        }
        if (isset($get['time_range']) && $get['time_range'] != '') {
            $get['time_range'] = str_replace('+~+', ' ~ ', $get['time_range']);
            list($start, $end) = explode(' ~ ', $get['time_range']);
            $_start = strtotime($start);
            $_end = strtotime($end) + 86400;
            if (($_end - $_start) / 86400 > 60) {
                $_start = strtotime('-60 days', $_end);
            }
            $db->whereBetween('f.created_at', [$_start, $_end]);
        }
        if (!in_array($user['authorize'], [1, 3, 4])) {
            $db->where('c.branch', '=', $user['employee']['department']);
        }
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $get['keyword'] = trim($get['keyword']);
            if (preg_match('/^\d{11}$/', $get['keyword'])) {
                $db->where('c.parent_tel', '=', "{$get['keyword']}");
            } elseif (preg_match('/^[\x{4e00}-\x{9fa5}]{2,10}(?:·[\x{4e00}-\x{9fa5}]{2,10})*$/u', $get['keyword'])) {
                $db->where('c.parent_name|c.name', '=', "{$get['keyword']}");
            } elseif (preg_match('/^[a-zA-Z0-9]+$/u', $get['keyword'])) {
                $keyword = strtolower($get['keyword']);
                $db->where('c.name_permalink|c.name_abbr', '=', $keyword);
            } else {
                $db->where('1=0');
            }
        }

        if (isset($get['action']) && $get['action'] == 'down') {
            $this->dataDown($db, $user['authorize']);//调用导出数据方法
            exit();
        }
        $this->assign(['authorize' => $user['authorize']]);
        return $this->_list($db);
    }

    /***
     * @return \think\response\View
     * 客户统计
     */
    public function customercount()
    {
        $title = '客户统计';
        $user = $this->user;
        $branch_sql = Db::name('saas_school_branch');
        $customer_sql = Db::name('saas_customer')->where('status', '<>', 3);
        $cdy_sql = Db::name('saas_employee')->where('status', '<>', 3);
        $get = $this->request->get();
        if (isset($get['date']) && $get['date'] != '') {
            $date = date('Y-m') . '月';
            $start = mktime(0, 0, 0, date('m'), 1, date('Y'));
            $end = strtotime(date('Y-m-d')) + 86400;
        } else {
            $date = date('Y-m-d');
            $start = strtotime($date);
            $end = $start + 86400;
        }
        //到访量
        $dfl = "SELECT count(DISTINCT f.follow_status) as dfl FROM `saas_customer` `c` inner JOIN `saas_customer_follow` `f` ON `c`.`id`=`f`.`customer_id` WHERE  `f`.`follow_status` = '5'";
        //成交量
        $done_sql = "SELECT count(*) as cjl FROM `saas_customer` `c` inner JOIN `saas_customer_follow` `f` ON 
                    `c`.`id`=`f`.`customer_id` WHERE  `f`.`follow_status` = '7' and 
                    f.id in ((select max(id) from saas_customer_follow  group by customer_id))";

        if (in_array($user['authorize'], [1, 3, 4])) {
            if (isset($get['branch']) && !empty($get['branch'])) {
                $branch = $branch_sql->where('id', '=', $get['branch'])->field('title')->find();
                $customer_sql->where('first_branch|branch', '=', $get['branch']);
                $cdy = $cdy_sql->where('department', '=', $get['branch'])->column('userid');
                $dfl .= 'and c.branch =' . $get['branch'];
                $done_sql .= 'and c.branch =' . $get['branch'];
            } else {
                $branch = $branch_sql->where('id', '=', 1)->field('title')->find();
                $customer_sql->where('branch', '=', 1);
                $cdy = $cdy_sql->where('department', '=', 1)->column('userid');
                $dfl .= ' and c.branch = 1';
                $done_sql .= ' and c.branch = 1';
            }
        } else {
            $branch = $branch_sql->where('id', '=', $user['employee']['department'])->field('title')->find();
            $customer_sql->where('first_branch|branch', '=', $user['employee']['department']);
            $cdy = $cdy_sql->where('department', '=', $user['employee']['department'])->column('userid');
            $dfl .= ' and c.branch =' . $user['employee']['department'];
            $done_sql .= ' and c.branch =' . $user['employee']['department'];
        }
        //采单员咨询量
        if ($cdy == []) {
            $str_cdy = 1;
        } else {
            $str_cdy = implode(',', $cdy);
        }
        if (isset($get['time_range']) && $get['time_range'] != '') {
            $get['time_range'] = str_replace('+~+', ' ~ ', $get['time_range']);
            list($start, $end) = explode(' ~ ', $get['time_range']);
            $start = strtotime($start);
            $end = strtotime($end) + 86400;
            if (($end - $start) / 86400 > 60) {
                $start = strtotime('-60 days', $end);
            }
            $total = $customer_sql->whereBetween('created_at', [$start, $end])->count();
            $number = $this->follow_sql()->whereIn('user_id', $cdy)->whereBetween('created_at', [$start, $end])->group('customer_id')->count();
           } else {
            $total = $customer_sql->whereBetween('created_at', [$start, $end])->count();
            $number = $this->follow_sql()->whereIn('user_id', $cdy)->whereBetween('created_at', [$start, $end])->group('customer_id')->count();
           }
        //到访量
        $dfl .= ' and f.created_at BETWEEN ' . $start . ' and ' . $end;
        $dfl = Db::query($dfl);
        //成交量
        $done_sql .= ' and f.created_at BETWEEN ' . $start . ' and ' . $end;
        //$done_sql = "SELECT count(1) as cjl from saas_customer_follow where id IN (SELECT max(id) from saas_customer_follow GROUP BY customer_id) AND `follow_status` = 7 AND created_at BETWEEN $start and $end and user_id IN ($str_cdy)";
        $done = Db::query($done_sql);
        return view('', ['branch' => $branch,
            'title' => $title,
            'total' => $total,
            'number' => $number,
            'dfl' => $dfl[0]['dfl'],
            'done' => $done[0]['cjl'],
            'date' => $date
        ]);
    }

    /**
     * @return \think\response\Json
     * 客户统计数据
     */
    public function get_data()
    {
        $user = $this->user;

        $way = $this->request->post('way');
        if (isset($way) && $way == 'month') {
            $_start = mktime(0, 0, 0, date('m'), 1, date('Y'));
            $_end = strtotime(date('Y-m-d')) + 86400;
        } elseif (isset($way) && $way == 'day') {
            $_start = strtotime(date('Y-m-d'));
            $_end = $_start + 86400;
        }

        $branch = $this->request->post('branch');
        $time = $this->request->post('time');

        $dfl = "SELECT c.sales_id,count(DISTINCT f.follow_status) as tj FROM `saas_customer` `c` inner JOIN `saas_customer_follow` `f` ON `c`.`id`=`f`.`customer_id` WHERE  `f`.`follow_status` = '5'";
        $cjl_sql = "SELECT f.user_id,count(*) as tj FROM `saas_customer` `c` inner JOIN `saas_customer_follow` `f` ON 
                    `c`.`id`=`f`.`customer_id` WHERE  `f`.`follow_status` = '7' and 
                    f.id in ((select max(id) from saas_customer_follow  group by customer_id))";
        $cdy_sql = Db::name('saas_employee')->where('status', '<>', 3);

        if (in_array($user['authorize'], [1, 3, 4])) {
            if (!empty($branch)) {
                $cdy_sql->where('department', '=', $branch);
                $dfl .= ' and c.branch =' . $branch;
                $cjl_sql .= ' and c.branch =' . $branch;
            } else {
                $cdy_sql->where('department', '=', 1);
                $dfl .= ' and c.branch = 1';
                $cjl_sql .= ' and c.branch = 1';
            }
        } else {
            $cdy_sql->where('department', '=', $user['employee']['department']);
            $dfl .= ' and c.branch =' . $user['employee']['department'];
            $cjl_sql .= ' and c.branch =' . $user['employee']['department'];
        }
        $cdy = $cdy_sql->column('name', 'userid');
        $keys = array_keys($cdy);
        if ($time != '') {
            $time = str_replace('+~+', ' ~ ', $time);
            list($start, $end) = explode(' ~ ', $time);
            $_start = strtotime($start);
            $_end = strtotime($end) + 86400;
            if (($_end - $_start) / 86400 > 60) {
                $_start = strtotime('-60 days', $_end);
            }
            $db = $this->follow_sql()->whereBetween('created_at', [$_start, $_end]);
        } else {
            $db = $this->follow_sql()->whereBetween('created_at', [$_start, $_end]);
        }
        $zx = $db->whereIn('user_id', $keys)->group('customer_id')->field('user_id,customer_id')->select();
        $zx_user = [];
        foreach ($keys as $k => $v) {
            $num = 0;
            foreach ($zx as $k1 => $v1) {
                if ($v == $zx[$k1]['user_id']) {
                    $num += 1;
                }
            }
            $zx_user[$k]['num'] = $num;
            $zx_user[$k]['user_id'] = $v;
        }
        $zx_user = array_column($zx_user, 'num');
        //成交量
        if (empty($keys)) {
            $str_cdy = 1;
        } else {
            $str_cdy = implode(',', $keys);
        }
        $cjl_sql .= ' and f.created_at BETWEEN ' . $_start . ' and ' . $_end . ' GROUP BY f.user_id';
        $cjl_total = Db::query($cjl_sql);
        $cjl_last = $this->handle_data($cjl_total, $keys);
        //到访量
        $dfl .= ' and f.created_at BETWEEN ' . $_start . ' and ' . $_end;
        $dfl_total = Db::query($dfl);
        $dfl_last = $this->handle_data($dfl_total, $keys);
        $arr = ['name' => array_values($cdy), 'zixun' => $zx_user, 'chengjiao' => $cjl_last, 'daofang' => $dfl_last];
        return json($arr);
    }

    private function follow_sql()
    {
        $sql = Db::name('saas_customer_follow')->order('created_at desc');
        return $sql;
    }

    //统计数据处理
    private function handle_data($total, $keys)
    {
        $count = [];
        foreach ($total as $k => $v) {
            if (isset($v['sales_id'])) {
                $count[$v['sales_id']] = $v['tj'];
            } elseif (isset($v['user_id'])) {
                $count[$v['user_id']] = $v['tj'];
            }
        }
        $last = [];
        foreach ($keys as $k => $v) {
            foreach ($count as $k1 => $v1) {
                if ($k1 == $v) {
                    $last[$k] = $v1;
                    break;
                }
                $last[$k] = 0;
            }
        }
        return $last;
    }
}