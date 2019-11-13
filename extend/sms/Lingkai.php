<?php


namespace sms;


use GuzzleHttp\Client;
use service\HttpService;
use think\Db;
use think\facade\Log;

class Lingkai implements SmsInterface
{
    /**
     * @var 接口请求地址
     */
    private $request_url;

    /**
     * @var 账号
     */
    private $corp_id;

    /**
     * @var 密码
     */
    private $pwd;

    /**
     * @var 扩展号
     */
    private $cell;

    /**
     * @var 短信签名
     */
    private $sign;

    private $type;



    public function __construct($type = 1)
    {
        $this->request_url = sysconf('sms_code_gateway');;//'https://sdk2.028lk.com/sdk2/BatchSend2.aspx';
        $this->corp_id = sysconf('sms_code_account');//'CQJS009895';
        $this->pwd = sysconf('sms_code_password');//'sx8866';
        $this->cell = '';
        $this->sign = sysconf('sms_code_sign');
        $this->type = $type;
    }

    public function send($mobiles, $content, $sign = '')
    {
        $userid = session('user.id');
        session('user.id') != null || $userid = 0;
        if (is_array($mobiles)) {
            $mobiles = join(',', $mobiles);
        }
        $data = [
            'CorpID' => $this->corp_id,
            'Pwd' => $this->pwd,
            'Mobile' => $mobiles,
            'Content' => rawurlencode($content),
            'Cell' => $this->cell,
            'SendTime' => ''
        ];
        $res = HttpService::get($this->request_url, $data);
        Log::error($res);
        $count = explode(',',$mobiles);
        if($count>1){
            $mobile = explode(',',$mobiles);
            for($i=0;$i<count($mobile);$i++){
                Db::name('system_sms')->insert([
                    'ip' => request()->ip(),
                    'mobiles' => $mobile[$i],
                    'content' => "【{$this->sign}】{$content}",
                    'sign' => $this->sign,
                    'userid' => $userid,
                    'type' => $this->type,
                    'res' => $res > 0 ? 1 : 0,
                    'created_at' => time()
                ]);
            }
        }else{
            Db::name('system_sms')->insert([
                'ip' => request()->ip(),
                'mobiles' => $mobiles,
                'content' => "【{$this->sign}】{$content}",
                'sign' => $this->sign,
                'userid' => $userid,
                'type' => $this->type,
                'res' => $res > 0 ? 1 : 0,
                'created_at' => time()
            ]);
        }
        if ($res > 0) {
            return true;
        }
        return false;
    }
}