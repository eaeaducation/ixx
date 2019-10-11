<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/26 0026
 * Time: 17:35
 */

namespace app\financial\controller;

use app\api2\controller\Kufenqi;
use function Composer\Autoload\includeFile;
use service\HttpService;
use think\Db;
use controller\BasicAdmin;
use QRCode;
use think\facade\Log;
use yb\Taipai;

class Orderpay extends BasicAdmin
{
    public function payList()
    {
        $this->title = '订单支付列表';
        $db = Db::name('saas_pay_order')
            ->order('created_at desc')
            ->where('status', '<>', 299)
            ->field('id, name, money, type, status, order_no, mobile, paytime, created_at');

        //统计
        $total = Db::name('saas_pay_order')
            ->where('status', '<>', 299);

        $get = $this->request->get();
        if (isset($get['status']) && $get['status'] != '') {
            $db->where('status', '=', $get['status']);
            $total->where('status', '=', $get['status']);
        }
        if (isset($get['name']) && $get['name'] != '') {
            $db->where('name', 'like', '%' . $get['name'] . '%');
            $total->where('name', 'like', '%' . $get['name'] . '%');
        }
        if (isset($get['order_no']) && $get['order_no'] != '') {
            $db->where('order_no', '=', $get['order_no']);
            $total->where('order_no', '=', $get['order_no']);
        }
        if (isset($get['status']) && $get['status'] != '') {
            $db->where('status', '=', $get['status']);
            $total->where('status', '=', $get['status']);
        }
        if (isset($get['type']) && $get['type'] != '') {
            $db->where('type', '=', $get['type']);
            $total->where('type', '=', $get['type']);
        }
        if (isset($get['time_range']) && $get['time_range'] != '') {
            $get['time_range'] = str_replace('+~+', ' ~ ', $get['time_range']);
            list($start, $end) = explode(' ~ ', $get['time_range']);
            $_start = strtotime($start);
            $_end = strtotime($end) + 86400;
            if (($_end - $_start) / 86400 > 60) {
                $_start = strtotime('-60 days', $_end);
            }
            $db->whereBetween('paytime', [$_start, $_end]);
            $total->whereBetween('paytime', [$_start, $_end]);
        }

        if (isset($get['action']) && $get['action'] == 'down') {
            $this->dataDown($db);
        }
        $sum = sprintf('%.2f', $total->sum('money'));
        $this->assign('sum', $sum);
        return $this->_list($db);
    }

    public function receiptQr()
    {
        $url = url('/order/pay/payType', [], null, true);
        $code = QrCode::createQRCodeString($url);
        $this->assign('code', $code);
        $this->assign('title', '收款二维码');
        return view();
    }

    /*
     * t退款
     */
    public function refund()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $order = Db::name('saas_pay_order')
                ->where('id', '=', $post['id'])
                ->find();
            if ($order['type'] == 2) {
                // 易宝退款
                $notify = json_decode($order['notify'], 1);
                $taipai = new Taipai();
                $res = $taipai->refund($order['order_no'], generate_order_no('R'), $notify['uniqueOrderNo'], $order['money'], $post['reason']);
                if (isset($res['result']) && $res['result']['code'] == 'OPR00000') {
                    Db::name('saas_pay_order')->where('id', '=', $order['id'])->update(['status' => 300]);
                    $this->success('退款申请已提交！', '');
                } else {
                    $this->error($res['result']['message']);
                }
            } elseif ($order['type'] == 1) {
                $order['reason'] = $post['reason'];
                $kfq = new Kufenqi();
                $row = $kfq->refundApply($order);
                Log::error($row);
                if ($row['result'] == 200) {
                    Db::name('saas_pay_order')->where('id', '=', $post['id'])->update(['status' => 300]);
                    $this->success('退款申请已提交！', '');
                } else {
                    $this->error($row['errMsg']);
                }
            }
        } else {
            $id = $this->request->get('id');
            $this->assign('id', $id);
            return view();
        }
    }

    public function refuList()
    {
        $this->title = '退款订单列表';
        $db = Db::name('saas_pay_order')
            ->order('created_at desc')
            ->where('status', 'in', [299, 300])
            ->field('id, name, money, type, status, order_no, mobile, paytime, created_at');
        $get = $this->request->get();
        foreach (['status', 'order_no', 'type'] as $key) {
            if (isset($get[$key]) && $get[$key] != '') {
                $db->where($key, '=', $get[$key]);
            }
        }

        if (isset($get['name']) && $get['name'] != '') {
            $db->where('name', 'like', '%' . $get['name'] . '%');
        }

        if (isset($get['time_range']) && $get['time_range'] != '') {
            $get['time_range'] = str_replace(' + ~+', ' ~', $get['time_range']);
            list($start, $end) = explode(' ~', $get['time_range']);
            $_start = strtotime($start);
            $_end = strtotime($end) + 86400;
            if (($_end - $_start) / 86400 > 60) {
                $_start = strtotime(' - 60 days', $_end);
            }
            $db->whereBetween('paytime', [$_start, $_end]);
        }
        return $this->_list($db);
    }

    /**
     * @param $db
     * 导出数据
     */
    protected function dataDown($db)
    {
        $res = $db->select();
        foreach ($res as $key => $val) {
            if ($val['type'] == 1) {
                $res[$key]['type'] = '库分期';
            } else {
                $res[$key]['type'] = '全额';
            }

            switch ($val['status']) {
                case 2:
                    $res[$key]['status'] = '未支付';
                    break;
                case 1:
                    $res[$key]['status'] = '支付失败';
                    break;
                case 99:
                    $res[$key]['status'] = '支付成功';
                    break;
                case 300:
                    $res[$key]['status'] = '退款处理中';
                    break;
                case 299:
                    $res[$key]['status'] = '退款成功';
                    break;
            }
            $res[$key]['created_at'] = date('Y-m-d H:i:s', $val['created_at']);
            $res[$key]['paytime'] = !empty($val['paytime']) ? date('Y-m-d H:i:s', $val['paytime']) : '';
        }
        $key = [
            'name' => '姓名',
            'mobile' => '手机号',
            'money' => '金额',
            'created_at' => '支付时间',
            'order_no' => '订单号',
            'paytime' => '到账时间',
            'type' => '支付渠道',
            'status' => '支付状态'
        ];
        $title = "客户支付列表";
        down_excel($res, $key, $title);
    }
}