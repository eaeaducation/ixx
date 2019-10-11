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

namespace app\mobile\controller;

use think\Db;


/**
 * 后台入口
 * Class Index
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 10:41
 */
class Index extends MobileBase
{

    public function index()
    {
        $user = $this->user;
        //  dump($user);die;
        return $this->fetch('', ['user' => $user]);
    }



    public function customer_add()
    {
        if ($this->request->isGet()) {
            return $this->fetch('');
        }
        if ($this->request->isPost()) {
            $data = $this->request->put();
            //去重
            $parent_tel = trim($data['parent_tel']);
            if (empty($parent_tel)) {
                return json(['code' => 0, 'msg' => '电话号码必须填写!']);
            }
            $num = Db::name('saas_customer')->where('parent_tel', $parent_tel)->count(1);
            if ($num > 0) {
                return json(['code' => 0, 'msg' => '已经存在这个客户,添加失败!']);
            }
            //sales_id采单员 为自己
            $data['sales_id'] = session('user.id');
            //销售员 collect_id 为自己
            $data['collect_id'] = session('user.id');
            //校区 为自己的校区
            $data['branch'] = session('user.employee.department');
            $data['first_branch'] = session('user.employee.department');
            $data['created_at'] = time();
            $res = Db::name('saas_customer')->insert($data);
            if ($res) {
                return json(['code' => 1, 'msg' => '添加客户成功']);
            } else {
                return json(['code' => 0, 'msg' => '添加客户失败,请稍后重试']);
            }
        }
    }

}