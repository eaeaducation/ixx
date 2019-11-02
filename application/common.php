<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

use service\DataService;
use service\NodeService;
use think\Db;
use sms\Zhishen;
use think\facade\Cache;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * 打印输出数据到文件
 * @param mixed $data 输出的数据
 * @param bool $force 强制替换
 * @param string|null $file
 */
function p($data, $force = false, $file = null)
{
    is_null($file) && $file = env('runtime_path') . date('Ymd') . '.txt';
    $str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . PHP_EOL;
    $force ? file_put_contents($file, $str) : file_put_contents($file, $str, FILE_APPEND);
}

/**
 * RBAC节点权限验证
 * @param string $node
 * @return bool
 */
function auth($node)
{
    return NodeService::checkAuthNode($node);
}

/**
 * 设备或配置系统参数
 * @param string $name 参数名称
 * @param bool $value 默认是null为获取值，否则为更新
 * @return string|bool
 * @throws \think\Exception
 * @throws \think\exception\PDOException
 */
function sysconf($name, $value = null)
{
    static $config = [];
    if ($value !== null) {
        list($config, $data) = [[], ['name' => $name, 'value' => $value]];
        return DataService::save('SystemConfig', $data, 'name');
    }
    if (empty($config)) {
        $config = Db::name('SystemConfig')->column('name,value');
    }
    return isset($config[$name]) ? $config[$name] : '';
}

/**
 * 日期格式标准输出
 * @param string $datetime 输入日期
 * @param string $format 输出格式
 * @return false|string
 */
function format_datetime($datetime, $format = 'Y-m-d H:i:s')
{
    return date($format, strtotime($datetime));
}

/**
 * UTF8字符串加密
 * @param string $string
 * @return string
 */
function encode($string)
{
    list($chars, $length) = ['', strlen($string = iconv('utf-8', 'gbk', $string))];
    for ($i = 0; $i < $length; $i++) {
        $chars .= str_pad(base_convert(ord($string[$i]), 10, 36), 2, 0, 0);
    }
    return $chars;
}

/**
 * UTF8字符串解密
 * @param string $string
 * @return string
 */
function decode($string)
{
    $chars = '';
    foreach (str_split($string, 2) as $char) {
        $chars .= chr(intval(base_convert($char, 36, 10)));
    }
    return iconv('gbk', 'utf-8', $chars);
}

/**
 * 下载远程文件到本地
 * @param string $url 远程图片地址
 * @return string
 */
function local_image($url)
{
    return \service\FileService::download($url)['url'];
}

/**
 * @param $mobiles
 * @param $content
 * @param string $sign
 * @param int $type
 * @return mixed
 * @throws \think\Exception
 * @throws \think\exception\PDOException
 * @author Jasmine2
 * 发送短信，验证码类型
 */
function send_sms($mobiles, $content, $sign = '', $type = 1)
{
    $zhishen = new Zhishen($type);
    return $zhishen->send($mobiles, $content, $sign);
}

function send_all_sms($mobiles, $content, $sign = '', $type = 1)
{
    $zhishen = new Zhishen($type);
    if (is_array($mobiles)) {
        $count = count($mobiles);
        if ($count > 2) {
            $mobile_more = array_chunk($mobiles, 2);
            foreach ($mobile_more as $mobiles) {
                $sms_res = $zhishen->send($mobiles, $content, $sign);
            }
        }
        return $sms_res;
    } else {
        return $zhishen->send($mobiles, $content, $sign);
    }
}

/**
 * @param $mobiles
 * @param $content
 * @param string $sign
 * @return mixed
 * @throws \think\Exception
 * @throws \think\exception\PDOException
 * @author Jasmine2
 * 发送短信，营销类型
 */
function send_sms_other($mobiles, $content, $sign = '')
{
    return send_sms($mobiles, $content, $sign, 3);
}

function send_all_sms_other($mobiles, $content, $sign = '')
{
    return send_all_sms($mobiles, $content, $sign, 3);
}

function alert($msg)
{
    // todo 系统级别错误处理
}

function getFastOptions($auth_id, $returnArray = false)
{
    $list = Db::name('system_fast_word')->where('authorize_id', '=', $auth_id)->select();
    if ($returnArray) {
        return $list;
    }
    $str = '<option value="">--请选择--</option>';
    foreach ($list as $k => $item) {
        $str .= '<option value="' . $item['content'] . '">' . $item['content'] . '</option>';
    }
    return $str;
}


/**
 * @param $ip
 * @param $range
 * @return bool
 * @author Jasmine2
 * 检测$IP是否在$range中
 */
function checkIp($ip, $range)
{
    if (!strripos($range, '/')) {
        return ip2long($ip) === ip2long($range);
    }
    list ($subnet, $bits) = explode('/', $range);
    $ip = ip2long($ip);
    $subnet = ip2long($subnet);
    $mask = -1 << (32 - $bits);
    $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
    return ($ip & $mask) == $subnet;
}

/**
 * @return bool
 * @throws \think\Exception
 * @throws \think\exception\PDOException
 * @author Jasmine2
 * IP过滤
 */
function checkIpFromConfig()
{
    $list = sysconf('ipwhitelist');
    if ($list == '') {
        return true;
    }
    $list = explode(',', $list);
    $checked = false;
    foreach ($list as $item) {
        if (checkIp(request()->ip(), $item)) {
            $checked = true;
            break;
        }
    }
    return $checked;
}

/**
 * @param $user
 * @return bool
 * @throws \think\Exception
 * @throws \think\exception\PDOException
 * @author Jasmine2
 * 例外会员组
 */
function except_group($user)
{
    $list = sysconf('except_group');
    if ($list == '') {
        return false;
    }
    $list = explode(',', $list);
    if (in_array($user['authorize'], $list)) {
        return true;
    }
    return false;
}

/**
 * @param $mobile
 * @return string
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 * @author Jasmine2
 * 手机号码归属地
 */
function get_mobile_region($mobile)
{
    $mobile = substr($mobile, 0, 7);
    if (Cache::has('mobile_list_' . $mobile)) {
        $data = Cache::get('mobile_list_' . $mobile);
    } else {
        $data = \app\common\share\MobileRegion::where(['mobile' => $mobile])->find();
        if ($data) {
            Cache::set('mobile_list_' . $mobile, $data);
        }
    }
    if (count($data) > 0) {
        if ($data['corp'] == 1) {
            $corp = '中国移动';
        } elseif ($data['corp'] == 2) {
            $corp = '中国联通';
        } elseif ($data['corp'] == 3) {
            $corp = '中国电信';
        } else {
            $corp = $data['corp'];
        }
        return sprintf("<span class='hidden-md'>%s|%s%s</span>", $corp, $data['province'], $data['city']);
    }
    return '<a data-tips-text="点击更新" data-update="' . $mobile . '" data-field="update" 
    data-action="' . url('@admin/tools/updateMobile') . '" href="javascript:void(0)"><i class="fa fa-refresh"></i></a>';
}

/**
 * @param $top_category
 * @return array|mixed
 * @author Jasmine2
 * 按照一级类目获取二级类目列表
 */
function get_category($top_category)
{
    if (Cache::has('category_' . $top_category)) {
        $categories = Cache::get('category_' . $top_category);
    } else {
        $categories = Db::name('saas_category')->where('top_id', '=', $top_category)
            ->where('status', '=', 1)
            ->column('title', 'key');
        Cache::set('category_' . $top_category, $categories, 2592000);
    }
    return $categories;
}

