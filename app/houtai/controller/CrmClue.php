<?php
/**
 * 客户线索 Controller 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-10-16
 * Time: 20:11:15
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */
namespace app\houtai\controller;


use common\logic\CrmClueLogic;
use common\logic\CrmCustomerLogic;
use common\model\CrmCustomer;
use common\model\Result;
use common\model\CrmClue as ModelCrmClue;

class CrmClue extends Base {
    public function index(){
        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        $params['add_uid'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new CrmClueLogic();
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

        $params['add_uid'] = $this->getUserId();
        $params['is_del'] = 0;

        $logic = new CrmClueLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['clue_time_format'] = '';
            if($v['clue_time'] > 0){
                $v['clue_time_format'] = date('Y-m-d',$v['clue_time']);
            }

            $v['encrypt_id'] = think_encrypt($v['clue_id']);
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

        $logic = new CrmClueLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->time = $lastTime;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['clue_time_format'] = '';
            if($v['clue_time'] > 0){
                $v['clue_time_format'] = date('Y-m-d',$v['clue_time']);
            }

            $v['encrypt_id'] = think_encrypt($v['clue_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    public function add(){
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
            $result->msg = '请选择线索对象！';
            return $result;
        }

        if(string_empty($params['clue_content'])){
            $result->success = false;
            $result->msg = '请填写线索内容！';
            return $result;
        }

        $vo = new ModelCrmClue();
        $vo->setCustomerId($params['customer_id']);

        if(isset($params['clue_time']) && strlen($params['clue_time']) > 0){
            $vo->setClueTime(strtotime($params['clue_time']));
        }

        $vo->setClueContent($params['clue_content']);

        $time = time();
        $vo->setAddUid($this->getUserId());
        $vo->setEditUid($this->getUserId());
        $vo->setAddTime($time);
        $vo->setEditTime($time);

        $logic = new CrmClueLogic();
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

        $mapVo = new ModelCrmClue();
        $mapVo->setClueId($id);

        $logic = new CrmClueLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('客户线索不存在！');
            return false;
        }

        $model['clue_time_format'] = '';
        if($model['clue_time'] > 0){
            $model['clue_time_format'] = date('Y-m-d',$model['clue_time']);
        }

        $mapVo = new CrmCustomer();
        $mapVo->setCustomerId($model['customer_id']);

        $logic = new CrmCustomerLogic();
        $modelCustomer = $logic->find($mapVo);
        if($modelCustomer){
            $model['customer_name'] = $modelCustomer['customer_name'];
        }

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
            $result->msg = '请选择线索对象！';
            return $result;
        }

        if(string_empty($params['clue_content'])){
            $result->success = false;
            $result->msg = '请填写线索内容！';
            return $result;
        }

        $id = think_decrypt($params['clue_id']);
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $vo = new ModelCrmClue();
        $vo->setCustomerId($params['customer_id']);

        if(isset($params['clue_time']) && strlen($params['clue_time']) > 0){
            $vo->setClueTime(strtotime($params['clue_time']));
        }

        $vo->setClueContent($params['clue_content']);

        $time = time();
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime($time);

        $mapVo = new ModelCrmClue();
        $mapVo->setClueId($id);

        $logic = new CrmClueLogic();
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

        $mapVo = new ModelCrmClue();
        $mapVo->setClueId($id);

        $logic = new CrmClueLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('客户线索不存在！');
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

        $mapVo = new ModelCrmClue();
        $mapVo->setClueId($id);

        $logic = new CrmClueLogic();

        $model = $logic->find($mapVo);
        if($model){
            $list = $logic->load_other($model['customer_id'],$id,$this->getUserId());
            foreach($list as $k=>&$v){
                $v['clue_time_format'] = '';
                if($v['clue_time'] > 0){
                    $v['clue_time_format'] = date('Y-m-d',$v['clue_time']);
                }
                unset($v);
            }

            if(count($list) > 0){
                $result->msg = '获取数据成功！';
            }else{
                $result->msg = '没有其它线索！';
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

        $logic = new CrmClueLogic();
        $vo = new ModelCrmClue();
        $mapVo = new ModelCrmClue();

        $vo->setIsDel(1);
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime(time());

        $mapVo->setClueId($id);

        $result = $logic->update($vo,$mapVo);
        if($result->success){
            $result->msg = '删除成功！';
            $result->url = url('index');
        }

        return $result;
    }
}