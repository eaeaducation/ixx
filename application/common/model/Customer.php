<?php
/**
 * User: Jasmine2
 * Date: 2018/6/21 17:54
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\common\model;


use think\Model;

/**
 * Class Customer
 * @package app\common\model
 * @author Jasmine2
 * @method Customer findOrFail($data = null) static
 * @method static where($data = null)
 * @property array $follows
 * @property string $name
 * @property string $mother_tel
 * @property string $father_tel
 * @property string $other_tel
 */
class Customer extends Model
{
    public $table = 'saas_customer';

    protected $auto = ['name_permalink', 'name_abbr'];

    protected function follows()
    {
        return $this->hasMany(CustomerFollow::class, 'customer_id', 'id')->order('created_at', 'desc');
    }

    public function setNamePermalinkAttr()
    {
        return convert_pinyin_permalink($this->getAttr('name'));
    }

    public function setNameAbbrAttr()
    {
        return convert_pinyin_abbr($this->getAttr('name'));
    }
}