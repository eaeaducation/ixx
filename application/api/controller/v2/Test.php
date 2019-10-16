<?php


namespace app\api\controller\v2;


use app\api\controller\AppBasicApi;

class Test extends AppBasicApi
{
    public function test()
    {
        $data = [
            'mobile' => '13022964973',
            'password' => '123456',
            'code' => '123456'
        ];
        dump(api_encrypt($data));
    }

}