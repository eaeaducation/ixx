<?php


namespace app\xiaochengxu\controller;


use controller\BasicAdmin;
use think\Db;

class Coupon extends BasicAdmin
{
    public $table = 'saas_xcx_activity';

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '优惠券活动';
        $db = Db::name($this->table)
            ->where('deleted', '=', 0)
            ->order('id desc');
        if ($this->request->has('title')) {
            $db->whereLike('title', $this->request->get('title'));
        }
        return parent::_list($db, true);
    }

    public function addCoupon()
    {
        $title = '创建优惠券';
        if ($this->request->isPost()) {
            $post = $this->request->post();
            list($s_time, $e_time) = explode(' ~ ', $post['activity_time']);
            $s_time = strtotime($s_time);
            $e_time = strtotime($e_time) + 86400;
            $rule = [
              'user_coupon_limit_num' => $post['user_coupon_limit_num'],//用户最多领取多少张
              'full_reducation' => $post['max_money_limit'],//用户消费满多少可用
              'term_of_validity' => $post['term_of_validity']//优惠券有效期
            ];
            $data = [
                'title' => $post['activity_title'],
                'type' => 1,
                'activity_num' => $post['coupon_num'],
                'single_amount' => $post['coupon_price'],
                'beg_time' => $s_time,
                'end_time' => $e_time,
                'created_at' => time(),
                'activity_rule' => json_encode($rule),
                'rule_detail' => $post['rule_detail'],
                'activity_theme_img' => $post['activity_theme']
            ];
            Db::name('saas_xcx_activity')->insert($data);
            list($base, $spm, $url) = [url('@admin'), $this->request->get('spm'), url('xiaochengxu/coupon/index')];
            $this->success("创建成功!", "{$base}#{$url}?spm={$spm}");
        }
        return $this->fetch('form', [
            'title' => $title,
        ]);
    }

    public function edit()
    {
        $title = '编辑优惠券';
        $id = $this->request->get('id');
        $coupon = Db::name($this->table)
            ->where('deleted', '=', 0)
            ->where('id', '=', $id)
            ->find();
        if ($this->request->isPost()) {
            $post = $this->request->post();
            list($s_time, $e_time) = explode(' ~ ', $post['activity_time']);
            $s_time = strtotime($s_time);
            $e_time = strtotime($e_time) + 86400;
            $rule = [
                'user_coupon_limit_num' => $post['user_coupon_limit_num'],//用户最多领取多少张
                'full_reducation' => $post['max_money_limit'],//用户消费满多少可用
                'term_of_validity' => $post['term_of_validity']//优惠券有效期
            ];
            $data = [
                'title' => $post['activity_title'],
                'type' => 1,
                'activity_num' => $post['coupon_num'],
                'single_amount' => $post['coupon_price'],
                'beg_time' => $s_time,
                'end_time' => $e_time,
                'activity_rule' => json_encode($rule),
                'rule_detail' => $post['rule_detail'],
                'activity_theme_img' => $post['activity_theme']
            ];
            Db::name('saas_xcx_activity')->where('id', $id)->update($data);
            list($base, $spm, $url) = [url('@admin'), $this->request->get('spm'), url('xiaochengxu/coupon/index')];
            $this->success("编辑成功!", "{$base}#{$url}?spm={$spm}");
        }
        return $this->fetch('form', [
            'title' => $title,
            'vo' => $coupon
        ]);
    }

    public function del()
    {
        $id = $this->request->get('id');
        $coupon = Db::name($this->table)
            ->where('deleted', '=', 0)
            ->where('id', '=', $id)
            ->find();
        if (!$coupon) {
            $this->error('该活动已删除');
        }
        $res = Db::name($this->table)
            ->where('id', '=', $id)
            ->update(['deleted'=> 1]);
        if ($res) {
            $this->success('删除成功');
        }
        $this->error('删除失败');
    }
}