function html_select(array $data, $name, $selected = '', $class = '', array $options = [], $hidden_first = true)
{
    $options_html = '';
    foreach ($options as $k => $v) {
        $options_html .= " " . $k . '="' . $v . '"';
    }
    $html = '<select name="' . $name . '" class="' . $class . '"' . $options_html . '>';
    $hidden_first || $html .= '<option value=\'\'> - 请选择 - </option>';
    foreach ($data as $k => $v) {
        if (is_string($v)) {
            if ($selected == $k) {
                $html .= "<option value='{$k}' selected>{$v}</option>";
            } else {
                $html .= "<option value='{$k}'>{$v}</option>";
            }
        } else {
            if ($selected == $k) {
                $html .= "<option value='{$k}' selected>{$v['name']} {$v['english_name']}</option>";
            } else {
                $html .= "<option value='{$k}'>{$v['name']} {$v['english_name']}</option>";
            }
        }
    }
    return $html .= "</select>";
}

function html_radio(array $data, $name, $selected = '', $class = '', array $options = [])
{
    $html = '';
    $options_html = '';
    foreach ($options as $k => $v) {
        $options_html .= " " . $k . '="' . $v . '"';
    }
    foreach ($data as $k => $v) {
        if (is_string($v)) {
            if ($selected == $k) {
                $html .= "<input type='radio' name='{$name}' class='{$class}' value='{$k}' checked title='{$v}' style=\"display: none !important;\" {$options_html}/>";
            } else {
                $html .= "<input type='radio' name='{$name}' class='{$class}' value='{$k}' title='{$v}' style=\"display: none !important;\" {$options_html}/>";
            }
        }
    }
    return $html;
}

function html_checkbox(array $data, $name, $selected = '', $class = '', array $options = [])
{
    $selected = explode(',', $selected);
    $html = '';
    $options_html = '';
    foreach ($options as $k => $v) {
        $options_html .= " " . $k . '="' . $v . '"';
    }
    foreach ($data as $k => $v) {
        if (is_string($v)) {

            if (in_array($k, $selected)) {
                $html .= "<input type='checkbox' name='{$name}[]' class='{$class}' value='{$k}' checked title='{$v}' style=\"display: none !important;\"{$options_html}/>";
            } else {
                $html .= "<input type='checkbox' name='{$name}[]' class='{$class}' value='{$k}' title='{$v}' style=\"display: none !important;\"{$options_html}/>";
            }
        }
    }
    return $html;
}

/**
 * @param $top_category
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 * @author Jasmine2
 * 构造二级类目 html select 元素
 */
function get_category_select($top_category, $name, $selected = '', $class = '', $options = [], $hidden_first = false)
{
    $categories = get_category($top_category);
    return html_select($categories, $name, $selected, $class, $options, $hidden_first);
}

/**
 * @param $top_category
 * @param $name
 * @param string $selected
 * @param string $class
 * @return string
 * @author Jasmine2
 * 构造二级类目 html radio 元素
 */
function get_category_radio($top_category, $name, $selected = '', $class = '', $options = [])
{
    $categories = get_category($top_category);
    return html_radio($categories, $name, $selected, $class, $options);
}

/**
 * @param $key
 * @param $top_category
 * @return mixed|string
 * @author Jasmine2
 * 列表显示时或需要时 反转 key 为对应的名称
 */
function convert_category($key, $top_category)
{
    $categories = get_category($top_category);
    if (isset($categories[$key])) {
        return $categories[$key];
    }
    return '';
}

function format_date($timestamp, $format = 'Y-m-d')
{
    if ($timestamp <= 0) {
        return '';
    }
    return date($format, $timestamp);
}

function format_time($timestamp, $format = 'Y-m-d H:i:s')
{
    if ($timestamp <= 0) {
        return '';
    }
    return date($format, $timestamp);
}

/**
 * 格式化价格
 * @param $price
 * @return string
 */
function format_money($price)
{
    return number_format($price, 2);
}

/**
 * @return array|mixed
 * @author Jasmine2
 * 获取所有校区
 */
function get_branches()
{
    if (Cache::has('branches')) {
        $branches = Cache::get('branches');
    } else {
        $branches = Db::name('saas_school_branch')
            ->column('title', 'id');
        Cache::set('branches', $branches, 2592000);
    }
    return $branches;
}

/**
 * 根据校区名字获取校区id
 */
function get_branches_name($branches_name = '')
{
    if (empty($branches_name)) {
        return "";
    }
    if (Cache::has('branches' . $branches_name)) {
        $branches_id = Cache::get('branches' . $branches_name);
    } else {
        $branches = Db::name('saas_school_branch')
            ->where('title', $branches_name)
            ->find();
        if (empty($branches)) {
            $branches_id = '';
        } else {
            $branches_id = $branches['id'];
        }
        Cache::set('branches' . $branches_name, $branches_id, 2592000);
    }
    return $branches_id;
}

/**
 * 校区下拉列表
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 * @author Jasmine2
 */
function get_branches_select($name, $selected = '', $class = '', $options = [], $hidden_first = true)
{
    $branches = get_branches();
    return html_select($branches, $name, $selected, $class, $options, $hidden_first);
}

/**
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 * 教材下拉列表
 */
function get_textbook_select($name, $selected = '', $class = '', $options = [], $hidden_first = true)
{
    $textbook = get_textbook();
    return html_select($textbook, $name, $selected, $class, $options, $hidden_first);
}

/**
 * @return array|mixed
 * 获取所有教材
 */
function get_textbook()
{
    if (Cache::has('textbook')) {
        $textbook = Cache::get('textbook');
    } else {
        $textbook = Db::name('saas_textbook')
            ->column('title', 'id');
        Cache::set('textbook', $textbook, 2592000);
    }
    return $textbook;
}

/**
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 * 课程下拉列表
 */
function get_courses_select($name, $selected = '', $class = '', $options = [], $hidden_first = true)
{
    $courses = get_courses();
    return html_select($courses, $name, $selected, $class, $options, $hidden_first);
}

/**
 * @return array|mixed
 * 获取全部课程
 */
function get_courses()
{
    if (session('user.id') == 10000) {
        if (Cache::has('courses')) {
            $courses = Cache::get('courses');
        } else {
            $courses = Db::name('saas_courses')->where('status', '<>', 3)->column('title', 'id');
            Cache::set('courses', $courses, 2592000);
        }
    } else {
        $branch = session('user.employee.department');
        if (Cache::has('courses_' . $branch)) {
            $courses = Cache::get('courses_' . $branch);
        } else {
            $courses = Db::name('saas_courses')->where('status', '<>', 3)->where('branch', '=', $branch)->column('title', 'id');
            Cache::set('courses_' . $branch, $courses, 2592000);
        }
    }
    return $courses;
}

/**
 * 取课程时间的下拉框
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 */
function get_courses_time_select($name, $selected = '', $class = '', $options = [], $hidden_first = true)
{
    $CoursesTime = get_courses_time();
    return html_select($CoursesTime, $name, $selected, $class, $options, $hidden_first);
}

/**
 * 获取课程时间
 * @return array
 */
function get_courses_time()
{
    $CoursesTimeInfo = Db::name('saas_courses_time')
        ->field('id,start_time, end_time')
        ->where('status', '=', '1')
        ->select();
    $CoursesTime = [];
    foreach ($CoursesTimeInfo as $k => $v) {
        $CoursesTime[$v['id']] = $v['start_time'] . '~' . $v['end_time'];
    }
    return $CoursesTime;
}

/**
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 * 校区列表多选框
 */
function get_branches_checkbox($name, $selected = '', $class = '', array $options = [])
{
    $branches = get_branches();
    return html_checkbox($branches, $name, $selected, $class, $options);
}

/**
 * @param $branch_id
 * @return mixed|string
 * 通过校区id获取校区名称
 */
function convert_branch($branch_id)
{
    $branches = get_branches();
    if (isset($branches[$branch_id])) {
        return $branches[$branch_id];
    }
    return '';
}

/**
 * @return array|mixed
 * 获取全部渠道
 */
function get_channels()
{
    if (Cache::has('channels')) {
        $channels = Cache::get('channels');
    } else {
        $channels = Db::name('saas_channel')
            ->where('status', '=', 1)
            ->column('title', 'id');
        Cache::set('channels', $channels, 2592000);
    }
    return $channels;
}

