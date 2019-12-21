<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/30
 * Time: 16:46
 */

namespace app\admin\controller;

use app\common\model\SystemUser;
use controller\BasicAdmin;
use service\DataService;
use think\Db;
use think\Exception;
use think\facade\Cache;

class Employee extends BasicAdmin
{
    public $table = 'saas_employee';

    /**
     * @return array|string
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function index()
    {
        $get = $this->request->get();
        $this->title = '员工管理';
        $db = Db::name($this->table)
            ->alias('e')
            ->field('e.*, a.title as username, u.status as sys_status')
            ->join('system_user u', 'e.userid=u.id')
            ->join('system_auth a', 'u.authorize=a.id')
            ->order('e.id desc')
            ->where('e.status', '<>', 3)
            ->where('u.status', '<>', 3);
        (isset($get['branch']) && $get['branch'] !== '') && $db->where('department', '=', $get['branch']);
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $get['keyword'] = trim($get['keyword']);
            if (preg_match('/^\d{4}$/', $get['keyword'])) {
                $db->where('e.mobile', 'like', "%{$get['keyword']}%");
            } elseif (preg_match('/^\d{11}$/', $get['keyword'])) {
                $db->where('e.mobile', '=', "{$get['keyword']}");
            } elseif (preg_match('/[a-zA-Z\x{4e00}-\x{9fa5}]/u', $get['keyword'])) {
                $db->where('e.name|e.english_name', 'like', "%{$get['keyword']}%");
            } else {
                $db->where('1=0');
            }
        }
        if (isset($get['birthday']) && $get['birthday'] != '') {
            switch ($get['birthday']) {
                case 'day':
                    $db->where('FROM_UNIXTIME(e.birthday,\'%m%d\')=\'' . date('md') . '\'');
                    break;
                case 'month':
                    $db->where('FROM_UNIXTIME(e.birthday,\'%m\')=\'' . date('m') . '\'');
                    break;
                case 'next':
                    $db->where('FROM_UNIXTIME(e.birthday,\'%m\')=\'' . date('m', strtotime('+1 month')) . '\'');
                    break;
            }
        }
        if (isset($get['authorize']) && $get['authorize'] != '') {
            $db->where('u.authorize', '=', $get['authorize']);
        }
        if (!in_array($this->user['authorize'], [1, 3, 4, 23])) {
            $db->where('department', '=', $this->user['employee']['department']);
        }
        if (isset($get['action']) && $get['action'] == 'down') {
            $this->dataDown($db);//调用导出数据方法

        }
        $db->where('authorize','<>',1);
        return parent::_list($db, true);
    }

    /**
     * @return array|string
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function add()
    {
        $this->title = '新建员工档案';
        return $this->_form($this->table, 'employee');
    }

    /**
     * @param $vo
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    protected function _form_filter(&$vo)
    {
        if ($this->request->isGet() && isset($vo['id'])) {
            $vo['user'] = Db::name('system_user')->where('id', '=', $vo['userid'])->find();
        }
        if ($this->request->isPost()) {
            !isset($vo['birthday']) || $vo['birthday'] = strtotime($vo['birthday']);
            !isset($vo['contract_date_begin']) || $vo['contract_date_begin'] = strtotime($vo['contract_date_begin']);
            !isset($vo['contract_date_end']) || $vo['contract_date_end'] = strtotime($vo['contract_date_end']);
            !isset($vo['zz_date']) || $vo['zz_date'] = strtotime($vo['zz_date']);
        }
        if ($this->request->isPost() && isset($vo['id'])) {
            $this->_form_after();
        }
        if ($this->request->isPost() && !isset($vo['id'])) {
            // system_user 插入
            $user = $this->request->post('user/a');
            $user['phone'] = $vo['mobile'];
            if ($user['password'] != '') {
                $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
                $user['password_encode'] = encode($user['password']);
            }
            if (!isset($user['authorize']) || $user['authorize'] == '') {
                $this->error('请选择用户角色');
            }
            if (Db::name('system_user')->where('phone', '=', $vo['mobile'])->field('id')->find()) {
                $this->error('用户手机号码已存在, 请重新输入或联系管理员解封!');
            }
            // 检测邮箱重复
            if (Db::name('system_user')->where('mail', '=', $user['mail'])->field('id')->find()) {
                $this->error('用户邮箱已存在, 请重新输入!');
            }
            $vo['userid'] = Db::name('system_user')->insertGetId($user);
        }
    }

    /**
     * @param $vo
     * @author Jasmine2
     */
    protected function _form_result($vo)
    {
        list($base, $spm, $url) = [url('@admin'), $this->request->get('spm'), url('admin/employee/index')];
        if (Cache::has('users10') || Cache::has('users11')) {
            Cache::rm('users10');
            Cache::rm('users11');
        }
        if ($vo === true) {
            $this->success('员工信息修改成功！', "{$base}#{$url}?spm={$spm}");
        } else {
            $this->success('添加员工成功！', "{$base}#{$url}?spm={$spm}");
        }
    }

