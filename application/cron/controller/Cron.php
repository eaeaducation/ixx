<?php

namespace app\cron\controller;

use controller\BasicAdmin;
use sms\Lingkai;
use think\Db;
use think\Request;

class Cron extends BasicAdmin
{

    public function __construct(Request $request)
    {
        $token = $request->get('token', '');
        if ($token != 'yFJv12EZl3DKvL76XilO9rEgrYrXJC') {
            return;
        }

    }

    /**
     * 会员生日祝福短信群发
     */
    public function sendBirthdayInfo()
    {
        $current_date = date('Ymd');
        $content = '尊敬的EA会员,祝你生日快乐';
        $data = Db::name('saas_customer')
            ->where('status', '<>', 3)
            ->where("FROM_UNIXTIME(birthday, concat('%Y%m%d')) = $current_date")
            ->column('parent_tel');
        if ($data) {
            $sms = new Lingkai('3');
//            $sms->send($data, $content, '');
        }
    }
}
