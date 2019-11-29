<?php


namespace app\xiaochengxu\controller;


use controller\BasicAdmin;
use service\DataService;
use think\Db;

class Message extends BasicAdmin
{

    public $table = "saas_xcx_message";

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
        return $this->_form('saas_xcx_message', 'form');
    }

    public function edit()
    {
        return $this->_form('saas_xcx_message', 'form');
    }

    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("删除成功!", '');
        }
        $this->error("删除失败, 请稍候再试!");
    }
}