    /**
     * @author Jasmine2
     * @throws
     * 只处理更新
     */
    protected function _form_after()
    {
        $user = $this->request->post('user/a');
        $user['phone'] = $this->request->post('mobile');
        $user_id = $user['id'];
        if ($user['password'] != '') {
            $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
            $user['password_encode'] = encode($user['password']);
        } else {
            unset($user['password']);
        }
        unset($user['id']);
        Db::name('system_user')->where('id', '=', $user_id)->update($user);
    }

    /**
     * @return array|string
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function edit()
    {
        $this->title = '编辑员工';
        return $this->_form($this->table, 'employee');

    }

    /**
     * @throws
     * @author HZB
     * 员工删除同时对机构账号软删除
     */
    public function del()
    {
        $id = $this->request->post('id');
        $userid = Db::name($this->table)->field('userid')->where('id', 'in', $id)->column('userid');
        Db::name($this->table)->where('id', 'in', $id)->update(['status' => 3]);
        Db::name("system_user")->where('id', 'in', $userid)->update(['is_deleted' => 1]);
        $this->success('删除成功', '');
    }

    /**
     * 员工绑卡
     */
    public function card()
    {
        $id = $this->request->request('id');
        if ($this->request->isPost()) {
            $card = $this->request->request('cardNo');
            $check_card = $this->check_card($card);
            if ($check_card == 'true') {
                $this->error('该卡已绑定员工，请不要重复绑卡');
            }
            if (strlen($card) >= 10) {
                $res = Db::name($this->table)->where('id', '=', $id)->update(['icard' => $card]);
                if ($res) {
                    $this->success('绑卡成功', '');
                } else {
                    $this->error('绑卡失败');
                }
            }
        }
        $this->assign('id', $id);
        return $this->fetch('form');

    }

    /**
     * 检测卡号绑定唯一
     */
    public function check_card($card)
    {
        $res = Db::name($this->table)->where('icard', '=', $card)->where('status', '<>', 3)->select();
        if ($res) {
            return 'true';
        } else {
            return 'false';
        }
    }

    /**
     * @param $db
     * 导出数据
     */
    protected function dataDown($db)
    {
        $res = $db->select();
        //处理数据
        $data = [];
        foreach ($res as $k => $v) {
            $v['birthday'] = date('Y-m-d', $v['birthday']);
            $v['personnel_status'] = convert_category($v['personnel_status'], 17);
            $v['contract_status'] = convert_category($v['contract_status'], 21);
            $v['department'] = convert_branch($v['department']);
            isset($v['icard']) ? $v['icard'] : $v['icard'] = "未绑卡";
            $data[] = $v;
        }
        //不要的字段删除下面的配置即可
        $key = [
            'name' => '姓名',
            'mobile' => '手机号',
            'birthday' => '生日',
            'personnel_status' => '人事状态',
            'contract_status' => '合同状态',
            'department' => '所属校区',
            'username' => '用户名',
            'icard' => '员工卡号',
            'username' => '用户身份',
        ];
        $title = "员工表";
        down_excel($data, $key, $title);
    }
}