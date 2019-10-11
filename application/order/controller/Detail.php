<?php
/**
 * User: Jasmine2
 * Date: 2018/7/12 15:12
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\order\controller;


use controller\BasicAdmin;
use Naixiaoxin\ThinkWechat\Facade;
use think\Db;
use QRCode;

/**
 * Class Design
 * @package app\survey\controller
 * @author Jasmine2
 * 下单完成后，订单详细信息
 */
class Detail extends BasicAdmin
{

    public function detail()
    {
        $order_id = $this->request->request('id');
        $money_yh = 0;

        //订单详情
        $order_info = Db::name("saas_order")->alias("o")->join("saas_customer c","o.student_id = c.id")->
        join("saas_class l","o.class_id = l.id",'left')->where("o.id","=",$order_id)->
        field("o.*,c.name,c.id as cid,l.title")->find();
        $order_real_price = $order_info['price'];


        //课程明细
        $course_yh = 0;
        $order_course = Db::name('saas_order_log')->alias("o")->join("saas_courses c","o.goods_id = c.id","left")->
        field("o.*,c.title")->where("o.order_id","=",$order_id)->where("o.goods_type","=",1)->select();
        if($order_course){
            $course_yh = $this->get_total_yh($order_course);
            $money_yh +=$course_yh;
        }
        //杂费明细
        $zf_yh = 0;
        $order_zf = Db::name('saas_order_log')->alias("o")->join("saas_textbook c","o.goods_id = c.id","left")->
        field("o.*,c.title")->where("o.order_id","=",$order_id)->where("o.goods_type","=",3)->select();
        if($order_zf){
            $zf_yh = $this->get_total_yh($order_zf);
            $money_yh +=$zf_yh;
        }

        //教材明细
        $jc_yh = 0;
        $order_jc = Db::name('saas_order_log')->alias("o")->join("saas_textbook c","o.goods_id = c.id","left")->
        field("o.*,c.title")->where("o.order_id","=",$order_id)->where("o.goods_type","=",2)->select();
        if($order_jc){
            $jc_yh = $this->get_total_yh($order_jc);
            $money_yh +=$jc_yh;
        }

        $order_info['monty_yh'] = $money_yh;
        $order_info['order_nums'] = (count($order_course)>0?(count($order_course)+1):0) + (count($order_zf)>0?(count($order_zf)+1):0) + (count($order_jc)>0?(count($order_jc)+1):0);

        //支付二维码
        $qr_order = QrCode::createQRCodeString($this->request->domain() .'/order/pay/paytype', 150);

        if($this->request->request('type')){
            return $this->fetch('detail_phone',['order_info'=>$order_info,'order_course'=>$order_course,'order_zf'=>$order_zf,'order_jc'=>$order_jc]);
        }else{
            return $this->fetch('detail',['order_info'=>$order_info,'order_course'=>$order_course,'order_zf'=>$order_zf,'order_jc'=>$order_jc,'qr_order'=>$qr_order]);
        }
    }

    public function detail_print(){
        $order_id = $this->request->request('id');
        $money_yh = 0;

        //订单详情
        $order_info = Db::name("saas_order")->alias("o")->join("saas_customer c","o.student_id = c.id")->
        join("saas_class l","o.class_id = l.id",'left')->where("o.id","=",$order_id)->
        field("o.*,c.name,l.title")->find();
        $order_real_price = $order_info['price'];

        //通过学生ID获取到所在校区
        $branch_student = Db::name('saas_customer')->where('id','=',$order_info['student_id'])->column('branch');
        if($branch_student){
            $branch = $branch_student['0'];
        }else{
            $branch = $this->user['employee']['department'];
        }
        $branch_info = Db::name('saas_school_branch')->where('id','=',$branch)->find();

        //经办人
        $print_user = $this->user['employee']['name'];
        //课程明细
        $course_yh = 0;
        $order_course = Db::name('saas_order_log')->alias("o")->join("saas_courses c","o.goods_id = c.id","left")->
        field("o.*,c.title")->where("o.order_id","=",$order_id)->where("o.goods_type","=",1)->select();
        if($order_course){
            $course_yh = $this->get_total_yh($order_course);
            $money_yh +=$course_yh;
        }
        //杂费明细
        $zf_yh = 0;
        $order_zf = Db::name('saas_order_log')->alias("o")->join("saas_textbook c","o.goods_id = c.id","left")->
        field("o.*,c.title")->where("o.order_id","=",$order_id)->where("o.goods_type","=",3)->select();
        if($order_zf){
            $zf_yh = $this->get_total_yh($order_zf);
            $money_yh +=$zf_yh;
        }
        //教材明细
        $jc_yh = 0;
        $order_jc = Db::name('saas_order_log')->alias("o")->join("saas_textbook c","o.goods_id = c.id","left")->
        field("o.*,c.title")->where("o.order_id","=",$order_id)->where("o.goods_type","=",2)->select();
        if($order_jc){
            $jc_yh = $this->get_total_yh($order_jc);
            $money_yh +=$jc_yh;
        }
        $order_info['monty_yh'] = $money_yh;
        $order_info['order_nums'] = (count($order_course)>0?(count($order_course)+1):0) + (count($order_zf)>0?(count($order_zf)+1):0) + (count($order_jc)>0?(count($order_jc)+1):0);
        return $this->fetch('',['order_info'=>$order_info,'order_course'=>$order_course,'order_zf'=>$order_zf,'order_jc'=>$order_jc,'branch_info'=>$branch_info,'print_user'=>$print_user]);
    }

    /**
     * @return \think\response\View
     * 爱学学订单二维码
     */
    private function qr_ewm_order($order_id, $cid, $name)
    {
        $app = Facade::officialAccount();
        $enven_key = 'order='.$order_id . '_cid='. $cid . '_name=' . $name;
        $tempqr = $app->qrcode->temporary($enven_key, 15 * 3600);
        $code = QrCode::createQRCodeString($tempqr['url'], 150);
        return $code;
    }

    private function get_total_yh($array){
        $yh = 0;
        foreach($array as $k => $v){
            $yh += $v['old_price'] - $v['price'];
        }
        return $yh;
    }

}