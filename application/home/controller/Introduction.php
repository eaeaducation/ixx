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


namespace app\home\controller;

use QRCode;
use service\DataService;
use service\WechatService;
use think\App;
use think\Controller;
use think\Db;
use think\db\Query;
use think\facade\Config;
use think\facade\View;
use Naixiaoxin\ThinkWechat\Facade;
use app\wechat\controller\CustomerRegister;
use think\facade\Log;


/**
 * 前台 转介绍页面
 * Class Introduction
 * @package app\home\controller
 */
class Introduction extends controller
{
    /**
     * @return mixed
     * 转介绍活动首页
     */
    public function index()
    {
        $url = "http://" . $_SERVER['HTTP_HOST'] . "/home/Introduction/index";
        $userInfo = WechatService::webOauth($url);
        $openId = $userInfo['openid'];
        //查询用户信息
        $call_back = url('index');
        $customer = CustomerRegister::checkRegister($openId, $call_back);
        if ($customer['parent_name'] != "未知" && !empty($customer['parent_name'])) {
            $title = $customer['parent_name'];
        } elseif (!empty($customer['name'])) {
            $title = $customer['name'] . '家长';
        } elseif (!empty($customer['parent_tel'])) {
            $title = $customer['parent_tel'] . '用户';
        } else {
            $title = '微信用户';
        }
        //查询活动信息
        $introductions = Db::name('saas_show_introduction')
            ->where('status', '<>', 3)->select();

        $info = Db::name('wechat_order')//支付情况
        ->where('openid', '=', $openId)
            ->where('aid', '=', '1111')
            ->where('status', '=', '1')
            ->find();
        $info2 = Db::name('saas_introducer_prize')->where('customer_id', $customer['id'])->Select();//完成情况

        foreach ($introductions as $k => &$v) {
            $v['reach'] = 0;
            foreach ($info2 as $k1 => $v1) {
                if ($v['id'] == $v1['act_id']) {
                    $v['reach'] = 1;
                }
            }
        }
        return $this->fetch('', [
                "introductions" => $introductions,
                'title' => $title,
                'customer_id' => empty($customer) ? '' : $customer['id'],
                'openid' => $openId,
                'info' => $info,
                'info2' => $info2
            ]
        );
    }

    /**
     * 生成专属二维码
     */
    public function ewm()
    {
        if ($this->request->isGet()) {
            $customer_id = $this->request->get('customer_id');
            $actid = $this->request->get('actid');
            $title = $this->request->get('title');
            $url = "http://" . $_SERVER['HTTP_HOST'] . url('add') . "?customer_id=" . $customer_id . "&actid=" . $actid . "&title=" . $title;

//            $app = Facade::officialAccount();
//            $tempqr = $app->qrcode->temporary($id, 15 * 3600);
            $code = QRCode::createQRCodeString($url, 182);

            $image = "data:image/png;base64," . $code;
            $imageName = "25220_" . date("His", time()) . "_" . rand(1111, 9999) . '.png';
            if (strstr($image, ",")) {
                $image = explode(',', $image);
                $image = $image[1];
            }

            $path = "./static/home/introduction/ewm/" . date("Ymd", time());
            if (!is_dir($path)) { //判断目录是否存在 不存在就创建
                mkdir($path, 0777, true);
            }
            $imageSrc = $path . "/" . $imageName;  //图片名字

            $r = file_put_contents($imageSrc, base64_decode($image));//返回的是字节数

            //合成图片
            $Absolute_Path = substr($_SERVER['SCRIPT_FILENAME'], 0, -10);
            //本地的绝对路径
            $dst_path = 'static/home/introduction/images/zsbg.png';//背景图
            $src_path = $imageSrc; //二维码
            $hz = substr(strrchr($dst_path, '.'), 1);
            $path = 'static/home/introduction/ewm/';
            //生成新图片名
            $image = $path . date("YmdHis") . rand(1000, 9999) . "." . $hz;
            //创建图片的实例
            $dst = imagecreatefromstring(file_get_contents($dst_path));
            $src = imagecreatefromstring(file_get_contents($src_path));
            //获取水印图片的宽高
            $src_w = 250;
            $src_h = 250;
            list($src_w, $src_h) = getimagesize($src_path);
            //如果水印图片本身带透明色，则使用imagecopy方法
            imagecopy($dst, $src, 270, 745, 0, 0, $src_w, $src_h);
            //输出图片
            list($src_w, $src_h, $dst_type) = getimagesize($dst_path);
            switch ($dst_type) {
                case 1://GIF
                    imagegif($dst, $image);
                    break;
                case 2://JPG
                    imagejpeg($dst, $image);
                    break;
                case 3://PNG
//              header('Content-Type: image/png');
                    imagepng($dst, $image);
                    break;
                default:
                    break;
            }
            return $this->fetch('', ['image' => $image]);
        }
    }

