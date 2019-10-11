<?php
/**
 * User: Jasmine2
 * Date: 2018/7/12 15:12
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\survey\controller;


use controller\BasicAdmin;
use think\Db;

/**
 * Class Design
 * @package app\survey\controller
 * @author Jasmine2
 * 调查问卷设计
 */
class Design extends BasicAdmin
{
    public $table = 'saas_survey_question';

    public function index()
    {
        $this->title = '创建问卷';
        return $this->_form($this->table, 'index');
    }

    public function edit()
    {
        $id = $this->request->get('id');
        if ($this->request->isPost()) {
            return $this->_form($this->table, 'index');
        } else {
            $survey = Db::name('saas_survey')->where('id', '=', $id)->find();
            $has_answer = Db::name('saas_survey_answer')->where('qid', '=', $id)->find();
            $disable = !empty($has_answer)?'yes':'no';
            if (!$survey) {
                $this->error('问卷不存在');
            }
            $survey['strtime'] = date('Y-m-d', $survey['start_time']) . ' - ' . date('Y-m-d', $survey['end_time']);
            $survey_question = Db::name('saas_survey_question')->where('qid', '=', $id)->select();
            if ($survey_question) {
                foreach ($survey_question as $k => $v) {
                    $survey_question[$k]['type'] = $this->get_qtype($v['type']);
                    $survey_question[$k]['type_index'] = $v['type'];
                    $survey_question[$k]['type_name'] = $this->get_qtype2($v['type']);
                    if ($v['items']) {
                        $survey_question[$k]['items'] = explode('|', $v['items']);
                    } else {
                        unset($survey_question[$k]['items']);
                    }
                }
            }
            $yl_url = $this->request->domain() . url('survey/question/index', ['id' => $id]);
            $ylend_url = $this->request->domain() . url('survey/question/complete', ['id' => $id]);
            $this->title = $survey['title'];
            return $this->view('index', [
                'vo' => $survey,
                'vo_cont' => $survey_question,
                'disable' => $disable,
                'yl_url' => $yl_url,
                'ylend_url' => $ylend_url,
            ]);
        }
    }


    public function _form_filter(&$vo)
    {
        $userid = isset($this->user['id']) ? $this->user['id'] : '10000';
        $id = $this->request->get('id');
        if ($this->request->isPost()) {
            $survey_data = $this->request->post('cont');
            if (isset($survey_data['times'])) {
                $survey_data['times'] = urldecode($survey_data['times']);
                list($start, $end) = explode(' - ', $survey_data['times']);
            }
            $start_time = isset($start) ? strtotime($start) : '';
            $end_time = isset($end) ? strtotime($end) : '';
            $susrvey['title'] = $survey_data['title'];
            $susrvey['bg_pic'] = $survey_data['bgpic'];
            $susrvey['desc'] = urldecode($survey_data['desc']);
            $susrvey['start_time'] = $start_time;
            $susrvey['end_time'] = $end_time;
            $susrvey['userid'] = $userid;
            $susrvey['finishword'] = $survey_data['finishword'];
            $susrvey['finish_pic'] = $survey_data['finishpic'];
            $susrvey['isyl'] = $survey_data['isyl'];
            $susrvey['created_at'] = time();
            if (!$id) {
                $qid = Db::name('saas_survey')->insertGetId($susrvey);
                if (isset($survey_data['data']) && is_array($survey_data['data'])) {
                    $res = [];
                    // dump($survey_data['data']);die;
                    foreach ($survey_data['data'] as $k => $v) {
                        $items = '';
                        $setdata['sort'] = $v['sort'];
                        $setdata['type'] = $this->get_qtype($v['qtype']);
                        $setdata['name'] = $v['qtitle'];
                        $setdata['is_require'] = $v['is_require']=='yes'?'1':'0';
                        $setdata['qid'] = $qid;
                        if (isset($survey_data['data'][$k]['items'])) {
                            $items = implode('|', $v['items']);
                        }
                        $setdata['items'] = $items;
                        $res[] = $setdata;
                    }

                    Db::name($this->table)->insertAll($res);
                }
            } else {
                Db::name('saas_survey')->where("id", "=", $id)->update($susrvey);
                Db::name($this->table)->where("qid", "=", $id)->delete();
                if (isset($survey_data['data']) && is_array($survey_data['data'])) {
                    foreach ($survey_data['data'] as $k => $v) {
                        $items = '';
                        $setdata['sort'] = $v['sort'];
                        $setdata['type'] = $this->get_qtype($v['qtype']);
                        $setdata['name'] = $v['qtitle'];
                        $setdata['is_require'] = $v['is_require']=='yes'?'1':'0';
                        $setdata['qid'] = $id;
                        if (isset($v['items'])) {
                            $items = implode('|', $v['items']);
                        }
                        $setdata['items'] = $items;
                        Db::name($this->table)->where("qid", "=", $id)->insert($setdata);
                    }
                }
            }
        }
    }

    protected function _form_result($data)
    {
        list($base, $spm, $url) = [url('@admin'), $this->request->get('spm'), url('survey/survey/index')];
        $this->success('保存成功！', "{$base}#{$url}?spm={$spm}");
    }

    protected function get_qtype($type)
    {
        $array1 = [
            'radio' => '1',
            'checkbox' => '2',
            'input' => '3',
            'text' => '4',
            'select' => '5',
        ];
        $array2 = [
            '1' => 'radio',
            '2' => 'checkbox',
            '3' => 'input',
            '4' => 'text',
            '5' => 'select'
        ];
        if (is_string($type)) {
            return $array1[$type];
        } else {
            return $array2[$type];
        }

    }

    protected function get_qtype2($type)
    {
        $array = [
            '1' => '【单选】',
            '2' => '【多选】',
            '3' => '【单行问答】',
            '4' => '【多行问答】',
            '5' => '【其他】',
        ];
        return $array[$type];
    }

}