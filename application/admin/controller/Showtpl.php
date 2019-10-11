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
use QRCode;
use service\HttpService;

/**
 * 后台入口
 * Class Index
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 10:41
 */
class Showtpl extends BasicAdmin
{


    public function tpladd()
    {
        if (input("post.")) {
            if (input("post.upload_type") == 'shangchuan') {
                $data['demo_pic1'] = input("post.demo_pic1");
                $data['demo_pic2'] = input("post.demo_pic2");
            } else {
                $data['demo_pic1'] = input("post.demo_pic3");
                $data['demo_pic2'] = input("post.demo_pic4");
            }
            $data['tpl_name'] = input("post.tpl_name");
            $data['cate_id'] = input("post.cate_id");
            $data['json_data'] = input("post.json_data");
            $data['desc'] = input("post.desc");
            $data['created_at'] = time();
            $res = Db("saas_show_tpl")->insert($data);
            if ($res) {
                return json(['code' => '0000', 'success' => 'true', 'msg' => '添加成功！']);
            } else {
                return json(['code' => '0009', 'success' => 'false', 'msg' => '添加失败！']);
            }
        } else {
            return $this->fetch('', [
                'title' => '添加模板',
            ]);
            // $this->fetch('main');
        }
    }

    public function tpledit()
    {
        if (input("post.")) {
            if (input("post.upload_type") == 'shangchuan') {
                $data['demo_pic1'] = input("post.demo_pic1");
                $data['demo_pic2'] = input("post.demo_pic2");
            } else {
                $data['demo_pic1'] = input("post.demo_pic3");
                $data['demo_pic2'] = input("post.demo_pic4");
            }
            $tplid = input("post.tpl_id");
            $data['tpl_name'] = input("post.tpl_name");
            $data['cate_id'] = input("post.cate_id");
            $data['json_data'] = input("post.json_data");
            $data['desc'] = input("post.desc");
            $data['created_at'] = time();
            $res = Db("saas_show_tpl")->where("id", "=", $tplid)->update($data);
            if ($res) {
                return json(['code' => '0000', 'success' => 'true', 'msg' => '修改成功！']);
            } else {
                return json(['code' => '0009', 'success' => 'false', 'msg' => '修改失败！']);
            }
        } else {
            if (input("get.tplid")) {
                $tplid = input("get.tplid");
                $tpl_row = Db("saas_show_tpl")->where("id", "=", $tplid)->find();

                return $this->fetch('', [
                    'title' => '修改模板',
                    'tpl_id' => $tplid,
                    'data' => $tpl_row
                ]);
            }
        }


    }


    /*
     * 动态加载活动所选择的模板
     * */
    public function tplsetok()
    {
        $tplid = !empty(input("get.tplid")) ? input("get.tplid") : '1';
        $myid = !empty(input("get.myid")) ? input("get.myid") : '1';
        $act_id = !empty(input("get.actid")) ? input("get.actid") : '1';
        $cid = !empty(input("get.cid")) ? input("get.cid") : '';
        $userid = !empty(input("get.userid")) ? input("get.userid") : '10000';
        $res_tpl = Db::name("saas_show_tpl")->where('id', '=', $tplid)->find();
        $tpl = $res_tpl['tpl_name'];
        $this->assign('myid', $myid);
        $this->assign('userid', $userid);
        $this->assign('actid', $act_id);
        $this->assign('cid', $cid);
        return $this->fetch($tpl);
    }

    /**
     * 返回 【动态加载活动所选择的模板】 数据源
     * @throws
     */
    public function get_json_res()
    {
        $act_id = !empty(input("get.actid")) ? input("get.actid") : '';
        $myid = input("get.myid");
        $res_act_tpl = Db::name("saas_activity")->where('id', '=', $myid)->find();
        return response($res_act_tpl['tpl_json'], 200, ['content-type' => 'application/json']);
    }

    /*
    * 动态加载 【模板管理】 中 的预览
    * */
    public function tplmgmt()
    {
        $tplid = !empty(input("get.tplid")) ? input("get.tplid") : '1';

        $res_tpl = Db("saas_show_tpl")->where('id', '=', $tplid)->find();

        $tpl = $res_tpl['tpl_name'];
        $this->assign('tplid', $tplid);
        return $this->fetch($tpl);
    }

    /*
     * 返回 【动态加载活动所选择的模板】 数据源
     * */
    public function get_json_res_tplmgmt()
    {
        $tplid = input("get.tplid");
        $res_act_tpl = Db("saas_show_tpl")->where('id', '=', $tplid)->find();
        return json(json_decode($res_act_tpl['json_data'], true));
    }

    /*
     *活动发布 动态加载 【活动】 中 的模板
     * */
    public function tplrelease()
    {
        $act_id = !empty(input("get.actid")) ? input("get.actid") : '1';
        $userid = !empty(input("get.userid")) ? input("get.userid") : '10000';
        $cid = !empty(input("get.cid")) ? input("get.cid") : '';
        $res_tpl = Db("saas_activity")->where('id', '=', $act_id)->find();
        $tplid = $res_tpl['tpl_id'];
        $acttitle = $res_tpl['title'];
        $tpl = Db("saas_show_tpl")->where('id', '=', $tplid)->find();
        $tplname = $tpl['tpl_name'];
        $this->assign('actid', $act_id);
        $this->assign('userid', $userid);
        $this->assign('cid', $cid);
        $this->assign('acttitle', $acttitle);
        return $this->fetch($tplname);
    }

    private function getRandom($param)
    {
        $str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $key = "";
        for ($i = 0; $i < $param; $i++) {
            $key .= $str{mt_rand(0, 32)};
        }
        return $key;
    }

    /*
     * 返回 【活动模板发布】加载 数据源
     * */
    public function get_json_release()
    {
        $actid = input("get.actid");
        $res_act_tpl = Db("saas_activity")->where('id', '=', $actid)->find();
        return json(json_decode($res_act_tpl['tpl_json'], true));

    }

    /*
    * 返回 参与活动后的跳转页面（二维码推广页）
    * */
    public function activeBack()
    {
        $actid = $this->request->get('actid');
        $uid = $this->request->get('uid');
        $userid = $this->request->get('userid');
        $url = $this->request->domain() . url('admin/showtpl/tplrelease') . '?actid=' . $actid . '&cid=' . $uid . '&userid=' . $userid;
        $qrcode_img = QrCode::createQRCodeString($url, 200);
        return $this->fetch('activeback', ['actid' => $actid, 'qrcode' => $qrcode_img]);
    }

    private function getAccessTokenUtil ()
    {
        $appid = sysconf('wechat_appid');
        $secret = sysconf('wechat_appsecret');
        $request_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
        $res = HttpService::get($request_url,[]);
        $res = json_decode($res, true);
        $token = $res['access_token'];
        return $token;
    }

    private function jsapiTicketUtil ()
    {
        $token = $this->getAccessTokenUtil();
        $request_url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='. $token .'&type=jsapi';
        $res = HttpService::get($request_url,[]);
        $res = json_decode($res, 1);
        $jsapi_ticket = $res['ticket'];
        return $jsapi_ticket;
    }
}
