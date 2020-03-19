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
use app\admin\controller\Index;


class Count extends BasicAdmin
{
    public function index()
    {
        // dump(123);die;

        return $this->fetch();
    }

    /**
     * 新增客户渠道统计
     */
    public function customer_data()
    {
        $sql = "select source,count(*)as num FROM saas_customer group by source ORDER BY count(*) desc LIMIT 0,7";
        $res = Db::query($sql);
//         dump($res);die;
        //找出是0或者空的 记录起来
        $null = 0;
        $kong = 0;
        foreach ($res as $v) {
            //   $key=$v['']
            if ($v['source'] === null) {
                $null = $v['num'];
            }
            if ($v['source'] === 0) {
                $kong = $v['num'];
            }
        }
        //循环给查渠道对应的名称
        $info = [];
        $keys = [];
        foreach ($res as $k => $v) {
            if ($v['source'] != null) {
                $keys[$k] = convert_channel($v['source']);//前台需要,不必看懂为什么
                $info[$k]['name'] = convert_channel($v['source']);
                $info[$k]['value'] = $v['num'];
            }
        }
        $arr = [['name' => "未知", 'value' => $null + $kong]];
        $arr2 = ['未知'];
        $key = array_merge($arr2, $keys);
        $info = array_merge($arr, $info);
        $data['value'] = $info;
        $data['key'] = $key;
        return json($data);
    }

    /**
     * 校区收费情况
     */
    public function branch_cost()
    {
        $sql = "SELECT SUM(f.price)AS total,COUNT(f.id)as count, c.branch FROM saas_cash_flow  as f LEFT JOIN saas_customer  c ON f.cid=c.id WHERE f.type=1 GROUP BY c.branch";
        $res = Db::query($sql);
        $branch = [];
        $total = [];
        $count = [];
        foreach ($res as $k => $v) {
            if ($v['branch'] == 0) {
                $v['branch'] = "未分校区";
            } else {
                $v['branch'] = convert_branch($v['branch']);
            }
            $branch[$k] = $v['branch'];
            $total[$k] = $v['total'];
            $count[$k] = $v['count'];
        }
        $data['branch'] = $branch;
        $data['total'] = $total;
        $data['count'] = $count;
        //统计新签(报名)金额
        $new_order = Db::query("SELECT SUM(price) AS new_order FROM saas_cash_flow  WHERE type =1");
        //统计续费金额
        $old_order = Db::query("SELECT SUM(price) AS old_order FROM saas_cash_flow  WHERE type =2");
        $data['new_order'] = $new_order[0]['new_order'];
        $data['old_order'] = $old_order[0]['old_order'];
        return json($data);
    }

    /**
     * 待续费,待跟进,待收款
     */
    public function Continued()
    {
        $index = new Index();
        $data = $index->Continued();
        return json($data);
    }

    /**
     * 7天报名统计
     */
    public function sign_up_7day()
    {
        $sql = "SELECT FROM_UNIXTIME(created_at, '%m月%d日')as date,COUNT(1) as count 
                FROM	saas_cash_flow
                WHERE	DATE_SUB(CURDATE(), INTERVAL 7 DAY) < date(FROM_UNIXTIME(created_at)) AND type = 1
              GROUP BY  FROM_UNIXTIME(created_at, '%Y年%m月%d');";
        $res = Db::query($sql);
        $data = [];
        $date = [];
        foreach ($res as $k => $v) {
            $date[$k] = $v['date'];
            $data[$k] = $v['count'];
        }
        $sql2 = "SELECT c.branch as branch,COUNT(1)as count
FROM	saas_cash_flow as f LEFT JOIN saas_customer c ON f.cid=c.id
WHERE	DATE_SUB(CURDATE(), INTERVAL 7 DAY) < date(FROM_UNIXTIME(f.created_at))AND type = 1
GROUP BY c.branch";
        $res2 = Db::query($sql2);
        $branch = [];
        $count = [];
        foreach ($res2 as $k => $v) {
            $branch[$k] = $v['branch'];
            $count[$k] = $v['count'];
        }
// 取最多的 +2是为了留点空余
        $max = max($count) + 2;
        $branch_arr = [];
        foreach ($branch as $k => $v) {
            if ($v == 0) {
                $branch_arr[$k] = ["name" => "未分校区", "max" => $max];
            } else {
                $branch_arr[$k] = ["name" => convert_branch($v), "max" => $max];
            }
        }

        $info = ['data' => $data,
            "date" => $date,
            "branch" => $branch_arr,
            'count' => $count
        ];

        return json($info);
    }

