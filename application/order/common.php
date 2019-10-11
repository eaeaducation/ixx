<?php
/**
 * User: Youjingqiang
 * Date: 2019/3/15 11:45
 * Email: youjingqiang@gmail.com
 * Copyright (c) Youjingqiang <youjingqiang@gmail.com>
 */

function is_wechat()
{
    if (strpos(\think\facade\Request::server('HTTP_USER_AGENT'), 'MicroMessenger')) {
        return true;
    }
    return false;
}

function is_alipay()
{
    if (strpos(\think\facade\Request::server('HTTP_USER_AGENT'), 'Alipay')) {
        return true;
    }
    return false;
}