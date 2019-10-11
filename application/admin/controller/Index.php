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

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\App;
use think\Db;
use think\facade\Cache;

/**
 * 后台入口
 * Class Index
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 10:41
 */
class Index extends BasicAdmin
{
    /**
     * 后台框架布局
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        NodeService::applyAuthNode();
        $list = (array)Db::name('SystemMenu')->where(['status' => '1'])->order('sort asc,id asc')->select();
        $menus = $this->buildMenuData(ToolsService::arr2tree($list), NodeService::get(), !!session('user'));
        if (empty($menus) && !session('user.id')) {
            $this->redirect('@admin/login');
        }
        return $this->fetch('', ['title' => '系统管理', 'menus' => $menus]);
    }

    public function fragment()
    {
        return $this->fetch('');
    }

    /**
     * 后台主菜单权限过滤
     * @param array $menus 当前菜单列表
     * @param array $nodes 系统权限节点数据
     * @param bool $isLogin 是否已经登录
     * @return array
     */
    private function buildMenuData($menus, $nodes, $isLogin)
    {
        foreach ($menus as $key => &$menu) {
            !empty($menu['sub']) && $menu['sub'] = $this->buildMenuData($menu['sub'], $nodes, $isLogin);
            if (!empty($menu['sub'])) {
                $menu['url'] = '#';
            } elseif (preg_match('/^https?\:/i', $menu['url'])) {
                continue;
            } elseif ($menu['url'] !== '#') {
                $node = join('/', array_slice(explode('/', preg_replace('/[\W]/', '/', $menu['url'])), 0, 3));
                $menu['url'] = url($menu['url']) . (empty($menu['params']) ? '' : "?{$menu['params']}");
                if (isset($nodes[$node]) && $nodes[$node]['is_login'] && empty($isLogin)) {
                    unset($menus[$key]);
                } elseif (isset($nodes[$node]) && $nodes[$node]['is_auth'] && $isLogin && !auth($node)) {
                    unset($menus[$key]);
                }
            } else {
                unset($menus[$key]);
            }
        }
        return $menus;
    }

    /**
     * 主机信息显示
     * @throws
     * @return string
     */
    public function main()
    {
        if ($this->user['id'] != 10000 && !in_array($this->user['authorize'], [1, 3, 4])) {
            $branch = $this->user['employee']['department'];
        }
        $db = Db::name('saas_customer')->where('status', '<>', '3');

        if ($this->user['id'] != 10000 && !in_array($this->user['authorize'], [1, 3, 4])) {
            $branch = $this->user['employee']['department'];
            $db->where('branch', '=', $branch);
        }
        $customer_count = Db::name('saas_customer')->where('status', '<>', '3')->where('is_student', '<>', '1')->count();
        $students_count = Db::name('saas_customer')->where('status', '<>', '3')->where('is_student', '=', '1')->count();
        $db_2 = Db::name('saas_class')->where('status', '1');
        if ($this->user['id'] != 10000 && !in_array($this->user['authorize'], [1, 3, 4])) {
            $branch = $this->user['employee']['department'];
            $db_2->where('branch', '=', $branch);
        }
        $class_count = $db_2->count();
        $db_3 = Db::name('saas_courses')->where('status', '1');
        if ($this->user['id'] != 10000 && !in_array($this->user['authorize'], [1, 3, 4])) {
            $branch = $this->user['employee']['department'];
            $db_3->where('branch', '=', $branch);
        }
        $courses_count = $db_3->count();
        $count = [
            'customer' => $customer_count,
            'students' => $students_count,
            'class' => $class_count,
            'courses' => $courses_count
        ];
        //查找待付款的用户 5个
        $nopay = Db::name('saas_order')->where('status', 4)->limit(0, 5)->order('id desc')->select();
        //统计待付款用户数量
        $nopay_num = Db::name('saas_order')->where('status', 4)->count(1);
        //即将过期的学员  剩余课时小于等于5
        $comingSoon = $this->expiredStudent();
        $comingSoon_num = count($comingSoon);
        $info = [
            'title' => '首页',
            'notices' => get_notices(),
            'count' => $count,
            'fallow' => 0,
            'nopay' => $nopay,//未支付用户
            'nopay_num' => $nopay_num,//未支付数量
            'comingSoon' => $comingSoon,//即将过期的学员
            'comingSoon_num' => $comingSoon_num,   //即将过期的学员 数量
        ];
        //查找 销售今日需要跟进的客户
        if (in_array(session('user.authorize'), [5, 11])) {
            $info['fallow'] = $this->countTodayFollow();
            $info['fallow_data'] = $this->getTodayFollow2();
        }
        return $this->fetch('', $info);
    }

