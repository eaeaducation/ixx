<?php
/**
 * User: Jasmine2
 * Date: 2018/5/23 16:13
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 * 站点管理入口文件
 */
function success($msg, $code = 200)
{
    header('Content-Type:application/json');
    die(json_encode([
        'success' => true,
        'msg' => $msg,
        'code' => $code
    ]));
}

function error($msg, $code = 400)
{
    header('Content-Type:application/json');
    die(json_encode([
        'success' => false,
        'msg' => $msg,
        'code' => $code
    ]));
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if (in_array($action, ['add', 'del', 'add-domain', 'rm-domain'])) {
    check_token();
    switch ($action) {
        case 'add':
            add();
            break;
        case 'del':
            del();
            break;
        case 'add-domain':
            add_domain();
            break;
        case 'rm-domain':
            rm_domain();
            break;
    }
} else {
    error('action no allowed.', 404);
}
function check_token()
{
    $token = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
    if (is_null($token) || $token == '') {
        error('Token can\'t be null', 403);
    }
    $token == sha1(date('Ymd')) || error('unavailable token');
}

function add()
{
    $data = $_POST;
    unset($data['action']);
    $sitelist_file = getcwd() . '/config/sitelist.php';
    $sitelist = include($sitelist_file);
    $sitenames = array_column($sitelist, 'sitename');
    if (in_array($data['sitename'], $sitenames)) {
        error('site already exists.', 405);
    }
    if (isset($data['domains'])) {
        if (is_string($data['domains'])) {
            $data['domains'] = [$data['domains']];
        }
    } else {
        $data['domains'] = [];
    }
    array_push($sitelist, $data);
    $date = date('Y-m-d H:i:s');
    $new_content = "<?php
/**
 * User: Jasmine2
 * Date: {$date}
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */
return ";

    $new_content .= var_export($sitelist, true);
    @copy($sitelist_file, getcwd() . '/config/.sitelist.bak');
    @file_put_contents($sitelist_file, $new_content . ';');
    success('site add success.');
}

function del()
{
    $data = $_POST;
    unset($data['action']);
    $sitelist_file = getcwd() . '/config/sitelist.php';
    $sitelist = include($sitelist_file);
    $sitenames = array_column($sitelist, 'sitename');
    if (!in_array($data['sitename'], $sitenames)) {
        error('site not exists.', 404);
    }
    foreach ($sitelist as $k => $item) {
        if ($data['sitename'] == $item['sitename']) {
            unset($sitelist[$k]);
        }
    }
    $date = date('Y-m-d H:i:s');
    $new_content = "<?php
/**
 * User: Jasmine2
 * Date: {$date}
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */
return ";

    $new_content .= var_export($sitelist, true);
    @copy($sitelist_file, getcwd() . '/config/.sitelist.bak');
    @file_put_contents($sitelist_file, $new_content . ';');
    success('site del success.');
}

function add_domain()
{
    $data = $_POST;
    unset($data['action']);
    $sitelist_file = getcwd() . '/config/sitelist.php';
    $sitelist = include($sitelist_file);
    $sitenames = array_column($sitelist, 'sitename');
    if (!in_array($data['sitename'], $sitenames)) {
        error('site not exists.', 404);
    }
    foreach ($sitelist as &$item) {
        if ($data['sitename'] == $item['sitename']) {
            if (is_array($data['domains'])) {
                $item['domains'] = array_merge($item['domains'], $data['domains']);
            } elseif (is_string($data['domains'])) {
                array_push($item['domains'], $data['domains']);
            }
            break;
        }
    }
    $date = date('Y-m-d H:i:s');
    $new_content = "<?php
/**
 * User: Jasmine2
 * Date: {$date}
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */
return ";

    $new_content .= var_export($sitelist, true);
    @copy($sitelist_file, getcwd() . '/config/.sitelist.bak');
    @file_put_contents($sitelist_file, $new_content . ';');
    success('domain add success.');
}

function rm_domain()
{
    $data = $_POST;
    unset($data['action']);
    $sitelist_file = getcwd() . '/config/sitelist.php';
    $sitelist = include($sitelist_file);
    $sitenames = array_column($sitelist, 'sitename');
    if (!in_array($data['sitename'], $sitenames)) {
        error('site not exists.', 404);
    }
    if (!is_array($data['domains'])) {
        error('domains must be a array.');
    }
    foreach ($sitelist as &$item) {
        if ($data['sitename'] == $item['sitename']) {
            $item['domains'] = array_diff($item['domains'], $data['domains']);
            break;
        }
    }
    $date = date('Y-m-d H:i:s');
    $new_content = "<?php
/**
 * User: Jasmine2
 * Date: {$date}
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */
return ";

    $new_content .= var_export($sitelist, true);
    @copy($sitelist_file, getcwd() . '/config/.sitelist.bak');
    @file_put_contents($sitelist_file, $new_content . ';');
    success('domain rm success.');
}