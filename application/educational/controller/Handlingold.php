<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/4
 * Time: 14:10
 */

namespace app\educational\controller;

use controller\BasicAdmin;
use service\LogService;
use think\Db;
use think\Exception;
use think\facade\Log;
use  think\facade\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class Handlingold extends BasicAdmin
{
    public $table = 'saas_xiaobaocus';

    /**
     * @return array|string
     * 列表展示导入的客户
     */
    public function index()
    {
        $this->title = '老客户导入列表';
        $db = Db::name($this->table)->where('is_deal', '=', 0);

        $get = $this->request->get();
        (isset($get['name']) && $get['name'] !== '') && $db->where('name', 'like', "%{$get['name']}%");
        (isset($get['nickname']) && $get['nickname'] !== '') && $db->where('nickname', 'like', "%{$get['nickname']}%");
        foreach (['parent_tel','branch'] as $key) {
            if (isset($get[$key]) && $get[$key] != '') $db->where( $key, '=', $get[$key]);
        }
        if (!in_array($this->user['authorize'], [1, 3, 4, 22])) {
            $db->where('branch', '=', $this->user['employee']['department']);
        }
        $db->order('id desc');

        return parent::_list($db);
    }

    /**
     * @return array|string
     * 编辑  分班
     */
    public function edit()
    {
        if ($this->request->isGet()) {
            $db = Db::name('saas_class')->where('status', '<>', 3);
            if (!in_array($this->user['authorize'], [1, 3, 4])) {
                $db->where('branch', '=', $this->user['employee']['department']);
            }
            $class = $db->select();
            $import_id = $this->request->get('id');
            $this->assign('import_id',$import_id);
            $this->assign('class', $class);
            return $this->_form($this->table, 'form');
        } elseif ($this->request->isPost()) {
            $post = $this->request->post();
            if ($post['classid']) {
                $course_id = Db::name('saas_class_course')->field('course_id')->where('class_id', '=', $post['classid'])->column('course_id');

                $courses = Db::name('saas_class_course')->alias('cc')
                    ->where('cc.class_id', '=', $post['classid'])
                    ->join('saas_courses sc', 'cc.course_id=sc.id')
                    ->select();

                // 订单表插入
                $order = array();
                $order['orderno'] = generate_order_no();
                $order['class_id'] = $post['classid'];
                $order['student_id'] = $post['id'];
                $order['price'] = $post['price'];
                $order['created_at'] = $post['created_at'];
                $insert_order = Db::name('saas_order')->insertGetId($order);

                // 课程信息
                $courseinfo = array();
                foreach ($courses as $key => $value) {
                    $courseinfo['order_id'] = $insert_order;
                    $courseinfo['goods_id'] = $value['id'];
                    $courseinfo['goods_type'] = 1;
                    $courseinfo['old_price'] = $value['price'];
                    $courseinfo['price'] = $value['price'];
                    $courseinfo['goods_num'] = $value['total_class'];
                    $courseinfo['created_at'] = $post['created_at'];
                    $coursesall[] = $courseinfo;
                }

                // 杂费信息
                $textbook = Db::name('saas_course_textbook')
                    ->alias('ct')
                    ->field('st.title as textbook_title,st.price as textbook_price,st.cost_type,ct.course_id,st.id as textbook_id,st.content as textbook_content, st.type as textbook_type')
                    ->join('saas_textbook st', 'ct.textbook_id=st.id', 'left')
                    ->whereIn('course_id', $course_id)
                    ->group('st.id')
                    ->select();

                $zf = array();
                foreach ($textbook as $key => $value) {
                    if (!empty($value['textbook_id'])) {
                        $zf['order_id'] = $insert_order;
                        $zf['goods_id'] = $value['textbook_id'];
                        if ($value['textbook_type'] == 1) {
                            $zf['goods_type'] = 2;
                        } elseif ($value['textbook_type'] == 2) {
                            $zf['goods_type'] = 3;
                        }
                        $zf['old_price'] = $value['textbook_price'];
                        $zf['price'] = $value['textbook_price'];
                        $zf['goods_num'] = 1;
                        $zf['created_at'] = $post['created_at'];
                        $zfall[] = $zf;
                    }
                }
                // 课程和杂费信息插入订单日志表
                if (isset($zfall)) {
                    $zf_courses = array_merge($coursesall, $zfall);
                } else {
                    $zf_courses = $coursesall;
                }

                // 插入课程-学生表
                $class_ids = $post['classid'];
                $class = [];
                $class['class_id'] = $class_ids;
                $class['customer_id'] = $post['id'];
                $class['created_at'] = $post['created_at'];
                Db::name('saas_class_student')->insert($class);
//                Db::name($this->table)->where('id','=',$post['import_id'])->update(['is_deal'=>1]);

                Db::startTrans();
                try {
                    Db::name('saas_order_log')->insertAll($zf_courses);
                    Db::commit();
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                    Db::rollback();
                }
                $this->success('班级选择成功，订单已生成！', '', $insert_order);
            }
        }
    }


    /**
     * 导入客户
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function importxiaobao_form()
    {
        if ($this->request->isPost()) {
            $file = $this->request->post('file', false);
            if ($file == '') {
                $this->error('请先上传文件!');
            }
            $file = PROJECT_PATH . str_replace($this->request->domain(), '', $file);
            return $this->importxiaobao_customer($file);
        }
        return $this->fetch('importxiaobao_form');
    }

    private function importxiaobao_customer($file)
    {
        set_time_limit(0);
        ignore_user_abort(1);

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(TRUE);
        $PHPExcel = $reader->load($file);
        $sheet = $PHPExcel->getActiveSheet();

        $colNum = ord($sheet->getHighestColumn()) - ord('A') + 1;
        $rowNum = $sheet->getHighestRow();
        if ($rowNum > 1000) {
            $this->error('一次最多导入1000条数据');
        }
        $data = [];
        for ($i = 2; $i <= $rowNum; $i++) {
            $tmp = [];
            for ($j = 1; $j <= $colNum; $j++) {
                $tmp[] = $sheet->getCellByColumnAndRow($j, $i)->getValue();
            }
            array_push($data, $tmp);
        }
        foreach ($data as $k => $v) {
            $arraylength = implode('', $v);
            if (empty($arraylength)) {
                unset($data[$k]);
            }
        }
        $xiaobaocus = [];
        $employees = Db::name('saas_employee')->column('userid', 'name');
        $_parent_tel = Db::name('saas_customer')->field('parent_tel')->select();
        $parent_tel = [];
        $repeat_tel = [];
        foreach ($_parent_tel as $v) {
            $parent_tel[] = $v['parent_tel'];
        }
        foreach ($data as $key => $item) {
            switch ($item[3]) {
                case "女":
                    $item[3] = 0;
                    break;
                case "男":
                    $item[3] = 1;
                    break;
                case "未知":
                    $item[3] = 2;
                    break;
            }
            if (empty($item[5])) {
                $this->error('每条数据的监护人电话都必须填写');
            }
            if (empty($item[6])) {
                $this->error('每条数据的校区都必须填写');
            }
            if (in_array($item[5], $parent_tel)) {
                $repeat_tel[] = $item[5];
                unset($item);
                continue;
            }
            $insertArr = [
                'name' => isset($item[1]) ? $item[1] : "",
                'nickname' => isset($item[2]) ? $item[2] : "",
                'gender' => isset($item[3]) ? $item[3] : "",
                'parent_name' => isset($item[4]) ? $item[4] : "",
                'parent_tel' => $item[5],
                'branch' => get_branches_name($item[6]),
                'sales_id' => isset($employees[$item[8]]) ? $employees[$item[8]] : '',
                'collect_id' => isset($employees[$item[7]]) ? $employees[$item[7]] : '',
                'name_permalink' => isset($item[1]) ? convert_pinyin_permalink($item[1]) : '',
                'name_abbr' => isset($item[1]) ? convert_pinyin_abbr($item[1]) : '',
                'created_at' => time(),
                'is_student' => 1,
                'status' => 99,
                'first_branch' => get_branches_name($item[6]),
                'other_info' => isset($item[12]) ? substr($item[12], 0, 128) : '',
                'icard' => isset($item[13]) ? $item[13] : ''
            ];
            $customerid = Db::name('saas_customer')->insertGetId($insertArr);

            array_push($xiaobaocus, [
                'name' => isset($item[1]) ? $item[1] : "",
                'nickname' => isset($item[2]) ? $item[2] : "",
                'gender' => isset($item[3]) ? $item[3] : "",
                'parent_name' => isset($item[4]) ? $item[4] : "",
                'parent_tel' => $item[5],
                'branch' => get_branches_name($item[6]),
                'price' => isset($item[10]) ? $item[10] : "",
                'created_at' => isset($item[11]) ? Date::excelToTimestamp($item[11], "Asia/Shanghai") : "",
                'total_info' => isset($item) ? implode(';', $item) : '' . ";",
                'customer_id' => $customerid,
                'userid' => $this->user['id']
            ]);
        }
        $xiaobao = array_chunk($xiaobaocus, 500);
        Db::startTrans();
        try {
            foreach ($xiaobao as $value) {
                Db::name('saas_xiaobaocus')->insertAll($value);
            }
            Db::commit();

        } catch (Exception $e) {
            Log::error($e->getMessage());
            Db::rollback();
        }
        if (count($repeat_tel) > 0) {
            $url = url('repeat_tel');
            Cache::set('repeat_tel' . $this->user['id'], $repeat_tel, 5000);
            LogService::write('导入客户', '批量导入了' . count($xiaobaocus) . '条客户');
            $this->success('成功导入[' . count($xiaobaocus) . ']条记录,重复[' . count($repeat_tel) . ']条记录', $url);
        } else {
            LogService::write('导入客户', '批量导入了' . count($xiaobaocus) . '条客户');
            $this->success('成功导入[' . count($xiaobaocus) . ']条记录', '');
        }

    }

    /**
     * 导出电话号码重复的用户
     */
    public function repeat_tel()
    {
        $data = Cache::get('repeat_tel' . $this->user['id']);
        $key = ['tel' => '电话', 'info' => "信息"];
        $res = [];
        foreach ($data as $k => $v) {
            $res[$k]['tel'] = $v;
            $res[$k]['info'] = "该客户已存在";
        }
        down_excel($res, $key, '重复客户信息');
    }

    /**
     * 分配完成后删除
     */
    public function del()
    {
        $post = $this->request->post();
        Db::name($this->table)->where('id', '=', $post['id'])->update(['is_deal' => 1]);
        LogService::write('删除客户', '删除了一个客户');
        $this->success("删除成功!", '');
    }
}