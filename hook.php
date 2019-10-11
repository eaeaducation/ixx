<?php
/**
 * User: Jasmine2
 * Date: 2018/5/23 16:12
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 * git 自动部署的webhook
 */
$target = '/alidata/www/saas'; // 生产环境web目录
$token = 'saas';
$json = json_decode(file_get_contents('php://input'), true);
if (empty($_SERVER['HTTP_X_GITLAB_TOKEN']) || $_SERVER['HTTP_X_GITLAB_TOKEN'] !== $token) {
    exit('error request');
}
$repo = $json['repository']['name'];
$cmd = "cd $target && git pull origin master";
$out = system($cmd);