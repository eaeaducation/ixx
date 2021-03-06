<?php


namespace app\api_xcx\controller;


use controller\BasicXcx;
use service\HttpService;
use think\Db;
use think\Exception;
use think\exception\ErrorException;
use think\facade\Log;
use think\facade\Request;
use think\facade\Session;

class Order extends BasicXcx
{
    /**
     * store_cart 状态status：1正常2已加如待支付订单列表3已支付
     * 购物车
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cartList()
    {
        $user = $this->getUser();
        $order = Db::name('store_cart')->alias('c')
            ->join('store_goods g', 'c.gid = g.id', 'left')
            ->where('c.status', '=', 1)
            ->where('c.cid', '=', $user->id)
            ->order('c.created_at desc')
            ->field('c.id as cart_id, c.price, g.goods_title, g.goods_image, c.number, c.param, g.id, c.selected')
            ->select();
        return $this->success('', $order);
    }

    /**
     * 购物车取消
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function cancelCart()
    {
        $user = $this->getUser();
        $post = $this->request->post();
        $res = Db::name('store_cart')->where('id', '=', $post['cart_pid'])->delete();
        if ($res) {
            return $this->success('删除成功', '');
        } else {
            return $this->error('删除失败，请稍候再试');
        }
    }

    /**
     * 加入购物车
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function addCart()
    {
        $user = $this->getUser();
        $post = $this->request->post();
        if (!$user) {
            return $this->error('用户不存在');
        }
        //检查库里是否又该商品
        $is_exist = Db::name('store_cart')
            ->where('gid', '=', $post['pro_id'])
            ->where('cid', '=', $user->id)
            ->where('param', '=', $post['param'])
            ->field('id, number, price')
            ->find();
        $data = [];
        if (!$is_exist) {
            $data['cid'] = $user->id;
            $data['gid'] = $post['pro_id'];
            $data['price'] = $post['price'];
            $data['status'] = 1;
            $data['number'] = $post['num'];
            $data['param'] = $post['param'];
            $data['created_at'] = time();
            $res = Db::name('store_cart')->insert($data);
        } else {
            $data['number'] = $post['num'] + $is_exist['number'];
            $data['price'] = $post['price'] + $is_exist['price'];
            $res = Db::name('store_cart')->where('id', '=', $is_exist['id'])->update($data);
        }
        if ($res) {
            return $this->success('添加成功', '');
        } else {
            return $this->error('添加失败，请稍后再试');
        }
    }

    /**
     * 用户订单列表
     */
    public function orderList()
    {
        $user = $this->getUser();
        $post = $this->request->post();
        $order = Db::name('saas_order')->alias('o');
        if (isset($post['status']) && !empty($post['status'])) {
            $order->where('o.status', '=', $post['status']);//未付款4；已付款5
        } else {
            $order->where('o.status', '<>', 3);
        }

        $order->where('o.student_id', '=', $user->id)
            ->order('o.id desc')
            ->field('o.id, o.status, o.price, o.orderno, o.created_at');
        $data = [];
        foreach ($order->select() as $key => $item) {
            $data[$key]['id'] = $item['id'];
            $data[$key]['status'] = $item['status'];
            $data[$key]['total_price'] = $item['price'];
            $data[$key]['orderno'] = $item['orderno'];
            $data[$key]['created'] = date('Y-m-d H:i:s', $item['created_at']);
            //优惠券
            $coupon = Db::name('saas_xcx_coupon_detail')
                ->where('cid', '=', $user->id)
                ->where('oid', '=', $item['id'])
                ->where('type', '=', 1)
                ->where('status', '=', 1)
                ->sum('amount');
            $data[$key]['coupon_price'] = $coupon;
            $detail = Db::name('saas_order_log l')
                ->join('store_goods g', 'l.goods_id = g.id', 'left')
                ->field('g.goods_title, g.goods_logo, l.goods_num, l.price')
                ->where('l.order_id', '=', $item['id'])
                ->select();
            if ($detail) {
                foreach ($detail as $k => $v) {
                    $data[$key]['goods'][$k]['goods_name'] = $v['goods_title'];
                    $data[$key]['goods'][$k]['goods_logo'] = $v['goods_logo'];
                    $data[$key]['goods'][$k]['lesson_num'] = $v['goods_num'];
                    $data[$key]['goods'][$k]['goods_price'] = $v['price'];
                }
            }
        }
        return $this->success('订单列表获取成功', $data);
    }