/**
 * 获取渠道下拉框
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 */
function get_channels_select($name, $selected = '', $class = '', $options = [], $hidden_first = true)
{
    $channels = get_channels();
    return html_select($channels, $name, $selected, $class, $options, $hidden_first);
}

/**
 * @param $channel_id
 * @return mixed|string
 * 通过渠道key 获取渠道名称
 */
function convert_channel($channel_id)
{
    $channels = get_channels();
    if (isset($channels[$channel_id])) {
        return $channels[$channel_id];
    }
    return '';
}

/**
 * @param $chinese
 * @return string
 * @author Jasmine2
 * 获取姓名的拼音
 */
function convert_pinyin_permalink($chinese)
{
    $pinyin = new \Overtrue\Pinyin\Pinyin();
    return implode('', array_map(function ($pinyin) {
        return $pinyin;
    }, $pinyin->name($chinese, false)));
}

/**
 * @param $chinese
 * @return string
 * @author Jasmine2
 * 获取姓名的拼音首字母
 */
function convert_pinyin_abbr($chinese)
{
    $pinyin = new \Overtrue\Pinyin\Pinyin();
    return implode('', array_map(function ($pinyin) {
        return $pinyin[0];
    }, $pinyin->name($chinese, false)));
}

/**
 * @param string $auth
 * @return array|mixed
 * 获取全部系统用户 或通过authorize(角色id)获取角色下系统用户
 */
function get_users($auth = '')
{
    if (Cache::has('users' . $auth)) {
        $users = Cache::get('users' . $auth);
    } else {
        $db = Db::name('system_user')
            ->where('status', '=', 1);
        if ($auth != '') {
            $db->where('authorize', '=', $auth);
        }
        $users = $db->column('concat(username, "[",ifnull(name, \'not set\'),"]")', 'id');
        Cache::set('users' . $auth, $users, 300);
    }
    return $users;
}

/**
 * @param $authorize
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 * @author Jasmine2
 * 获取用户选择列表
 */
function get_users_select($authorize, $name, $selected = '', $class = '', $options = [], $hidden_first = true)
{
    $users = get_users($authorize);
    return html_select($users, $name, $selected, $class, $options, $hidden_first);
}

/**
 * @param string $pid
 * @return array|mixed|PDOStatement|string|\think\Collection
 * 获取下级authorizes(默认顶级)
 */
function get_authorizes($pid = '')
{
    if (Cache::has('authorizes' . $pid)) {
        $authorizes = Cache::get('authorizes' . $pid);
    } else {
        $db = Db::name('system_auth')
            ->where('id', '<>', 1)
            ->where('status', '=', 1);
        if ($pid != '') {
            $db->where('pid', '=', $pid);
        }
        $authorizes = $db->select();
        Cache::set('authorizes' . $pid, $authorizes, 2592000);
    }
    return $authorizes;
}

/**
 * @param $pid
 * @param $name
 * @param string $selected
 * @param string $class
 * @return string
 * @author Jasmine2
 * 获取角色单选列表
 */
function get_authorizes_radio($pid, $name, $selected = '', $class = '', $options = [])
{
    $authorizes = array_column(get_authorizes($pid), 'title', 'id');
    return html_radio($authorizes, $name, $selected, $class, $options);
}

/**
 * @param $pid
 * @param $name
 * @param string $selected
 * @param string $class
 * @return string
 * 获取角色多选按钮
 */
function get_authorizes_checkbox($pid, $name, $selected = '', $class = '', array $options = [])
{
    $authorizes = array_column(get_authorizes($pid), 'title', 'id');
    return html_checkbox($authorizes, $name, $selected, $class, $options);
}

/**
 * @param $pid
 * @param $name
 * @param string $selected
 * @param string $class
 * @return string
 * 获取角色下拉列表
 */
function get_authorizes_select_tree($pid, $name, $selected = '', $class = '', $options = [], $hidden_first = true)
{
    $authorizes = \service\ToolsService::arr2table(get_authorizes($pid));
    $temp = [];
    foreach ($authorizes as &$authorize) {
        $temp[$authorize['id']] = $authorize['spl'] . $authorize['title'];
    }
    return html_select($temp, $name, $selected, $class, $options, $hidden_first);
}

/**
 * 获取角色名称
 * @param $auth_id
 * @return string
 */
function convert_auth_title($auth_id)
{
    if (session('user.id') == '10000') {
        return '超级管理员';
    }
    $authorizes = array_column(get_authorizes(), 'title', 'id');
    if (array_key_exists($auth_id, $authorizes)) {
        return $authorizes[$auth_id];
    }
    return '';
}

/**
 * @return array|PDOStatement|string|\think\Collection
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 * @author Jasmine2
 * 获取站点通知
 */
function get_notices()
{
    if (session('user.id')) {
        $user = session('user');
        $db = Db::name('saas_notice')->alias('n')
            ->join('saas_notice_subtable s', 'n.id=s.notice_id', 'left')
            ->where('n.begin_time', '<', time())
            ->where('n.end_time', '>', time())
            ->where('n.status', '<>', '3')
            ->order('n.level desc,n.id desc')
            ->field('n.*')
            ->group('n.id')
            ->limit(10);
        $authorize = $user['authorize'];
        if ($user['id'] != 10000) {
            if (isset($user['employee']) && !is_null($user['employee']['department'])) {  //用户有校区
                // 查所有给这个校区通知的通知id
                $notice = Db('saas_notice_subtable')->field('notice_id')->where('branch', '=', $user['employee']['department'])->group('notice_id')->select();
                $notice_id = [];
                foreach ($notice as $v) {  //二维转一维
                    $notice_id[] = $v['notice_id'];
                }
                if (empty($notice_id)) {   //如果这个校区下没有通知
                    return [];
                }
                if ($authorize != '') {   //查这个校区下的通知 哪个是给这个角色的
                    $db->where('s.auth', '=', $authorize);
                    $db->where('s.notice_id', 'in', $notice_id);
                }
            } else {      //如果这个用户没有校区
                if ($authorize != '') {   //判断有无角色,有角色则查 所有这个角色的通知,不区分校区
                    $db->where('s.auth', '=', $authorize);
                } else {  //没校区,没角色
                    return [];
                }
            }
        }
        return $db->select();
    } else {
        return [];
    }
}

/**
 * @param int $userid $userid
 * @return string
 * 通过system_user id 获取该用户在saas_employee中的name
 */
function get_user_realname($userid)
{
    if ($userid == '10000') {
        return '超级管理员';
    } else {
        if (Cache::has('employees')) {
            $employees = Cache::get('employees');
        } else {
            $employees = Db::name('saas_employee')
                ->where('status', '=', 1)
                ->where('userid is not null')
                ->column('name', 'userid');
            Cache::set('employees', $employees, 900);
        }
        if (isset($employees[$userid])) {
            return $employees[$userid];
        } else {
            return '';
        }
    }
}

/**
 * 获取雇员下拉列表
 */
function get_employee_select($authorize, $name, $department = '', $selected = '', $class = '', $options = [], $hidden_first = true)
{
    $users = get_users($authorize);
    $user_id = array_keys($users);
    $employee = get_employee($user_id, $department);
    return html_select($employee, $name, $selected, $class, $options, $hidden_first);
}

/**
 * 获取雇员
 */
function get_employee($user_id, $department)
{
    $db = Db::name('saas_employee')->where('status', '=', 1)->whereIn('userid', $user_id);
    if ($department == '' || session('user.id') == 10000) {

        $res = $db->column('name,english_name', 'userid');
    } else {
        $res = $db->where('department', '=', $department)->column('name,english_name', 'userid');
    }
    return $res;
}

/**
 * 获取雇员下拉列表(输出为 员工表name 和id非 userid)
 */
