<?php


namespace app\company\controller;
header('Access-Control-Allow-Origin:*');//允许所有来源访问

header('Access-Control-Allow-Method:POST,GET');//允许访问的方式

use app\admin\controller\Log;
use controller\BasicAdmin;
use GuzzleHttp\Psr7\UploadedFile;
use service\FileService;
use think\Db;
use think\Request;

class Recruit extends BasicAdmin
{
    /**
     *
     */
    public function index()
    {
        return $this->fetch('');
    }

    /**
     * 上传word
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function upload()
    {
//        $request = new Request();
//        \think\facade\Log::error($request->request());
//        return json_encode($request->request());
//        $document = substr($request->request('document'), 22);
//        $ext = 'doc';
//        $names = md5(time());
//        $filename = date('ymd/') . "{$names}.{$ext}";
//        $fileContent = base64_decode($document);
//        \think\facade\Log::error($fileContent);
        $file = $this->request->file('file');
        \think\facade\Log::error($file);die;
//        $ext = strtolower(pathinfo($file->getInfo('name'), 4));
//        $names = md5(time());
//        $filename = date('ymd/') . "{$names}.{$ext}";
//        $fileContent = '';
//        while (!$file->eof()) {
//            $fileContent .= $file->current();
//            $file->next();
//        }
//        $res = FileService::save($filename, $fileContent);
//        $filePath = $res['url'];
//        return json([
//            'filePath' => $filePath,
//            'createDate' => time(),
//        ]);
//        if($_FILES["file"]["type"]=="application/octet-stream" && $_FILES["file"]["size"]<1048576)
//        {
//            //造存储路径
//            $filename=date("YmdHis").$_FILES["file"]["name"];
//            //转编码格式
//            $filename = iconv("utf-8","gb2312",$filename);
//
//            //文件是否存在
//            if(!file_exists($filename))
//            {
//                //移动文件保存
//                move_uploaded_file($_FILES["file"]["tmp_name"],$filename);
//            }
//        }
    }
    public function form()
    {
        if ($this->request->isPost()) {
            $post = $this->request->request();
            $file = $this->request->file('file');
            $user = Db::name('company_recruit')->where('mobile', $post['mobile'])->find();
            if ($user) {
                return json([
                    'code' => 0,
                    'msg' => '您已提交过资料了，请勿重复提交',
                    'data' => [],
                ]);
            }
            //上传word文件
            $ext = strtolower(pathinfo($file->getInfo('name'), 4));
            if (!in_array($ext, ['doc', 'docx', 'pdf'])) {
                return json([
                    'code' => 0,
                    'msg' => '请选择上传doc,pdf,docx格式的文档',
                    'data' => [],
                ]);
            }
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
                'name' => $post['username'],
                'mobile' => $post['mobile'],
                'gender' => $post['sex'],
                'school' => $post['graduation'],
                'major' => $post['major'],
                'education' => $post['branch'],
                'resume' => $filePath,
                'created_time' => time()
            ];
            $res = Db::name('company_recruit')->insert($data);
            if ($res) {
                return json([
                    'code' => 1,
                    'msg' => '资料提交成功',
                    'data' => [],
                ]);
            } else {
                return json([
                    'code' => 1,
                    'msg' => '资料提交失败，请稍后重试',
                    'data' => [],
                ]);
            }
        } else {
            return $this->fetch('form');
        }
    }
}