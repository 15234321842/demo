<?php
/**
 * 销售记录 Controller 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-10-18
 * Time: 21:37:55
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace app\houtai\controller;


use common\logic\CrmCustomerLogic;
use common\logic\SellRecordLogic;
use common\model\CrmCustomer;
use common\model\Result;
use common\model\SellRecord as ModelSellRecord;

class SellRecord extends Base {

    public function index(){
        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        if(!isset($params['customer_name'])){
            $params['customer_name'] = '';
        }

        $params['add_uid'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;
        $params['sell_date'] = date('Y-m',time());

        $logic = new SellRecordLogic();
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

        if(!isset($params['customer_name'])){
            $params['customer_name'] = '';
        }

        if(!isset($params['sell_date']) || string_empty($params['sell_date'])){
            $params['sell_date'] = date('Y-m',time());
        }

        $params['add_uid'] = $this->getUserId();
        $params['is_del'] = 0;

        $logic = new SellRecordLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['sell_money'] = floatval($v['sell_money']);

            $v['sell_date_format'] = '';
            if($v['sell_date'] > 0){
                $v['sell_date_format'] = date('Y-m-d',$v['sell_date']);
            }

            $v['encrypt_id'] = think_encrypt($v['record_id']);
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

        if(!isset($params['customer_name'])){
            $params['customer_name'] = '';
        }

        if(!isset($params['sell_date']) || string_empty($params['sell_date'])){
            $params['sell_date'] = date('Y-m',time());
        }

        $params['add_uid'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new SellRecordLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->time = $lastTime;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['sell_money'] = floatval($v['sell_money']);

            $v['sell_date_format'] = '';
            if($v['sell_date'] > 0){
                $v['sell_date_format'] = date('Y-m-d',$v['sell_date']);
            }

            $v['encrypt_id'] = think_encrypt($v['record_id']);
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

        if(string_empty($params['sell_money'])){
            $result->success = false;
            $result->msg = '请填写销售金额！';
            return $result;
        }

        if(string_empty($params['customer_id'])){
            $result->success = false;
            $result->msg = '请选择销售客户！';
            return $result;
        }

        if(string_empty($params['sell_explain'])){
            $result->success = false;
            $result->msg = '请填写销售说明！';
            return $result;
        }

        $vo = new ModelSellRecord();
        $vo->setSellMoney($params['sell_money']);
        $vo->setCustomerId($params['customer_id']);

        if(isset($params['sell_date']) && strlen($params['sell_date']) > 0){
            $vo->setSellDate(strtotime($params['sell_date']));
        }

        $vo->setSellExplain($params['sell_explain']);

        $time = time();
        $vo->setAddUid($this->getUserId());
        $vo->setEditUid($this->getUserId());
        $vo->setAddTime($time);
        $vo->setEditTime($time);

        $logic = new SellRecordLogic();
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

        $mapVo = new ModelSellRecord();
        $mapVo->setRecordId($id);

        $logic = new SellRecordLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('销售记录不存在！');
            return false;
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

        if(string_empty($params['sell_money'])){
            $result->success = false;
            $result->msg = '请填写销售金额！';
            return $result;
        }

        if(string_empty($params['customer_id'])){
            $result->success = false;
            $result->msg = '请选择销售客户！';
            return $result;
        }

        if(string_empty($params['sell_explain'])){
            $result->success = false;
            $result->msg = '请填写销售说明！';
            return $result;
        }

        $id = think_decrypt($params['record_id']);
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $vo = new ModelSellRecord();
        $vo->setSellMoney($params['sell_money']);
        $vo->setCustomerId($params['customer_id']);

        if(isset($params['sell_date']) && strlen($params['sell_date']) > 0){
            $vo->setSellDate(strtotime($params['sell_date']));
        }

        $vo->setSellExplain($params['sell_explain']);

        $time = time();
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime($time);

        $mapVo = new ModelSellRecord();
        $mapVo->setRecordId($id);

        $logic = new SellRecordLogic();
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

        $mapVo = new ModelSellRecord();
        $mapVo->setRecordId($id);

        $logic = new SellRecordLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('销售记录不存在！');
            return false;
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

        $logic = new SellRecordLogic();
        $vo = new ModelSellRecord();
        $mapVo = new ModelSellRecord();

        $vo->setIsDel(1);
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime(time());

        $mapVo->setRecordId($id);

        $result = $logic->update($vo,$mapVo);
        if($result->success){
            $result->msg = '删除成功！';
            $result->url = url('index');
        }

        return $result;
    }
}