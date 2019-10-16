<?php


namespace app\api\controller\v2;


use app\api\controller\AppBasicApi;

class Config extends AppBasicApi
{
    /**
     * app配置接口
     */
    public function app_conf()
    {
        $data = [
            //数据传输加密秘钥
            'api_key' => 'AaOb0iLGzk9O2lRq',
            'api_iv' => '1t31keXoH8EIvYCX'
        ];
        return $this->success('', api_encrypt($data));
    }
}