<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/14
 * Time: 9:55
 */

namespace app\educational\controller;

use app\admin\controller\Log;
use controller\BasicAdmin;
use service\LogService;
use think\Db;

class Renew extends BasicAdmin
{
    public function index()
    {
        $this->title = '到期学员';
        $get = $this->request->get();
        $db = Db::name('saas_order_log')
            ->alias('ol')
            ->join('saas_order o', 'ol.order_id=o.id')
            ->join('saas_customer c', 'o.student_id=c.id')
            ->join('saas_courses sc', 'ol.goods_id=sc.id')
            ->field('ol.id,ol.order_id,ol.goods_id,ol.goods_num,ol.consume_num,(ol.goods_num-ol.consume_num) as last_num,o.student_id,c.id,c.name,c.parent_tel,c.branch,c.gender,c.branch,c.nickname,sc.title')
            ->where('ol.goods_type', '=', '1')
            ->where('o.status', '=', '5')
            ->order('last_num')
            ->group('ol.goods_id')
            ->group('ol.order_id');
        if (isset($get['branch']) && $get['branch'] != '') $db->where('c.branch', '=', $get['branch']);
        if (isset($get['time_range']) && $get['time_range'] != '') {
            $get['time_range'] = str_replace('+~+', ' ~ ', $get['time_range']);
            list($start, $end) = explode(' ~ ', $get['time_range']);
            $_start = strtotime($start);
            $_end = strtotime($end) + 86400;
            $db->whereBetween('o.created_at', [$_start, $_end]);
        }
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $get['keyword'] = trim($get['keyword']);
            if (preg_match('/^\d{11}$/', $get['keyword'])) {
                $db->where('c.parent_tel', '=', "{$get['keyword']}");
            } elseif (preg_match('/[a-zA-Z\x{4e00}-\x{9fa5}]/u', $get['keyword'])) {
                $db->where('c.name|c.nickname', 'like', "%{$get['keyword']}%");
            }
        }
        if (!in_array($this->user['authorize'], [1, 3, 4])) {
            $db->where('c.branch', '=', $this->user['employee']['department']);
        }
        return $this->_list($db, true);
    }

    protected function _data_filter(&$data)
    {
        foreach ($data as $key => $value) {
            if ($value['last_num'] < 0) {
                unset($data[$key]);
            }
        }
    }

    /**
     * 短信发送
     * @return \think\response\View
     */
    public function sms()
    {
        if ($this->request->isPost()) {
            $ids = $this->request->get('id');
            $content = $this->request->post('content', false);
            if (!$content) {
                $this->error('短信内容不能为空!');
            }
            $mobiles = Db::name('saas_customer')->where('id', 'in', $ids)->column('parent_tel');
            $ret = send_sms_other($mobiles, $content, '爱学学');
            if ($ret) {
                LogService::write('发送短信', '给' . count($mobiles) . '个客户成功发送短信');
                $this->success("短信发送成功!", '');
            } else {
                $this->error('短信发送失败');
            }
        } else {
            $fastowrd = getFastOptions(session('user.authorize'), true);
            return view('sms_content', [
                'fastword' => $fastowrd
            ]);
        }
    }
}