function get_employeeId_select($authorize, $name, $department = '', $selected = '', $class = '', $options = [], $hidden_first = true)
{
    $users = get_users($authorize);
    $user_id = array_keys($users);
    $employee = get_employeeId($user_id, $department);
    return html_select($employee, $name, $selected, $class, $options, $hidden_first);
}

/*
 * 获取某校区下所有员工
 */
function get_all_employeeId($department)
{
    if (Cache::has('employee' . $department)) {
        $employee = Cache::get('employee' . $department);
    } else {
        $employee = Db::name('saas_employee')
            ->field('id,name')
            ->where('status', '=', 1)
            ->where('department', $department)
            ->select();
        Cache::set('employee' . $department, $employee, 2592000);
    }
    return $employee;
}

/**
 * 获取雇员
 * (输出为 员工表name 和id非 userid)
 */
function get_employeeId($user_id, $department)
{
    $db = Db::name('saas_employee')->where('status', '=', 1)->whereIn('userid', $user_id);
    if ($department == '' || session('user.id') == 10000) {

        $res = $db->column('name', 'id');
    } else {
        $res = $db->where('department', '=', $department)->column('name', 'userid');
    }
    return $res;
}

/**
 * 获取员工姓名
 * 参数:员工id(employee表的id)
 * 返回:员工姓名
 */
function get_employee_name($user_id)
{
    if ($user_id == 0) {
        return '';
    }
    if (Cache::has('employee_name' . $user_id)) {
        $employee_name = Cache::get('employee_name' . $user_id);
    } else {
        $db = Db::name('saas_employee')
            ->where('status', '<>', 3);
        if ($user_id != '') {
            $db->where('id', '=', $user_id);
        }
        $employee_name = $db->column('name', 'id');
        Cache::set('employee_name' . $user_id, $employee_name, 2592000);
    }
    if (isset($employee_name[$user_id])) {
        return $employee_name[$user_id];
    } else {
        return "未知";
    }
}


/**
 * 获取员工姓名
 * 参数:员工id(employee表的userid)
 * 返回:员工姓名
 */
function get_employee_name2($user_id)
{
    if ($user_id == 0) {
        return '';
    }
    if (Cache::has('employee_name2' . $user_id)) {
        $employee_name = Cache::get('employee_name2' . $user_id);
    } else {
        $db = Db::name('saas_employee')
            ->where('status', '<>', 3);
        if ($user_id != '') {
            $db->where('userid', '=', $user_id);
        }
        $employee_name = $db->column('name', 'userid');
        Cache::set('employee_name2' . $user_id, $employee_name, 2592000);
    }
    if (isset($employee_name[$user_id])) {
        return $employee_name[$user_id];
    } else {
        return "未知";
    }
}

/**
 * 获取客户(学生)姓名
 * 参数:学生id
 * 返回:学生姓名
 */
function get_customer_name($customer_id)
{
    if ($customer_id == 0) {
        return '';
    }
    if (Cache::has('customer_name' . $customer_id)) {
        $customer_name = Cache::get('customer_name' . $customer_id);
    } else {
        $db = Db::name('saas_customer')
            ->where('status', '<>', 3);
        if ($customer_id != '') {
            $db->where('id', '=', $customer_id);
        }
        $customer_name = $db->column('name', 'id');
        Cache::set('customer_name' . $customer_id, $customer_name, 2592000);
    }
    if (isset($customer_name[$customer_id])) {
        return $customer_name[$customer_id];
    } else {
        return "";
    }
}

/**
 * @param $customer_id
 * @return string
 * 通过客户id获取家长姓名
 */
function get_parent_name($customer_id)
{
    if ($customer_id == 0) {
        return '';
    }
    if (Cache::has('parent_name' . $customer_id)) {
        $parent_name = Cache::get('parent_name' . $customer_id);
    } else {
        $db = Db::name('saas_customer')
            ->where('status', '<>', 3);
        if ($customer_id != '') {
            $db->where('id', '=', $customer_id);
        }
        $parent_name = $db->column('parent_name', 'id');
        Cache::set('parent_name' . $customer_id, $parent_name, 2592000);
    }
    if (isset($parent_name[$customer_id])) {
        return $parent_name[$customer_id];
    } else {
        return "";
    }
}

/**
 * 根据客户电话获取客户id
 */
function get_customer_id($parent_tel)
{
    $customer = Db::name('saas_customer')
        ->field('id')
        ->where('status', '<>', 3)
        ->where('parent_tel', $parent_tel)
        ->find();
    $customer_id = $customer['id'];
    return $customer_id;
}

/**
 * 获取客户(学生)电话
 * 参数:学生id
 * 返回:学生电话
 */
function get_customer_tel($customer_id)
{
    if ($customer_id == 0) {
        return '';
    }
    if (Cache::has('customer_tel' . $customer_id)) {
        $customer_tel = Cache::get('customer_tel' . $customer_id);
    } else {
        $db = Db::name('saas_customer')
            ->where('status', '<>', 3);
        if ($customer_id != '') {
            $db->where('id', '=', $customer_id);
        }
        $customer_tel = $db->column('parent_tel', 'id');
        Cache::set('customer_tel' . $customer_id, $customer_tel, 2592000);
    }
    if (isset($customer_tel[$customer_id])) {
        return $customer_tel[$customer_id];
    } else {
        return "";
    }
}

/**
 * @return array|mixed
 * @author Jasmine2
 * 获取销售员
 */
function get_sellers($type = '')
{
    $electricpiners = [];
    if ($type == 'dxy') {
        $type_auth = 10;
    } elseif ($type == 'cc') {
        $type_auth = '11,5,9,24';
    } else {
        $type_auth = '10,11';
    }
    $userid = !empty(session('user')['id']) ? session('user')['id'] : '';
    if (Cache::get('electricpin_select' . $userid . $type)) {
        $electricpiners = Cache::get('electricpin_select' . $userid . $type);
    } else {
        if (isset(session('user')['employee']['department']) && !empty(session('user')['employee']['department'])) {
            $departmentid = session('user')['employee']['department'];
            $db = Db::name("saas_employee")->alias("s");
            $db->join("system_user u", "s.userid = u.id", "right")->
            join("system_auth a", "u.authorize = a.id", "left")->
            where("s.status", "<>", "3")->where("u.authorize", "in", $type_auth)->
            where("u.is_deleted", "<>", "1")->order("s.created_at", "desc");
            if (!in_array(session('user')['authorize'], [1, 3, 4])) {
                $db->where("s.department", "=", $departmentid);
            }
            $electricpiners = $db->column('s.name,s.english_name', 's.userid');
            Cache::set('electricpin_select' . $userid . $type, $electricpiners, '2592000');
        } else {
            $db = Db::name("saas_employee")->alias("s");
            $db->alias("s")->join("system_user u", "s.userid = u.id", "right")->
            join("system_auth a", "u.authorize = a.id", "left")->
            where("s.status", "<>", "3")->where("u.authorize", "in", $type_auth)->where("u.is_deleted", "<>", "1")
                ->order("s.created_at", "desc");
            $electricpiners = $db->column('s.name,s.english_name', 's.userid');
            Cache::set('electricpin_select' . $userid . $type, $electricpiners, '2592000');
        }
    }
    return $electricpiners;
}

/**
 * @param $who
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 * @author Jasmine2
 */
function get_sellers_select($who, $name, $selected = '', $class = '', $options = [], $hidden_first = true)
{
    $electricpiners = get_sellers($who);

    return html_select($electricpiners, $name, $selected, $class, $options, $hidden_first);
}

