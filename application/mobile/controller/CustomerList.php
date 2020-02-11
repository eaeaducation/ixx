<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/17
 * Time: 14:34
 */

namespace app\mobile\controller;

use think\Db;
use app\common\model\Customer;

class CustomerList extends MobileBase
{
    public function index()
    {
        if ($this->request->isGet()) {
            return $this->fetch('index');
        }
        if ($this->request->isPost()){
            $page = $this->request->post('page');
            $len = 20;
            $set = ($page-1)*$len;
            $db = Db::name('saas_customer')
                ->where('status', '<>', 3)
                ->where('is_student', '<>', 1)
                ->field('name,parent_tel,id')
                ->order('created_at desc');
            $keyword = $this->request->request('keyword');
            if (!empty($keyword)) {
                if (preg_match('/^\d{4}$/', $keyword)) {
                    $db->where('parent_tel', 'like', '%'.$keyword.'%');
                } elseif (preg_match('/^[\x{4e00}-\x{9fa5}]{1,10}(?:·[\x{4e00}-\x{9fa5}]{2,10})*$/u', $keyword)) {
                    $db->where('parent_name|name|tags', 'like', "%$keyword%");
                }
            } else {
                $db->limit($set, $len);
            }
            $authorize = $this->user['authorize'];
            if (!in_array($authorize, [1,3,4])) {
                $branch = $this->user['employee']['department'];
                $db->where('branch', '=', $branch);
            }
            $data = $db->select();
            return json($data);
        }
    }

    /**
     * @return \think\response\View
     * 客户详情
     */
    public function detail()
    {
        if ($this->request->isGet()) {
            $id = $this->request->get('id');
            $customer = customer::findOrFail($id);
            return view('', ['customer' => $customer]);
        }
        if ($this->request->isPost()) {
            $post = $this->request->put('data');
            $post['birthday'] = strtotime($post['birthday']);
            $id = $post['id'];
            unset($post['id']);
            $res = Db::name('saas_customer')->where('id', '=', $id)->update($post);
            if ($res) {
                return json(['code' => 1, 'msg' => '数据编辑成功']);
            } else {
                return json(['code' => 0, 'msg' => '数据编辑失败,请稍后重试']);
            }
        }
    }

    /**
     * @return \think\response\View
     * 咨詢記錄
     */
    public function consultation()
    {
        $id = $this->request->get('id');
        $row = Db::name('saas_customer_follow')
            ->where('id', '=', $id)
            ->field('id,follow_status,remind_time,content,user_id,created_at,type,interest')
            ->find();
        return view('', ['follow' => $row]);
    }

    /**
     * @return \think\response\Json|\think\response\View
     * 新建咨询
     */
    public function createConsultation()
    {
        if ($this->request->isGet()) {
            $cid = $this->request->get('id');
            return view('', ['cid' => $cid]);
        }
        if ($this->request->isPost()) {
            $post = $this->request->post('data');
            $content = [
                [
                    'content' => $post['content'],
                    'created_at' => $post['created_at']
                ]
            ];
            $post['user_id'] = session('user.id');
            $post['created_at'] = time();
            $post['remind_time'] = strtotime($post['remind_time']);
            $post['content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
            $res = Db::name('saas_customer_follow')->insert($post);
            if ($res) {
                return json(['code' => 1, 'msg' => '新增咨询成功', 'data' => $post['customer_id']]);
            } else {
                return json(['code' => 0, 'msg' => '新增咨询失败,请稍后重试']);
            }
        }
    }
}