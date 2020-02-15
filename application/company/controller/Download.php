<?php


namespace app\company\controller;


use controller\BasicAdmin;

class Download extends BasicAdmin
{
    public function ccClass()
    {
        return $this->fetch('ccclass');
    }
}