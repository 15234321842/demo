<?php
/**
 * 目标 Controller 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-10-21
 * Time: 08:53:35
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace app\houtai\controller;


use common\logic\WorkAimLogic;
use common\model\Result;
use common\model\WorkAim as ModelWorkAim;

class WorkAim extends Base
{
    public function index(){
        $lastTime = time();
        $params = array();
        $params = $this->request->param();


        $params['user_id'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;
        $params['sel_year'] = date('Y',time());

        $logic = new WorkAimLogic();
        $list = $logic->paginate($params);

        $this->assign('list',$list);
        $this->assign('lastTime',$lastTime);
        $this->assign('params',$params);

        return $this->fetch();
    }

    public function scroll_loading(){
        $result = new Result();

        $params = array();
        $params = $this->request->param();

        if(!isset($params['sel_year']) || string_empty($params['sel_year'])){
            $params['sel_year'] = date('Y',time());
        }

        $params['user_id'] = $this->getUserId();
        $params['is_del'] = 0;

        $logic = new WorkAimLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['finish_status_text'] = config('app.finish_status.'.$v['finish_status']);

            $v['date_begin_format'] = '';
            $v['date_end_format'] = '';
            if($v['date_begin'] > 0){
                $v['date_begin_format'] = date('Y-m-d',$v['date_begin']);
            }
            if($v['date_end'] > 0){
                $v['date_end_format'] = date('Y-m-d',$v['date_end']);
            }

            $v['encrypt_id'] = think_encrypt($v['aim_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    public function pull_to_refresh(){
        $result = new Result();

        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        if(!isset($params['sel_year']) || string_empty($params['sel_year'])){
            $params['sel_year'] = date('Y',time());
        }

        $params['user_id'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new WorkAimLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->time = $lastTime;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['finish_status_text'] = config('app.finish_status.'.$v['finish_status']);

            $v['date_begin_format'] = '';
            $v['date_end_format'] = '';
            if($v['date_begin'] > 0){
                $v['date_begin_format'] = date('Y-m-d',$v['date_begin']);
            }
            if($v['date_end'] > 0){
                $v['date_end_format'] = date('Y-m-d',$v['date_end']);
            }

            $v['encrypt_id'] = think_encrypt($v['aim_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    public function add(){
        $finishStatusList = config('app.finish_status');
        $this->assign('finishStatusList',$finishStatusList);

        return $this->fetch();
    }

    public function add_save(){
        $result = new Result();
        $result->msg = '添加失败！';
        $result->success = false;

        if(!$this->request->isPost()){
            $result->msg = '添加失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->post();

        if(string_empty($params['aim_name'])){
            $result->success = false;
            $result->msg = '请填写目标名称！';
            return $result;
        }

        if(string_empty($params['date_begin'])){
            $result->success = false;
            $result->msg = '请选择目标开始日期！';
            return $result;
        }

        if(string_empty($params['date_end'])){
            $result->success = false;
            $result->msg = '请选择目标完成日期！';
            return $result;
        }

        if(string_empty($params['realize_plan'])){
            $result->success = false;
            $result->msg = '请填写目标实现计划！';
            return $result;
        }

        $vo = new ModelWorkAim();
        $vo->setAimName($params['aim_name']);
        $vo->setUserId($this->getUserId());

        if(isset($params['date_begin']) && strlen($params['date_begin']) > 0){
            $vo->setDateBegin(strtotime($params['date_begin']));
        }
        if(isset($params['date_end']) && strlen($params['date_end']) > 0){
            $vo->setDateEnd(strtotime($params['date_end']));
        }

        $vo->setRealizePlan($params['realize_plan']);
        $vo->setFinishSummary($params['finish_summary']);
        $vo->setFinishStatus($params['finish_status']);

        $time = time();
        $vo->setAddUid($this->getUserId());
        $vo->setEditUid($this->getUserId());
        $vo->setAddTime($time);
        $vo->setEditTime($time);

        $logic = new WorkAimLogic();
        $result = $logic->add($vo);

        return $result;
    }

    public function edit(){
        $id = 0;
        $params = $this->request->param();
        if(!isset($params['id'])){
            $this->error('非法参数！');
            exit;
        }
        $id = think_decrypt($params['id']);
        if(string_empty($id)){
            $this->error('非法参数！');
            exit;
        }

        $mapVo = new ModelWorkAim();
        $mapVo->setAimId($id);

        $logic = new WorkAimLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('目标不存在！');
            return false;
        }

        $finishStatusList = config('app.finish_status');
        $this->assign('finishStatusList',$finishStatusList);

        $this->assign('model',$model);

        return $this->fetch();
    }

    public function edit_save(){
        $result = new Result();
        $result->msg = '编辑失败！';
        $result->success = false;

        if(!$this->request->isPost()){
            $result->msg = '编辑失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->post();

        if(string_empty($params['aim_name'])){
            $result->success = false;
            $result->msg = '请填写目标名称！';
            return $result;
        }

        if(string_empty($params['date_begin'])){
            $result->success = false;
            $result->msg = '请选择目标开始日期！';
            return $result;
        }

        if(string_empty($params['date_end'])){
            $result->success = false;
            $result->msg = '请选择目标完成日期！';
            return $result;
        }

        if(string_empty($params['realize_plan'])){
            $result->success = false;
            $result->msg = '请填写目标实现计划！';
            return $result;
        }

        $id = think_decrypt($params['aim_id']);
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $vo = new ModelWorkAim();
        $vo->setAimName($params['aim_name']);
        $vo->setUserId($this->getUserId());

        if(isset($params['date_begin']) && strlen($params['date_begin']) > 0){
            $vo->setDateBegin(strtotime($params['date_begin']));
        }
        if(isset($params['date_end']) && strlen($params['date_end']) > 0){
            $vo->setDateEnd(strtotime($params['date_end']));
        }

        $vo->setRealizePlan($params['realize_plan']);
        $vo->setFinishSummary($params['finish_summary']);
        $vo->setFinishStatus($params['finish_status']);

        $time = time();
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime($time);

        $mapVo = new ModelWorkAim();
        $mapVo->setAimId($id);

        $logic = new WorkAimLogic();
        $result = $logic->update($vo,$mapVo);

        return $result;
    }

    public function view(){
        $id = 0;
        $params = $this->request->param();
        if(!isset($params['id'])){
            $this->error('非法参数！');
            exit;
        }
        $id = think_decrypt($params['id']);
        if(string_empty($id)){
            $this->error('非法参数！');
            exit;
        }

        $mapVo = new ModelWorkAim();
        $mapVo->setAimId($id);

        $logic = new WorkAimLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('目标不存在！');
            return false;
        }

        $this->assign('model',$model);

        return $this->fetch();
    }

    public function del(){
        $result = new Result();
        $result->msg = '删除失败！';
        $result->success = false;

        if($this->request->isPost() == false){
            $result->msg = '删除失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->param();
        if(!isset($params['id'])){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }
        $id = think_decrypt($params['id']);
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $logic = new WorkAimLogic();
        $vo = new ModelWorkAim();
        $mapVo = new ModelWorkAim();

        $vo->setIsDel(1);
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime(time());

        $mapVo->setAimId($id);

        $result = $logic->update($vo,$mapVo);
        if($result->success){
            $result->msg = '删除成功！';
            $result->url = url('index');
        }

        return $result;
    }
}