<?php


namespace app\xiaochengxu\controller;


use controller\BasicAdmin;
use think\Db;

class Award extends BasicAdmin
{
    public $table = 'saas_xcx_award';

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '参加活动，赢取现金大礼';
        $db = Db::name($this->table)
            ->where('deleted', '=', 0)
            ->where('type', '=', 2)
            ->order('id desc');
        if ($this->request->has('title')) {
            $db->whereLike('title', $this->request->get('title'));
        }
        return parent::_list($db, true);
    }

    public function addShare()
    {
        $title = '创建活动';
        if ($this->request->isPost()) {
            $post = $this->request->post();
            list($s_time, $e_time) = explode(' ~ ', $post['activity_time']);
            $s_time = strtotime($s_time);
            $e_time = strtotime($e_time) + 86400;
            $rule = [
                'user_activity_limit_num' => $post['user_activity_limit_num'],//用户最多领取多少张
                'max_share_num_limit' => $post['max_share_num_limit'],//用户消费满多少可用
                'term_of_validity' => $post['term_of_validity']//优惠券有效期
            ];
            $data = [
                'title' => $post['activity_title'],
                'type' => 2,
                'activity_num' => $post['activity_num'],
                'single_amount' => $post['activity_price'],
                'beg_time' => $s_time,
                'end_time' => $e_time,
                'created_at' => time(),
                'activity_rule' => json_encode($rule),
                'rule_detail' => $post['rule_detail'],
                'activity_theme_img' => $post['activity_theme'],
                'operate_type' => $post['operate_type'],
            ];
            if ($post['operate_type'] == 5) {
                $data['h5_url'] = $post['h5_url'];
            } elseif ($post['operate_type'] == 4) {
                $productinfo = [
                    'product_id' => $post['product_id'],
                    'product_price' => $post['product_price']
                ];
                $data['product_info'] = json_encode($productinfo);
            }
            Db::name('saas_xcx_award')->insert($data);
            $this->success("创建成功!", "");
        }
        return $this->fetch('form', [
            'title' => $title,
        ]);
    }

    public function edit()
    {
        $title = '编辑活动';
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
                'user_activity_limit_num' => $post['user_activity_limit_num'],//用户最多领取多少张
                'max_share_num_limit' => $post['max_share_num_limit'],//用户消费满多少可用
                'term_of_validity' => $post['term_of_validity']//优惠券有效期
            ];
            $data = [
                'title' => $post['activity_title'],
                'type' => 2,
                'activity_num' => $post['activity_num'],
                'single_amount' => $post['activity_price'],
                'beg_time' => $s_time,
                'end_time' => $e_time,
                'activity_rule' => json_encode($rule),
                'rule_detail' => $post['rule_detail'],
                'activity_theme_img' => $post['activity_theme']
            ];
            Db::name('saas_xcx_award')->where('id', $id)->update($data);
            $this->success("编辑成功!", "");
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

    public function detail()
    {
        $id = $this->request->get('id');
        $this->title = '优惠券活动详情';
        $db = Db::name('saas_xcx_award_detail')
            ->where('del', '=', 0)
            ->where('aid', '=', $id)
            ->order('id desc');
        if ($this->request->has('title')) {
            $db->whereLike('a_title', $this->request->get('title'));
        }
        return parent::_list($db, true);
    }
}