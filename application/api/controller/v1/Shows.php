<?php
/**
 * User: Jasmine2
 * Date: 2018/6/26 10:39
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\api\controller\v1;


use app\common\model\Customer;
use controller\BasicApi;
use jwt\JwtHelper;
use Naixiaoxin\ThinkWechat\Facade;
use think\facade\Cache;
use think\Db;
use zoujingli;
use think\facade\Log;

class Shows extends BasicApi
{
    /**
     * error code 说明.
     * <ul>
     *    <li>-41001: encodingAesKey 非法</li>
     *    <li>-41003: aes 解密失败</li>
     *    <li>-41004: 解密后得到的buffer非法</li>
     *    <li>-41005: base64加密失败</li>
     *    <li>-41016: base64解密失败</li>
     * </ul>
     */
    public static $OK = 0;
    public static $IllegalAesKey = -41001;
    public static $IllegalIv = -41002;
    public static $IllegalBuffer = -41003;
    public static $DecodeBase64Error = -41004;

    private $appid;
    private $sessionKey;

    /**
     * 用户注册
     */
    public function register()
    {
        $mobile = $this->request->request('mobile');
        $name = $this->request->request('name');
        $code = $this->request->request('code');
        $act_id = $this->request->request('act_id');
        $check = $this->checkMobile($mobile);
        if ($check == 'false') {
            return json(['success' => false, 'code' => 200, 'msg' => '该手机号已注册']);
        };
        $codeCache = Cache::get($mobile . '_code');
        if ($code == $codeCache) {
            $data = [
                'parent_tel' => $mobile,
                'parent_name' => $name,
                'created_at' => time(),
                'device_info' => $_SERVER['HTTP_USER_AGENT'],
                'activity_id' => $act_id,
            ];
            $res = DB::table('saas_customer')->insert($data);
            if ($res) {
                return json(['success' => true, 'code' => 200, 'msg' => '注册成功']);
            } else {
                return json(['success' => false, 'code' => 200, 'msg' => '注册失败']);
            }
        } elseif ($code != $codeCache && !empty($codeCache)) {
            return json(['success' => false, 'code' => 200, 'msg' => '验证码错误']);
        } elseif ($code != $codeCache && empty($codeCache)) {
            return json(['success' => false, 'code' => 200, 'msg' => '验证码已失效，请重新获取']);
        }
    }

    /**
     * 收集用户
     */
    public function collect()
    {
        $mobile = $this->request->request('mobile');//手机号
        $act_id = $this->request->request('actid');//活动id
        $wjid = $this->request->request('wjid');//问卷id
        $fid = $this->request->request('fid');//问卷参与者id
        $userid = $this->request->request('userid');//销售ID
        $name = $this->request->request('name');//监护人name
        $cname = $this->request->request('cname');//学员name
        $cid = $this->request->request('cid');//推广的客户id
        $relation = $this->request->request('relation');//关系
        $gender = $this->request->request('gender');//性别
        $isxcx = $this->request->request('xcx');//是否是小程序页面过来的
        $branch = $this->request->request('branch');//是否是小程序页面过来的
        $source = $this->request->request('source', 1);//是否是小程序页面过来的
        $check = $this->checkMobile($mobile);
        //参与多份不同问卷，客户表不做处理，直接更新问卷参与者表
        if ($check) {
            if (!empty($wjid) && !empty($fid)) {
                Db::name('saas_survey_person')
                    ->where('id', '=', $fid)
                    ->update(['cid' => $check['id']]);
            }
            $this->success('您已参与！', ['duplicate' => true, 'id' => $check['id'], 'userid' => $userid]);
        }
        if (!empty($act_id)) {
            $source = 1;
        } elseif (!empty($wjid)) {
            $source = 6;
        }
        if (!empty($isxcx)) {
            $source = 3;
        }
        $data = [
            'parent_tel' => $mobile,
            'created_at' => time(),
            'parent_name' => $name,
            'device_info' => $this->request->header('user-agent'),
            'activity_id' => $act_id,
            'userid' => $userid,
            'source' => $source,
            'recommend' => $cid,
            'name' => $cname,
            'relation' => $relation,
            'gender' => $gender,
            'wjid' => $wjid,
            'branch' => $branch
        ];
        if (!empty($userid)) {
            $data['branch'] = $this->getuserbranch($userid);
            $authorize = $this->getauthorize($userid);
            if ($authorize == '10') {
                $data['sales_id'] = $userid;
            } elseif ($authorize == '11') {
                $data['collect_id'] = $userid;
            }
        }
        $id = DB::table('saas_customer')->insertGetId($data);
        if ($id) {
            if (!empty($wjid)) {
                Db::name('saas_survey_person')
                    ->where('id', '=', $fid)
                    ->update(['cid' => $id]);
            }
            $this->success('参与成功', ['id' => $id, 'userid' => $userid]);
        } else {
            $this->error('参与失败');
        }
    }

    /**
     * @param $mobile
     * @return mixed
     * 手机检测是否注册
     */
    private function checkMobile($mobile)
    {
        $customer = DB::table('saas_customer')->where('parent_tel', $mobile)->find();
        if (!$customer) {
            return false;
        } else {
            return $customer;
        }
    }

    private function getauthorize($userid)
    {
        $authorize_row = Db::name("system_user")->where("id", "=", $userid)->field("authorize")->find();
        if ($authorize_row) {
            return $authorize_row['authorize'];
        } else {
            return false;
        }
    }

    private function getuserbranch($userid)
    {
        $user_info = Db::name("saas_employee")->where("userid", "=", $userid)->where("status", "<>", "3")->field("department")->find();
        if ($user_info) {
            return $user_info['department'];
        } else {
            return '';
        }
    }

    //搜集客户数据
    public function getcusinfo()
    {
        $sign = '400f5249e2dd8c7ed703c7c3ca686c38';
        $sign_key = $this->request->request('sign');
        $customer = [];
        if ($sign_key != $sign) {
            $this->error("error request !");
        } else {
            $content = $this->request->request();
            $encryptedData = $content['encryptedData'];
            $iv = $content['vi'];
            $key = json_decode($content['session_key'], true);
            $this->sessionKey = $key['session_key'];
            $this->appid = 'wx2697b0c1211d481f';
            $code = $this->decryptData($encryptedData, $iv, $data);
            if ($code == 0) {
                $customer['parent_tel'] = $data->purePhoneNumber;
                $check = $this->checkMobile($customer['parent_tel']);
                if (!$check) {
                    $customer['source'] = 3;
                    $customer['created_at'] = time();
                    Db::name('saas_customer')->insert($customer);
                }
                $this->success('', $customer['parent_tel']);
            } else {
                $this->error('数据解析失败！');
            }
        }
    }

    //搜集客户数据
    public function addCustomer()
    {
        $sign = '400f5249e2dd8c7ed703c7c3ca686c38';
        $sign_key = $this->request->request('sign');
        $customer = [];
        if ($sign_key != $sign) {
            $this->error("error request !");
        } else {
            $content = $this->request->request();
            $encryptedData = $content['encryptedData'];
            $iv = $content['vi'];
            $key = json_decode($content['session_key'], true);
            $this->sessionKey = $key['session_key'];
            $this->appid = 'wx2697b0c1211d481f';
            $code = $this->decryptData($encryptedData, $iv, $data);
            if ($code == 0) {
                $customer['parent_tel'] = $data->purePhoneNumber;
                $check = $this->checkMobile($customer['parent_tel']);
                if ($content['tjropenid']) {
                    $introduce_id = Db::name('saas_customer')->field('id')->where('xcxopenid', '=', $content['tjropenid'])->find();
                    if (!$check) {
                        $customer['source'] = 3;
                        $customer['created_at'] = time();
                        $customer['xcxopenid'] = $content['openid'];
                        $cid = Db::name('saas_customer')->insertGetId($customer);
                    } else {
                        $row['xcxopenid'] = $content['openid'];
                        Db::name('saas_customer')->where('parent_tel', '=', $customer['parent_tel'])->update($row);
                        $id = Db::name('saas_customer')->where('parent_tel', '=', $customer['parent_tel'])->field('id')->find();
                        $cid = $id['id'];
                    }
                    $data = [];
                    $data['customer_id'] = $cid;
                    $data['introducer_id'] = $introduce_id['id'];
                    $data['created_at'] = time();
                    $data['actid'] = 12;
                    if ($data['customer_id'] != $data['introducer_id']) {
                        $res = Db::name('saas_customer_introducer')
                            ->where('customer_id', '=', $cid)
                            ->where('actid', '=', 12)
                            ->select();
                        if (!$res) {
                            Db::name('saas_customer_introducer')->insert($data);
                        }
                    }
                    $this->success('', $customer['parent_tel']);
                } else {
                    if (!$check) {
                        $customer['source'] = 3;
                        $customer['created_at'] = time();
                        $customer['xcxopenid'] = $content['openid'];
                        $cid = Db::name('saas_customer')->insertGetId($customer);
                        if (!$cid){
                            $this->error('客户保存失败');
                        }
                    } else {
                        $row['xcxopenid'] = $content['openid'];
                        Db::name('saas_customer')->where('parent_tel', '=', $customer['parent_tel'])->update($row);
                    }
                    $this->success('', $customer['parent_tel']);
                }
            } else {
                $this->error('数据解析失败！');
            }
        }
    }


    //小程序订单_下单后_异步通知
    public function xcx_order_after()
    {
        $app = Facade::payment('wxxcx');
        $response = $app->handlePaidNotify(function ($message, $fail) {
            Log::error($message);
            if (isset($message['result_code']) && $message['result_code'] == 'SUCCESS') {
                $has_order = Db::name("wechat_order")->where("orderno", "=", $message['out_trade_no'])->find();
                if ($has_order) {
                    $param['status'] = 1;
                    $param['cus_num'] = 1;
                    Db::name("wechat_order")->where("orderno", "=", $message['out_trade_no'])->update($param);
                } else {
                    $param['orderno'] = $message['out_trade_no'];
                    $param['title'] = $message['attach'];
                    $param['trade_type'] = 'JSAPI';
                    $param['type'] = 1;
                    $param['price'] = $message['total_fee'];
                    $param['body'] = $message['attach'];
                    $param['status'] = 1;
                    $param['isxcx'] = 1;
                    $param['created_at'] = time();
                    $param['openid'] = $message['openid'];
                    $param['cus_num'] = 1;
                    Db::name("wechat_order")->insert($param);
                }
                return true;
            } else {
                return false;
            }
        });
        $response->send();
    }

    public function decryptData($encryptedData, $iv, &$data)
    {
        if (strlen($this->sessionKey) != 24) {
            return Shows::$IllegalAesKey;
        }
        $aesKey = base64_decode($this->sessionKey);

        if (strlen($iv) != 24) {
            return Shows::$IllegalIv;
        }
        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj = json_decode($result);

        if ($dataObj == NULL) {
            return Shows::$IllegalBuffer;
        }
        if ($dataObj->watermark->appid != $this->appid) {
            return Shows::$IllegalBuffer;
        }
        $data = $dataObj;
        return Shows::$OK;
    }

    private function xmlToarray($xml, $isfile = false)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        if ($isfile) {
            if (!file_exists($xml)) return false;
            $xmlstr = file_get_contents($xml);
        } else {
            $xmlstr = $xml;
        }
        $result = json_decode(json_encode(simplexml_load_string($xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }

    public function getcusbyopenid()
    {
        $openid = $this->request->post('openid');
        if (isset($openid)) {
            $res = Db::name('saas_customer')
                ->field('*')
                ->where('wxopenid', '=', $openid)
                ->find();
            if ($res) {
                return $res;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getcusbyxcxopenid()
    {
        $openid = $this->request->post('openid');
        if (isset($openid)) {
            $res = Db::name('saas_customer')
                ->field('*')
                ->where('xcxopenid', '=', $openid)
                ->find();
            if ($res) {
                return $res;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function is_full()
    {
        $post = $this->request->post();
        $order_num = Db::name('wechat_order')
            ->where('cus_num', '=', 1)
            ->where('title', '=', $post['title'])
            ->select();
        if ($post['title'] == '双十二素质课') {
            if (count($order_num) >= 2) {
                return 0;
            } else {
                return 1;
            }
        } else {
            if (count($order_num) >= 5) {
                return 0;
            } else {
                return 1;
            }
        }
    }
}