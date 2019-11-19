<?php


namespace app\api_xcx\controller;


use controller\BasicXcx;
use sms\Lingkai;

class Test extends BasicXcx
{
    public function test()
    {
//        dump(session_id());
        $str = '尊敬的马晨溪家长，您好！您与12:20分反馈的问题，我们已经安排相关人员跟进，目前的进度是：11月14日已提交审核，3-5个工作日内处理成功。投诉的相关人员问题，公司内部已处理，为您造成的不便敬请谅解，感谢您的理解;
高新公馆电话：029-68578757
如有其他请咨询EA国际教育西北总部：4006692620';
//        dump($content);die;
        $res = send_all_sms('15109293688', $str, '', 2);
        dump($res);
    }
}