<?php


namespace app\api\controller\v2;


use app\api\controller\AppBasicApi;
use think\Db;

class Content extends AppBasicApi
{
    /**
     * 用户注册协议
     */
    public function reg_agreement()
    {
        $reg_text = Db::name('saas_content')
            ->where('catid', '=', 76)
            ->order('id asc')
            ->value('content');
        return view('', ['content' => $reg_text]);
    }
}