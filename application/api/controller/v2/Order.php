<?php


namespace app\api\controller\v2;


use app\api\controller\AppBasicApi;
use think\Db;

class Order extends AppBasicApi
{
    /**
     * 用户已选择的订单
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function my_order()
    {
        $user = $this->getUser();
        $order = Db::name('saas_order')->alias('o')
            ->join('saas_order_log l', 'o.id = l.order_id', 'right')
            ->join('store_goods g', 'l.goods_id = g.id', 'left')
            ->where('o.status', '<>', 3)
            ->where('l.goods_type', '=', 1)
            ->where('o.student_id', '=', $user->id)
            ->order('o.id desc')
            ->field('o.status, l.id, l.goods_id, l.price, o.id as oid, o.orderno, g.goods_title')
            ->select();
        $return_data = api_encrypt($order);
        return $this->success('获取成功', $return_data);
    }

    /**
     * 删除订单
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $res = Db::name('saas_order')->where('id', $post['oid'])->update(['status' => 3]);
            if ($res) {
                return $this->success('订单删除成功');
            } else {
                return $this->error('订单删除失败，请稍候再试');
            }
        }
    }
}