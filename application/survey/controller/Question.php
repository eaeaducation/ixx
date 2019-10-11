<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/12
 * Time: 11:11
 */

namespace app\survey\controller;

use controller\BasicAdmin;
use think\Db;
use think\facade\Log;

class Question extends BasicAdmin
{
    /**
     * 展示页
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Hzb
     */
    public function index($id = 0)
    {
        if ($id == 0) {
            exit($this->fetch('error', ['msg' => '请求方式不正确']));
        }
        $survey = Db::name('saas_survey')
            ->alias('s')
            ->where('s.status', '<>', '3')
            ->where('s.end_time', '>', time())
            ->where('s.id', '=', $id)
            ->find();
        if ($survey) {
            $questions = Db::name('saas_survey_question')
                ->where('qid', '=', $id)
                ->order('sort asc')
                ->select();
            foreach ($questions as &$question) {
                $question['items'] == '' || $question['items'] = explode('|', $question['items']);
            }
            return $this->fetch('', [
                'survey' => $survey,
                'questions' => $questions
            ]);
        } else {
            return $this->fetch('error', ['msg' => '问卷不存在已过期']);
        }
    }

    /**
     * 保存调查数据
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Hzb
     */
    public function add()
    {
        $db = Db::name('saas_survey_person');
        $this->_form($db, 'index');
    }

    protected function _add_form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            if ($vo['status'] != '2') {
                $this->error('问卷暂时关闭');
            }
            $tid = explode('_', $vo['fingerprint']);
            $recode = Db::name('saas_survey_answer')
                ->alias('sw')
                ->field('sp.id')
                ->join('saas_survey_person sp', 'sw.fid=sp.id', 'left')
                ->where('sp.fingerprint', '=', $vo['fingerprint'])
                ->where('sw.qid', '=', $tid[1])
                ->select();

            if (is_array($vo['answer'])) {
                $answer_str = '';
                foreach ($vo['answer'] as $k => $v) {
                    if (!is_array($v)) {
                        $answer_str .= $v;
                    } else {
                        $answer_str .= json_encode($v);
                    }
                }
                if (strlen($answer_str) < 1) {
                    exit($this->error('请至少回答一题'));
                }
            }

            if ($recode) {
                $this->error('您已经回答过啦，谢谢');
            }
            $vo['userip'] = $this->request->ip();
        }
    }

    protected function _add_form_result($result, $data)
    {
        $insert = array();
        $i = 0;
        foreach ($data['answer'] as $key => $value) {
            if (!empty($value)) {
                $i++;
                $insert[$i]['qid'] = $data['qid'];
                $insert[$i]['tid'] = $key;
                $insert[$i]['answer'] = $value;
                $insert[$i]['fid'] = $result;
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $i = $i + $k + 1;
                        $insert[$i]['qid'] = $data['qid'];
                        $insert[$i]['tid'] = $key;
                        $insert[$i]['answer'] = $v;
                        $insert[$i]['fid'] = $result;
                    }
                }
            }
        }

        foreach ($insert as $k => $v) {
            if (is_array($v['answer'])) {
                unset($insert[$k]);
            }
        }
        Db::name('saas_survey_answer')->insertAll($insert);
        $this->success();
    }

    /**
     * 完成问卷跳转页面
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Hzb
     */
    public function complete($id = 0)
    {
        if ($id == 0) {
            exit($this->fetch('error'));
        }
        $finger = $this->request->get('fingerprint');
        $list = Db::name('saas_survey')
            ->field('*')
            ->where('status', '<>', '3')
            ->where('start_time', '<', time())
            ->where('end_time', '>', time())
            ->where('id', '=', $id)
            ->find();
        $fid = Db::name('saas_survey_person')
            ->field('id')
            ->where('fingerprint', '=', $finger)
            ->find();
        $list['wjid'] = $id;
        $list['fid'] = $fid['id'];
        return $this->fetch('', ['vo' => $list]);
    }
}