    /**
     * @return mixed
     * 统计销售今日需要跟进的客户
     */
    protected function countTodayFollow()
    {
        $time = strtotime(date('y-m-d'));
        $user_id = $this->user['id'];
        $sql = "SELECT	count(1) as count
         FROM	saas_customer_follow  as f
          LEFT JOIN saas_customer AS c ON f.customer_id = c.id
          WHERE	f.id IN (SELECT	max(id) AS id	FROM	saas_customer_follow	GROUP BY	customer_id)AND f.remind_time <= $time";
        //超级管理员查看全部 不用做任何限制
        //销售主管只看自己校区的
        if (in_array(session('user.authorize'), [5])) {
            $branch = $this->user['employee']['department'];
            $sql .= " AND c.branch=$branch";
        }
        //销售员只看自己的
        if (in_array(session('user.authorize'), [11])) {
            $sql .= " AND c.collect_id=$user_id";
        }
        $sql .= " order by f.remind_time";
        $data = Db::query($sql);
        return $data[0]['count'];

    }

    /**
     * @return mixed
     * 给待办事项那块用的
     */
    public function getTodayFollow2()
    {
        $time = strtotime(date('y-m-d'));
        $user_id = $this->user['id'];
        $sql = "SELECT	c.id,c.`name`,c.parent_tel,c.parent_name,c.collect_id,f.follow_status,f.created_at,f.remind_time
         FROM	saas_customer_follow AS f
          LEFT JOIN saas_customer AS c ON f.customer_id = c.id
          WHERE	f.id IN (SELECT	max(id) AS id	FROM	saas_customer_follow	GROUP BY	customer_id)AND f.remind_time <= $time";
        //超级管理员查看全部 不用做任何限制
        //校区管理员和销售主管只看自己校区的
        if (in_array(session('user.authorize'), [5])) {
            $branch = $this->user['employee']['department'];
            $sql .= " AND c.branch=$branch";
        }
        //销售员只看自己的
        if (in_array(session('user.authorize'), [11])) {
            $sql .= " AND c.collect_id=$user_id";
        }
        $sql .= " order by f.remind_time";
        $data = Db::query($sql);
        return $data;

    }

    public function getTodayFollow()
    {
        $time = strtotime(date('y-m-d'));
        $user_id = $this->user['id'];
        $sql = "SELECT	c.id,c.`name`,c.parent_tel,c.parent_name,c.collect_id,f.created_at,f.remind_time
         FROM	saas_customer_follow AS f
          LEFT JOIN saas_customer AS c ON f.customer_id = c.id
          WHERE	f.id IN (SELECT	max(id) AS id	FROM	saas_customer_follow	GROUP BY	customer_id)AND f.remind_time <= $time";
        //超级管理员查看全部 不用做任何限制
        //校区管理员和销售主管只看自己校区的
        if (in_array(session('user.authorize'), [5])) {
            $branch = $this->user['employee']['department'];
            $sql .= " AND c.branch=$branch";
        }
        //销售员只看自己的
        if (in_array(session('user.authorize'), [11])) {
            $sql .= " AND c.collect_id=$user_id";
        }
        $sql .= " order by f.remind_time";
        $data = Db::query($sql);
        return $this->fetch('fallow', ['fallow' => $data]);

    }

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function showNotice()
    {
        return $this->_form('saas_notice', 'notice_view');
    }

