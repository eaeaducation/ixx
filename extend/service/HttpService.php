<?php
/**
 * User: Jasmine2
 * Date: 2017-6-27 16:55
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace service;

use CURLFile;
use GuzzleHttp\Client;

/**
 * HTTP请求服务
 * Class HttpService
 * @package service
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/22 15:32
 */
class HttpService
{
    /**
     * HTTP GET 请求
     * @param string $url 请求的URL地址
     * @param array $data GET参数
     * @param int $second 设置超时时间（默认30秒）
     * @param array $header 请求Header信息
     * @return bool|string
     */
    public static function get($url, $data = [], $second = 30, $header = [])
    {
        if (!empty($data)) {
            $url .= (stripos($url, '?') === false ? '?' : '&');
            $url .= (is_array($data) ? http_build_query($data) : $data);
        }
        $client = new Client();
        $response = $client->get($url, [
            'headers' => $header,
            'timeout' => $second
        ]);
        return $response->getStatusCode() == 200 ? $response->getBody()->getContents() : false;
    }

    /**
     * POST 请求（支持文件上传）
     * @param string $url HTTP请求URL地址
     * @param array|string $data POST提交的数据
     * @param int $second 请求超时时间
     * @param array $header 请求Header信息
     * @return bool|string
     */
    public static function post($url, $data = [], $second = 30, $header = [])
    {
        self::_setUploadFile($data);
        $client = new Client();
        $response = $client->post($url, [
            'headers' => $header,
            'timeout' => $second,
            'form_params' => $data,
        ]);
        return $response->getStatusCode() == 200 ? $response->getBody()->getContents() : false;
    }

    public static function json($url, $data = [], $second = 30, $header = [])
    {
        $client = new Client();
        $response = $client->post($url, [
            'headers' => $header,
            'timeout' => $second,
            'json' => $data,
        ]);
        return $response->getStatusCode() == 200 ? $response->getBody()->getContents() : false;
    }

    public static function raw($url, $data = '', $second = 30, $header = [])
    {
        $client = new Client();
        $response = $client->post($url, [
            'headers' => $header,
            'timeout' => $second,
            'body' => $data
        ]);
        return $response->getStatusCode() == 200 ? $response->getBody()->getContents() : false;
    }

    /**
     * 设置POST文件上传兼容
     * @param array $data
     * @return string
     */
    private static function _setUploadFile(&$data)
    {
        if (!is_array($data)) {
            return null;
        }
        foreach ($data as &$value) {
            if (!(is_string($value) && strlen($value) > 0 && $value[0] === '@')) {
                continue;
            }
            $filename = realpath(trim($value, '@'));
            $filemime = FileService::getFileMine(strtolower(pathinfo($filename, PATHINFO_EXTENSION)));
            $value = class_exists('CURLFile', false) ? new CURLFile($filename, $filemime) : "{$value};type={$filemime}";
        }
    }
}
