<?php


namespace app\statistics\controller;


use controller\BasicAdmin;
use think\Db;
use think\facade\Log;

class Sale extends BasicAdmin
{
    public function customers()
    {
        $this->assign('title',  '校区获客数据统计');
        return $this->fetch('');
    }

    public function customersData()
    {
        $this->assign('title',  '校区获客数据统计');
        $table = 'saas_customer';
        $get = $this->request->get();
        $date = date('Y');
        if (isset($get['time_range']) && !empty($get['time_range'])) {
            $date = $get['time_range'];
        }
        $branchs = get_branches();
        $customers_data = Db::name($table)->alias('c')
            ->field('count(id) as customer_num, sum(IF(is_student, 1, 0)) as student_num, FROM_UNIXTIME(c.created_at,\'%Y%m\') as date, branch')
            ->where('c.status', '<>', 3)
            ->where("FROM_UNIXTIME(c.created_at,'%Y') = $date")
            ->group("c.branch,date")
            ->select();
        $cate_data = [];
        foreach ($customers_data as $item) {
            if (!$item['branch']) {
                unset($item);
                continue;
            }
            $cate_data[$item['date']][$item['branch']] = [
                'customer_num' => $item['customer_num'],
                'student_num' => $item['student_num'],
            ];
        }
        $deal_data = [];
        $branch_data = [];
        foreach ($branchs as $i => $v) {
            $branch_data[$i]['branch_customer_num'] = 0;
            $branch_data[$i]['branch_student_num'] = 0;
        }
        foreach ($cate_data as $key => $item) {
            foreach ($item as $k => $value) {
                foreach ($branchs as $i => $v) {
                    if ($k == $i) {
                        $deal_data[$key][$k]['customer_num'] = $value['customer_num'];
                        $deal_data[$key][$k]['student_num'] = $value['student_num'];
                        $branch_data[$i]['branch_customer_num'] += $value['customer_num'];
                        $branch_data[$i]['branch_student_num'] += $value['student_num'];
                    }
                    if (!isset($deal_data[$key][$i])) {
                        $deal_data[$key][$i]['customer_num'] = 0;
                        $deal_data[$key][$i]['student_num'] = 0;
                        $branch_data[$i]['branch_customer_num'] += 0;
                        $branch_data[$i]['branch_student_num'] += 0;
                    }
                }
            }
        }
//        dump($branch_data);die;
        return $this->fetch('customers_data', [
            'deal_data' => $deal_data,
            'branch_data' => $branch_data,
            'branchs' => $branchs
            ]);
    }
}