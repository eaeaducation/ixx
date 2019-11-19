<?php


namespace app\xiaochengxu\controller;


use controller\BasicAdmin;
use think\Db;

class Message extends BasicAdmin
{

    public function  index()
    {
        $this->title = '系统消息';
        $db = Db::name('saas_xcx_message')
            ->where('deleted', '=', 0)
            ->order('id desc');
        return parent::_list($db, true);
    }

    public function addMessage()
    {

    }
}