function get_tips_list($data)
{
    $html = '';
    foreach ($data as $k => $v) {
        if ($k == '0') {
            $html .= '<button id="tips' . $k . '" type="button" class="layui-btn layui-btn-xs layui-btn-radius layui-btn-danger tipsbtu">' . $v . '</button>';
        } elseif ($k == '1') {
            $html .= '<button id="tips' . $k . '" type="button" class="layui-btn layui-btn-xs layui-btn-radius layui-btn-normal tipsbtu">' . $v . '</button>';
        } elseif ($k == '2') {
            $html .= '<button id="tips' . $k . '" type="button" class="layui-btn layui-btn-xs layui-btn-radius layui-btn-success tipsbtu">' . $v . '</button>';
        } elseif ($k == '3') {
            $html .= '<button id="tips' . $k . '" type="button" class="layui-btn layui-btn-xs layui-btn-radius layui-btn-warm tipsbtu">' . $v . '</button>';
        } else {
            $html .= '<button id="tips' . $k . '" type="button" class="layui-btn layui-btn-xs layui-btn-radius layui-btn-default tipsbtu">' . $v . '</button>';
        }
    }
    return $html;
}

/**
 * 根据上级id获取科目
 * @param $category_id
 * @return array|mixed
 */
function get_subjects()
{
    if (Cache::has('subject')) {
        $subjects = Cache::get('subject');
    } else {
        $subjects = Db::name('saas_subject')
            ->where('status', '<>', '3')
            ->column('title', 'id');
        Cache::set('subject', $subjects, 2592000);
    }
    return $subjects;
}

/**
 * 获取科目下拉框
 * @param $category_id
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 */
function get_subjects_select($name, $selected = '', $class = '', $options = [], $hidden_first = false)
{
    $subjects = get_subjects();
    return html_select($subjects, $name, $selected, $class, $options, $hidden_first);
}

/**
 * 科目转换
 * @param $subject
 * @param $category_id
 * @return string
 */
function convert_subject($subject)
{
    $subjects = get_subjects();
    if (isset($subjects[$subject])) {
        return $subjects[$subject];
    }
    return '';
}

/**
 * @author Jasmine2
 * 获取科目
 */
function get_school_year()
{
    $school_year = Db::name('saas_school_year')
        ->where('status', '=', 1)
        ->order('sort asc,id desc')
        ->column('title', 'id');
    return $school_year;
}

/**
 * 获取学年下拉框
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 */
function get_school_year_select($name, $selected = '', $class = '', $options = [], $hidden_first = true)
{
    return html_select(get_school_year(), $name, $selected, $class, $options, $hidden_first);
}

/**
 * @author Jasmine2
 * 生成课程编码
 */
function generate_course_no()
{
    $date_salt = date('ymd');
    $random = rand(100, 999);
    return 'C' . $date_salt . $random;
}

/**
 * 生成订单号
 * @return string
 */
function generate_order_no($prefix = 'O')
{
    $date_salt = date('YmdHis');
    $random = rand(100, 999);
    return $prefix . $date_salt . $random;
}

/**
 * @return string
 * 排课时生成唯一课程编号
 */
function generate_class_courses_no()
{
    $date_salt = date('YmdHis');
    $random = rand(1000, 9999);
    return 'D' . $date_salt . $random;
}

/**
 * @param string $id
 * @return array|mixed|PDOStatement|string|\think\Collection
 * 获取课程(默认全部)
 */
function get_courses_title($id = '')
{
    if (Cache::has('courses' . $id)) {
        $courses = Cache::get('courses' . $id);
    } else {
        $db = Db::name('saas_courses')
            ->where('status', '<>', 3);
        if ($id != '') {
            $db->where('id', '=', $id);
        }
        $courses = $db->column('title', 'id');
        Cache::set('courses' . $id, $courses, 2592000);
    }
    if (isset($courses[$id])) {

        return $courses[$id];
    } else {
        return '';
    }
}

/**
 * 获取教材
 * @param string $id
 * @return mixed
 */
function get_textbook_title($id = '')
{
    if (Cache::has('textbook' . $id)) {
        $textbook = Cache::get('textbook' . $id);
    } else {
        $db = Db::name('saas_textbook')
            ->where('status', '<>', 3);
        if ($id != '') {
            $db->where('id', '=', $id);
        }
        $textbook = $db->column('title', 'id');
        Cache::set('textbook' . $id, $textbook, 2592000);
    }
    return $textbook[$id];
}

/**
 * 获取星期
 */
function get_week($date)
{
    $week = ['日', '一', '二', '三', '四', '五', '六'];
    return $week[date('w', $date)];
}

/**
 * 判断学生是否打卡
 */
function get_da_ka($student_id, $course_id, $kb_id)
{
    $date = time();
    $s_time = strtotime(date('Y-m-d', $date));
    $e_time = strtotime(date('Y-m-d 23:59:59', $date));
    if (Cache::has($s_time . '-' . $student_id . '-' . $course_id . '-' . $kb_id)) {
        return '1';
    }
    $res = Db::name('saas_course_log')
        ->where('student_id', '=', $student_id)
        ->where('course_id', '=', $course_id)
        ->where('course_sub_id', '=', $kb_id)
        ->whereBetween('created_at', [$s_time, $e_time])
        ->find();
    if ($res) {
        Cache::set($s_time . '-' . $student_id . '-' . $course_id . '-' . $kb_id, $res, 86400);
        return '1';
    } else {
        return '0';
    }
}

/**
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 * 获取全部班级下拉选择
 */
function get_class_select($name, $selected = '', $class = '', $options = [], $hidden_first = true)
{
    $class_info = get_class_title();

    return html_select($class_info, $name, $selected, $class, $options, $hidden_first);
}

/**
 * 取班级名称
 * @return array|mixed
 */
function get_class_title()
{
    if (in_array(session('user.authorize'), [1, 3, 4])) {
        if (Cache::has('class')) {
            $class = Cache::get('class');
        } else {
            $class = Db::name('saas_class')->where('status', '<>', 3)->column('title', 'id');
            Cache::set('class', $class, 2592000);
        }
    } else {
        $branch = session('user.employee.department');
        if (Cache::has('class_' . $branch)) {
            $class = Cache::get('class_' . $branch);
        } else {
            $class = Db::name('saas_class')->where('branch', '=', $branch)->where('status', '<>', 3)->column('title', 'id');
            Cache::set('class_' . $branch, $class, 2592000);
        }
    }

    return $class;

}

/**
 * @param $class_id
 * @return string
 * 获取班级名称
 */
function convert_class($class_id)
{
    $class_title = get_class_title();
    if (isset($class_title[$class_id])) {
        return $class_title[$class_id];
    }
    return '';
}

/**
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 * 获取指定教室名称
 */
function get_room_title($id = '')
{
    if (Cache::has('room' . $id)) {
        $room = Cache::get('room' . $id);
    } else {
        $db = Db::name('saas_room')
            ->where('status', '<>', 3);
        if ($id != '') {
            $db->where('id', '=', $id);
        }
        $room = $db->column('code', 'id');
        Cache::set('room' . $id, $room, 2592000);
    }
    return $room[$id];
}

/**
 * checkCode 检测短信验证码
 */
if (!function_exists('checkCode')) {
    function checkCode($mobile, $code)
    {
        if (Cache::has('captcha_' . $mobile)) {
            $_code = Cache::get('captcha_' . $mobile);
            return boolval($code == $_code);
        } else {
            return false;
        }
    }
}
/**
 * sms_lock 短信锁
 */
if (!function_exists('sms_lock')) {
    function sms_lock($mobile)
    {
        /**
         * ip 限制
         * 手机号码限制
         * 5分钟限制
         */
        if (Cache::has('lock_' . $mobile)) {
            return '短信验证码每分钟只能发送一次';
        } else {
            Cache::set('lock_' . $mobile, 1, 60);
        }
        if (Cache::has('sms_lock_' . $mobile)) {
            $times = Cache::get('sms_lock_' . $mobile);
            if ($times >= 10) {
                return '短信验证码每天只能发送10次';
            } else {
                Cache::set('sms_lock_' . $mobile, $times + 1, strtotime(date('Y-m-d')) + 86400 - time());
            }
        } else {
            Cache::set('sms_lock_' . $mobile, 1, strtotime(date('Y-m-d')) + 86400 - time());
        }
        return false;
    }
}
/**
 * @param string $userid
 * @return array|mixed|PDOStatement|string|\think\Collection
 * 通过平台用户id获取平台用户角色
 */
