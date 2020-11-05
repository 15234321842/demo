<?php
/**
 * 销售流程控制器 SellFlow 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-10-07
 * Time: 11:08:00
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace app\houtai\controller;


use common\logic\CrmCustomerLogic;
use common\logic\SellFlowLogic;
use common\model\CrmCustomer;
use common\model\Result;
use common\model\SellFlow as ModelSellFlow;

class SellFlow extends Base {
    public function index(){
        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        if(!isset($params['customer_name'])){
            $params['customer_name'] = '';
        }
        if(!isset($params['mobile'])){
            $params['mobile'] = '';
        }

        $params['add_uid'] = $this->getUserId();
        $params['aim_list'] = 1;
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new CrmCustomerLogic();
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
        if(!isset($params['mobile'])){
            $params['mobile'] = '';
        }

        $params['add_uid'] = $this->getUserId();
        $params['aim_list'] = 1;
        $params['is_del'] = 0;

        $logic = new CrmCustomerLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['customer_relations'] = config('app.customer_relations.'.$v['customer_relations']);
            $v['regular_customer_text'] = config('app.regular_customer.'.$v['regular_customer']);
            $v['first_mobile'] = '';
            if(!string_empty($v['mobile'])){
                $mobileList = explode(',',$v['mobile']);
                $v['first_mobile'] = $mobileList[0];
            }

            $v['encrypt_id'] = think_encrypt($v['customer_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));
            $v['edit_url'] = letu_url('edit',array('id'=>$v['encrypt_id']));

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
        if(!isset($params['mobile'])){
            $params['mobile'] = '';
        }

        $params['add_uid'] = $this->getUserId();
        $params['aim_list'] = 1;
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new CrmCustomerLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->time = $lastTime;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['customer_relations'] = config('app.customer_relations.'.$v['customer_relations']);
            $v['regular_customer_text'] = config('app.regular_customer.'.$v['regular_customer']);
            $v['first_mobile'] = '';
            if(!string_empty($v['mobile'])){
                $mobileList = explode(',',$v['mobile']);
                $v['first_mobile'] = $mobileList[0];
            }

            $v['encrypt_id'] = think_encrypt($v['customer_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));
            $v['edit_url'] = letu_url('edit',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    public function aim_list_scroll_loading(){
        $result = new Result();

        $params = array();
        $params = $this->request->param();

        $aim_list = 0;
        if(isset($params['add'])){
            $aim_list = $params['add'];
        }

        $params['add_uid'] = $this->getUserId();
        $params['aim_list'] = $aim_list;
        $params['is_del'] = 0;

        $logic = new CrmCustomerLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['customer_relations'] = config('app.customer_relations.'.$v['customer_relations']);
            $v['regular_customer_text'] = config('app.regular_customer.'.$v['regular_customer']);
            $v['first_mobile'] = '';
            if(!string_empty($v['mobile'])){
                $mobileList = explode(',',$v['mobile']);
                $v['first_mobile'] = $mobileList[0];
            }

            $v['encrypt_id'] = think_encrypt($v['customer_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));
            $v['edit_url'] = letu_url('edit',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    public function aim_list_pull_to_refresh(){
        $result = new Result();

        $lastTime = time();
        $params = $this->request->param();

        $aim_list = 0;
        if(!isset($params['add'])){
            $aim_list = $params['add'];
        }

        $params['add_uid'] = $this->getUserId();
        $params['aim_list'] = $aim_list;
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new CrmCustomerLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->time = $lastTime;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['customer_relations'] = config('app.customer_relations.'.$v['customer_relations']);
            $v['regular_customer_text'] = config('app.regular_customer.'.$v['regular_customer']);
            $v['first_mobile'] = '';
            if(!string_empty($v['mobile'])){
                $mobileList = explode(',',$v['mobile']);
                $v['first_mobile'] = $mobileList[0];
            }

            $v['encrypt_id'] = think_encrypt($v['customer_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));
            $v['edit_url'] = letu_url('edit',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    /**
     * 未添加名单 - 已添加名单
     * @return mixed
     */
    public function aim_list(){
        $add = 0;
        $lastTime = time();

        $params = $this->request->param();
        if(isset($params['add'])){
            $add = $params['add'];
        }

        $params['add_uid'] = $this->getUserId();
        $params['aim_list'] = $add;
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new CrmCustomerLogic();
        $list = $logic->paginate($params);

        $this->assign('list',$list);
        $this->assign('lastTime',$lastTime);
        $this->assign('params',$params);

        $this->assign('add',$add);
        return $this->fetch();
    }

    /**
     * 添加目标名单
     * @return Result
     */
    public function aim_list_add(){
        $result = new Result();
        $result->msg = '操作失败！';
        $result->success = false;

        if($this->request->isPost() == false){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->param();
        if(!isset($params['id'])){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }
        $id = $params['id'];
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $aim_list = 0;
        if(isset($params['add'])){
            if(!($params['add'] > 0)){
                $aim_list = 1;
            }
        }

        $logic = new SellFlowLogic();
        $mapVo = new CrmCustomer();

        $mapVo->setCustomerId($id);
        $mapVo->setAddUid($this->getUserId());

        $result = $logic->addDelAimList($aim_list,$mapVo);

        return $result;
    }

    function view(){
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

        $mapVo = new CrmCustomer();
        $mapVo->setCustomerId($id);

        $logic = new CrmCustomerLogic();
        $model = $logic->find($mapVo);

        $mobileList = $model['mobile'];
        $model['mobile'] = array();
        if(!string_empty($mobileList)){
            $model['mobile'] = explode(',',$mobileList);
            !isset($model['mobile'][0]) && $model['mobile'][0] = '';
            !isset($model['mobile'][1]) && $model['mobile'][1] = '';
            !isset($model['mobile'][2]) && $model['mobile'][2] = '';
        }

        $mapVo = new ModelSellFlow();
        $mapVo->setCustomerId($id);

        $logic = new SellFlowLogic();
        $sellFlow = $logic->find($mapVo);
        if($sellFlow){
            $model = array_merge($model,$sellFlow);
        }
        $model['encrypt_id'] = think_encrypt($model['customer_id']);
        $this->assign('model',$model);

        return $this->fetch();
    }

    function flow_step(){
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

        $mapVo = new CrmCustomer();
        $mapVo->setCustomerId($id);

        $logic = new CrmCustomerLogic();
        $model = $logic->find($mapVo);

        $mobileList = $model['mobile'];
        $model['mobile'] = array();
        if(!string_empty($mobileList)){
            $model['mobile'] = explode(',',$mobileList);
            !isset($model['mobile'][0]) && $model['mobile'][0] = '';
            !isset($model['mobile'][1]) && $model['mobile'][1] = '';
            !isset($model['mobile'][2]) && $model['mobile'][2] = '';
        }

        $mapVo = new ModelSellFlow();
        $mapVo->setCustomerId($id);

        $logic = new SellFlowLogic();
        $sellFlow = $logic->find($mapVo);
        if($sellFlow){
            $model = array_merge($model,$sellFlow);
        }
        $model['encrypt_id'] = think_encrypt($model['customer_id']);
        $this->assign('model',$model);

        $sandlerIsFinishList = config('app.sandler_is_finish');
        $this->assign('sandlerIsFinishList',$sandlerIsFinishList);

        return $this->fetch();
    }

    function flow_step_edit(){
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

        $mapVo = new CrmCustomer();
        $mapVo->setCustomerId($id);

        $logic = new CrmCustomerLogic();
        $model = $logic->find($mapVo);

        $mobileList = $model['mobile'];
        $model['mobile'] = array();
        if(!string_empty($mobileList)){
            $model['mobile'] = explode(',',$mobileList);
            !isset($model['mobile'][0]) && $model['mobile'][0] = '';
            !isset($model['mobile'][1]) && $model['mobile'][1] = '';
            !isset($model['mobile'][2]) && $model['mobile'][2] = '';
        }

        $mapVo = new ModelSellFlow();
        $mapVo->setCustomerId($id);

        $logic = new SellFlowLogic();
        $sellFlow = $logic->find($mapVo);
        if($sellFlow){
            $model = array_merge($model,$sellFlow);
        }
        $model['encrypt_id'] = think_encrypt($model['customer_id']);
        $this->assign('model',$model);

        $sandlerIsFinishList = config('app.sandler_is_finish');
        $this->assign('sandlerIsFinishList',$sandlerIsFinishList);

        return $this->fetch();
    }

    public function flow_step_edit_save(){
        $result = new Result();
        $result->msg = '编辑失败！';
        $result->success = false;

        if(!$this->request->isPost()){
            $result->msg = '编辑失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->post();

        $id = think_decrypt($params['customer_id']);
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $vo = new ModelSellFlow();
        $vo->setSandlerTrust($params['sandler_trust']);
        $vo->setSandlerAppoint($params['sandler_appoint']);
        $vo->setSandlerPain($params['sandler_pain']);
        $vo->setSandlerBudget($params['sandler_budget']);
        $vo->setSandlerDecision($params['sandler_decision']);
        $vo->setSandlerScheme($params['sandler_scheme']);
        $vo->setSandlerDeal($params['sandler_deal']);

        $vo->setTrustContent($params['trust_content']);
        $vo->setAppointContent($params['appoint_content']);
        $vo->setPainContent($params['pain_content']);
        $vo->setBudgetContent($params['budget_content']);
        $vo->setDecisionContent($params['decision_content']);
        $vo->setSchemeContent($params['scheme_content']);
        $vo->setDealContent($params['deal_content']);

        $mapVo = new ModelSellFlow();
        $mapVo->setCustomerId($id);

        $logic = new SellFlowLogic();
        $result = $logic->update($vo,$mapVo);

        return $result;
    }

    public function set_sandler_finish_status(){
        $result = new Result();
        $result->msg = '编辑失败！';
        $result->success = false;

        if(!$this->request->isPost()){
            $result->msg = '编辑失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->post();

        $id = think_decrypt($params['id']);
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $vo = new ModelSellFlow();

        if(isset($params['sandler_trust'])){
            $vo->setSandlerTrust($params['sandler_trust']);
        }
        if(isset($params['sandler_appoint'])){
            $vo->setSandlerAppoint($params['sandler_appoint']);
        }
        if(isset($params['sandler_pain'])){
            $vo->setSandlerPain($params['sandler_pain']);
        }
        if(isset($params['sandler_budget'])){
            $vo->setSandlerBudget($params['sandler_budget']);
        }
        if(isset($params['sandler_decision'])){
            $vo->setSandlerDecision($params['sandler_decision']);
        }
        if(isset($params['sandler_scheme'])){
            $vo->setSandlerScheme($params['sandler_scheme']);
        }
        if(isset($params['sandler_deal'])){
            $vo->setSandlerDeal($params['sandler_deal']);
        }

        $mapVo = new ModelSellFlow();
        $mapVo->setCustomerId($id);

        $logic = new SellFlowLogic();
        $result = $logic->update($vo,$mapVo);

        return $result;
    }
}