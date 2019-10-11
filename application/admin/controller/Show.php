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
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\App;
use think\Db;
use think\Exception;

/**
 * 后台入口
 * Class Index
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 10:41
 */
class Show extends BasicAdmin
{
    /**
     * @return mixed
     * @throws
     * @author Jasmine2
     */
    public function tpllist()
    {
        $this->title = "模板列表";
        $list = Db::name("saas_show_tpl")->order('cate_id asc, id desc')->where('is_del', 0)->select();
        $result = [];
        foreach ($list as $element) {
            $result[$element['cate_id']][] = $element;
        }
        $types = get_category(2);
        $this->assign('types', $types);
        return $this->view('', [
            'list' => $result
        ]);
    }

    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            empty($vo['demo_pic1']) && $this->error('效果图 1，请上传后再提交数据！');
            empty($vo['demo_pic2']) && $this->error('效果图 2，请上传后再提交数据！');
            empty($vo['json_data']) && $this->error('内容配置不能为空!');
            !(is_string($vo['json_data']) && (is_object(json_decode($vo['json_data'])) || is_array(json_decode($vo['json_data']))))
            && $this->error('json 结构不正确');
        }
    }

    protected function _form_result($vo)
    {
        list($base, $spm, $url) = [url('@admin'), $this->request->get('spm'), url('admin/show/tpllist')];
        $this->success('模板编辑成功!', "{$base}#{$url}?spm={$spm}");
    }

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function tpladd()
    {
        $this->title = '添加模板';
        $table = 'saas_show_tpl';
        return $this->_form($table, 'form');
    }

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function tpledit()
    {
        $table = 'saas_show_tpl';
        return $this->_form($table, 'form');
    }

    /**
     * @return \think\response\Json
     * 删除模板
     */
    public function tpldel()
    {
        $get = $this->request->get();
        $id = $get['id'];
        $res = Db::table('saas_show_tpl')->where('id', $id)->update(['is_del' => 1]);
        if ($res) {
            $this->success("模板删除成功！", '');
        } else {
            $this->error("模板删除失败！", '');
        }
    }

    public function sethd()
    {
        $post = $this->request->post();
        $hdid = $post['hdid'];
        $tplid = $post['tplid'];
        $tpljson = DB('saas_show_tpl')->field("json_data")->where('id', $tplid)->find();
        $tpljson = $tpljson ["json_data"];
        $data = [
            "tpl_id" => $tplid,
            "act_id" => $hdid,
            'created_at' => time(),
            'tpl_json' => $tpljson
        ];
        $info = Db("saas_active_tpl")->where('act_id', $hdid)->find();
        if (!$info) {
            $res = Db("saas_active_tpl")->where('act_id', $hdid)->insert($data);
        } else {
            $res = Db("saas_active_tpl")->where('act_id', $hdid)->update($data);
        }
        if ($res) {
            return json(['code' => 1, 'msg' => "模板选择成功"]);
        } else {
            return json(['code' => -1, 'msg' => "模板选择失败"]);
        }
    }


}