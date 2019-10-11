<?php

namespace sms;

/**
 * Created by PhpStorm.
 * User: jasmine
 * Date: 2018/5/19
 * Time: 15:06
 */

interface SmsInterface
{
    public function send($mobiles, $content, $sign = '');
}