if (!function_exists('employee_auth')) {
    function employee_auth($userid)
    {
        if (empty($userid)) {
            return false;
        }
        $userauth = Db::name('system_user')
            ->alias('u')
            ->join('system_auth au', 'u.authorize=au.id', 'left')
            ->field('u.authorize,au.title')
            ->where('u.id', '=', $userid)
            ->column('title', 'u.id');
        return $userauth[$userid];
    }
}
/**
 * 获取全部教室下拉选择
 * @param int $branch
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 */
function get_room_select($branch = 0, $name, $selected = '', $class = '', $options = [], $hidden_first = true)
{
    $rooms = Db::name('saas_room')
        ->where('status', '=', 1);
    if ($branch != 0) {
        $rooms->where('branch', '=', $branch);
    }
    $rooms = $rooms->column('code', 'id');
    return html_select($rooms, $name, $selected, $class, $options, $hidden_first);
}

/**
 * 获取
 * @return array
 */
function get_teachers()
{
    $db = Db::name('saas_employee')
        ->where('status', '1')
        ->where('is_teacher', '1');
    if (!in_array(session('user.authorize'), [1, 3, 4, 22])) {
        $branch = session('user.employee.department');
        $db->where('department', '=', $branch);
    }
    $teachers = $db->column('name', 'id');
    //查询教学总监
    $director = Db::name('system_user')
        ->alias('u')
        ->join('saas_employee e', 'u.id = e.userid', 'left')
        ->where('u.authorize', '=', 22)
        ->where('u.is_deleted', '=', 0)
        ->where('e.status', '<>', 3)
        ->column('e.name', 'e.id');
    $teachers = $teachers + $director;
    return $teachers;
}

/**
 * 获取全部老师下拉选择
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 */
function get_teacher_select($name, $selected = '', $class = '', $options = [], $hidden_first = true)
{
    $teachers = get_teachers();
    return html_select($teachers, $name, $selected, $class, $options, $hidden_first);
}

/**
 * 星期单选按钮
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @return string
 */
function get_week_radio($name, $selected = '', $class = '', $options = [])
{
    $weekend = [
        '1' => '星期一',
        '2' => '星期二',
        '3' => '星期三',
        '4' => '星期四',
        '5' => '星期五',
        '6' => '星期六',
        '0' => '星期日',
    ];
    return html_checkbox($weekend, $name, $selected, $class, $options);
}

/**
 * @param $mobile
 * @return string
 * 拨打电话快捷方式
 */
if (!function_exists('make_call')) {
    function make_call($mobile)
    {
        if (sysconf('call_center_type') != '' && sysconf('call_center_type') != 'no') {
            $action = url('marketing/call/make_call');
            return <<<EOF
        &nbsp;
<a data-update="$mobile" data-action="$action" data-confirm-msg="您确定要联系这个客户吗?" data-tips-text="点击拨打电话"><span class="fa fa-phone"> </span></a>
EOF;
        } else {
            return '';
        }
    }
}

/**
 * 数据导出成excel方法
 * $data  array 表格数据(数据库查询结果)
 * $key   array ["数据库键"=>"表格标题"]
 * $title  str 表格名称
 * 说明:行数由$data 记录数决定  列数由 $key决定
 */
function down_excel($data, $key, $title)
{
    $spreadsheet = new Spreadsheet();
    $worksheet = $spreadsheet->getActiveSheet();
    //设置工作表标题名称
    $worksheet->setTitle($title);
    //表头
    $worksheet->setCellValueByColumnAndRow(1, 1, $title);
    $i = 1;
    foreach ($key as $k => $v) {
        $worksheet->setCellValueByColumnAndRow($i, 2, $v);
        $i++;
    }
    $ask = count($key);
    $G = chr(64 + $ask);
    //合并单元格
    $worksheet->mergeCells("A1:" . $G . "1");
    $styleArray = [
        'font' => [
            'bold' => true
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
    ];
    //设置单元格样式
    $worksheet->getStyle('A1')->applyFromArray($styleArray)->getFont()->setSize(18);
    $worksheet->getStyle("A2:" . $G . "2")->applyFromArray($styleArray)->getFont()->setSize(12);
    foreach ($data as $k => $v) {
        $num = $k + 3;
        $s = 1;
        foreach ($key as $ki => $vi) {
            $worksheet->setCellValueByColumnAndRow($s, $num, $v[$ki]);//$j是行  前面的数字是列
            $s++;
        }
    }
    //设置宽度为true,不然太窄了
    $worksheet->getColumnDimension('B')->setWidth(20);
    $worksheet->getColumnDimension('A')->setWidth(20);
    $worksheet->getColumnDimension('C')->setWidth(20);
    $worksheet->getColumnDimension('D')->setWidth(20);
    $worksheet->getColumnDimension('E')->setWidth(20);
    $worksheet->getColumnDimension('F')->setWidth(20);
    $worksheet->getColumnDimension('G')->setWidth(20);
    $worksheet->getColumnDimension('H')->setWidth(20);
    $styleArrayBody = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['argb' => '666666'],
            ],
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
    ];
    $total_rows = count($data) + 2;
    //添加所有边框/居中
    $worksheet->getStyle("A1:$G" . $total_rows)->applyFromArray($styleArrayBody);
    $filename = "$title.xlsx";
    @ob_end_clean();//清除缓冲区,避免乱码
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save("php://output");
    exit();
}

/**
 * 字符串转换
 * @param $content
 * @param int $lenth
 * @return string
 */
function cn_substr($content, $lenth = 8)
{
    if (mb_strlen($content, 'utf-8') < 10) {
        return $content;
    } else {
        return mb_substr($content, 0, $lenth, 'utf-8') . '...';
    }
}

/**
 * mobile_mask 手机号码脱敏
 */
if (!function_exists('mobile_mask')) {
    function mobile_mask($mobile)
    {
        if (strlen($mobile) == 11) {
            return substr($mobile, 0, 3) . "****" . substr($mobile, -4, 4);
        }
    }
}

/**
 *bank_mask 银行卡号脱敏
 */
if (!function_exists('bank_mask')) {
    function bank_mask($bankno)
    {
        if (strlen($bankno) >= 11) {
            return "**** **** **** " . substr($bankno, -4, 4);
        }
    }
}


/**
 * 根据authorize_id取对应会员组
 * @param $authorize_id
 * @return bool
 */
function get_authorize_name($authorize_id)
{
    if (empty($authorize_id)) {
        return false;
    }
    $authorize = Db::name('system_auth')
        ->where('status', '=', '1')
        ->where('id', '=', $authorize_id)
        ->column('title', 'id');
    return $authorize[$authorize_id];
}

/**
 * @param $extras_id
 * @return string
 * 杂费名称
 */
function convert_extras($extras_id)
{
    $extras = get_extras();
    if (isset($extras[$extras_id])) {
        return $extras[$extras_id];
    }
    return '';
}

/**
 * @return array|mixed
 * 获取全部杂费
 */
function get_extras()
{
    if (Cache::has('extras')) {
        $extras = Cache::get('extras');
    } else {
        $extras = Db::name('saas_textbook')
            ->column('title', 'id');
        Cache::set('extras', $extras, 2592000);
    }
    return $extras;
}

/**
 * userid 通过用户ID获取所在校区名称
 */
