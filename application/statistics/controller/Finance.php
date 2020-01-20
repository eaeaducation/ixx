<?php


namespace app\statistics\controller;


use controller\BasicAdmin;
use think\Db;
use think\facade\Log;

class Finance extends BasicAdmin
{

    public function orders()
    {
        $this->assign('title',  '校区当月订单成交量');
        $get = $this->request->get();
        $branchs = get_branches();
        $order_data = Db::name('saas_order_log')->alias('ol')
            ->field('count(ol.id) as deal_num, sum(ol.old_price) deal_old_price, sum(ol.price) deal_price, c.branch')
            ->join('saas_order o', 'o.id = ol.order_id', 'left')
            ->join('saas_courses c', 'c.id = ol.goods_id', 'left')
            ->where('o.status', 'in', [5, 6]);
        $order_data = $order_data->group('c.branch')->select();
        $cate_data = [];
        foreach ($order_data as $item) {
            if (!$item['branch']) {
                isset($item);
                continue;
            }
            $cate_data[$item['branch']] = [
                'deal_num' => $item['deal_num'],
                'deal_old_price' => $item['deal_old_price'],
                'deal_price' => $item['deal_price']
            ];
        }
        $deal_data =
            [
            'deal_num' => [],
            'deal_old_price' => [],
            'deal_price' => []
        ];
        foreach ($cate_data as $key => $item) {
            foreach ($branchs as $k => $v) {
                if ($key == $k) {
                    $deal_data['deal_num'][$k] = $item['deal_num'];
                    $deal_data['deal_old_price'][$k] = round($item['deal_old_price'], 2);
                    $deal_data['deal_price'][$k] = round($item['deal_price'], 2);
                }
                if (!isset($deal_data['deal_num'][intval($k)])) {
                    $deal_data['deal_num'][$k] = 0;
                    $deal_data['deal_old_price'][$k] = 0;
                    $deal_data['deal_price'][$k] = 0;
                }
            }
        }
        $this->assign('deal_data', json_encode($deal_data));
        dump($deal_data);die;
        return $this->fetch('');
    }

    public function orderDate()
    {

    }
}