    public function delOrder()
    {
        $this->getUser();
        $post = $this->request->post();
        if (!isset($post['id'])) {
            return $this->error('订单参数错误');
        }
        $res = Db::name('saas_order')
            ->where('id', '=', $post['id'])
            ->update(['status' => 3]);
        return $this->success('删除成功', $res);
    }

    /**
     * 预支付详情页
     */
    public function prePaydetail()
    {
        $user = $this->getUser();
        $post = $this->request->post();
        if (!isset($post['pids']) || empty($post['pids'])) {
            return $this->error('请先选择要支付的商品');
        }
        $pids = explode(',', $post['pids']);
        //查询用户购物车需要支付的商品详情
        if ($post['type'] == 'cart') {
            $order = Db::name('store_cart')->alias('c')
                ->join('store_goods g', 'c.gid = g.id', 'left')
                ->where('c.gid', 'in', $pids)
                ->where('c.status', '=', 1)
                ->where('c.cid', '=', $user->id)
                ->order('c.created_at desc')
                ->field('c.price, g.goods_title, g.goods_image, c.number, c.param, g.id as gid')
                ->select();
        } else {
            $order = Db::name('store_goods')->alias('g')
                ->where('g.id', 'in',  $pids)
                ->field('g.goods_title, g.goods_image, g.id as gid')
                ->find();
        }

        //查询用户可用的优惠券
        $can_use_coupon = Db::name('saas_xcx_coupon_detail xad')
            ->join('saas_xcx_coupon xa', 'xad.aid = xa.id')
            ->where('xad.cid', '=', $user->id)
            ->where('xad.is_can_use', '=', 1)
            ->where('xad.status', '=', 0)
            ->field('xad.id, xad.a_title, xad.amount, xa.activity_theme_img')
            ->select();
        $data = [
            'pre_order' => $order,
            'coupon' => $can_use_coupon
        ];
        return $this->success('待下单订单获取成功', $data);
    }


    /**
     * 小程序统一下单
     */
    public function orderPay()
    {
        $user = $this->getUser();
        $sessionid = $this->request->header('sessionid');
        $sessioninfo = cache($sessionid);
        $post = $this->request->post();
        $coupon_id = '';
        if (isset($post['coupon_ids']) && !empty($post['coupon_ids'])) {
            $coupon_id = $post['coupon_ids'];
        }
        //统一支付签名
        $param = [
            'appid' => $this->app_id,//appid
            'body' => !empty($post['title']) ? $post['title']: '在线选课',
            'openid' => $sessioninfo['open_id'],
            'mch_id' => $this->app_mch_id,
            'nonce_str' => $this->getRandom(32),
            'notify_url' => $this->request->domain() . '/notify/notify/xcxNotify',
            'spbill_create_ip' => $this->request->ip(),
            'out_trade_no' => isset($post['orderno']) && !empty($post['orderno']) ? $post['orderno'] : generate_order_no(),
            'total_fee' => intval($post['price'] * 100),
            'trade_type' => "JSAPI",
        ];
//        Log::error($param);die;
        ksort($param);
        $params = post_str($param, false);
        $sign = strtoupper(md5($params . "&key=" . $this->app_mch_key));
        //封装统一支付xml参数
        $param['sign'] = $sign;
        $form_data = $this->arrayToxml($param);
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $res = Httpservice::raw($url, $form_data);
        $res = $this->xmlToarray($res);
        if ($res['result_code'] == "SUCCESS") {
            if (empty($post['orderno'])) {
                if (isset($post['data']) && !empty($post['data'])) {
                    $this->shopOrder($user, $post, $param['out_trade_no'], $coupon_id);
                } else {
                    return $this->error('参数异常', $res);
                }
            }
            //微信支付数据
            $data = [
                'appId' => $this->app_id,
                'nonceStr' => $this->getRandom(32),
                'package' => 'prepay_id=' . $res['prepay_id'],
                'signType' => 'MD5',
                'timeStamp' => '"' . time() . '"',
            ];
            $param = post_str($data, false);
            $sign = strtoupper(md5($param . "&key=" . $this->app_mch_key));
            unset($data['appId']);
            $data['paySign'] = $sign;
            return $this->success('微信下单成功', $data);
        } else {
            return $this->error('微信下单失败', $res);
        }
    }