if (!function_exists('get_user_branch')) {
    function get_user_branch($userid)
    {
        if (Cache::has('departments')) {
            $departments = Cache::get('departments');
        } else {
            $departments = Db::name('saas_employee')
                ->where('status', '=', 1)
                ->where('userid is not null')
                ->column('department', 'userid');
            Cache::set('departments', $departments, 900);
        }
        if (isset($departments[$userid])) {
            return convert_branch($departments[$userid]);
        } else {
            return '';
        }
    }
}

/**
 * @param $aid
 * @param $name
 * @param string $selected
 * @param string $class
 * @return string
 * 获取当前角色下所有角色单选列表
 */
function get_authorizes_radio_underauth($aid, $name, $selected = '', $class = '', $options = [])
{
    $authorizes = array_column(get_authorizes_underauth($aid), 'title', 'id');
    return html_radio($authorizes, $name, $selected, $class, $options);
}

/**
 * @param string $aid
 * @return array|mixed|PDOStatement|string|\think\Collection
 * 获取当前角色的所有下级authorizes(默认顶级)
 */
function get_authorizes_underauth($aid = '')
{
    $pid = Db::name('system_auth')->field('pid')->where('id', '=', $aid)->find();
    if (Cache::has('underauthorizes' . $aid)) {
        $authorizes = Cache::get('underauthorizes' . $aid);
    } else {
        $db = Db::name('system_auth')
            ->where('id', '<>', 1)
            ->where('status', '=', 1);
        if ($aid != '') {
            $db->where('pid', '>=', $pid['pid']);
        }
        $authorizes = $db->select();
        Cache::set('underauthorizes' . $aid, $authorizes, 2592000);
    }
    return $authorizes;
}

/**
 * 计算短信总金额
 * @param $counts
 * @return int
 */
function sum_money($counts)
{
    $money = $counts * 0.075;
    return $money;
}

function post_str($post_data = array(), $encode = true)
{
    $o = "";
    foreach ($post_data as $k => $v) {
        if ($encode)
            $o .= "$k=" . urlencode($v) . "&";
        else
            $o .= "$k=" . $v . "&";

    }
    $o = substr($o, 0, -1);
    return $o;
}

/**
 * 跟进权限节点获取菜单名称
 * @param string $node
 * @return string
 */
function get_node_name($node = '')
{
    if (empty($node)) return '未知节点';
    if ($node == "admin/login/form_v2") return '登录';
    if ($node == "admin/login/out") return '退出';
    if (Cache::has('node_title_all')) {
        $node_title_all = Cache::get('node_title_all');
    } else {
        $node_title_all = Db::name('system_node')->column('title', 'node');
        Cache::set('node_title_all', $node_title_all, 2592000);
    }
    if (isset($node_title_all[$node]) && !empty($node_title_all[$node])) {
        return $node_title_all[$node];
    } else {
        return $node;
    }
}

/**
 * 获取学生打卡状态
 * @param $status
 * @return string
 */
function get_students_clock_status($status)
{
    if (empty($status)) return '未知';
    switch ($status) {
        case 1:
            echo '正常打卡';
            break;
        case 2:
            echo '补课';
            break;
        case 3:
            echo '请假';
            break;
        case 4:
            echo '旷课';
            break;
    }
}

/**
 * @param $class_id
 * 生成saas_class_course表的课程信息
 */
function set_class_courses($class_id)
{
    Db::name('saas_class_course')->where('class_id', $class_id)->delete();
    $courses_info = Db::name('saas_courses_detail')
        ->field('class_id,courses_id as course_id ,created_at')
        ->where('class_id', '=', $class_id)
        ->group('courses_id')
        ->select();
    Db::name('saas_class_course')->insertAll($courses_info);
}

/**
 * 获取体验课（准备中）
 */
function get_trial_class()
{
    $db = Db::name('saas_course_teacher_log')->alias('t')
        ->join('saas_courses c', 't.course_id = c.id', 'left')
        ->where('t.is_deleted', '=', '0')
        ->where('t.status', '=', '3')
        ->where('t.is_ok', '=', '2')
        ->order('t.id desc');
    if (!in_array(session('authorize'), [1, 3, 4])) {
        $branch = session('user.employee.department');
        $db->where('c.branch', '=', $branch);
    }
    $course = $db->column('c.title', 't.id');
    return $course;
}

/**
 * 体验课下拉框
 * @param $name
 * @param string $selected
 * @param string $class
 * @param array $options
 * @param bool $hidden_first
 * @return string
 */
function get_trial_class_select($name, $selected = '', $class = '', $options = [], $hidden_first = true)
{
    $teachers = get_trial_class();
    return html_select($teachers, $name, $selected, $class, $options, $hidden_first);
}

/**
 * @param array $data 数据
 * @param string $name 表单名称
 * @param string $title 显示值
 * @param string $key key值
 * @param string $label label名称
 * @param null $default 默认选中
 * @param bool $spl 是否是多级列表
 * @return string
 */
function getSelectList($data, $name, $key = 'id', $title, $label, $default = null, $spl = false)
{
    $str = '<div class="layui-form-item">
        <label class="layui-form-label">' . $label . '</label>
        <div class="layui-input-block">
            <select name=\'' . $name . '\' class=\'layui-select full-width\' style=\'display:none\'>';
    foreach ($data as $k => $v) {
        if ($default == $v[$key]) {
            if ($spl) {
                if (isset($v['type'])) {
                    if ($v['type'] == 1) {
                        $str .= "<option selected  value='" . $v[$key] . "'>" . $v['spl'] . $v[$title] . '【PC端】' . "</option>";
                    } elseif ($v['type'] == 2) {
                        $str .= "<option selected  value='" . $v[$key] . "'>" . $v['spl'] . $v[$title] . '【移动端】' . "</option>";
                    } elseif ($v['type'] == 3) {
                        $str .= "<option selected  value='" . $v[$key] . "'>" . $v['spl'] . $v[$title] . '【商城】' . "</option>";
                    }
                } else {
                    $str .= "<option selected  value='" . $v[$key] . "'>" . $v['spl'] . $v[$title] . "</option>";
                }
            } else {
                if (isset($v['type'])) {
                    if ($v['type'] == 1) {
                        $str .= "<option selected  value='" . $v[$key] . "'>" . $v[$title] . '【PC端】' . "</option>";
                    } elseif ($v['type'] == 2) {
                        $str .= "<option selected  value='" . $v[$key] . "'>" . $v[$title] . '【移动端】' . "</option>";
                    } elseif ($v['type'] == 3) {
                        $str .= "<option selected  value='" . $v[$key] . "'>" . $v[$title] . '【商城】' . "</option>";
                    }
                } else {
                    $str .= "<option selected  value='" . $v[$key] . "'>" . $v[$title] . "</option>";
                }
            }
        } else {
            if ($spl) {
                if (isset($v['type'])) {
                    if ($v['type'] == 1) {
                        $str .= "<option value='" . $v[$key] . "'>" . $v['spl'] . $v[$title] . '【PC端】' . "</option>";
                    } elseif ($v['type'] == 2) {
                        $str .= "<option value='" . $v[$key] . "'>" . $v['spl'] . $v[$title] . '【移动端】' . "</option>";
                    } elseif ($v['type'] == 3) {
                        $str .= "<option value='" . $v[$key] . "'>" . $v['spl'] . $v[$title] . '【商城】' . "</option>";
                    }
                } else {
                    $str .= "<option value='" . $v[$key] . "'>" . $v['spl'] . $v[$title] . "</option>";
                }

            } else {
                if (isset($v['type'])) {
                    if ($v['type'] == 1) {
                        $str .= "<option value='" . $v[$key] . "'>" . $v[$title] . '【PC端】' . "</option>";
                    } elseif ($v['type'] == 2) {
                        $str .= "<option value='" . $v[$key] . "'>" . $v[$title] . '【移动端】' . "</option>";
                    } elseif ($v['type'] == 3) {
                        $str .= "<option value='" . $v[$key] . "'>" . $v[$title] . '【商城】' . "</option>";
                    }
                } else {
                    $str .= "<option value='" . $v[$key] . "'>" . $v[$title] . "</option>";
                }
            }
        }
    }
    $str .= '</select></div></div>';
    return $str;
}

