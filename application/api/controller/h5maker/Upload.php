<?php
/**
 * User: Jasmine2
 * Date: 2018/8/4 14:38
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\api\controller\h5maker;


use controller\BasicApi;
use service\FileService;
use think\Db;

class Upload extends BasicApi
{
    public function upload()
    {
        $file = $this->request->file('image');
        $ext = strtolower(pathinfo($file->getInfo('name'), 4));
        $names = md5(time());
        $filename = date('ymd/') . "{$names}.{$ext}";
        $fileContent = '';
        while (!$file->eof()) {
            $fileContent .= $file->current();
            $file->next();
        }
        $res = FileService::save($filename, $fileContent);
        $filePath = $res['url'];
        $fileType = $this->request->post('fileType');
        $id = Db::name('theme_resource')->insertGetId([
            'url' => $filePath,
            'fileType' => $fileType,
            'width' => $this->request->post('width', 0),
            'height' => $this->request->post('height', 0),
        ]);
        return json([
            'filePath' => $filePath,
            'createDate' => time(),
            '__v' => 0,
            '_id' => $id
        ]);
    }

    public function index($id)
    {
        $type = $this->request->get('fileType');
        $data = Db::name('theme_resource')->where('fileType', '=', $type)->field('url as filePath,width,height')->select();
        return json($data);
    }
}