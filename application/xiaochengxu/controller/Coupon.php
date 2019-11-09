<?php


namespace app\xiaochengxu\controller;


use controller\BasicAdmin;
use think\Db;

class Coupon extends BasicAdmin
{
    public $table = 'saas_xcx_activity';

    /**
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '优惠券活动';
        $db = Db::name($this->table)
            ->where('deleted', '=', 0)
            ->order('id desc');
        if ($this->request->has('title')) {
            $db->whereLike('title', $this->request->get('title'));
        }
        return parent::_list($db, true);
    }

    public function addCoupon()
    {
        $title = '创建新活动';
        $survey = Db::name('saas_survey')
            ->alias('s')
            ->where('s.status', '<>', '3')
            ->where('s.end_time', '>', time())
            ->where('s.id', '=', 7)
            ->find();
        $questions = Db::name('saas_survey_question')
            ->where('qid', '=', 7)
            ->order('sort asc')
            ->select();
        foreach ($questions as &$question) {
            $question['items'] == '' || $question['items'] = explode('|', $question['items']);
        }
        $vo = [
            'id' => 0
        ];
        return $this->fetch('form', [
            'survey' => $survey,
            'questions' => $questions,
            'title' => $title,
            'vo' => $vo
        ]);
    }
}