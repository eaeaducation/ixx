<?php
// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller;


use controller\BasicAdmin;
use function GuzzleHttp\uri_template;
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\App;
use think\Db;

/**
 * User wbj726@gmail.com
 * Date 2018/6/30 18:14
 *  站内通知
 */
class Notice extends BasicAdmin
{
    /**
     * @var string
     * 定义当前操作表名
     */
    public $table = "saas_notice";

    /**
     * @return array|string
     * @throws
     * 站内通知列表
     */
    public function noticeList()
    {
        $db = Db::name($this->table)
            ->alias('n')
            ->where('n.status', '<>', 3)
            ->order('n.level desc , n.id desc')
            ->field('n.*');
        $get = $this->request->get();
        if (isset($get['title']) && $get['title'] != '') {
            $get['title'] = trim($get['title']);
            $db->where('n.title', 'like', "%{$get['title']}%");
        }
        //校区
        if ($this->user['id'] != 10000 && !in_array($this->user['authorize'], [1, 3, 4])) {
            $branch = $this->user['employee']['department'];
            $db->join('saas_notice_subtable si', 'n.id=si.notice_id', 'LEFT');
            $db->where('si.branch', '=', $branch);
        }
        if (isset($get['branch']) || isset($get['auth'])) {
            $db->join('saas_notice_subtable s', 'n.id=s.notice_id', 'LEFT');
            if ($get['branch'] != '') {
                $db->where('s.branch', '=', intval($get['branch']));
            }
            if ($get['auth'] != '') {
                $db->where('s.auth', '=', intval($get['auth']));
            }
            $db->group('n.id');
        }
        if (isset($get['create_at']) && $get['create_at'] != '') {
            list($start_time, $end_time) = explode(' - ', $get['create_at']);
            $start_time = strtotime(trim($start_time) . " 00:00:00");
            $end_time = strtotime(trim($end_time) . " 23:59:59");
            $db->whereBetween('created_at', [$start_time, $end_time]);
        }

        $this->title = '站内通知';
        return parent::_list($db, true);
    }

    /**
     * @return array|string
     * @throws
     * 添加站内通知
     */
    public function add()
    {
        $this->title = '添加通知';
        return $this->_form($this->table, 'form');
    }

    /**
     * @param $vo
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author Jasmine2
     */
    protected function _form_filter(&$vo)
    {
        if ($this->request->isGet() && isset($vo['id'])) {
            $authorizesAndBranches = Db::name('saas_notice_subtable')->where('notice_id', '=', $vo['id'])->select();
            $authorizes = array_filter(array_column($authorizesAndBranches, 'auth'));
            $branches = array_filter(array_column($authorizesAndBranches, 'branch'));
            $vo['auth'] = join(',', $authorizes);
            $vo['branch'] = join(',', $branches);
        }
        if ($this->request->isPost()) {
            if ($vo['content'] == '') {
                $this->error('通知内容不能为空!');
            }
            if (empty($vo['branch'])) {
                $this->error('请至少选择一个校区!');
            }
            if (empty($vo['auth'])) {
                $this->error('请至少选择一个角色!');
            }
            list($begin_time, $end_time) = explode(' - ', $vo['notice_time']);
            $vo['begin_time'] = strtotime($begin_time . " 00:00:00");
            $vo['end_time'] = strtotime($end_time . " 23:59:59");
        }
        if ($this->request->isPost() && isset($vo['id'])) {
            $this->_form_after($vo['id']);
        }
    }

    /**
     * @param $vo
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author Jasmine2
     */
    protected function _form_result($vo)
    {
        list($base, $spm, $url) = [url('@admin'), $this->request->get('spm'), url('admin/notice/noticeList')];
        if ($vo === true) {
            $this->success('修改通知成功！', "{$base}#{$url}?spm={$spm}");
        } else {
            $this->_form_after($vo);
            $this->success('添加通知成功！', "{$base}#{$url}?spm={$spm}");
        }
    }

    /**
     * @param $main_id
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author Jasmine2
     */
    protected function _form_after($main_id)
    {
        Db::name('saas_notice_subtable')->where('notice_id', '=', $main_id)->delete();
        $authorizes = $this->request->post('auth/a');
        $branches = $this->request->post('branch/a');
        $size = max(count($authorizes), count($branches));
        $data = [];
        for ($i = 0; $i < $size; $i++) {
            $temp = [
                'notice_id' => $main_id,
                'auth' => 0,
                'branch' => 0
            ];
            if (isset($authorizes[$i])) {
                $temp['auth'] = $authorizes[$i];
            }
            if (isset($branches[$i])) {
                $temp['branch'] = $branches[$i];
            }
            array_push($data, $temp);
        }
        Db::name('saas_notice_subtable')->insertAll($data);
    }

    /**
     * @return mixed
     * @throws
     * 通知详情
     */
    public function noticeview()
    {
        $this->title = '通知详情';
        return $this->_form($this->table, 'noticeview');
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
        $this->title = '编辑通知';
        return $this->_form($this->table, 'form');
    }

    /**
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author Jasmine2
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("通知删除成功!", '');
        }
        $this->error("通知删除失败, 请稍候再试!");
    }
}


