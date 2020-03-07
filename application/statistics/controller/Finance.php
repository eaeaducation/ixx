<?php


namespace app\statistics\controller;


use controller\BasicAdmin;
use think\Db;
use think\facade\Log;

class Finance extends BasicAdmin
{
    public function orders()
    {
        $this->assign('title',  '校区年度数据统计');
        return $this->fetch('');
    }

    public function orders_data()
    {
        $post = $this->request->post();
        $branchs = get_branches();
        $date = isset($post['date']) && !empty($post['date']) ? $post['date'] :  date('Y');
//        $order_data = Db::name('saas_order_log')->alias('ol')
//            ->field('count(ol.id) as deal_num, sum(ol.old_price) deal_old_price, sum(ol.price) deal_price, c.branch')
//            ->join('saas_order o', 'o.id = ol.order_id', 'left')
//            ->join('saas_courses c', 'c.id = ol.goods_id', 'left')
//            ->join('saas_cash_flow cf', 'cf.orderno = o.orderno', 'left')
//            ->where('cf.type', '=', 1)
//            ->where("FROM_UNIXTIME(cf.created_at,'%Y') = $date")
//            ->where('o.status', 'in', [5, 6]);
        $order_data = Db::name('saas_cash_flow')->alias('cf')
            ->join('saas_customer c', 'c.id = cf.cid', 'left')
            ->field('sum(IF(cf.type=1, 1, 0)) as sign_num, sum(IF(cf.type=2, 1, 0)) as renew_num, sum(IF(cf.type=1, price, 0)) as sign_price, sum(IF(cf.type=2, price, 0)) as renew_price,c.branch')
            ->where("FROM_UNIXTIME(cf.created_at,'%Y') = $date");
        $order_data = $order_data->group('c.branch')->select();
        $cate_data = [];
        foreach ($order_data as $item) {
            if (!$item['branch']) {
                isset($item);
                continue;
            }
            $cate_data[$item['branch']] = [
                'sign_num' => $item['sign_num'],
                'renew_num' => $item['renew_num'],
                'sign_price' => $item['sign_price'],
                'renew_price' => $item['renew_price']
            ];
        }
        $deal_data =
            [
            'sign_num' => [],
            'renew_num' => [],
            'sign_price' => [],
            'renew_price' => []
        ];
        foreach ($cate_data as $key => $item) {
            foreach ($branchs as $k => $v) {
                if ($key == $k) {
                    $deal_data['sign_num'][$k] = $item['sign_num'];
                    $deal_data['renew_num'][$k] = $item['renew_num'];
                    $deal_data['total_num'][$k] = $item['renew_num'] + $item['sign_num'];
                    $deal_data['sign_price'][$k] = round($item['sign_price'], 2);
                    $deal_data['renew_price'][$k] = round($item['renew_price'], 2);
                    $deal_data['total_price'][$k] = round($item['sign_price']+$item['renew_price'], 2);
                }
                if (!isset($deal_data['sign_num'][intval($k)])) {
                    $deal_data['sign_num'][$k] = 0;
                    $deal_data['renew_num'][$k] = 0;
                    $deal_data['total_num'][$k] = 0;
                    $deal_data['sign_price'][$k] = 0;
                    $deal_data['renew_price'][$k] = 0;
                    $deal_data['total_price'][$k] = 0;
                }
            }
        }
        return [
            'branchs' => array_values($branchs),
            'sign_num' => array_values($deal_data['sign_num']),
            'renew_num' => array_values($deal_data['renew_num']),
            'total_num' => array_values($deal_data['total_num']),
            'sign_price' => array_values($deal_data['sign_price']),
            'renew_price' => array_values($deal_data['renew_price']),
            'total_price' => array_values($deal_data['total_price']),
            'year' => $date,
        ];
    }

    /**
     * 财务数据金额统计
     */
    public function financeDate()
    {
        $this->assign('title',  '财务数据金额统计');
        return $this->fetch('finance');
    }

    public function finance_year_data()
    {
        $post = $this->request->post();
        $date = isset($post['date']) && !empty($post['date']) ? $post['date'] :  date('Y');
        $db = Db::name('saas_cash_flow')->alias('cf')
            ->join('saas_customer c', 'c.id = cf.cid', 'left')
            ->field('FROM_UNIXTIME(cf.created_at,"%Y%m") as time, sum(If(type=1,cf.price,0)) as sign_price, sum(IF(type=2,cf.price,0)) as renew_price');
        $db->where("FROM_UNIXTIME(cf.created_at,'%Y') = $date");
        $year_data = $db->group('time')->select();
        $month = [];
        $sign_price = [];
        $renew_price = [];
        $total_price = [];
        if ($year_data) {
            foreach ($year_data as $item) {
                $month[] = $item['time'];
                $sign_price[] = $item['sign_price'];
                $renew_price[] = $item['renew_price'];
                $total_price[] = $item['sign_price'] + $item['renew_price'];
            }
        }
        $db1 = Db::name('saas_cash_flow')->alias('cf')
            ->join('saas_customer c', 'c.id = cf.cid', 'left')
            ->field('c.branch, FROM_UNIXTIME(cf.created_at,"%Y%m") as time, sum(price) as total_price');
        $db1->where("FROM_UNIXTIME(cf.created_at,'%Y') = $date");
        $year_branch_data = $db1->group('time,c.branch')->select();
        $branchs = get_branches();
        $month1 = [];
        $branch_data = [];
        foreach ($year_branch_data as $key => $value) {
            foreach ($branchs as $k => $item) {
                if (!in_array($value['time'], $month1)) {
                    $month1[] = $value['time'];
                }
                if ($value['branch'] == $k) {
                    $branch_data[$k][$value['time']] = $value['total_price'];
                }
                if (!isset($branch_data[$k][$value['time']])) {
                    $branch_data[$k][$value['time']] = 0;
                }
            }
        }
        foreach ($branch_data as $key => &$value) {
            $branch_data[$key] = array_values($value);
        }
        $result = [
            'year' => $date,
            'month' => $month,
            'sign_price' => $sign_price,
            'renew_price' => $renew_price,
            'total_price' => $total_price,
            'school' => $branchs,
            'branchs' => array_values($branchs),
            'month1' => $month1,
            'branch_data' => $branch_data
        ];
        return $result;
    }
}