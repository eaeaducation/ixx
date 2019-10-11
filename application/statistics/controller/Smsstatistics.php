<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/24
 * Time: 10:57
 * @author mei
 */

namespace app\statistics\controller;

use controller\BasicAdmin;
use think\Db;
use service\DataService;
use think\facade\Cache;


class Smsstatistics extends BasicAdmin
{
    /**
     * 默认数据模型
     * @var string
     */
    public $table = 'system_sms';

    /**
     * 短信管理列表
     * @return array|string
     */
    public function index()
    {
        $this->title = '短信管理';
        $get = $this->request->get();
        $time_range = !empty($get['time_range']) ? $get['time_range'] : '';
        $type = !empty($get['type']) ? $get['type'] : '';
        $res = !empty($get['res']) ? $get['res'] : '';
        $mobile = !empty($get['mobile']) ? $get['mobile'] : '';
        $db = Db::name($this->table)
            ->order('id desc');
        if (!empty($mobile)) $db->where('mobiles', '=', $mobile);
        if (!empty($type)) $db->where('type', '=', $type);
        if (!empty($res) && $res == 1) {
            $db->where('res', '=', $res);
        } elseif (!empty($res) && $res != 1) {
            $db->where('res', '<>', '1');
        }
        if (!empty($time_range)) {
            list($_start, $_end) = explode(' ~ ', $time_range);
            $start = strtotime($_start . " 00:00:00");
            $end = strtotime($_end . " 23:59:59");
            $db->whereBetween('created_at', [$start, $end]);
        }
        return parent::_list($db, true);
    }

    public function daily()
    {
        $this->title = '消费日结';
        $get = $this->request->get();
        $time_range = !empty($get['time_range']) ? $get['time_range'] : '';
        $res = !empty($get['res']) ? $get['res'] : '1';
        $db = Db::name($this->table)
            ->field('FROM_UNIXTIME(created_at,"%Y-%m-%d") as days,count(id) as counts')
            ->where('res', '=', '1')
            ->group('FROM_UNIXTIME(created_at,"%Y-%m-%d")');
        if (!empty($time_range)) {
            list($_start, $_end) = explode(' ~ ', $time_range);
            $start = strtotime($_start . " 00:00:00");
            $end = strtotime($_end . " 23:59:59");
            $db->whereBetween('created_at', [$start, $end]);
        }
        if ($res == 1) {
            $db->where('res', '=', '1');
        } else {
            $db->where('res', '<>', '1');
        }
        return parent::_list($db, true);
    }

}