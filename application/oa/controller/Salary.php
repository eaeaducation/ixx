<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/17
 * Time: 14:34
 */

namespace app\oa\controller;

use controller\BasicAdmin;
use service\DataService;
use think\Db;
use think\facade\Log;

class Salary extends BasicAdmin
{

    public $table = "saas_salary";

    /**
     * 薪资列表
     */
    public function index()
    {
        $db = Db::name($this->table)
            ->alias('s')
            ->join('saas_employee e', 's.employee_id=e.id')
            ->where('s.status', '<>', '3')
            ->field('e.name,s.*');
        $get = $this->request->get();
        if (isset($get['branch']) && $get['branch'] != '') {
            $get['branch'] = trim($get['branch']);
            $db->where('s.branch', '=', $get['branch']);
        }
        if (isset($get['created_at']) && $get['created_at'] != '') {
            list($start_time, $end_time) = explode(' - ', $get['created_at']);
            $start_time = strtotime(trim($start_time) . " 00:00:00");
            $end_time = strtotime(trim($end_time) . " 23:59:59");
            $db->whereBetween('s.created_at', [$start_time, $end_time]);
        }
        if (!in_array($this->user['authorize'], [1, 3, 4])) {
            $db->where('branch', '=', $this->user['employee']['department']);
        }
        $this->title = '员工薪资';
        return parent::_list($db, true);
    }

    /**
     * 添加薪资
     */
    public function add()
    {
        $cate = get_category(38);
        $this->assign('category', $cate);
        return $this->_form($this->table, 'form');
    }

    public function edit()
    {
        $cate = Db::name('saas_salary')
            ->alias('s')
            ->join('saas_salary_log l', 's.id=l.salary_id')
            ->where('l.salary_id', '=', $this->request->get('id'))
            ->select();
        $this->assign('category', $cate);
        return $this->_form($this->table, 'form');
    }

    protected function _form_filter(&$data)
    {
        if ($this->request->isPost() && $this->request->post('id')) {
            Db::name('saas_salary_log')
                ->where('salary_id', '=', $data['id'])
                ->delete();
            $update = array();
            $update['remarks'] = $data['remarks'];
            $update['salary'] = array_sum($data['salary']);
            Db::name('saas_salary')->where('id', '=', $data['id'])->update($update);
            $this->dealson($data, $this->request->post('id'));
        } elseif ($this->request->isPost()) {
            if (empty($data['employee_id'])) {
                $this->error('请先选择员工');
            }
            // 工资表主表插入
            $insert = array();
            $insert['employee_id'] = $data['employee_id'];
            $insert['salary'] = array_sum($data['salary']);
            $insert['remarks'] = $data['remarks'];
            $insert['branch'] = $data['branch'];
            $insert['created_at'] = time();
            $id = Db::name('saas_salary')->insertGetId($insert);
            $this->dealson($data, $id);
        }
    }

    // 处理子表数据
    protected function dealson($data, $id)
    {
        // 工资子表插入
        $deal = array();
        foreach ($data['salary'] as $k => $v) {
            $deal[$k]['salary'] = $v;
        }
        foreach ($data['category'] as $k => $v) {
            $deal[$k]['category'] = $v;
        }
        foreach ($data['remark'] as $k => $v) {
            $deal[$k]['remark'] = $v;
        }
        $insertall = [];
        foreach ($deal as $key => $item) {
            if (!empty($item['salary'])) {
                $insertall[$key]['salary_id'] = $id;
                $insertall[$key]['salary'] = $item['salary'];
                $insertall[$key]['category'] = $item['category'];
                $insertall[$key]['remark'] = $item['remark'];
            }
        }
        Db::name('saas_salary_log')->insertAll($insertall);
        $this->success('保存成功', '');
    }


    /**
     * 薪资删除
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("删除成功!", '');
        }
        $this->error("删除失败, 请稍候再试!");
    }

    /**
     * @return mixed
     * 工资预览打印
     */
    public function salaryview()
    {
        $get = $this->request->get();
        $salary = Db::name($this->table)
            ->alias('s')
            ->join('saas_employee e', 's.employee_id=e.id')
            ->where('s.status', '<>', '3')
            ->where('s.id', '=', $get['id'])
            ->field('e.name,s.employee_id,e.contract_status,s.salary,s.remarks,s.created_at')
            ->find();
        $salarylog = Db::name('saas_salary_log')
            ->where('salary_id', '=', $get['id'])
            ->field('salary_id,salary,category,remark')
            ->select();
        $salary['res'] = $salarylog;
        $salary['num'] = count($salary['res']);
        return $this->view('', ['salary' => $salary]);
    }

    /**
     * @return \think\response\Json
     * 通过校区ID获取 校区下的员工
     */
    public function getBranchData()
    {
        if ($this->request->isPost()) {
            $branch = $this->request->post('branch');
            //根据所属校区获取该校区的员工
            $employee = Db::name('system_user')->alias('u')
                ->join('saas_employee e', 'e.userid=u.id')
                ->field('e.name,e.id,u.authorize,e.is_teacher')
                ->where('e.status', '<>', '3')
                ->where('u.status', '<>', '3')
                ->where('e.department', '=', $branch)
                ->select();
            $teacher = [];
            foreach ($employee as $v) {
                if ($v['is_teacher'] == 1) {
                    $teacher[] = [
                        'name' => $v['name'],
                        'id' => $v['id']
                    ];
                }

            }
            $info = [
                'teacher' => $teacher,
            ];
            return json($info);
        }
    }

    public function printsalary()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $ids = explode(',', $post['id']);
            $salary = Db::name($this->table)
                ->alias('s')
                ->join('saas_employee e', 's.employee_id=e.id')
                ->where('s.status', '<>', '3')
                ->where('s.id', 'in', $ids)
                ->field('s.id,e.name,s.employee_id,e.contract_status,s.salary,s.remarks,s.created_at')
                ->select();
            $salarylog = Db::name('saas_salary_log')
                ->where('salary_id', 'in', $ids)
                ->field('salary_id,salary,category,remark')
                ->select();
            foreach ($salary as $key => $value) {
                foreach ($salarylog as $k => $v) {
                    if ($value['id'] == $v['salary_id']) {
                        $salary[$key]['salarylog'][] = $v;
                    }
                }
            }
            $salary['res'] = $salarylog;
            $salary['num'] = count($salary['res']);
            return $this->view('', ['salary' => $salary]);
        }
    }
}