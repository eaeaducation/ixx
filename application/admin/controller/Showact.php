<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/20
 * Time: 11:00
 */

namespace app\admin\controller;

use think\Db;
use controller\BasicAdmin;
use service\DataService;
use QRCode;

class Showact extends BasicAdmin
{
    public $table = 'saas_activity';

    /**
     * 活动列表
     * @throws
     */
    public function actlist()
    {
        $this->title = '活动列表';
        $get = $this->request->get();
        //搜索关键字
        $keyword = !empty($get['keyword']) ? $get['keyword'] : '';
        $time = !empty($get['time']) ? $get['time'] : '';

        //数据处理
        $db = Db::table('saas_activity');
        if (!empty($keyword)) {
            $db->where('title', 'like', '%' . $keyword . '%');
        }

        if (!empty($time)) {
            list($stime, $etime) = explode(' - ', $time);
            $begintime = strtotime($stime);
            $endtime = strtotime($etime);
            $db->where('begin_time', '>=', $begintime)->where('end_time', '<=', $endtime);
        }
        $userid = $this->user['id'];
        $authorize = $this->user['authorize'];
        if ($userid != '10000') {
            if (!in_array($authorize, [1, 3, 4, 5, 9, 10, 11])) {
                $db->where("userid", "=", "10000");
            } else {
                $db->where("userid", "in", "10000," . $this->user['id']);
            }
        }
        $db->where('status', '<>', 3);
        $db->order('id desc');
        return parent::_list($db);
    }

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     * 活动添加
     */
    public function actadd()
    {
        $this->title = '添加活动';
        $list = Db::name("saas_show_tpl")->where('is_del', '=', 0)->order('cate_id asc, id desc')->select();
        $result = [];
        foreach ($list as $element) {
            $result[$element['cate_id']][] = $element;
        }
        $types = get_category(2);
        $this->assign('types', $types);
        $this->assign('list', $result);

        if ($this->request->isPost()) {
            $post = $this->request->post();
            $row = $this->getData($post);
            $row['userid'] = $this->user['id'];
            $db = Db::name("saas_activity");
            $res = $db->insert($row);
            if ($res) {
                $actid = $db->getLastInsID();
                return json(['success' => true, 'code' => 200, 'msg' => '活动添加成功', 'acttitle' => $row['title'], 'actid' => $actid]);
            } else {
                return json(['success' => false, 'code' => 200, 'msg' => '活动添加失败']);
            }
        }
        return $this->_form($this->table, 'add');
    }

    protected function _form_result()
    {
        list($base, $spm, $url) = [url('@admin'), $this->request->get('spm'), url('admin/showact/actlist')];
        $this->success('操作成功!', "{$base}#{$url}?spm={$spm}");
    }

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function edit()
    {
        $this->title = '活动修改';
        return $this->_form($this->table, 'form');
    }

    /**
     * @param $vo
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            list($begin_time, $end_time) = explode(' - ', $vo['time']);
            $vo['begin_time'] = strtotime($begin_time);
            $vo['end_time'] = strtotime($end_time);
            if (isset($vo['tpl_id'])) {
                $tpl = Db::name('saas_show_tpl')->where('id', '=', $vo['tpl_id'])->find();
                if ($tpl) {
                    $vo['tpl_json'] = $tpl['json_data'];
                }
            }
        }
    }

    /**
     * 获取模板json 数据处理
     */
    public function getData($row)
    {
        $data = [];
        list($begin_time, $end_time) = explode(' - ', $row['time']);
        $data['begin_time'] = strtotime($begin_time);
        $data['end_time'] = strtotime($end_time);
        if (isset($row['tplid'])) {
            $tpl = Db::name('saas_show_tpl')->where('id', '=', $row['tplid'])->find();
            if ($tpl) {
                $data['tpl_json'] = $tpl['json_data'];
            }
        }
        $data['created_at'] = time();
        $data['title'] = $row['title'];
        $data['type'] = $row['type'];
        $data['tpl_id'] = $row['tplid'];
        $data['status'] = $row['status'];
        return $data;
    }

    /**
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author Jasmine2
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("活动删除成功！", '');
        }
        $this->error("活动删除失败，请稍候再试！");
    }

    /**
     * 活动参与详情
     * @return array|string
     */
    public function details()
    {
        $this->title = '活动详情';
        $aid = $this->request->get('id');
        $db = Db::name('saas_customer')->alias('c')
            ->join('wechat_order o', 'o.cid = c.id', 'left')
            ->field('o.title,o.body,o.price,o.status,c.parent_name,c.name,c.parent_tel,o.created_at,o.id,c.collect_id,c.sales_id')
            ->where('o.aid', '=', $aid)
            ->order('o.created_at desc');
        $user = session('user');
        if (!in_array($user['authorize'], [1, 3, 4])) {
            $db->where('c.userid','=',$user['id']);
        }
        if ($this->request->has('parent_tel') && $this->request->get('parent_tel') != '') {
            $db->where('parent_tel', '=', $this->request->get('parent_tel'));
        }
        if ($this->request->has('status') && $this->request->get('status') != '') {
            $db->where('o.status', '=', $this->request->get('status'));
        }
        if ($this->request->has('dxy') && $this->request->get('dxy') != '') {
            $db->where('sales_id', '=', $this->request->get('dxy'));
        }
        if ($this->request->has('cc') && $this->request->get('cc') != '') {
            $db->where('collect_id', '=', $this->request->get('cc'));
        }
        return parent::_list($db, true);
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
        $userid = $this->user['id'];
        $id = $request->get('id');
        if ($id) {
            $url = $this->request->domain() . url('admin/showtpl/tplrelease') . '?actid=' . $id . '&userid=' . $userid;
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

