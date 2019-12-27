<?php


namespace app\xiaochengxu\controller;


use controller\BasicAdmin;
use think\Db;

class Activity extends BasicAdmin
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
        $this->title = '营销活动';
        $db = Db::name($this->table)
            ->where('del', '=', 0)
            ->order('id desc');
        if ($this->request->has('title')) {
            $db->whereLike('title', $this->request->get('title'));
        }
        return parent::_list($db, true);
    }

    public function addActivity()
    {
        $title = '创建活动';
        if ($this->request->isPost()) {
            $post = $this->request->post();
            list($s_time, $e_time) = explode(' ~ ', $post['activity_time']);
            $s_time = strtotime($s_time);
            $e_time = strtotime($e_time) + 86400;
            $data = [
                'title' => $post['title'],
                'theme_img' => $post['theme_img'],
                'status' => $post['status'],
                'beg_time' => $s_time,
                'end_time' => $e_time,
                'created_at' => time(),
                'detail' => $post['detail'],
                'h5_url' => $post['h5_url'],
            ];
            Db::name('saas_xcx_activity')->insert($data);
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
        $activity = Db::name($this->table)
            ->where('del', '=', 0)
            ->where('id', '=', $id)
            ->find();
        if ($this->request->isPost()) {
            $post = $this->request->post();
            list($s_time, $e_time) = explode(' ~ ', $post['activity_time']);
            $s_time = strtotime($s_time);
            $e_time = strtotime($e_time) + 86400;
            $data = [
                'title' => $post['title'],
                'theme_img' => $post['theme_img'],
                'status' => $post['status'],
                'beg_time' => $s_time,
                'end_time' => $e_time,
                'created_at' => time(),
                'detail' => $post['detail'],
                'h5_url' => $post['h5_url'],
            ];
            Db::name('saas_xcx_activity')->where('id', $id)->update($data);
            $this->success("编辑成功!", "");
        }
        return $this->fetch('form', [
            'title' => $title,
            'vo' => $activity
        ]);
    }

    public function del()
    {
        $id = $this->request->get('id');
        $coupon = Db::name($this->table)
            ->where('del', '=', 0)
            ->where('id', '=', $id)
            ->find();
        if (!$coupon) {
            $this->error('该活动已删除');
        }
        $res = Db::name($this->table)
            ->where('id', '=', $id)
            ->update(['del'=> 1]);
        if ($res) {
            $this->success('删除成功');
        }
        $this->error('删除失败');
    }

    public function detail()
    {
        $id = $this->request->get('id');
        $this->title = '优惠券活动详情';
        $db = Db::name('saas_xcx_activity_detail')
            ->where('del', '=', 0)
            ->where('aid', '=', $id)
            ->order('id desc');
        if ($this->request->has('title')) {
            $db->whereLike('a_title', $this->request->get('title'));
        }
        return parent::_list($db, true);
    }
}