    /**
     * @return mixed|\think\response\Json
     * 转介绍的新客户入库
     */
    public function add()
    {
        if ($this->request->isGet()) {
            $customer_id = $this->request->get('customer_id');
            $actid = $this->request->get('actid');
            $title = $this->request->get('title');
            return $this->fetch('', ['customer_id' => $customer_id, 'actid' => $actid, 'title' => $title]);
        }
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $check = $this->checkMobile($post['phone']);
            if ($check) {
                return json(['code' => -1, 'msg' => "您已参加了此活动,请前往公众号获取入场方式"]);
            }
            $data['parent_name'] = $post['name'];
            $data['parent_tel'] = $post['phone'];
            $data['branch'] = $post['branch'];
            $data['first_branch'] = $post['branch'];
            $data['source'] = 1;
            $data['created_at'] = time();
            session('introducer_customer', $data);
            $data2['customer_id'] = Db::name('saas_customer')->insertGetId($data);//入库 获取客户ID
            $data2['introducer_id'] = $post['introducer_id'];//介绍人id
            $data2['actid'] = $post['actid'];
            $data2['created_at'] = time();
            $data2['status'] = 1;
            Db::name('saas_customer_introducer')->insert($data2);
            //获取活动达成人数
            $_condition = Db::name('saas_show_introduction')
                ->where('id', $data2['actid'])->field('condition')->find();
            $condition = $_condition['condition'];
            //统计该客户转介绍的人数
            $introducer_num = Db::name('saas_customer_introducer')->where('introducer_id', $data2['introducer_id'])
                ->where('actid', $data2['actid'])->count(1);
            //判断 如果达成 往saas_introducer_prize表中插入领奖记录
            if ($introducer_num >= $condition) {

                $count = Db::name('saas_introducer_prize')
                    ->where('act_id', $data2['actid'])
                    ->where('customer_id', $data2['introducer_id'])
                    ->count(1);
                if ($count < 1) {
                    $data3 = [
                        'act_id' => $data2['actid'],
                        'customer_id' => $data2['introducer_id'],
                        'introducer_num' => $introducer_num,
                        'status' => 1,
                        'created_at' => time()
                    ];
                    Db::name('saas_introducer_prize')->insert($data3);
//推送
                    $openid = Db::name('saas_customer')->where('id', 18126)->field('wxopenid')->find();
                    $openid = $openid['wxopenid'];
                    $tpl_id = "BtmU0zzQ1RccgglWHhEVagqwEm39a7gYNeMlCAlBn10"; // 官网
//                $tpl_id = "inBKKILqpe2NEXcCkay2aONFW_amzP8_jaLI3Sh8JjY";   // 测试
                    $app = Facade::officialAccount();
                    $app->template_message->send([
                        'touser' => $openid,
                        'template_id' => $tpl_id,
                        'url' => $this->request->domain() . url('paycat') . '?openid' . $openid,
                    ]);
                } else {
                    $data3 = [
                        'act_id' => $data2['actid'],
                        'customer_id' => $data2['introducer_id'],
                        'introducer_num' => $introducer_num,
                    ];
                    Db::name('saas_introducer_prize')
                        ->where('act_id', $data2['actid'])
                        ->where('customer_id', $data2['introducer_id'])
                        ->update($data3);
                }
            }
            //微信通知客户领奖  暂留

            return json(['code' => 99, 'msg' => "恭喜,报名成功!"]);
        }
    }

    /**
     * @param $mobile
     * @return array|bool|null|\PDOStatement|string|\think\Model
     * 验证转介绍的客户是否已经是存在的客户
     */
    private
    function checkMobile($mobile)
    {
        $customer = DB::table('saas_customer')->where('parent_tel', $mobile)->find();
        if (empty($customer)) {
            return false;
        } else {
            return $customer;
        }
    }

    /**
     * @return mixed
     * 客户查看自己转介绍的客户
     */
    public
    function myact()
    {
        $get = $this->request->get();

        if (isset($get['cid'])) {
            $cid = $get['cid'];
        } else {
            $this->error('非法操作', 'index');
        }
        $customer = Db::name('saas_customer_introducer')->where('status', '<>', 3)->where('introducer_id', $cid)->select();
        $act_id = [];
        foreach ($customer as $k => &$v) {
            array_push($act_id, $v['actid']);
        }
        $act_id = array_unique($act_id);
        $act = Db::name('saas_show_introduction')->field('title,id')->whereIn('id', $act_id)->select();
        return $this->fetch('', ['act' => $act, 'customer' => $customer]);
    }

    public function xcxmyact()
    {
        $get = $this->request->get();
        $introducer_id = Db::name('saas_customer')->where('xcxopenid', '=', $get['openid'])->field('id')->find();

        $cid = Db::name('saas_customer_introducer')
            ->where('introducer_id', '=', $introducer_id['id'])
            ->where('actid', '=', 12)
            ->field('customer_id')
            ->select();
        $cid = array_column($cid, 'customer_id');

        $data = Db::name('saas_customer')
            ->whereIn('id', $cid)
            ->field('parent_tel, xcxopenid')
            ->select();
        Log::error($data);
        return $this->fetch('', ['data' => $data]);
    }

    public
    function welcome()
    {
        return $this->fetch();
    }

    public function payact()
    {
        return $this->fetch();
    }

    public function xcxpayact()
    {
        $time = sysconf('activeTime');
        $time = strtotime($time);
        $this->assign('quality_price',sysconf('shier_quality_price'));
        $this->assign('elite_price',sysconf('shier_elite_price'));
        return $this->fetch('', ['time' => $time]);
    }

    public function fail()
    {
        return $this->fetch();
    }

    public function success_get()
    {
        return $this->fetch();
    }
}