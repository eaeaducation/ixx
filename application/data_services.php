<?php

use think\Db;

/**
 * User: Jasmine2
 * Date: 2018/7/6 11:49
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 * 各项数据查询服务
 */

/**
 * @param string $interval
 * @author Jasmine2
 * @throws
 * @return array
 * 即将过生日的员工
 */
function get_birthday_employee($interval = 'day')
{
    $db = Db::name('saas_employee')
        ->order('id desc')
        ->where('status', '<>', 3);
    switch ($interval) {
        case 'day':
            $db->where('FROM_UNIXTIME(birthday,\'%m%d\')=\'' . date('md') . '\'');
            break;
        case 'month':
            $db->where('FROM_UNIXTIME(birthday,\'%m\')=\'' . date('m') . '\'');
            break;
        case 'next':
            $db->where('FROM_UNIXTIME(birthday,\'%m\')=\'' . date('m', strtotime('+1 month')) . '\'');
            break;
    }
    return $db->select();
}