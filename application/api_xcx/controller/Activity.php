<?php


namespace app\api_xcx\controller;


use controller\BasicXcx;
use think\Db;

class Activity extends BasicXcx
{
    /**
     * 当前正在进行的优惠券活动列表
     */
    public function couponList()
    {
        $current_time = time();
        //优惠券头部宣传tu
        $theme_img = Db::name('saas_content')->field('title,thumb')->where('status', '=', 99)->where('catid', '=', '93')->find();
        $all_coupon = Db::name('saas_xcx_activity')
            ->where('beg_time', '<', $current_time)
            ->where('end_time', '>', $current_time-86400)
            ->where('deleted', '=', 0)
            ->where('type', '=', 1)
            ->select();
        $data = [
            'theme_img' => $theme_img['thumb'],
            'coupon_list' => $all_coupon
        ];
        return $this->success('活动列表', $data);
    }

    /**
     * 用户参与优惠券活动
     */
    public function receiveCoupon()
    {
        $user = $this->getUser();
        $aid = $this->request->post('id');
        $current_time = time();
        if (!$aid) return $this->error('参数错误');
        $activity_detail = Db::name('saas_xcx_activity')
            ->where('id', '=', $aid)
            ->where('deleted', '=', 0)
            ->where('type', '=', 1)
            ->find();
        if ($current_time < $activity_detail['beg_time']) {
            return $this->error('该活动暂未开始');
        }
        if ($current_time > $activity_detail['end_time'] + 86400) {
            return $this->error('该活动已结束');
        }
        //查询用户领取zhuantai
        $receive_num = Db::name('saas_xcx_activity_detail')
            ->where('aid', '=', $aid)
            ->where('cid', '=', $user->id)
            ->count();
        $receive_rule = json_decode($activity_detail['activity_rule'], 1);
        if ($receive_num >= $receive_rule['user_coupon_limit_num']) {
            return $this->error('该优惠券每个用户限领'.$receive_rule['user_coupon_limit_num'].'张');
        }
        $data = [
            'a_title' => $activity_detail['title'],
            'cid' => $user->id,
            'aid' => $activity_detail['id'],
            'type' => 1,
            'is_can_use' => 1,
            'amount' => $activity_detail['single_amount'],
            'created_at' => time()
        ];
        $res = Db::name('saas_xcx_activity_detail')->insert($data);
        return $this->success('领取成功');
    }
}