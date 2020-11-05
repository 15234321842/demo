<?php
/**
 * 客户跟进 Controller 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-10-18
 * Time: 15:44:18
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */


namespace app\houtai\controller;


use common\logic\CrmCustomerLogic;
use common\logic\CrmFollowLogic;
use common\model\CrmCustomer;
use common\model\Result;
use common\model\CrmFollow as ModelCrmFollow;

class CrmFollow extends Base {

    public function index(){
        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        $params['add_uid'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new CrmFollowLogic();
        $list = $logic->paginate($params);

        $this->assign('list',$list);
        $this->assign('lastTime',$lastTime);
        $this->assign('params',$params);

        $followTypeList = config('follow_type');
        $this->assign('followTypeList',$followTypeList);

        return $this->fetch();
    }

    public function scroll_loading(){
        $result = new Result();

        $params = array();
        $params = $this->request->param();

        $params['add_uid'] = $this->getUserId();
        $params['is_del'] = 0;

        $logic = new CrmFollowLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['follow_time_format'] = '';
            $v['follow_type_text'] = config('app.follow_type.'.$v['follow_type']);
            if($v['follow_time'] > 0){
                $v['follow_time_format'] = date('Y-m-d',$v['follow_time']);
            }

            $v['encrypt_id'] = think_encrypt($v['follow_id']);
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

        $params['add_uid'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new CrmFollowLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->time = $lastTime;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['follow_time_format'] = '';
            $v['follow_type_text'] = config('app.follow_type.'.$v['follow_type']);
            if($v['follow_time'] > 0){
                $v['follow_time_format'] = date('Y-m-d',$v['follow_time']);
            }

            $v['encrypt_id'] = think_encrypt($v['follow_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    public function add(){
        $followTypeList = config('follow_type');
        $this->assign('followTypeList',$followTypeList);

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

        if(string_empty($params['customer_id'])){
            $result->success = false;
            $result->msg = '请选择跟进对象！';
            return $result;
        }

        if(string_empty($params['follow_time'])){
            $result->success = false;
            $result->msg = '请选择跟进日期！';
            return $result;
        }

        if(string_empty($params['follow_log'])){
            $result->success = false;
            $result->msg = '请填写跟进日志！';
            return $result;
        }

        $vo = new ModelCrmFollow();
        $vo->setCustomerId($params['customer_id']);

        if(isset($params['follow_time']) && strlen($params['follow_time']) > 0){
            $vo->setFollowTime(strtotime($params['follow_time']));
        }

        $vo->setFollowType($params['follow_type']);
        $vo->setFollowLog($params['follow_log']);
        $vo->setFollowUid($this->getUserId());

        $time = time();
        $vo->setAddUid($this->getUserId());
        $vo->setEditUid($this->getUserId());
        $vo->setAddTime($time);
        $vo->setEditTime($time);

        $logic = new CrmFollowLogic();
        $result = $logic->add($vo);

        return $result;
    }

    function edit(){
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

        $mapVo = new ModelCrmFollow();
        $mapVo->setFollowId($id);

        $logic = new CrmFollowLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('跟进日志不存在！');
            return false;
        }

        $model['follow_time_format'] = '';
        if($model['follow_time'] > 0){
            $model['follow_time_format'] = date('Y-m-d',$model['follow_time']);
        }

        $mapVo = new CrmCustomer();
        $mapVo->setCustomerId($model['customer_id']);

        $logic = new CrmCustomerLogic();
        $modelCustomer = $logic->find($mapVo);
        if($modelCustomer){
            $model['customer_name'] = $modelCustomer['customer_name'];
        }

        $followTypeList = config('follow_type');
        $this->assign('followTypeList',$followTypeList);

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

        if(string_empty($params['customer_id'])){
            $result->success = false;
            $result->msg = '请选择跟进对象！';
            return $result;
        }

        if(string_empty($params['follow_time'])){
            $result->success = false;
            $result->msg = '请选择跟进日期！';
            return $result;
        }

        if(string_empty($params['follow_log'])){
            $result->success = false;
            $result->msg = '请填写跟进日志！';
            return $result;
        }

        $id = think_decrypt($params['follow_id']);
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $vo = new ModelCrmFollow();
        $vo->setCustomerId($params['customer_id']);

        if(isset($params['follow_time']) && strlen($params['follow_time']) > 0){
            $vo->setFollowTime(strtotime($params['follow_time']));
        }

        $vo->setFollowType($params['follow_type']);
        $vo->setFollowLog($params['follow_log']);
        $vo->setFollowUid($this->getUserId());

        $time = time();
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime($time);

        $mapVo = new ModelCrmFollow();
        $mapVo->setFollowId($id);

        $logic = new CrmFollowLogic();
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

        $mapVo = new ModelCrmFollow();
        $mapVo->setFollowId($id);

        $logic = new CrmFollowLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('跟进日志不存在！');
            return false;
        }

        $mapVo = new CrmCustomer();
        $mapVo->setCustomerId($model['customer_id']);

        $logic = new CrmCustomerLogic();
        $modelCustomer = $logic->find($mapVo);
        if($modelCustomer){
            $mobileList = $modelCustomer['mobile'];
            $model['mobile'] = array();
            if(!string_empty($mobileList)){
                $model['mobile'] = explode(',',$mobileList);
                !isset($model['mobile'][0]) && $model['mobile'][0] = '';
                !isset($model['mobile'][1]) && $model['mobile'][1] = '';
                !isset($model['mobile'][2]) && $model['mobile'][2] = '';
            }

            $model['customer_name'] = $modelCustomer['customer_name'];
            $model['regular_customer'] = $modelCustomer['regular_customer'];
            $model['customer_relations'] = $modelCustomer['customer_relations'];
        }

        $this->assign('model',$model);

        return $this->fetch();
    }

    public function load_other(){
        $result = new Result();
        $result->success = false;
        $result->msg = '获取数据失败！';

        $id = 0;
        $params = $this->request->param();
        if(!isset($params['id'])){
            $result->success = false;
            $result->msg = '非法参数！';
            return response($result,200,[],'json');
        }
        $id = think_decrypt($params['id']);
        if(string_empty($id)){
            $result->success = false;
            $result->msg = '非法参数！';
            return response($result,200,[],'json');
        }

        $mapVo = new ModelCrmFollow();
        $mapVo->setFollowId($id);

        $logic = new CrmFollowLogic();

        $model = $logic->find($mapVo);
        if($model){
            $list = $logic->load_other($model['customer_id'],$id,$this->getUserId());
            foreach($list as $k=>&$v){
                $v['follow_time_format'] = '';
                $v['follow_type_text'] = config('app.follow_type.'.$v['follow_type']);
                if($v['follow_time'] > 0){
                    $v['follow_time_format'] = date('Y-m-d',$v['follow_time']);
                }
                unset($v);
            }

            if(count($list) > 0){
                $result->msg = '获取数据成功！';
            }else{
                $result->msg = '没有其它跟进日志！';
            }

            $result->success = true;
            $result->data = $list;
            $result->dataType = Result::DATATYPE_ARRAY;
        }

        return response($result,200,[],'json');
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

        $logic = new CrmFollowLogic();
        $vo = new ModelCrmFollow();
        $mapVo = new ModelCrmFollow();

        $vo->setIsDel(1);
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime(time());

        $mapVo->setFollowId($id);

        $result = $logic->update($vo,$mapVo);
        if($result->success){
            $result->msg = '删除成功！';
            $result->url = url('index');
        }

        return $result;
    }
}