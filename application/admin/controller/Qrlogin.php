<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/16
 * Time: 18:54
 */

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\App;
use think\Db;
use think\facade\Cache;
use QRCode;
use app\common\model\SystemUser;
use service\LogService;

class Qrlogin extends BasicAdmin
{
    /*
     * md5(aixuexue,16) = c9b2533069ebfa67
     * */
    public $table = 'system_user';
    private $qrkey = 'c9b2533069ebfa67';


    public function getLoginQr()
    {
        return $this->fetch('qrcode');
    }

    public function index()
    {
        $sign = $this->request->request('qrcode');
        echo $sign;
    }

    public function createQrCode()
    {
        $lsin = $this->Login_sign();
        $lsin_url = base64_encode($lsin);
        $url = $this->request->domain() . url('admin/qrlogin/index') . '?qrcode=' . $lsin_url;
        $data = [
            'msg' => 1,
            'url' => $url,
            'lsign' => $lsin,
            'lsin_url' => $lsin_url,
            'data' => QrCode::createQRCodeString($url, 300)
        ];
        return json($data);
    }

    public function check_lsign_cache()
    {
        $lsign = $this->request->post('lsign');
        if (Cache::has($lsign)) {
            $res = Cache::get($lsign);
            if($res['status'] == '1'){
                $this->scan_login($res['phone']);
                $this->success('yes');
            }else{
                $this->error('no');
            }
        }else{
            $this->error('error');
        }
    }

    private function Login_sign()
    {
        $date_salt = date('YmdHis');
        $random = rand(100, 999);
        return 'scan' . $date_salt . $random;
    }

    private function scan_login($username)
    {
        $user = SystemUser::where('phone', '=', $username)->where('is_deleted', '=', 0)->find();
        // 更新登录信息
        $data = ['login_at' => Db::raw('now()'), 'login_num' => Db::raw('login_num+1')];
        $user->save($data);
        session('user', $user);
        !empty($user['authorize']) && NodeService::applyAuthNode();
        LogService::write('系统管理', '用户扫码登录系统成功');
    }

}