    /**
     * 刘强
     */

    public function follow()
    {
        $sql = "SELECT f.follow_status,count(1) AS counts 
                FROM saas_customer c
                LEFT JOIN ( SELECT * FROM saas_customer_follow WHERE id IN ( ( SELECT max( id ) FROM saas_customer_follow GROUP BY customer_id ) ) ) f ON c.id = f.customer_id where c.status <> 3
                GROUP BY f.follow_status";
        $row = Db::query($sql);
        $data = [];
        foreach ($row as $k => $v) {
            if ($v['follow_status'] > 0) {
                $data[$v['follow_status'] - 1] = $v['counts'];
            }
        }

        $status = get_category(7);
        foreach ($status as $k => $v) {
            $status[$k] = strip_tags($v);
        }
        $status = array_values($status);
        $statusLength = count($status);
        $arr = [];
        for ($i = 0; $i < $statusLength; $i++) {
            if (empty($data[$i])) {
                $arr[$i] = 0;
            } else {
                $arr[$i] = $data[$i];
            }
        }
        $db = Db::name('saas_order')->where('status', '=', 5);
        //签单次数
        $success_order = $db->count();

        $total = Db::name('saas_cash_flow')->sum('price');

        $order = Db::name('saas_order')->where('status', '<>', 3)->count();

        $per = round($success_order / $order, 2) * 100 . '%';

        $res = ['data' => $arr, 'status' => $status, 'order' => $success_order, 'total' => round($total), 'per' => $per];
        return json($res);
    }

    public function sale()
    {
        $db = Db::name('saas_order')->alias('o')
            ->join('saas_customer c', 'c.id = o.student_id', 'left')
            ->join('saas_employee e', 'c.sales_id=e.userid', 'left')
            ->field('sum(o.price) as price,e.name')
            ->where('o.status', '=', 5)
            ->order('price asc')
            ->group('c.sales_id');
        $res = $db->select();
        $data = [];
        $name = [];

        foreach ($res as $k => $v) {
            if ($k < 10) {
                $data[$k] = round($v['price'], 2);
                $name[$k] = $v['name'];
            }
        }
        return json(['data' => $data, 'name' => $name]);
    }

    public function course()
    {
        $sql = 'SELECT count(*) as count from saas_customer where is_student = 1 and status <> 3 GROUP BY branch';
        $data = Db::query($sql);
        $branch = array_values(get_branches());

        $len = count($branch);

        foreach ($data as $k => $v) {
            if ($k > 0) {
                $student[$k - 1] = $v['count'];
            }
        }

        $student_len = count($student);
        $diff = $len - $student_len;

        for ($i = 0; $i < $diff; $i++) {
            array_push($student, 0);
        }

        $course_sql = 'SELECT branch,count(*) as count from saas_courses where status <> 3 GROUP BY branch';
        $courses = Db::query($course_sql);

        $cou = [];
        foreach ($courses as $k => $v) {
            $cou[$k] = $v['count'];
        }

        $cou_len = count($cou);
        for ($i = 0; $i < $len - $cou_len; $i++) {
            array_push($cou, 0);
        }

        return json(['branch' => $branch, 'stu' => $student, 'cou' => $cou]);
    }

    /**
     * 统计近30条销售电话合计
     */
    public function get_follow()
    {
        $day = 60;
        $result = [];
        for ($i = $day - 1; 0 <= $i; $i--) {
            $result[] = date('m-d', strtotime('-' . $i . ' day'));
            $nums[] = 0;
        }
        $sql = "SELECT	count(1) AS count,	FROM_UNIXTIME(created_at, '%m-%d') AS datetime
          FROM	saas_customer_follow
          WHERE	DATE_SUB(CURDATE(), INTERVAL 60 day) < date(FROM_UNIXTIME(created_at))
          GROUP BY	FROM_UNIXTIME(created_at, '%Y年%m月%d')";
        $res = Db::query($sql);
        array_walk($res, function ($value, $key) use ($result, &$nums) {
            $index = array_search($value['datetime'], $result);
            $nums[$index] = $value['count'];
        });
        $data = [
            'days' => $result,
            'nums' => $nums
        ];
        //  dump($data);die;
        return json($data);
    }
}