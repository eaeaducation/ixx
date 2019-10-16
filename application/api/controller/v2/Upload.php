<?php


namespace app\api\controller\v2;


use app\api\controller\AppBasicApi;
use service\FileService;

class Upload extends AppBasicApi
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
        $data = [
            'filePath' => $filePath,
            'createDate' => time(),
        ];
        return $this->success('上传成功', api_encrypt($data));
    }
}