    /**
     * 修改密码
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function pass()
    {
        if (intval($this->request->request('id')) !== intval(session('user.id'))) {
            $this->error('只能修改当前用户的密码！');
        }
        if ($this->request->isGet()) {
            $this->assign('verify', true);
            return $this->_form('SystemUser', 'user/pass');
        }
        $data = $this->request->post();
        if ($data['password'] !== $data['repassword']) {
            $this->error('两次输入的密码不一致，请重新输入！');
        }
        $user = Db::name('SystemUser')->where('id', session('user.id'))->find();

        if (!password_verify($data['oldpassword'], $user['password'])) {
            $this->error('旧密码验证失败，请重新输入！');
        }
        if (DataService::save('SystemUser', ['id' => session('user.id'),
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'password_encode' => encode($data['password'])
        ])
        ) {
            $this->success('密码修改成功，下次请使用新密码登录！', '');
        }
        $this->error('密码修改失败，请稍候再试！');
    }

    /**
     * 修改资料
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info()
    {
        if (intval($this->request->request('id')) === intval(session('user.employee.id'))) {
            $db = Db::name("saas_employee");
            return $this->_form($db, 'user/form');
        }
        $this->error('只能修改当前用户的资料！');
    }

    public function count_data()
    {
        $branch = $this->request->post('branch');
        if ($this->user['id'] != 10000 && !in_array($this->user['authorize'], [1, 3, 4])) {
            $branch = $this->user['employee']['department'];
        }

        if (Cache::has('index_count_data' . $branch)) {
            $data = Cache::get('index_count_data' . $branch);
        } else {
            $sql = "SELECT COUNT(1)as count , FROM_UNIXTIME(created_at,'%Y-%m-%d')as day
        FROM `saas_customer`
       WHERE DATE_SUB(CURDATE(), INTERVAL  11 DAY) <= date(FROM_UNIXTIME(created_at))";
            if (isset($branch) && $branch != '') {
                $sql .= " AND branch=$branch ";
            }
            $sql .= " GROUP BY FROM_UNIXTIME(created_at,'%Y-%m-%d')";
            $db = Db::name('saas_customer');
            $count_info = $db->query($sql); //统计的全部客户
            $sql2 = "select COUNT(1)as count , FROM_UNIXTIME(created_at,'%Y-%m-%d')as day
        from `saas_customer`
       where DATE_SUB(CURDATE(), INTERVAL  11 DAY) <= date(FROM_UNIXTIME(created_at)) AND  is_student=1";
            if (isset($branch) && $branch != '') {
                $sql2 .= " AND branch=$branch ";
            }
            $sql2 .= " GROUP BY FROM_UNIXTIME(created_at,'%Y-%m-%d')";
            $db = Db::name('saas_customer');
            $count_info2 = $db->query($sql2); //成交人数
            $data = [];
            foreach ($count_info as $k => $v) {
                $data['day'][] = $v['day'];
                $data['data'][] = $v['count'];
                foreach ($count_info2 as $k1 => $v1) {
                    if ($v['day'] == $v1['day']) {
                        $data['rate'][$k] = number_format($v1['count'] / $v['count'] * 100, 2);
                    } elseif (!isset($data['rate'][$k])) {
                        $data['rate'][$k] = 0;
                    }
                }

            }
            //饼状图统计数据

            $sql3 = "SELECT	COUNT(1) AS count,follow_status
                    FROM	saas_customer AS c
                    RIGHT  JOIN (SELECT	t.*	FROM	saas_customer_follow t	
                    RIGHT JOIN ((SELECT	max(id) AS id 	FROM	saas_customer_follow	GROUP BY	customer_id	)) AS n ON t.id = n.id
                   ) AS f
                   ON c.id = f.customer_id WHERE";
            if (isset($branch) && $branch != '') {
                $sql3 .= "  c.branch=$branch AND";
            }
            $sql3 .= "  c.status <> 3 GROUP BY follow_status";
            $count_info3 = Db::query($sql3);
            $sql4 = "SELECT	COUNT(1) AS count
               FROM	saas_customer AS c
                 RIGHT  JOIN (	SELECT	*	FROM	saas_customer_follow	WHERE	id IN (	(SELECT	max(id)	FROM	saas_customer_follow	GROUP BY	customer_id	))) AS f ON c.id = f.customer_id WHERE c.status <> 3";
            if (isset($branch) && $branch != '') {
                $sql4 .= " AND c.branch=$branch";
            }
            $is_null = Db::query($sql4);
            $db = Db::name('saas_customer')->where('is_student', '<>', 1)->where('status', '<>', 3);
            if (isset($branch) && $branch != '') {
                $db->where('branch', $branch);
            }
            $is_all = $db->count();
            if ($is_all > $is_null[0]['count']) {

                $no_set = $is_all - $is_null[0]['count'];
            } else {
                $no_set = 0;
            }
            if (empty($count_info3[0])) {
                $count_info3[0]['count'] = 0;
            } else {
                $count_info3[0]['count'] += $no_set;
            }
            foreach ($count_info3 as &$v) {
                if (empty($v['follow_status'])) {
                    $v['name'] = "未分配";
                } else {
                    $v['name'] = strip_tags(get_category(7)[$v['follow_status']]);
                }
                $data['follow_status']['key'][] = $v['name'];
                $v['value'] = $v['count'];
                unset($v['follow_status']);
                unset($v['count']);

            }
            $data['follow_status']['value'] = $count_info3;
            $data['time'] = date('m月d日H:i', time());
            Cache::set('index_count_data' . $branch, $data, 28800);
        }
        return json($data);
    }

    /**
     * 到期学员
     */
    public function expiredStudent()
    {
        $this->title = '到期学员';
        $db = Db::name('saas_order_log')
            ->alias('ol')
            ->join('saas_order o', 'ol.order_id=o.id')
            ->join('saas_customer c', 'o.student_id=c.id')
            ->join('saas_courses sc', 'ol.goods_id=sc.id')
            ->field('ol.id,ol.order_id,ol.goods_id,ol.goods_num,ol.consume_num,(ol.goods_num-ol.consume_num) as last_num,o.student_id,c.id,c.name,c.parent_tel,c.branch,c.gender,c.branch,c.nickname,sc.title')
            ->where('ol.goods_type', '=', '1')
            ->where('o.status', '=', '5')
            ->order('last_num')
            ->group('ol.goods_id')
            ->group('ol.order_id');
        if ($this->user['id'] != 10000 && !in_array($this->user['authorize'], [1, 3, 4])) {
            $branch = $this->user['employee']['department'];
            $db->where('c.branch', '=', $branch);
        }
        $data = $db->select();
        foreach ($data as $k => $v) {
            if ($v['last_num'] <= 0 || $v['last_num'] > 5) {
                unset($data[$k]);
            }
        }
        return $data;
    }

    /**
     * 给首页实时数据统计图写的方法
     * 待续费,待跟进,待收款
     */
    public function Continued()
    {
        $nopay_num = Db::name('saas_order')->where('status', 4)->count(1);//未支付
        $comingSoon = $this->expiredStudent();
        $comingSoon_num = count($comingSoon);// 待续费
        $fallow = $this->countTodayFollow(); //待跟进
        return $data = [
            'nopay_num' => $nopay_num,
            'comingSoon_num' => $comingSoon_num,
            'fallow' => $fallow,
        ];
    }
}
