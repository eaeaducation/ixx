<?php


namespace app\api_xcx\controller;


use controller\BasicXcx;
use think\Db;
use think\facade\Log;

class Discover extends BasicXcx
{
    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $ban = Db::name('saas_content')->where('catid', '=', '89')->field('thumb')->select();
        $content = Db::name('saas_content')
            ->where('catid', '=', '88')
            ->field('thumb, title, link, id')
            ->select();
        $data = [
            'banner' => $ban,
            'content' => $content
        ];
        return $this->success('', $data);
    }


    public function detail()
    {
        $get = $this->request->get();
        $row = Db::name('saas_content')->where('id', '=', $get['id'])->find();
        $data = ['content' => base64_encode($row['content'])];
        return $this->success('', $data);
    }
}