/**
 * 取内容分类
 * @param $key
 * @return string
 */
function getContentCategory($key)
{
    if (Cache::has('content_category')) {
        $data = Cache::get('content_category');
    } else {
        $data = \think\Db::table('saas_content_category')->column('name,pid,type', 'id');
        Cache::set('content_category', $data, 86400);
    }
    if (isset($data[$key])) {
        $type = $data[$key]['type'];
        if ($data[$key]['pid'] != 0) {
            $pid = $data[$key]['pid'];
            if ($type == 1) {
                return $data[$key]['name'] . '(' . $data[$pid]['name'] . ')【PC端】';
            } elseif ($type == 2) {
                return $data[$key]['name'] . '(' . $data[$pid]['name'] . ')【移动端】';
            } elseif ($type == 3) {
                return $data[$key]['name'] . '(' . $data[$pid]['name'] . ')【商城】';
            }
        }
        if ($type == 1) {
            return $data[$key]['name'] . '【PC端】';
        } elseif ($type == 2) {
            return $data[$key]['name'] . '【移动端】';
        } elseif ($type == 3) {
            return $data[$key]['name'] . '【商城】';
        }
    }
    return 'not set';
}

/**
 * @param $to
 * @param $subject  string  主题
 * @param $nickname string  发件人昵称
 * @param $content string 邮件内容
 * @return bool
 */
function sendmail($to, $nickname, $subject, $content)
{
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Host = sysconf('mailhost');
    $mail->SMTPSecure = 'ssl';
    $mail->Port = sysconf('mailport');
    $mail->CharSet = 'UTF-8';
    $mail->FromName = $nickname;
    $mail->Username = sysconf('mailuser');
    $mail->Password = sysconf('mailpass');
    $mail->From = sysconf('mailuser');
    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->Body = $content;
    return $status = $mail->send();
}

/**
 * @param int $id $id
 * @return string
 * 通过employee id 获取该用户在saas_employee中的name
 */
function get_user_name($id)
{
    if (Cache::has('employeename')) {
        $employees = Cache::get('employeename');
    } else {
        $employees = Db::name('saas_employee')
            ->where('status', '=', 1)
            ->where('id is not null')
            ->column('name', 'id');
        Cache::set('employees', $employees, 900);
    }
    if (isset($employees[$id])) {
        return $employees[$id];
    } else {
        return '';
    }
}

/**
 * 资金流动记录
 * 主要记录每一条客户的资金流动信息
 * @param      string $orderno 订单NO,如果是活动秀则是 aid
 * @param       int $cid 客户id
 * @param       float $price 金额  为正表示流入 为负表示流出
 * @param       int $type 类型 :1报名   2续费   3退费   4购买商品(道具,书本等)  5活动秀 6 易宝 7 库分期
 * @param        int $goods_id 商品id(报班存班级ID,买课存课程id,活动秀为空,后续根据type判断)
 * @param       string $desc 描述备注
 * @return bool
 */
function cash_flow($orderno, $cid, $price, $type, $goods_id, $desc = '')
{
    $data = [
        'orderno' => $orderno,
        'cid' => $cid,
        'price' => $price,
        'type' => $type,
        'goods_id' => $goods_id,
        'desc' => $desc,
        'created_at' => time()
    ];
    $res = Db::name('saas_cash_flow')->insert($data);
    if ($res) {
        return true;
    } else {
        return false;
    }
}

/**
 * 商城获取商品title
 * @param $goods_id
 * @return array|mixed
 */
function order_title($goods_id)
{
    if (Cache::get('store_goods_' . $goods_id)) {
        $title = Cache::get('store_goods_' . $goods_id);
        return $title;
    }
    $title = Db::name('store_goods')
        ->where('status', '<>', 3)
        ->where('id', '=', $goods_id)
        ->value('goods_title');
    Cache::set('store_goods_' . $goods_id, $title, 259200);
    return $title;
}

/**
 * 商城获取商品图片
 * @param $goods_id
 * @return array|mixed
 */
function order_images($goods_id)
{
    if (Cache::get('store_goods_' . $goods_id)) {
        $image = Cache::get('store_goods_images_' . $goods_id);
        return $image;
    }
    $image = Db::name('store_goods')
        ->where('status', '<>', 3)
        ->where('id', '=', $goods_id)
        ->value('goods_logo');
    Cache::set('store_goods_images_' . $goods_id, $image, 259200);
    return $image;
}

/**
 * 获取库分期银行卡编码对应名称
 * @param $val
 */
function get_kfq_bankcode()
{
    $data = [];
    $data['CITIC'] = '中信银行';
    $data['ABC'] = '农业银行';
    $data['BOC'] = '中国银行';
    $data['SPDB'] = '浦发银行';
    $data['CMBC'] = '民生银行';
    $data['CIB'] = '兴业银行';
    $data['CEB'] = '光大银行';
    $data['HXB'] = '华夏银行';
    $data['NBCB'] = '宁波银行';
    $data['JSBK'] = '江苏银行';
    $data['BOS'] = '上海银行';
    $data['BJRCB'] = '北京农商';
    $data['GRCB'] = '广州农商';
    $data['ICBC'] = '工商银行';
    $data['CCB'] = '建设银行';
    $data['GDB'] = '广发银行';
    $data['PSBC'] = '邮储银行';
    return $data;
}

/**
 * 支付渠道select
 * @param $type
 * @param $selected
 * @return string
 */
function pay_channel($type, $selected)
{
    $data = [];
    $data['1'] = '分期';
    $data['2'] = '全额';
    $html = html_select($data, $type, $selected, '', [], false);
    return $html;
}

/**
 * 支付状态select
 * @param $status
 * @param $selected
 * @return string
 */
function pay_status($status, $selected)
{
    $data = [];
    $data['2'] = '未支付';
    $data['1'] = '支付失败';
    $data['99'] = '支付成功';
    $data['300'] = '退款处理中';
    $html = html_select($data, $status, $selected, '', [], false);
    return $html;
}


function get_student_course($student_id)
{
    $courses = Db::name('saas_order o')
        ->field('l.goods_num, l.consume_num, c.title, l.class_id, cl.teacher_id')
        ->join('saas_order_log l', 'o.id = l.order_id', 'left')
        ->join('saas_courses c', 'c.id = l.goods_id', 'left')
        ->join('saas_class cl', 'cl.id = l.class_id', 'left')
        ->where('o.student_id', '=', $student_id)
        ->where('o.status', '<>', 3)
        ->select();
        $html_table = '';
        if ($courses) {
            foreach ($courses as $value) {
                $remaining_course = $value['goods_num']-$value['consume_num'];
                $html_table .= "<tr>
                        <td class='text-center' style='width: 25%;'>".$value['title']."</td>
                        <td class='text-center' style='width: 15%;'>".$value['goods_num']."课时</td>
                        <td class='text-center' style='width: 15%;'>".$value['consume_num']."课时</td>
                        <td class='text-center' style='width: 15%;'>".$remaining_course."课时</td>
                        <td class='text-center' style='width: 17%;'>".convert_class($value['class_id'])."</td>
                        <td class='text-center' style='width: 15%;'>".get_employee_name($value['teacher_id'])."</td>
                    </tr>";
//                $html_table .= '课程:'.$value['title'].';&nbsp&nbsp&nbsp总课时:'.$value['goods_num'].';&nbsp&nbsp&nbsp剩余课时:'.$remaining_course.'<br />';
            }
        } else {
            $html_table .= "<tr><td>未分班</td></tr>";
//            $html_table .= '暂无课时记录';
        }

    return $html_table;
}