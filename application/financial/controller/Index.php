<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/26
 * Time: 14:44
 */

namespace app\financial\controller;

use think\Db;
use controller\BasicAdmin;
use service\DataService;

class Index extends BasicAdmin
{
    public $table = 'saas_employee_salary';
    public function index()
    {
        $this->title = '财务收支';
        $user = $this->user;

        $get = $this->request->get();

        if (isset($get['time_range']) && !empty($get['time_range'])) {
            list($_start, $_end) = explode(' ~ ', $get['time_range']);
            $month_start = strtotime($_start);
            $month_end = strtotime($_end . ' 23:59:59');
        } else {
            $month_start=mktime(0, 0 , 0,date("m"),1,date("Y"));
            $month_end = time();
        }
        $start = format_date($month_start);
        $end = format_date($month_end);
        $this->assign(['start' => $start, 'end' => $end]);

        $db = Db::name('saas_cash_flow')->alias('f')
            ->join('saas_order o', 'f.orderno = o.orderno', 'left')
            ->join('saas_customer c', 'c.id = f.cid', 'left')
            ->whereBetween('f.created_at', [$month_start, $month_end])
            ->field('f.price, f.cid, o.userid, c.name, c.sales_id, c.branch, f.created_at, f.type, c.collect_id, f.teacher_id')
            ->order('f.created_at desc');

        $total = Db::name('saas_cash_flow')->alias('f')
            ->join('saas_order o', 'f.orderno = o.orderno', 'left')
            ->join('saas_customer c', 'c.id = f.cid', 'left')
            ->whereBetween('f.created_at', [$month_start, $month_end]);

        if (isset($get['branch']) && !empty($get['branch'])) {
            $db->where('c.branch', '=', $get['branch']);
            $total->where('c.branch', '=', $get['branch']);
        }

        if (isset($get['cc']) && !empty($get['cc'])) {
            $db->where('c.sales_id', '=', $get['cc']);
            $total->where('c.sales_id', '=', $get['cc']);
        }

        if (!in_array($user['authorize'], [1,3,4])) {
            $db->where('c.branch', '=', $user['employee']['department']);
            $total->where('c.branch', '=', $user['employee']['department']);
        }

        $sum = $total->sum('f.price');
        $sum = sprintf("%.2f", $sum);
        $this->assign('sum', $sum);
        return $this->_list($db);
    }

    public function employee_salary()
    {
        $this->title = '员工工资';

        $db = Db::name('saas_employee_salary')->alias('s')
            ->join('saas_employee e', 's.user_id = e.userid', 'left')
            ->where('s.status', '<>', 3)
            ->where('e.status', '<>', 3)
            ->order('s.created_at desc')
            ->field('s.*, e.name');

        $get = $this->request->get();
        if (isset($get['user_id']) && $get['user_id'] != '') {
            $db->where('e.userid', '=', $get['user_id']);
        }
        if (isset($get['month']) && $get['month'] != '') {
            $month = strtotime($get['month']);
            $db->where('s.month', '=', $month);
        }

        return $this->_list($db);
    }

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 工资添加
     */
    public function add()
    {
        return $this->_form('saas_employee_salary', 'form');
    }

    public function edit()
    {
        return $this->_form('saas_employee_salary', 'form');
    }

    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("删除成功!", '');

        }
        $this->error("删除失败, 请稍候再试!");
    }

    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            if ($vo['user_id'] == '') {
                $this->error('请选择员工！');
            } else {
                $vo['status'] = 1;
                $vo['month'] = strtotime($vo['month']);
                $vo['total'] = $vo['basic'] + $vo['performance'] + $vo['bonus'] - $vo['deduction'];
            }
        }
    }
}