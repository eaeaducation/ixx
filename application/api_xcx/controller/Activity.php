<?php


namespace app\api_xcx\controller;


use controller\BasicXcx;
use think\Db;
use think\Exception;
use think\facade\Log;

/**
 * 活动类型：满减1，分享2，
 * Class Activity
 * @package app\api_xcx\controller
 */
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
        $all_coupon = Db::name('saas_xcx_coupon')
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
        $activity_detail = Db::name('saas_xcx_coupon')
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
        $receive_num = Db::name('saas_xcx_coupon_detail')
            ->where('aid', '=', $aid)
            ->where('cid', '=', $user->id)
            ->count();
        $total_receive_num = Db::name('saas_xcx_coupon_detail')
            ->where('aid', '=', $aid)
            ->count();
        if ($total_receive_num >= $activity_detail['activity_num']) {
            return $this->error('优惠券已领完');
        }
        $receive_rule = json_decode($activity_detail['activity_rule'], 1);
        if ($receive_num >= $receive_rule['user_coupon_limit_num']) {
            return $this->error('用户限领'.$receive_rule['user_coupon_limit_num'].'张');
        }
        Db::startTrans();
        try{
            $data = [
                'a_title' => $activity_detail['title'],
                'cid' => $user->id,
                'aid' => $activity_detail['id'],
                'type' => 1,
                'is_can_use' => 1,
                'amount' => $activity_detail['single_amount'],
                'created_at' => time()
            ];
            Db::name('saas_xcx_coupon_detail')->insert($data);
            Db::name('saas_xcx_coupon')->where('id', '=', $aid)->setInc('partake_num');
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return $this->error('数据异常');
        }
        return $this->success('领取成功');
    }

    /**
     * 活动列表
     */
    public function activityList()
    {
        $current_time = time();
        $all_share = Db::name('saas_xcx_activity')
            ->where('beg_time', '<', $current_time)
            ->where('end_time', '>', $current_time-86400)
            ->where('del', '=', 0)
            ->where('status', '=', 99)
            ->field('title, theme_img, h5_url, beg_time, end_time, click_num')
            ->select();
        return $this->success('活动列表', $all_share);
    }

    /**
     * @throws Exception
     * 统计活动参与次数
     */
    public function clickActivity()
    {
        $aid = $this->request->post('aid');
        Db::name('saas_xcx_activity')
            ->where('id', '=', $aid)
            ->setInc('click_num');
        return $this->success('活动参与成功');
    }

    /**
     *用户分享
     */
    public function shareActivity()
    {
        $user = $this->getUser();
        $aid = $this->request->post('id');
        $current_time = time();
        if (!$aid) return $this->error('参数错误');
        $activity_detail = Db::name('saas_xcx_award')
            ->where('id', '=', $aid)
            ->where('deleted', '=', 0)
            ->where('type', '=', 2)
            ->find();
        if ($current_time < $activity_detail['beg_time']) {
            return $this->error('该活动暂未开始');
        }
        if ($current_time > $activity_detail['end_time'] + 86400) {
            return $this->error('该活动已结束');
        }
        //查询用户领取zhuantai
        $share_recode = Db::name('saas_xcx_award_detail')
            ->where('aid', '=', $aid)
            ->where('cid', '=', $user->id)
            ->find();
        $total_receive_num = Db::name('saas_xcx_award_detail')
            ->where('aid', '=', $aid)
            ->count();
        if ($total_receive_num >= $activity_detail['activity_num']) {
            return $this->error('活动已满额');
        }
        Db::startTrans();
        try{
            $data = [
                'a_title' => $activity_detail['title'],
                'cid' => $user->id,
                'aid' => $activity_detail['id'],
                'type' => 1,
                'is_can_use' => 1,
                'amount' => $activity_detail['single_amount'],
                'created_at' => time()
            ];
            if (!$share_recode) {
                Db::name('saas_xcx_award_detail')->insert($data);
                Db::name('saas_xcx_award')->where('id', '=', $aid)->setInc('partake_num');
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return $this->error('数据异常');
        }
        return $this->success('活动参加成功');
    }


    /**
     * 获取用户是否通过分享进入小程序
     * 获取分享者
     */
    public function getShareInfo()
    {
        $sharephone = $this->request->post('phone');
        $activity_id = $this->request->post('activity_id');
        Log::error('分享者手机号:'.$sharephone);
        if ($sharephone) {
            $user = Db::name('saas_customer')->where('parent_tel', '=', $sharephone)->find();
            $activity = Db::name('saas_xcx_award')->where('id', '=', $activity_id)->find();
            if ($activity['operate_type'] == 2) {
                //分享到朋友圈
                //更新用户分享次数（自增
                Db::name('saas_xcx_award_detail')
                    ->where('cid', '=', $user['id'])
                    ->where('aid', '=', $activity_id)
                    ->setInc('share_num');

                $user_share_detail = Db::name('saas_xcx_award_detail')
                    ->where('cid', '=', $user['id'])
                    ->where('aid', '=', $activity_id)
                    ->find();
                if ($user_share_detail['share'] == json_decode($activity['activity_rule'], 1)['max_share_num_limit']) {
                    Db::name('saas_xcx_award_detail')
                        ->where('cid', '=', $user['id'])
                        ->where('aid', '=', $activity_id)
                        ->update(['is_can_use' => 1]);
                }
            } elseif ($activity['operate_type'] == 3) {
                //转发给朋友
            }
        }
        return $this->success('');
    }
}