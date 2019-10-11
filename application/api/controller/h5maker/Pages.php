<?php
/**
 * User: Jasmine2
 * Date: 2018/8/4 14:37
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

namespace app\api\controller\h5maker;


use controller\BasicApi;
use think\App;
use think\Db;
use think\facade\Log;

class Pages extends BasicApi
{
    /**
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Jasmine2
     */
    public function index($id)
    {
        $theme = Db::name('theme_list')->findOrFail($id);
        $theme['pages'] = json_decode($theme['pages'], 1);
        return json(array_merge($theme, [
            '_id' => $theme['id']
        ]));
    }

    public function create()
    {
        $request = $this->request;
        $theme = json_decode($request->getContent(), 1);
        $saveData = $theme;
        if (isset($theme['pages']) && $theme['pages'] != '') {
            $saveData['pages'] = json_encode($theme['pages']);
        }
        $saveData['created_at'] = time();
        $id = Db::name('theme_list')->insertGetId($saveData);
        $theme['id'] = $id;
        $theme['_id'] = $id;
        $this->writeHtml($theme);
        return json($theme);
    }

    /**
     * @param $id
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author Jasmine2
     */
    public function update($id)
    {
        $request = $this->request;
        $theme = json_decode($request->getContent(), 1);
        $saveData = $theme;
        unset($saveData['_id']);
        unset($saveData['id']);
        unset($saveData['created_at']);
        $saveData['updated_at'] = time();
        if (isset($saveData['pages']) && $saveData['pages'] != '') {
            $saveData['pages'] = json_encode($saveData['pages']);
        }
        Db::name('theme_list')->where('id', '=', $id)->update($saveData);
        $theme['id'] = $id;
        $this->writeHtml($theme);
        return json($theme);
    }

    private function writeHtml($theme)
    {
        if ($theme['type'] == 'h5') {
            $content = view('h5', [
                'theme' => $theme,
            ]);
        } elseif ($theme['type'] == 'spa') {
            $content = view('spa', [
                'theme' => $theme
            ]);
        }
        $staticPath = \think\facade\App::getRootPath() . 'h5';
        @file_put_contents($staticPath . '/' . $theme['id'] . '.html', $content->getContent());
    }

    public function view($id)
    {
        $theme = Db::name('theme_list')->findOrFail($id);
        $theme['pages'] = json_decode($theme['pages'], 1);
        if ($theme['type'] == 'h5') {
            return view('h5', [
                'theme' => $theme,
            ]);
        } elseif ($theme['type'] == 'spa') {
            return view('spa', [
                'theme' => $theme
            ]);
        }
    }

/*
 * 功能暂且搁置
 */

//    public function showact()
//    {
//        $get = $this->request->get();
//        $id = $get['id'];
//        $type = $get['type'];
//        $tplData = Db::name('theme_list')->where('id', '=', $id)->find();
//        $tplData['tplid'] = $tplData['id'];
//        unset($tplData['id']);
//        DB::name('theme_act')->insert($tplData);
//    }
}