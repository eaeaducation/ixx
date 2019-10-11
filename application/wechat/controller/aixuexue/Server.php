<?php
/**
 * User: Jasmine2
 * Date: 2018/7/20 15:32
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\wechat\controller\aixuexue;

use app\admin\controller\Log;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;
use think\Controller;
use Naixiaoxin\ThinkWechat\Facade;
use think\Db;
use think\exception\PDOException;

/**
 * Class Show
 * @package app\wechat\controller\pay
 * @author Jasmine2
 * 爱学学通用工具类
 */
class Server extends Controller
{
    /**
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @author Jasmine2
     */
    public function index()
    {
        $app = Facade::officialAccount();
        $app->server->push(function ($message) {
            if (isset($message['FromUserName'])) {
                return $this->exe_message($message);
            }
            return $this->forText();
        });
        $response = $app->server->serve();
        $response->send();
    }


    private function exe_message($message)
    {
        switch ($message['MsgType']) {
            case 'event':
                $res = $this->forEvent($message);
                break;

            case 'text':
                $res = $this->forText($message);
                break;

//            case 'image':
//                $res=$this->callbackImage($message);
//                break;
//
//            case 'voice':
//                $res=$this->forVoice($message);
//                break;
//
//            case 'video':
//                $res=$this->forVideo($message);
//                break;
//
//            case 'shortvideo':
//                $res=$this->forShortvideo($message);
//                break;
//
//            case 'location':
//                $res=$this->forLocation($message);
//                break;
//
//            case 'link':
//                $res=$this->forLink($message);
//                break;

            default:
                $res = "unknown msg type: " . $message['MsgType'];   //未知的消息类型
                break;
        }
        return $res;

    }

    private function forEvent($message)
    {
        $send_msg = "";
        $user_openid = $message['FromUserName'];
        switch ($message['Event']) {
            case 'subscribe':
                if (isset($message['EventKey'])) {
                    if (!empty($message['EventKey'])) {
                        if (strpos($message['EventKey'], 'order') !== false) {
                            $orderid_str = explode("_", $message['EventKey'])['1'];
                            $cid_str = explode("_", $message['EventKey'])['2'];
                            $name_str = explode("_", $message['EventKey'])['3'];
                            $orderid = explode("=", $orderid_str)['1'];//订单id
                            $cid = explode("=", $cid_str)['1'];//客户id
                            $name = explode("=", $name_str)['1'];//学生姓名
                            $data['wxopenid'] = $user_openid;
                            $isok = Db::name('saas_customer')->where("id", "=", $cid)->update($data);
                            $isok ? $send_msg = "您通过扫码关注了爱学学,并同时关注了学员【" . $name . "】！" : '';
                            $this->send_tpl_news($orderid, $user_openid);
                        } else {
                            $eventkey = explode("_", $message['EventKey'])['1'];
                            $has_wxopenid = Db::name('saas_customer')->where("id", "=", $eventkey)->field('wxopenid,name')->find();
                            $data['wxopenid'] = $user_openid;
                            if (empty($has_wxopenid['wxopenid'])) {
                                $isok = Db::name('saas_customer')->where("id", "=", $eventkey)->update($data);
                                $isok ? $send_msg = "您通过扫码关注了爱学学,并同时关注了学员【" . $has_wxopenid['name'] . "】！" : '';
                            } else {
                                $send_msg = "您通过扫码关注了爱学学,您已经关注了学员【" . $has_wxopenid['name'] . "】！";
                            }
                        }
                    }
                } else {
                    $send_msg = $this->forText();
                }
                break;

            case 'SCAN':
                $send_msg = "您通过扫码来到了爱学学！";
                if (isset($message['EventKey'])) {
                    if (!empty($message['EventKey'])) {
                        if (strpos($message['EventKey'], 'order') !== false) {
                            $orderid = explode("=", $message['EventKey'])['1'];
                            $this->send_tpl_news($orderid, $user_openid);
                        } else {
                            $has_wxopenid = Db::name('saas_customer')->where("id", "=", $message['EventKey'])->field('wxopenid,name')->find();
                            if (empty($has_wxopenid['wxopenid'])) {
                                $data['wxopenid'] = $user_openid;
                                $isok = Db::name('saas_customer')->where("id", "=", $message['EventKey'])->update($data);
                                $isok ? $send_msg = "您通过扫码来到了爱学学,并关注了学员【" . $has_wxopenid['name'] . "】！" : '';
                            } else {
                                $send_msg = "您通过扫码来到了爱学学,您已经关注了学员【" . $has_wxopenid['name'] . "】！";
                            }
                        }
                    }
                } else {
                    $send_msg = $this->forText();
                }
                break;

            case 'unsubscribe':
                $send_msg = "您取消关注爱学学！";
                $data['wxopenid'] = '';
                Db::name('saas_customer')->where("wxopenid", "=", $user_openid)->update($data);
                break;

            default:
                $send_msg = $this->forText();   //其他事件 暂时不做任何业务处理。
                break;
        }
        return $send_msg;
    }

    /*
     * 发送模板消息 之 订单详情
     * */
    public function send_tpl_news($orderid, $openid)
    {
        //订单信息
        $order_info = Db::name("saas_order")->alias("o")->join("saas_customer c", "o.student_id = c.id")->
        join("saas_class l", "o.class_id = l.id")->where("o.id", "=", $orderid)->
        field("o.*,c.name,l.title")->find();
        $tpl_id = sysconf('tpl_id_order');
        $app = Facade::officialAccount();
        $app->template_message->send([
            'touser' => $openid,
            'template_id' => $tpl_id,
            'url' => $this->request->domain() . url('order/detail/detail') . '?id=' . $orderid . '&type=true',
            'data' => [
                'first' => [
                    'value' => '【' . $order_info['name'] . '】 小朋友家长，您的订单:',
                    'color' => '#040404'
                ],
                'keyword1' => [
                    'value' => $order_info['orderno'],
                    'color' => '#173177'
                ],
                'keyword2' => [
                    'value' => '您购买的【' . $order_info['title'] . '】，价格：【' . $order_info['price'] . ' /元】 已出单。',
                    'color' => '#173177'
                ],
                'keyword3' => [
                    'value' => date('Y-m-d H:i:s', time()),
                    'color' => '#173177'
                ],
                'remark' => [
                    'value' => ' 感谢您的查阅，请联系销售完成付款！',
                    'color' => '#040404'
                ]
            ],
        ]);

        return true;
    }

    private function forText()
    {
        $title = '史上最强双十一折扣';
        $url = 'http://saas.eaeducation.net/home/introduction/index';
        $image = 'http://saas.eaeducation.net/static/home/introduction/images/shuangshiyi.jpg';
        $items = [
            new NewsItem([
                'title' => $title,
                'description' => '原价13800精英课程 11111秒',
                'url' => $url,
                'image' => $image,
            ]),
        ];
        $news = new News($items);
        return $news;
    }

}