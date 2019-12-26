<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/4
 * Time: 16:08
 */

namespace app\courses\controller;

use controller\BasicAdmin;
use QRCode;
use think\Db;
use service\DataService;
use service\LogService;
use think\facade\Log;

/**
 * 课件管理
 * Class Courseware
 * @package app\courses\controller
 */
class Courseware extends BasicAdmin
{
    public $table = 'saas_courseware';

    /**
     * 展示
     * @return array|string
     */
    public function index()
    {
        $this->title = '课件管理';
        $db = Db::name($this->table)
            ->order('id desc');
        $get = $this->request->get();
        (isset($get['title']) && $get['title'] !== '') && $db->where('title', 'like', "%{$get['title']}%");
        foreach (['course_id'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', $get[$key]);
        }
        return parent::_list($db, true);
    }

    /**
     * 添加
     * @return array|string
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            return $this->fetch('form');
        }
        $post = $this->request->post();
        $words = !empty($post['words']) ? $post['words'] : [];
        $sentence = !empty($post['sentences']) ? $post['sentences'] : [];
        $insert_data = [
            'title' => $post['title'],
            'content' => !empty($post['content']) ? $post['content'] : '',
            'url' => $post['url'],
            'remark' => $post['remark'],
            'created_at' => time(),
            'course_id' => $post['course'],
            'file_category' => $post['file_category'],
            'file_subject' => $post['file_subject'],
            'courseware_tid' => $post['teacher_id'],
            'course_lecture' => $post['course_lecture'],
            'courseware_words' => json_encode($words),
            'courseware_sentence' => json_encode($sentence)
        ];
        Db::name('saas_courseware')->insert($insert_data);
        $this->success('恭喜, 数据保存成功!', '');
//        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑
     * @return array|string
     */
    public function edit()
    {
        $get = $this->request->get();
        if (!$this->request->isPost()) {
            $vo = Db::name('saas_courseware')->where('id', '=', $get['id'])->find();
            $this->assign('vo', $vo);
            return $this->fetch('form');
        }
        $post = $this->request->post();
        $words = !empty($post['words']) ? $post['words'] : [];
        $sentence = !empty($post['sentences']) ? $post['sentences'] : [];
        $update_data = [
            'title' => $post['title'],
            'content' => !empty($post['content']) ? $post['content'] : '',
            'url' => $post['url'],
            'remark' => $post['remark'],
            'course_id' => $post['course'],
            'file_category' => $post['file_category'],
            'file_subject' => $post['file_subject'],
            'courseware_tid' => $post['teacher_id'],
            'course_lecture' => $post['course_lecture'],
            'courseware_words' => json_encode($words),
            'courseware_sentence' => json_encode($sentence)
        ];
        Db::name('saas_courseware')->where('id', '=', $get['id'])->update($update_data);
        $this->success('恭喜, 数据更新成功!', '');
    }

    /**
     * 删除
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            LogService::write('课程管理', '删除了一项课件');
            $this->success("课件删除成功!", '');
        }
        $this->error("课件删除失败, 请稍候再试!");
    }

    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            LogService::write('课程管理', '修改了【' . $vo['title'] . '】课件');
        }
    }

    /**
     * @二维码
     */
    public function qrcode()
    {
        return $this->fetch('');
    }

    public function createQrCode()
    {
        $request = $this->request;
        $id = $request->get('id');
        if ($id) {
            $url = Db::name('saas_courseware')->where('id', $id)->value('url');
            $data = [
                'msg' => 1,
                'url' => $url,
                'data' => QrCode::createQRCodeString($url, 150)
            ];
            return json($data);
        } else {
            return json([
                'msg' => 0,
                'url' => '',
                'data' => ''
            ]);
        }
    }
}