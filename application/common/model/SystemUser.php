<?php
/**
 * User: Jasmine2
 * Date: 2018/7/3 15:05
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\common\model;


use think\Model;

/**
 * Class SystemUser
 * @package app\common\model
 * @author Jasmine2
 * @property SaasEmployee $employee
 */
class SystemUser extends Model
{
    protected $table = 'system_user';

    public function employee()
    {
        return $this->hasOne('saas_employee', 'userid', 'id');
    }
}