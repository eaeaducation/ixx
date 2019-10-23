<?php
/**
 * User: Jasmine2
 * Date: 2018/6/21 15:05
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\marketing\controller;

use app\common\model\Customer;
use controller\BasicAdmin;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use think\Db;
use think\Exception;
use  think\facade\Cache;
use service\DataService;
use think\facade\Log;
use service\LogService;

class Customerpool extends BasicAdmin
{
    public $table = 'saas_customer';

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function index()
    {
        $this->title = '客户列表';
        $db = Db::name($this->table)->alias('c')->field('c.*,f.follow_status,f.keyword,f.content,f.created_at as follow_time,f.remind_time');
        $db->join('(select * from saas_customer_follow where id in ((select max(id) from saas_customer_follow  group by customer_id))) f', 'c.id=f.customer_id', 'left');
      //  $db->where('is_student', '<>', '1');
        $db->where("c.status", "<>", "3");
        $get = $this->request->get();
        if (isset($get['isfp']) && $get['isfp'] == 'fp') {
            $db->where('c.status', '=', "99");
        }
        if (isset($get['isfp']) && $get['isfp'] == 'dfp') {
            $db->where('c.status', '<>', "99");
        }
        if (isset($get['branch']) && !empty($get['branch'])) {
            $db->where('c.branch', '=', $get['branch']);
        }
        if (isset($get['follow_status']) && !empty($get['follow_status'])) {
            $db->where('f.follow_status', '=', $get['follow_status']);
        }
        if (isset($get['source']) && !empty($get['source'])) {
            $db->where('c.source', '=', $get['source']);
        }
        $dxy = isset($get['dxy']) ? $get['dxy'] : 0;
        $cc = isset($get['cc']) ? $get['cc'] : 0;
        if (isset($get['time_range']) && $get['time_range'] != '') {
            $get['time_range'] = str_replace('+~+', ' ~ ', $get['time_range']);
            list($start, $end) = explode(' ~ ', $get['time_range']);
            $_start = strtotime($start);
            $_end = strtotime($end) + 86400;
            if (($_end - $_start) / 86400 > 60) {
                $_start = strtotime('-60 days', $_end);
            }
            $db->whereBetween('c.created_at', [$_start, $_end]);
        }
        if (isset($get['follow_time']) && $get['follow_time'] != '') {
            $get['follow_time'] = str_replace('+~+', ' ~ ', $get['follow_time']);
            list($start, $end) = explode(' ~ ', $get['follow_time']);
            $_start = strtotime($start);
            $_end = strtotime($end) + 86400;

            $db->whereBetween('f.created_at', [$_start, $_end]);
        }
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $get['keyword'] = trim($get['keyword']);
            if (preg_match('/^\d{11}$/', $get['keyword'])) {
                $db->where('c.parent_tel', '=', "{$get['keyword']}");
            } elseif (preg_match('/^[\x{4e00}-\x{9fa5}]{2,10}(?:·[\x{4e00}-\x{9fa5}]{2,10})*$/u', $get['keyword'])) {
                $db->where('c.parent_name|c.name|c.tags', 'like', '%'.$get['keyword'].'%');
            } elseif (preg_match('/^[a-zA-Z0-9]+$/u', $get['keyword'])) {
                $keyword = strtolower($get['keyword']);
                $db->where('c.name_permalink|c.name_abbr|c.name', 'like', '%'.$keyword.'%');
            } else {
                $db->where('1=0');
            }
        }
        if ($cc) $db->where('c.collect_id', '=', $cc);
        if ($dxy) $db->where('c.sales_id', '=', $dxy);
        $userid = $this->user['id'];
        if ($userid != '10000') {
            $authorize = $this->user['authorize'];
            $department = $this->user['employee']['department'];
            if (in_array($authorize, [3, 4])) {
                $db->where('1', '<', '2');
            } elseif (in_array($authorize, [5, 9])) {
                $db->where('c.branch', '=', $department);
            } elseif (in_array($authorize, [10, 11])) {
                $db->where('c.branch', '=', $department);
                $db->where(['c.collect_id|c.sales_id|c.userid' => $userid]);
            } elseif (in_array($authorize, [21])) {
                $db->where('1', '<', '2');
            } else {
                $db->where('c.branch', '=', $department);
                $db->where('c.sales_id', '=', $userid);
            }
            $this->assign('myauth', $authorize);
        }
        $db->order('c.created_at desc');
        return $this->_list($db);
    }

    public function del()
    {
        if (DataService::update($this->table)) {
            LogService::write('删除客户', '删除了一个客户');
            $this->success("删除成功!", '');

        }
        $this->error("删除失败, 请稍候再试!");
    }

    public function electricpin()
    {
        if ($this->request->isPost()) {
            $ids = $this->request->get('id');
            $row = Db::name($this->table)
                ->whereIn('id', $ids);
            if (empty(input('post.tel_user'))) {
                $this->error('您没有选择电销员');
            } else {
                list($authorize, $tel_user) = explode('-', $this->request->post('tel_user'));
                if (in_array($authorize, [11, 3, 5, 9])) {
                    $data['collect_id'] = $tel_user;
                } else {
                    $data['sales_id'] = $tel_user;
                }
                $data['status'] = 99;
                $row = $row->update($data);
                $idcount = count(explode(',', $ids));
                $fail = $idcount - $row;
                if ($row) {
                    $id = explode(',', $ids);
                    foreach ($id as $value) {
                        LogService::write('分配客户', '将客户分配给' . (empty(get_user_realname($tel_user)) ? $tel_user : get_user_realname($tel_user)), $value);
                    }
                    $this->success('分配成功' . $row . '个用户，分配失败' . $fail . '个用户', '');
                } else {
                    $this->error('分配失败');
                }
            }
        } else {
            $electricpiners = [];
            $userid = isset($this->user['id']) ? $this->user['id'] : '';
            //Cache::rm('electricpin_select'.$userid);
            if (Cache::get('electricpin_select' . $userid)) {
                $electricpiners = Cache::get('electricpin_select' . $userid);
            } else {
                if (isset($this->user['employee']['department']) && !empty($this->user['employee']['department'])) {
                    $departmentid = $this->user['employee']['department'];
                    $electricpiners = Db::name("saas_employee")->alias("s")->join("system_user u", "s.userid = u.id", "right")->
                    join("system_auth a", "u.authorize = a.id", "left")->
                    where("s.status", "<>", "3")->where("u.is_deleted", "<>", "1")
                        ->field("s.id,s.name,s.mobile,s.userid,s.status,s.department,u.authorize,a.title,s.english_name")->order("s.created_at", "desc");
                    if (!in_array(session('user')['authorize'], [1, 3, 4])) {
                        $electricpiners->where("s.department", "=", $departmentid);
                    }
                    $electricpiners = $electricpiners->select();
                    Cache::set('electricpin_select' . $userid, $electricpiners, '3600');
                } else {
                    $electricpiners = Db::name("saas_employee")->alias("s")->join("system_user u", "s.userid = u.id", "right")->
                    join("system_auth a", "u.authorize = a.id", "left")->
                    where("s.status", "<>", "3")->where("u.is_deleted", "<>", "1")
                        ->field("s.id,s.name,s.mobile,s.userid,s.status,s.department,u.authorize,a.title,s.english_name")->order("s.created_at", "desc")->select();
                    Cache::set('electricpin_select' . $userid, $electricpiners, '3600');
                }
            }
            return view('fpform', [
                'electricpiners' => $electricpiners,
                'title' => '选择销售员'
            ]);
        }
    }

    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            if (!isset($vo['id'])) {
                $is_save = Customer::get(['parent_tel' => $vo['parent_tel'], 'status' => [0,1,99]]);
                if ($is_save) {
                    $this->error('手机号码重复，客户 ' . $is_save['parent_name'] . ' 已录入！');
                }
                $vo['first_branch'] = $vo['branch'];
            } else {
                $info = Db::name('saas_customer')->where('id', '=', $vo['id'])->find();
                // 如果没有传值，则保持原来不变（校区及销售员采单员为二级联动，防止只改校区将原采单销售置空）
                foreach ($vo as $k=>$v){
                    if ($v == ''){
                        unset($vo[$k]);
                    }
                }
                $res = array_diff_assoc($vo, $info);
                $record = '';
                foreach ($res as $key => $value) {
                    switch ($key) {
                        case 'name':
                            $word = '姓名';
                            continue;
                        case 'gender':
                            $word = '性别';
                            $res[$key] = convert_category($res[$key], 3);
                            continue;
                        case 'parent_name':
                            $word = '家长姓名';
                            continue;
                        case 'parent_tel':
                            $word = '联系电话';
                            continue;
                        case 'relation':
                            $word = '亲属关系';
                            $res[$key] = convert_category($res[$key], 9);
                            continue;
                        case 'branch':
                            $word = '校区';
                            $res[$key] = convert_branch($res[$key]);
                            continue;
                        case 'source':
                            $word = '来源渠道';
                            $res[$key] = convert_channel($res[$key]);
                            continue;
                        case 'collect_id':
                            $word = '销售员';
                            $res[$key] = get_user_realname($res[$key]);
                            continue;
                        case 'sales_id':
                            $word = '采单员';
                            $res[$key] = get_user_realname($res[$key]);
                            continue;
                        default :
                            $word = '';
                    }
                    $record .= $word . '修改为' . '【' . $res[$key] . '】';
                }
                LogService::write('编辑客户', '将客户id为【' . $vo['id'] . '】客户的' . $record, $vo['id']);
            }
            if (empty($vo['collect_id']) && empty($vo['sales_id'])) {
                $vo['status'] = '';
            } else {
                $vo['status'] = 99;
            }
            $vo['name_permalink'] = convert_pinyin_permalink($vo['name']);
            $vo['name_abbr'] = convert_pinyin_abbr($vo['name']);
        }
    }

    protected function _form_result($result)
    {
        LogService::write('添加客户', '在客户池添加了一个客户', $result);
    }

    /**
     * 导入客户
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function import_form()
    {
        if ($this->request->isPost()) {
            $file = $this->request->post('file', false);
            if ($file == '') {
                $this->error('请先上传文件!');
            }
            $file = PROJECT_PATH . str_replace($this->request->domain(), '', $file);
            return $this->import_customer($file);
        }
        return $this->fetch('import_form');
    }

    private function import_customer($file)
    {
        set_time_limit(0);
        ignore_user_abort(1);

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(TRUE);
        $PHPExcel = $reader->load($file);
        $sheet = $PHPExcel->getActiveSheet();

        $colNum = ord($sheet->getHighestColumn()) - ord('A') + 1;
        $rowNum = $sheet->getHighestRow();
        if ($rowNum > 5000) {
            $this->error('一次最多导入5000条数据');
        }
        $data = [];
        for ($i = 2; $i <= $rowNum; $i++) {
            $tmp = [];
            for ($j = 1; $j <= $colNum; $j++) {
                $tmp[] = $sheet->getCellByColumnAndRow($j, $i)->getValue();
            }
            array_push($data, $tmp);
        }
        foreach ($data as $k => $v) {
            $arraylength = implode('', $v);
            if (empty($arraylength)) {
                unset($data[$k]);
            }
        }
        $insertArr = [];
        $employees = Db::name('saas_employee')->column('userid', 'name');
        $_parent_tel = Db::name('saas_customer')->field('parent_tel')->select();
        $channel = Db::name('saas_channel')->column('id', 'title');
        $parent_tel = [];
        $repeat_tel = [];
        foreach ($_parent_tel as $v) {
            $parent_tel[] = $v['parent_tel'];
        }
        foreach ($data as $key => $item) {
            $relation = get_category(9);
            $relationship = array_flip($relation);
            switch ($item[1]) {
                case "女":
                    $item[1] = 0;
                    break;
                case "男":
                    $item[1] = 1;
                    break;
                case "未知":
                    $item[1] = 2;
                    break;
            }
            if (empty($item[3])) {
                $this->error('每条数据的监护人电话都必须填写');
            }
            if (empty($item[7])) {
                $this->error('每条数据的校区都必须填写');
            }
            if (in_array($item[3], $parent_tel)) {
                $repeat_tel[] = $item[3];
                unset($item);
                continue;
            }
            if (isset($item[9]) || isset($item[10])) {
                $status = 99;
            }
            if (in_array($item[11], array_keys($channel))) {
                $source = $channel[$item[11]];
            } else {
                $source = '';
            }
            array_push($insertArr, [
                'name' => isset($item[0]) ? $item[0] : "",
                'gender' => isset($item[1]) ? $item[1] : "",
                'parent_name' => isset($item[2]) ? $item[2] : "",
                'parent_tel' => $item[3],
                'tags' => isset($item[4]) ? $item[4] : '',
                'relation' => isset($relationship[$item[5]]) ? $relationship[$item[5]] : '',
                'school' => isset($item[6]) ? $item[6] : '',
                'branch' => get_branches_name($item[7]),
                'address' => isset($item[8]) ? $item[8] : '',
                'sales_id' => isset($employees[$item[9]]) ? $employees[$item[9]] : '',
                'collect_id' => isset($employees[$item[10]]) ? $employees[$item[10]] : '',
                'name_permalink' => isset($item[0]) ? convert_pinyin_permalink($item[0]) : '',
                'name_abbr' => isset($item[0]) ? convert_pinyin_abbr($item[0]) : '',
                'created_at' => time(),
                'source' => $source,
                'is_student' => 0,
                'status' => isset($status) ? 99 : 1,
                'first_branch' => get_branches_name($item[7]),
                'other_info' => isset($item[12]) ? substr($item[12], 0, 128) : '',
            ]);
        }
        $data = array_chunk($insertArr, 1000);
        Db::startTrans();
        try {
            foreach ($data as $item) {
                Db::name('saas_customer')->insertAll($item);
            }
            Db::commit();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Db::rollback();
        }
        if (count($repeat_tel) > 0) {
            $url = url('repeat_tel');
            Cache::set('repeat_tel' . $this->user['id'], $repeat_tel, 5000);
            LogService::write('导入客户', '批量导入了' . count($insertArr) . '条客户');
            $this->success('成功导入[' . count($insertArr) . ']条记录,重复[' . count($repeat_tel) . ']条记录', $url);
        } else {
            LogService::write('导入客户', '批量导入了' . count($insertArr) . '条客户');
            $this->success('成功导入[' . count($insertArr) . ']条记录', '');
        }

    }

    public function repeat_tel()
    {
        $data = Cache::get('repeat_tel' . $this->user['id']);
        $key = ['tel' => '电话', 'info' => "信息"];
        $res = [];
        foreach ($data as $k => $v) {
            $res[$k]['tel'] = $v;
            $res[$k]['info'] = "该客户已存在";
        }
        down_excel($res, $key, '重复客户信息');
    }

    /**
     * 快捷操作
     * 更改校区和渠道
     */
    public function Quick()
    {
        if ($this->request->isPost()) {
            $ids = $this->request->get('id');
            $idArray = explode(',', $ids);
            $data = $this->request->post();
            if ($data['type'] == 1) {
                //修改校区
                if (empty($data['branch'])) {
                    $this->error('请选择校区!');
                }
                $branch = $data['branch'];
                $res = Db::name('saas_customer')->whereIn('id', $idArray)->update(['branch' => $branch]);
                if ($res) {
                    LogService::write('编辑客户', '批量编辑了' . count($idArray) . '条客户的校区');
                    $this->success('批量编辑成功', '');
                } else {
                    $this->error('批量编辑失败');
                }
            } elseif ($data['type'] == 2) {
                //修改渠道
                if (empty($data['source'])) {
                    $this->error('请选择渠道!');
                }
                $source = $data['source'];
                $res = Db::name('saas_customer')->whereIn('id', $idArray)->update(['source' => $source]);
                if ($res) {
                    LogService::write('编辑客户', '批量编辑了' . count($idArray) . '条客户的渠道');
                    $this->success('批量编辑成功', '');
                } else {
                    $this->error('批量编辑失败');
                }
            } elseif ($data['type'] == 3) {
                //修改采单员
                if (empty($data['cdy'])) {
                    $this->error('请选择采单员!');
                }
                $cdy = $data['cdy'];
                $res = Db::name('saas_customer')->whereIn('id', $idArray)->update(['sales_id' => $cdy]);
                if ($res) {
                    LogService::write('编辑客户', '批量编辑了' . count($idArray) . '条客户的采单员');
                    $this->success('批量编辑成功', '');
                } else {
                    $this->error('批量编辑失败');
                }
            }

        }
        return $this->fetch('', ['title' => '批量操作']);
    }


}