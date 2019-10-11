<?php

namespace hook;

use think\facade\Config;

/**
 * User: Jasmine2
 * Date: 2018/5/23 10:25
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 * 系统站点判断 Hook，核心文件，请勿修改
 */
class Site
{
    public function run($params)
    {
        $request = app('request');
        $site_list = config('sitelist.');
        $domain = $request->host();
        $site = null;

        $current_domain_prefix = substr($domain, 0, stripos($domain, '.'));
        foreach ($site_list as $k => $v) {
            if ($current_domain_prefix == $v['sitename']) {
                $site = $v;
                break;
            }

            if (isset($v['domains']) && ((is_string($v['domains']) && $domain == $v['domains'])
                    || (is_array($v['domains']) && in_array($domain, $v['domains'])))) {
                $site = $v;
                break;
            }
        }
        ($site != null || $request->ip() == '0.0.0.0') || die(Config::get('saas_error'));
        $database = config('database_list.' . $site['ds']);
        $database['database'] = $site['database'];
        foreach ($database as $k => $v) {
            Config::set('database.' . $k, $v);
        }
        $site_name = $site['sitename'];
        Config::set('site_name', $site_name);
        Config::set('cache.path', env('RUNTIME_PATH') . 'cache/' . $site_name);
        Config::set('log.path', env('RUNTIME_PATH') . 'log/' . $site_name);
        Config::set('database.share.type', env('share.type'));
        Config::set('database.share.hostname', env('share.hostname'));
        Config::set('database.share.database', env('share.database'));
        Config::set('database.share.username', env('share.username'));
        Config::set('database.share.password', env('share.password'));
        Config::set('database.share.hostport', env('share.hostport'));
    }
}