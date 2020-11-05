<?php
/**
 * 我的控制器 My 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-10-07
 * Time: 10:16:00
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace app\houtai\controller;


use common\logic\CrmCustomerLogic;
use common\logic\MyInsuranceLogic;
use common\logic\MyTalkScriptLogic;
use common\logic\UserLogic;
use common\model\CrmCustomer;
use common\model\MyInsurance;
use common\model\MyTalkScript;
use common\model\Result;
use common\model\User;

class My extends Base{

    public $needPermission = array('member','member_view','member_add','member_edit','member_add_save','member_edit_save');

    public function __construct(){
        parent::__construct();

        $this->checkPermission();
    }

    public function index(){
        return $this->fetch();
    }

    /**
     * 我的话术
     * @return mixed
     */
    public function talk_script(){
        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        $params['user_id'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new MyTalkScriptLogic();
        $list = $logic->paginate($params);

        $this->assign('list',$list);
        $this->assign('lastTime',$lastTime);
        $this->assign('params',$params);

        return $this->fetch();
    }

    /**
     * 我的话术-添加
     * @return mixed
     */
    public function talk_script_add(){
        $scriptTypeList = config('app.script_type');
        $this->assign('scriptTypeList',$scriptTypeList);

        return $this->fetch();
    }

    /**
     * 我的话术-添加保存
     * @return mixed
     */
    public function talk_script_add_save(){
        $result = new Result();
        $result->msg = '添加失败！';
        $result->success = false;

        if(!$this->request->isPost()){
            $result->msg = '添加失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->post();

        if(string_empty($params['script_title'])){
            $result->success = false;
            $result->msg = '请填写话术标题！';
            return $result;
        }

        if(string_empty($params['script_content'])){
            $result->success = false;
            $result->msg = '请填写话术内容！';
            return $result;
        }

        $vo = new MyTalkScript();
        $vo->setScriptTitle($params['script_title']);
        $vo->setScriptContent($params['script_content']);
        $vo->setScriptType($params['script_type']);
        $vo->setUserId($this->getUserId());

        $time = time();
        $vo->setAddUid($this->getUserId());
        $vo->setEditUid($this->getUserId());
        $vo->setAddTime($time);
        $vo->setEditTime($time);

        $logic = new MyTalkScriptLogic();
        $result = $logic->add($vo);

        return $result;
    }

    /**
     * 我的保单
     * @return mixed
     */
    public function my_insurance(){
        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        if(!isset($params['customer_name'])){
            $params['customer_name'] = '';
        }

        $params['user_id'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;
        $params['policy_type'] = 0;

        $logic = new MyInsuranceLogic();
        $list = $logic->paginate($params);

        $this->assign('list',$list);
        $this->assign('lastTime',$lastTime);
        $this->assign('params',$params);

        return $this->fetch();
    }

    /**
     * 客户保单
     * @return mixed
     */
    public function customer_insurance(){
        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        if(!isset($params['customer_name'])){
            $params['customer_name'] = '';
        }

        $params['user_id'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;
        $params['policy_type'] = 1;

        $logic = new MyInsuranceLogic();
        $list = $logic->paginate($params);

        $this->assign('list',$list);
        $this->assign('lastTime',$lastTime);
        $this->assign('params',$params);

        return $this->fetch();
    }

    public function insurance_scroll_loading(){
        $result = new Result();

        $params = array();
        $params = $this->request->param();

        if(!isset($params['customer_name'])){
            $params['customer_name'] = '';
        }

        $params['user_id'] = $this->getUserId();
        $params['is_del'] = 0;

        $logic = new MyInsuranceLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['first_premium'] = floatval($v['first_premium']);

            $v['first_pay_date_format'] = '';
            if($v['first_pay_date'] > 0){
                $v['first_pay_date_format'] = date('Y-m-d',$v['first_pay_date']);
            }

            $v['encrypt_id'] = think_encrypt($v['insurance_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    public function insurance_pull_to_refresh(){
        $result = new Result();

        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        if(!isset($params['customer_name'])){
            $params['customer_name'] = '';
        }

        $params['user_id'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new MyInsuranceLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->time = $lastTime;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['first_premium'] = floatval($v['first_premium']);

            $v['first_pay_date_format'] = '';
            if($v['first_pay_date'] > 0){
                $v['first_pay_date_format'] = date('Y-m-d',$v['first_pay_date']);
            }

            $v['encrypt_id'] = think_encrypt($v['insurance_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    public function talk_script_scroll_loading(){
        $result = new Result();

        $params = array();
        $params = $this->request->param();

        $params['user_id'] = $this->getUserId();
        $params['is_del'] = 0;

        $logic = new MyTalkScriptLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['script_type_text'] = config('app.script_type.'.$v['script_type']);

            $v['edit_time_format'] = '';
            if($v['edit_time'] > 0){
                $v['edit_time_format'] = date('Y-m-d H:i:s',$v['edit_time']);
            }

            $v['encrypt_id'] = think_encrypt($v['script_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    public function talk_script_pull_to_refresh(){
        $result = new Result();

        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        $params['user_id'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new MyTalkScriptLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->time = $lastTime;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['script_type_text'] = config('app.script_type.'.$v['script_type']);

            $v['edit_time_format'] = '';
            if($v['edit_time'] > 0){
                $v['edit_time_format'] = date('Y-m-d H:i:s',$v['edit_time']);
            }

            $v['encrypt_id'] = think_encrypt($v['script_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    public function member_scroll_loading(){
        $result = new Result();

        $params = array();
        $params = $this->request->param();

        $params['is_del'] = 0;

        $logic = new UserLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['vip_level_text'] = config('app.vip_level.'.$v['vip_level']);

            $v['reg_time_format'] = '';
            if($v['reg_time'] > 0){
                $v['reg_time_format'] = date('Y-m-d',$v['reg_time']);
            }

            $v['encrypt_id'] = think_encrypt($v['user_id']);
            $v['view_url'] = letu_url('member_view',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    public function member_pull_to_refresh(){
        $result = new Result();

        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new UserLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->time = $lastTime;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['vip_level_text'] = config('app.vip_level.'.$v['vip_level']);

            $v['reg_time_format'] = '';
            if($v['reg_time'] > 0){
                $v['reg_time_format'] = date('Y-m-d',$v['reg_time']);
            }

            $v['encrypt_id'] = think_encrypt($v['user_id']);
            $v['view_url'] = letu_url('member_view',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    /**
     * 我的保单-添加
     * @return mixed
     */
    public function insurance_my_add(){
        $peopleRelationList = config('app.people_relation');
        $this->assign('peopleRelationList',$peopleRelationList);

        $this->assign('policy_type',0);
        $this->assign('backUrl',url('my_insurance'));

        return $this->fetch('insurance_add');
    }

    /**
     * 客户保单-添加
     * @return mixed
     */
    public function insurance_customer_add(){
        $peopleRelationList = config('app.people_relation');
        $this->assign('peopleRelationList',$peopleRelationList);

        $this->assign('policy_type',1);
        $this->assign('backUrl',url('customer_insurance'));

        return $this->fetch('insurance_add');
    }

    /**
     * 保单添加保存
     * @return Result
     */
    public function insurance_add_save(){
        $result = new Result();
        $result->msg = '添加失败！';
        $result->success = false;

        if(!$this->request->isPost()){
            $result->msg = '添加失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->post();

        if(string_empty($params['insurance_company'])){
            $result->success = false;
            $result->msg = '请填写保险公司名称！';
            return $result;
        }

        if(string_empty($params['policy_holder'])){
            $result->success = false;
            $result->msg = '请填写投保人！';
            return $result;
        }

        if(string_empty($params['insured'])){
            $result->success = false;
            $result->msg = '请填写被保险人！';
            return $result;
        }

        if(string_empty($params['insurance_main'])){
            $result->success = false;
            $result->msg = '请填写主险名称！';
            return $result;
        }

        if(string_empty($params['first_pay_date'])){
            $result->success = false;
            $result->msg = '请选择首年缴费日期！';
            return $result;
        }

        if(string_empty($params['first_premium'])){
            $result->success = false;
            $result->msg = '请填写首年保费！';
            return $result;
        }

        $vo = new MyInsurance();
        $vo->setUserId($this->getUserId());
        $vo->setInsuranceCompany($params['insurance_company']);
        $vo->setServiceTelephone($params['service_telephone']);
        $vo->setPolicyHolder($params['policy_holder']);
        if($params['policy_type'] > 0){
            $vo->setCustomerId($params['customer_id']);
        }else{
            $vo->setCustomerId(0);
        }
        $vo->setInsured($params['insured']);
        $vo->setPeopleRelation($params['people_relation']);

        if(isset($params['first_pay_date']) && strlen($params['first_pay_date']) > 0){
            $vo->setFirstPayDate(strtotime($params['first_pay_date']));
        }
        if(isset($params['effective_date']) && strlen($params['effective_date']) > 0){
            $vo->setEffectiveDate(strtotime($params['effective_date']));
        }
        $vo->setFirstPremium($params['first_premium']);
        $vo->setInsuranceMain($params['insurance_main']);
        $vo->setPayYear($params['pay_year']);
        $vo->setInsuranceDetail($params['insurance_detail']);
        $vo->setPolicyType($params['policy_type']);
        $vo->setLinkPhone($params['link_phone']);
        $vo->setLinkAddress($params['link_address']);

        $time = time();
        $vo->setAddUid($this->getUserId());
        $vo->setEditUid($this->getUserId());
        $vo->setAddTime($time);
        $vo->setEditTime($time);

        $logic = new MyInsuranceLogic();
        $result = $logic->add($vo);

        return $result;
    }

    /**
     * 保单编辑保存
     * @return Result
     */
    public function insurance_edit_save(){
        $result = new Result();
        $result->msg = '编辑失败！';
        $result->success = false;

        if(!$this->request->isPost()){
            $result->msg = '编辑失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->post();

        if(string_empty($params['insurance_company'])){
            $result->success = false;
            $result->msg = '请填写保险公司名称！';
            return $result;
        }

        if(string_empty($params['policy_holder'])){
            $result->success = false;
            $result->msg = '请填写投保人！';
            return $result;
        }

        if(string_empty($params['insured'])){
            $result->success = false;
            $result->msg = '请填写被保险人！';
            return $result;
        }

        if(string_empty($params['insurance_main'])){
            $result->success = false;
            $result->msg = '请填写主险名称！';
            return $result;
        }

        if(string_empty($params['first_pay_date'])){
            $result->success = false;
            $result->msg = '请选择首年缴费日期！';
            return $result;
        }

        if(string_empty($params['first_premium'])){
            $result->success = false;
            $result->msg = '请填写首年保费！';
            return $result;
        }

        $id = think_decrypt($params['insurance_id']);
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $vo = new MyInsurance();
        $vo->setUserId($this->getUserId());
        $vo->setInsuranceCompany($params['insurance_company']);
        $vo->setServiceTelephone($params['service_telephone']);
        $vo->setPolicyHolder($params['policy_holder']);
        if($params['policy_type'] > 0){
            $vo->setCustomerId($params['customer_id']);
        }
        $vo->setInsured($params['insured']);
        $vo->setPeopleRelation($params['people_relation']);

        if(isset($params['first_pay_date']) && strlen($params['first_pay_date']) > 0){
            $vo->setFirstPayDate(strtotime($params['first_pay_date']));
        }
        if(isset($params['effective_date']) && strlen($params['effective_date']) > 0){
            $vo->setEffectiveDate(strtotime($params['effective_date']));
        }
        $vo->setFirstPremium($params['first_premium']);
        $vo->setInsuranceMain($params['insurance_main']);
        $vo->setPayYear($params['pay_year']);
        $vo->setInsuranceDetail($params['insurance_detail']);
        $vo->setLinkPhone($params['link_phone']);
        $vo->setLinkAddress($params['link_address']);

        $time = time();
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime($time);

        $mapVo = new MyInsurance();
        $mapVo->setInsuranceId($id);

        $logic = new MyInsuranceLogic();
        $result = $logic->update($vo,$mapVo);

        return $result;
    }

    /**
     * 我的话术-编辑保存
     * @return mixed
     */
    public function talk_script_edit_save(){
        $result = new Result();
        $result->msg = '编辑失败！';
        $result->success = false;

        if(!$this->request->isPost()){
            $result->msg = '编辑失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->post();

        if(string_empty($params['script_title'])){
            $result->success = false;
            $result->msg = '请填写话术标题！';
            return $result;
        }

        if(string_empty($params['script_content'])){
            $result->success = false;
            $result->msg = '请填写话术内容！';
            return $result;
        }

        $id = think_decrypt($params['script_id']);
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $vo = new MyTalkScript();
        $vo->setScriptTitle($params['script_title']);
        $vo->setScriptContent($params['script_content']);
        $vo->setScriptType($params['script_type']);
        $vo->setUserId($this->getUserId());

        $time = time();
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime($time);

        $mapVo = new MyTalkScript();
        $mapVo->setScriptId($id);

        $logic = new MyTalkScriptLogic();
        $result = $logic->update($vo,$mapVo);

        return $result;
    }

    /**
     * 保单编辑
     * @return mixed
     */
    public function insurance_edit(){
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

        $mapVo = new MyInsurance();
        $mapVo->setInsuranceId($id);

        $logic = new MyInsuranceLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('保单不存在！');
            return false;
        }

        if($model['policy_type'] == 1){
            $mapVo = new CrmCustomer();
            $mapVo->setCustomerId($model['customer_id']);

            $logic = new CrmCustomerLogic();
            $modelCustomer = $logic->find($mapVo);
            if($modelCustomer){
                $model['customer_name'] = $modelCustomer['customer_name'];
            }
        }

        $peopleRelationList = config('app.people_relation');
        $this->assign('peopleRelationList',$peopleRelationList);

        $this->assign('model',$model);

        return $this->fetch();
    }

    /**
     * 话术编辑
     * @return mixed
     */
    public function talk_script_edit(){
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

        $mapVo = new MyTalkScript();
        $mapVo->setScriptId($id);

        $logic = new MyTalkScriptLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('话术不存在！');
            return false;
        }

        $scriptTypeList = config('app.script_type');
        $this->assign('scriptTypeList',$scriptTypeList);

        $this->assign('model',$model);

        return $this->fetch();
    }

    /**
     * 保单详情
     * @return mixed
     */
    public function insurance_view(){
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

        $mapVo = new MyInsurance();
        $mapVo->setInsuranceId($id);

        $logic = new MyInsuranceLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('保单不存在！');
            return false;
        }

        $backUrl = url('my_insurance');
        if($model['policy_type'] == 1){
            $backUrl = url('customer_insurance');

            $mapVo = new CrmCustomer();
            $mapVo->setCustomerId($model['customer_id']);

            $logic = new CrmCustomerLogic();
            $modelCustomer = $logic->find($mapVo);
            if($modelCustomer){
                $model['customer_name'] = $modelCustomer['customer_name'];
            }
        }

        $this->assign('backUrl',$backUrl);
        $this->assign('model',$model);

        return $this->fetch();
    }

    /**
     * 话术详情
     * @return mixed
     */
    public function talk_script_view(){
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

        $mapVo = new MyTalkScript();
        $mapVo->setScriptId($id);

        $logic = new MyTalkScriptLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('话术不存在！');
            return false;
        }

        $this->assign('model',$model);

        return $this->fetch();
    }

    /**
     * 保单删除
     * @return Result
     */
    public function insurance_del(){
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

        $logic = new MyInsuranceLogic();
        $vo = new MyInsurance();
        $mapVo = new MyInsurance();

        $vo->setIsDel(1);
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime(time());

        $mapVo->setInsuranceId($id);

        $result = $logic->update($vo,$mapVo);
        if($result->success){
            $result->msg = '删除成功！';

            $model = $logic->find($mapVo);
            if($model['policy_type'] == 1){
                $result->url = url('customer_insurance');
            }else{
                $result->url = url('my_insurance');
            }
        }

        return $result;
    }

    /**
     * 话术删除
     * @return Result
     */
    public function talk_script_del(){
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

        $logic = new MyTalkScriptLogic();
        $vo = new MyTalkScript();
        $mapVo = new MyTalkScript();

        $vo->setIsDel(1);
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime(time());

        $mapVo->setScriptId($id);

        $result = $logic->update($vo,$mapVo);
        if($result->success){
            $result->msg = '删除成功！';
            $result->url = url('talk_script');
        }

        return $result;
    }

    /**
     * 设置密码
     * @return mixed
     */
    public function set_password(){
        return $this->fetch();
    }

    /**
     * 设置密码-保存
     * @return mixed
     */
    public function set_password_save(){
        $result = new Result();
        $result->msg = '设置密码失败！';
        $result->success = false;

        if(!$this->request->isPost()){
            $result->msg = '设置密码失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->post();

        if(string_empty($params['old_password'])){
            $result->success = false;
            $result->msg = '请填写原始密码！';
            return $result;
        }
        $old_password = $params['old_password'];

        if(string_empty($params['new_password'])){
            $result->success = false;
            $result->msg = '请填写新的密码！';
            return $result;
        }
        $new_password = $params['new_password'];

        if(string_empty($params['confirm_password'])){
            $result->success = false;
            $result->msg = '请填写确认密码！';
            return $result;
        }
        $confirm_password = $params['confirm_password'];

        if($new_password != $confirm_password){
            $result->success = false;
            $result->msg = '两次填写密码不一致！';
            return $result;
        }

        $logic = new UserLogic();
        $result = $logic->setPassword($this->getUserId(),$old_password,$new_password);

        return response($result,200,[],'json');
    }

    /**
     * 会员管理
     * @return mixed
     */
    public function member(){
        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new UserLogic();
        $list = $logic->paginate($params);

        $this->assign('list',$list);
        $this->assign('lastTime',$lastTime);
        $this->assign('params',$params);

        $sexList = config('sex');
        $this->assign('sexList',$sexList);

        $vipLevelList = config('vip_level');
        $this->assign('vipLevelList',$vipLevelList);

        $userTypeList = config('user_type');
        $this->assign('userTypeList',$userTypeList);

        $userStatusList = config('user_status');
        $this->assign('userStatusList',$userStatusList);

        return $this->fetch();
    }

    /**
     * 会员 - 添加
     * @return mixed
     */
    public function member_add(){
        $sexList = config('sex');
        $this->assign('sexList',$sexList);

        $vipLevelList = config('vip_level');
        unset($vipLevelList[33]);
        $this->assign('vipLevelList',$vipLevelList);

        $userTypeList = config('user_type');
        $this->assign('userTypeList',$userTypeList);

        $userStatusList = config('user_status');
        $this->assign('userStatusList',$userStatusList);

        $randPassword = rand_string(6);
        $this->assign('randPassword',$randPassword);

        return $this->fetch();
    }

    /**
     * 会员 - 添加保存
     * @return Result
     */
    public function member_add_save(){
        $result = new Result();
        $result->msg = '添加失败！';
        $result->success = false;

        if(!$this->request->isPost()){
            $result->msg = '添加失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->post();

        if(string_empty($params['user_name'])){
            $result->success = false;
            $result->msg = '请填写账号名称！';
            return $result;
        }

        if(string_empty($params['nickname'])){
            $result->success = false;
            $result->msg = '请填写账号昵称！';
            return $result;
        }

        if(string_empty($params['reg_mobile'])){
            $result->success = false;
            $result->msg = '请填写手机号码！';
            return $result;
        }

        if(string_empty($params['init_password'])){
            $result->success = false;
            $result->msg = '请填写初始密码！';
            return $result;
        }

        $time = time();

        $vo = new User();
        $vo->setUserName($params['user_name']);
        $vo->setNickname($params['nickname']);
        $vo->setRegMobile($params['reg_mobile']);
        $vo->setSex($params['sex']);
        $vo->setUserType($params['user_type']);
        $vo->setUserStatus($params['user_status']);
        $vo->setVipLevel($params['vip_level']);
        $vo->setInitPassword($params['init_password']);
        $vo->setLoginPassword(md5($params['init_password']));
        $vo->setRegTime($time);

        $vo->setAddUid($this->getUserId());
        $vo->setEditUid($this->getUserId());
        $vo->setAddTime($time);
        $vo->setEditTime($time);

        $logic = new UserLogic();
        $result = $logic->add($vo);

        return $result;
    }

    /**
     * 会员 - 详情
     * @return mixed
     */
    public function member_view(){
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

        $mapVo = new User();
        $mapVo->setUserId($id);
        $mapVo->setIsDel(0);

        $logic = new UserLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('会员不存在！');
            return false;
        }

        $sexList = config('sex');
        $this->assign('sexList',$sexList);

        $model['encrypt_id'] = think_encrypt($model['user_id']);

        $this->assign('model',$model);

        return $this->fetch();
    }

    /**
     * 会员删除
     * @return Result
     */
    public function member_del(){
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

        $logic = new UserLogic();
        $vo = new User();
        $mapVo = new User();

        $vo->setIsDel(1);
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime(time());

        $mapVo->setUserId($id);

        $result = $logic->update($vo,$mapVo);
        if($result->success){
            $result->msg = '删除成功！';
            $result->url = url('My/member');
        }

        return $result;
    }

    /**
     * 会员 - 编辑
     * @return mixed
     */
    public function member_edit(){
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

        $mapVo = new User();
        $mapVo->setUserId($id);
        $mapVo->setIsDel(0);

        $logic = new UserLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('会员不存在！');
            return false;
        }

        $sexList = config('sex');
        $this->assign('sexList',$sexList);

        $vipLevelList = config('vip_level');
        $this->assign('vipLevelList',$vipLevelList);

        $userTypeList = config('user_type');
        $this->assign('userTypeList',$userTypeList);

        $userStatusList = config('user_status');
        $this->assign('userStatusList',$userStatusList);

        $model['encrypt_id'] = think_encrypt($model['user_id']);

        $this->assign('model',$model);

        return $this->fetch();
    }

    /**
     * 会员 - 编辑保存
     * @return mixed
     */
    public function member_edit_save(){
        $result = new Result();
        $result->msg = '编辑失败！';
        $result->success = false;

        if(!$this->request->isPost()){
            $result->msg = '编辑失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->post();

        if(string_empty($params['nickname'])){
            $result->success = false;
            $result->msg = '请填写账号昵称！';
            return $result;
        }

        if(string_empty($params['reg_mobile'])){
            $result->success = false;
            $result->msg = '请填写手机号码！';
            return $result;
        }

        if(string_empty($params['init_password'])){
            $result->success = false;
            $result->msg = '请填写初始密码！';
            return $result;
        }

        $id = think_decrypt($params['user_id']);
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $time = time();

        $vo = new User();
        $vo->setNickname($params['nickname']);
        $vo->setRegMobile($params['reg_mobile']);
        $vo->setSex($params['sex']);
        $vo->setUserType($params['user_type']);
        $vo->setUserStatus($params['user_status']);
        $vo->setVipLevel($params['vip_level']);
        $vo->setInitPassword($params['init_password']);
        $vo->setLoginPassword(md5($params['init_password']));

        $vo->setEditUid($this->getUserId());
        $vo->setEditTime($time);

        $mapVo = new User();
        $mapVo->setUserId($id);

        $logic = new UserLogic();
        $result = $logic->update($vo,$mapVo);

        return response($result,200,[],'json');
    }
}