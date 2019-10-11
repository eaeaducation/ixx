<?php
/**
 * User: Jasmine2
 * Date: 2018/7/12 14:28
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\survey\controller;


use controller\BasicAdmin;
use think\Db;
use service\DataService;
use QRCode;

/**
 * Class Survey
 * @package app\survey\controller
 * @author Jasmine2
 * 调查问卷列表
 */
class Survey extends BasicAdmin
{
    public $table = 'saas_survey';

    public function index()
    {
        $this->title = '调查问卷';
        $db = Db::name($this->table)
            ->where('status', '<>', 3)
            ->order('id desc');
        if ($this->request->has('title')) {
            $db->whereLike('title', $this->request->get('title'));
        }
        return parent::_list($db, true);
    }

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function add()
    {
        $this->title = '创建问卷';
        return $this->_form($this->table, 'form');
    }

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function edit()
    {
        $this->title = '编辑问卷';
        return $this->_form($this->table, 'form');
    }


    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            list($start, $end) = explode(' - ', $vo['time_range']);
            $vo['start_time'] = strtotime($start);
            $vo['end_time'] = strtotime($end);
        } else {
            $vo['time_range'] = format_date($vo['start_time']) . ' - ' . format_date($vo['end_time']);
        }
    }

    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("问卷删除成功!", '');
        }
        $this->error("问卷删除失败, 请稍候再试!");
    }

    /**
     * @二维码
     */
    public function qrcode()
    {
        return $this->fetch('');
    }

    public function createQrCode()
    {
        $request = $this->request;
        $id = $request->get('id');
        if ($id) {
            $url = $this->request->domain() . url('survey/question/index', ['id' => $id]);
            $data = [
                'msg' => 1,
                'url' => $url,
                'data' => QrCode::createQRCodeString($url, 150)
            ];
            return json($data);
        } else {
            return json([
                'msg' => 0,
                'url' => '',
                'data' => ''
            ]);
        }
    }

    /**
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author mei
     */
    public function surveyStatistics()
    {
        $this->title = '问卷调查统计';
        $id = $this->request->get('id');
        $sur = array();
        //问卷标题
        $survey = Db::name('saas_survey')
            ->field('title,start_time,end_time,desc')
            ->where('id', '=', $id)
            ->findOrFail();
        list($title, $start_time, $end_time, $desc) = array_values($survey);
        //题目id
        $questions = Db::name('saas_survey_question')
            ->where('qid', '=', $id)
            ->select();
        $question_ids = array_column($questions, 'id');
        $all = Db::name('saas_survey_answer')
            ->alias('a')
            ->join('saas_survey_question q', 'q.id=a.tid', 'left')
            ->field('a.*,q.*,count(fid) as n')
            ->where('a.qid', '=', $id)
            ->where('a.tid', 'in', $question_ids)
            ->group('a.tid')
            ->select();
        //每个答案回答总数
        $answer_count = Db::name('saas_survey_answer')
            ->field('tid,answer,count(fid) as m')
            ->where('qid', '=', $id)
            ->where('tid', 'in', $question_ids)
            ->group('tid,answer')
            ->select();
        $answer = Db::name('saas_survey_answer')
            ->field('*')
            ->where('qid', '=', $id)
            ->where('tid', 'in', $question_ids)
            ->select();
        //重组数据
        foreach ($questions as $ke => $ve) {
            $questions[$ke]['count'] = 0;
            foreach ($all as $ke1 => $ve1) {
                if ($ve['id'] == $ve1['tid'] && $ve1['n']) {
                    $questions[$ke]['count'] = $ve1['n'];
                }
            }
        }
        foreach ($questions as $k => $v) {
            $sur[$k]['name'] = $v['name'];
            $sur[$k]['sort'] = $v['sort'];
            $sur[$k]['type'] = $v['type'];
            $items_rows = explode('|', $v['items']);
            if (is_array($items_rows)) {
                foreach ($items_rows as $k2 => $v2) {
                    $sur[$k]['items_rows'][$k2]['name'] = $v2;
                    $sur[$k]['items_rows'][$k2]['num'] = 0;
                    foreach ($answer_count as $k1 => $v1) {
                        if ($v2 == $v1['answer']) {
                            $sur[$k]['items_rows'][$k2]['name'] = $v2;
                            $sur[$k]['items_rows'][$k2]['num'] = $v1['m'];
                        }
                        if (($v['type'] == 3 || $v['type'] == 4) && $v['id'] == $v1['tid']) {
                            $sur[$k]['answer'][] = $v1['answer'];
                        }
                    }
                }
            }
            $sur[$k]['id'] = $v['id'];
            $sur[$k]['tid'] = $v['id'];
            $sur[$k]['n'] = $v['count'];
        }
        foreach ($sur as $kk => $vv) {
            $sur[$kk]['names'] = '';
            $sur[$kk]['num'] = '';
            if (is_array($vv['items_rows']) || count($vv['items_rows']) > 1) {
                foreach ($vv['items_rows'] as $kk1 => $vv1) {
                    $sur[$kk]['names'] .= "'" . $vv1['name'] . "',";
                    $sur[$kk]['num'] .= "" . $vv1['num'] . ",";
                }
            }
            $sur[$kk]['names'] = "[" . trim($sur[$kk]['names'], ',') . "]";
            $sur[$kk]['num'] = "[" . trim($sur[$kk]['num'], ',') . "]";
        }
        $fid = array_unique(array_column($answer, 'fid'));
        $total_count = count($fid);
        return $this->fetch('', ['list' => $sur,
            'title' => $title,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'total_count' => $total_count,
            'id' => $id,
            'desc' => strip_tags($desc)]);
    }

    /*
     * 设置问卷回收 开始 与 停止
     * */
    public function status()
    {
        if (DataService::update($this->table)) {
            $this->success("操作成功!", '');
        }
        $this->error("操作失败, 请稍候再试!");
    }

    /**
     * 问卷调查数据导出
     * @author mei
     */
    public function exportData()
    {
        $id = $this->request->get('id');
        $title = Db::name('saas_survey')
            ->field('title')
            ->where('id', '=', $id)
            ->findOrFail();
        $title = $title['title'];
        $questions = Db::name('saas_survey_question')
            ->where('qid', '=', $id)
            ->select();
        $question_ids = array_column($questions, 'id');
        $question_names = array_column($questions, 'name');
        $answer = Db::name('saas_survey_answer')
            ->field('*')
            ->where('tid', 'in', $question_ids)
            ->where('qid', '=', $id)
            ->select();
        //表头
        $key['fid'] = '参与者ID';
        $key['parent_name'] = '客户姓名';
        $key['parent_tel'] = '电话号码';
        for ($i = 0; $i < count($question_names); $i++) {
            $key['name' . $i] = $question_names[$i];
        }
        $res = [];
        $fid_row = array_unique(array_column($answer, 'fid'));
        //关联参与者与客户表
        $customer = Db::name('saas_survey_person')->alias('p')
            ->join('saas_customer c', 'p.cid = c.id', 'left')
            ->field('c.parent_name,c.parent_tel,p.cid,p.id')
            ->where('p.id', 'in', $fid_row)
            ->select();
        $i = 0;
        foreach ($fid_row as $k0 => $v0) {
            $res[$i]['fid'] = $v0;
            foreach ($customer as $kk => $vv) {
                if ($v0 == $vv['id']) {
                    if (!empty($vv['cid'])) {
                        $res[$i]['parent_name'] = $vv['parent_name'];
                        $res[$i]['parent_tel'] = $vv['parent_tel'];
                    } else {
                        $res[$i]['parent_name'] = '-';
                        $res[$i]['parent_tel'] = '-';
                    }
                }
            }
            foreach ($questions as $k => $v) {
                $res[$i]['name' . $k] = '-';
                foreach ($answer as $k1 => $v1) {
                    if ($v['id'] == $v1['tid'] && $v1['fid'] == $v0) {
                        $res[$i]['name' . $k] = $v1['answer'] . "|" . $res[$i]['name' . $k];
                    }
                }
                if (strpos($res[$i]['name' . $k], '|-') !== false) {
                    $res[$i]['name' . $k] = str_replace('|-', '', $res[$i]['name' . $k]);
                }
            }
            $i++;
        }
        down_excel($res, $key, $title);
    }

    /**
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 置空调查数据
     */
    public function setempty()
    {
        $id = $this->request->get('id');
        Db::name('saas_survey_answer')->where('qid', '=', $id)->delete();
        Db::name('saas_survey_person')->where('qid', '=', $id)->delete();
        $this->success('置空成功', '');
    }

    /**
     * @return array|string
     * 问卷引流详情
     * @author mei
     */
    public function detail()
    {
        $this->title = '引流详情';
        $id = $this->request->get('id');
        $keyword = $this->request->get('keyword');
        $db = Db::name('saas_customer')->where('wjid', '=', $id);
        if (isset($keyword) && $keyword != '') {
            $keyword = trim($keyword);
            if (preg_match('/^\d{11}$/', $keyword)) {
                $db->where('parent_tel', '=', "{$keyword}");
            } elseif (preg_match('/^[\x{4e00}-\x{9fa5}]{2,10}(?:·[\x{4e00}-\x{9fa5}]{2,10})*$/u', $keyword)) {
                $db->where('parent_name', '=', "{$keyword}");
            } else {
                $db->where('1=0');
            }
        }
        $db->order('id desc');
        $this->assign(['qid' => $id]);
        return $this->_list($db);
    }

    /**
     * 问卷回答详情
     * @return mixed
     * @author mei
     */
    public function answerList()
    {
        $this->title = '问卷回答详情';
        $get = $this->request->get();
        $cid = $get['cid'];
        $qid = $get['qid'];
        $parent_name = $get['parent_name'];
        $sur = array();
        //获取问卷参与者id
        $fid = Db::name('saas_survey_person')
            ->field('id')
            ->where('cid', '=', $cid)
            ->select();
        $fid = array_column($fid, 'id');
        //获取问卷名称
        $survey = Db::name('saas_survey')
            ->field('title,desc')
            ->where('id', '=', $qid)
            ->find();
        list($title, $desc) = array_values($survey);
        //问卷问题
        $questions = Db::name('saas_survey_question')
            ->where('qid', '=', $qid)
            ->select();
        //问卷答案
        $answers = Db::name('saas_survey_answer')
            ->field('answer,tid')
            ->where('qid', '=', $qid)
            ->where('fid', 'in', $fid)
            ->select();
        foreach ($questions as $k => $v) {
            $sur[$k]['sort'] = $v['sort'];
            $sur[$k]['name'] = $v['name'];
            foreach ($answers as $k1 => $v1) {
                if ($v1['tid'] == $v['id']) {
                    $sur[$k]['answer'] = $v1['answer'];
                }
            }
        }
        return $this->fetch('', [
            'list' => $sur,
            'title' => $title,
            'parent_name' => $parent_name,
            'desc' => strip_tags($desc)]);
    }
}