    /**
     * 生成订单
     * @param $post
     * @param $orderno
     * @return bool
     */
    public function shopOrder($user, $post, $orderno, $couponid)
    {
        Db::startTrans();
        try {
            $order = [
                'student_id' => $user->id,
                'class_id' => 0,
                'price' => $post['price'],
                'status' => 4,
                'created_at' => time(),
                'pay_type' => 2,
                'orderno' => $orderno,
                'audit_status' => 99
            ];
            $order_id = Db::name('saas_order')->insertGetId($order);
            //更新优惠券状态
            if ($couponid) {
                Db::name('saas_xcx_coupon_detail')
                    ->where('id', '=', $couponid)
                    ->where('cid', '=', $user->id)
                    ->update(['oid' => $orderno,'status' => 1]);
            }
            $order_log = [];
            foreach ($post['data'] as $key => $v) {
                $order_log[$key]['order_id'] = $order_id;
                $order_log[$key]['product_id'] = $v['gid'];
                $order_log[$key]['goods_type'] = 1;
                $order_log[$key]['old_price'] = $v['price'];
                $order_log[$key]['price'] = $v['price'];
                $order_log[$key]['consume_num'] = 0;
                $order_log[$key]['goods_num'] = (intval($v['p_num']) * intval($v['b_num']));
                $order_log[$key]['created_at'] = time();
                $order_log[$key]['class_id'] = 0;
                Log::error($v);
                //更新购物车物品状态
                $cart_goods = Db::name('store_cart')->where('cid', '=', $user->id)->where('gid', '=', $v['gid'])->where('status', '=', 1)->find();
                if ($cart_goods) {
                    $res = Db::name('store_cart')
                        ->where('cid', '=', $user->id)
                        ->where('gid', '=', $v['gid'])
                        ->update(['status' => 2]);
                    Log::error($res);
                }
            }
            $result = Db::name('saas_order_log')->insertAll($order_log);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return $this->error('数据更新');
        }
        return true;
//        foreach ($post['data'] as $key => $v) {
//            $order['student_id'] = $user->id;
//            $order['class_id'] = 0;
//            $order['price'] = $v['price'];
//            $order['status'] = 4;
//            $order['created_at'] = time();
//            $order['pay_type'] = 2;
//            $order['orderno'] = $orderno.'-'.$v['gid'];
//            $order_id = Db::name('saas_order')->insertGetId($order);
//
//            $order_log['order_id'] = $order_id;
//            $order_log['goods_id'] = $v['gid'];
//            $order_log['goods_type'] = 1;
//            $order_log['old_price'] = $v['price'];
//            $order_log['price'] = $v['price'];
//            $order_log['consume_num'] = 0;
//            $order_log['goods_num'] = $v['p_num'] * $v['b_num'];
//            $order_log['created_at'] = time();
//            $order_log['class_id'] = 0;
//            Db::name('saas_order_log')->insert($order_log);
//            //更新购物车物品状态
//            Db::name('store_cart')->where('cid', $user->id)->where('gid', $v['gid'])->update(['status' => 2]);
//        }
//        return true;
    }

    /**
     * 生成随机字符串
     * @param $param
     * @return string
     */
    private function getRandom($param)
    {
        $str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $key = "";
        for ($i = 0; $i < $param; $i++) {
            $key .= $str{mt_rand(0, 32)};
        }
        return $key;
    }


}