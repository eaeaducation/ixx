<?php


namespace app\common\model;


use think\Model;

class SaasClass extends Model
{
    public $table = 'saas_class';


    protected static function init()
    {
        SaasClass::event('afterWrite', function (SaasClass $class) {
            $changeData = $class->getChangedData();
            if (isset($changeData['status']) && $changeData['status'] == 2) {
                $class->audit_status = -95;
                $class->save();//申请，待审核班级
            }
        });
    }

}