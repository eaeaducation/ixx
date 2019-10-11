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
use  think\facade\Cache;

/**
 * 后台入口
 * Class Index
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 10:41
 */
class Showactb extends BasicAdmin
{

    /**
     * 自定义所选择的活动模板
     * @throws
     */
    public function updatetpl()
    {
        $act_id = input("get.act_id");
        $tplinfo = Db::name("saas_activity")->where('id', '=', $act_id)
            ->where('status', '<>', 3)
            ->field('id,tpl_id,tpl_json,status')
            ->find();
        //加入 判断待管理的模板的类型
        $tpl_type = '1';
        if($tplinfo){
            $tpl_type_row =  Db::name("saas_show_tpl")->where("id","=",$tplinfo['tpl_id'])->field("tpl_type")->find();
            $tpl_type = $tpl_type_row['tpl_type'];
        }
        $this->assign('json_data', json_decode($tplinfo['tpl_json'], true));
        $this->assign('myid', $tplinfo['id']);

        $this->assign('status', $tplinfo['status']);
        //tplid 模板表ID
        $this->assign('tplid', $tplinfo['tpl_id']);
        //id 为活动ID
        $this->assign('actid', $tplinfo['id']);

        if($tpl_type == '1'){
            return $this->fetch('');
        }else{
            return $this->fetch('updatetpl2');
        }
    }

    /*
     * 保存自定义活动模板的内容
     * */
    public function edtpl()
    {
        $id = input("get.myid");
        $data_post = input("post.");
        $data = $data_post['input'];
        $tpljson = Db("saas_activity")->where('status', '<>', 3)->where('id', '=', $id)->field('tpl_json')->find();
        $tpljson = json_decode($tpljson['tpl_json'], true);
        $tpljson['logo'] = $data_post['baselogo'];

        foreach ($tpljson['data'] as $k => $v) {
            $i = 0;
            foreach ($v as $k0 => $v0) {
                $tpljson['data'][$k][$k0]['value'] = $data[$k][$i];
                $i++;
            }
        }

        //模板新增公共部分配置
        if(isset($data_post['public']) && !empty($data_post['public'])){
            $l = 1;
            foreach($tpljson['public'] as $k1 => $v1){
                $tpljson['public'][$k1]['value'] = $data_post['public'][$l];
                $l++;
            }
        }

        $updata['tpl_json'] = json_encode($tpljson, JSON_UNESCAPED_UNICODE);
        $updata['created_at'] = time();
        $res = Db("saas_activity")->where('status', '<>', 3)->where('id', '=', $id)->update($updata);
        if ($res) {
            return json(['code' => '0000', 'success' => 'true', 'msg' => '保存预览成功！']);
        } else {
            return json(['code' => '0009', 'success' => 'false', 'msg' => '保存预览失败！']);
        }
    }

    /*
     * 发布活动
     * */
    public function tplfb(){
        $id = input("get.act_id");
        $type = input("get.type");
        if($type=='close'){
            $row = Db("saas_activity")->where('id','=',$id)->update(['status' => 2]);
            if($row){
                return json(['code'=>'0000','success'=>'true','msg'=>'取消发布成功！']);
            }else{
                return json(['code'=>'0009','success'=>'false','msg'=>'取消发布失败！']);
            }
        }else{
            $row = Db("saas_activity")->where('id','=',$id)->update(['status' => 1]);
            if($row){
                return json(['code'=>'0000','success'=>'true','msg'=>'发布成功！']);
            }else{
                return json(['code'=>'0009','success'=>'false','msg'=>'发布失败！']);
            }
        }


    }


}