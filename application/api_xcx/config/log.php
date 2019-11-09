<?php
$api_log_path = env('runtime_path') . 'log' . DIRECTORY_SEPARATOR . 'xcx_api'. DIRECTORY_SEPARATOR;
return [
    'path' => $api_log_path,
    'type' => 'File',
    'file_size' => 1024 * 1024 * 10,
    'apart_level' => ['emergency', 'alert', 'error',],
];
