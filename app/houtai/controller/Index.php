<?php
/**
 * 客户控制器 Index 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-09-27
 * Time: 17:46:11
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace app\houtai\controller;


use common\logic\CrmCustomerLogic;
use common\logic\IndustryLogic;
use common\logic\SchoolLogic;
use common\logic\SpecialtyLogic;
use common\logic\WorkPositionLogic;
use common\logic\WorkPostLogic;
use common\model\CrmCustomer;
use common\model\Industry;
use common\model\Result;
use common\model\School;
use common\model\Specialty;
use common\model\WorkPosition;
use common\model\WorkPost;

class Index extends Base
{
    public function index(){
        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        $params['add_uid'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new CrmCustomerLogic();
        $list = $logic->paginate($params);

        $this->assign('list',$list);
        $this->assign('lastTime',$lastTime);
        $this->assign('params',$params);

        $sexList = config('sex');
        $customerRelationsList = config('customer_relations');
        $customerClassList = config('customer_class');

        $this->assign('sexList',$sexList);
        $this->assign('customerRelationsList',$customerRelationsList);
        $this->assign('customerClassList',$customerClassList);

        return $this->fetch();
    }

    public function scroll_loading(){
        $result = new Result();

        $params = array();
        $params = $this->request->param();

        $params['add_uid'] = $this->getUserId();
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

        $params['add_uid'] = $this->getUserId();
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

    function add(){
        $sexList = config('sex');
        $somatotypeList = config('somatotype');
        $maritalStatusList = config('marital_status');
        $customerRelationsList = config('customer_relations');
        $customerClassList = config('customer_class');
        $educationIdList = config('education_id');
        $hobbiesIdsList = config('hobbies_ids');

        $this->assign('sexList',$sexList);
        $this->assign('somatotypeList',$somatotypeList);
        $this->assign('maritalStatusList',$maritalStatusList);
        $this->assign('customerRelationsList',$customerRelationsList);
        $this->assign('customerClassList',$customerClassList);
        $this->assign('educationIdList',$educationIdList);
        $this->assign('hobbiesIdsList',$hobbiesIdsList);

        return $this->fetch();
    }

    function add_batch(){
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

        if(strlen(trim($params['customer_name'])) == 0){
            $result->success = false;
            $result->msg = '客户名称不能为空！';
            return $result;
        }

        $vo = new CrmCustomer();
        $vo->setCustomerName($params['customer_name']);

        if(isset($params['mobile']) && is_array($params['mobile'])){
            $vo->setMobile(implode(',',letu_array_filter_empty($params['mobile'])));
        }

        $vo->setSex($params['sex']);
        $vo->setStature($params['stature']);
        $vo->setWeight($params['weight']);
        $vo->setSomatotype($params['somatotype']);
        $vo->setMaritalStatus($params['marital_status']);

        if(isset($params['birthdate']) && strlen($params['birthdate']) > 0){
            $vo->setBirthdate(strtotime($params['birthdate']));
        }

        $vo->setCustomerRelations($params['customer_relations']);
        $vo->setCustomerClass($params['customer_class']);
        $vo->setIndustryId($params['industry_id']);
        $vo->setPositionId($params['position_id']);
        $vo->setPostId($params['post_id']);
        $vo->setEducationId($params['education_id']);
        $vo->setSpecialtyId($params['specialty_id']);
        $vo->setSchoolId($params['school_id']);
        $vo->setBirthProvinceId($params['birth_province_id']);
        $vo->setBirthCityId($params['birth_city_id']);
        $vo->setBirthRegionId($params['birth_region_id']);
        $vo->setBirthProvinceName($params['birth_province_name']);
        $vo->setBirthCityName($params['birth_city_name']);
        $vo->setBirthRegionName($params['birth_region_name']);
        $vo->setBirthAddress($params['birth_address']);
        $vo->setResideProvinceId($params['reside_province_id']);
        $vo->setResideCityId($params['reside_city_id']);
        $vo->setResideRegionId($params['reside_region_id']);
        $vo->setResideProvinceName($params['reside_province_name']);
        $vo->setResideCityName($params['reside_city_name']);
        $vo->setResideRegionName($params['reside_region_name']);
        $vo->setResideAddress($params['reside_address']);
        $vo->setCompanyName($params['company_name']);
        $vo->setOfficeAddress($params['office_address']);
        $vo->setOfficePhone($params['office_phone']);
        $vo->setFax($params['fax']);
        $vo->setCompanySite($params['company_site']);
        $vo->setPersonSite($params['person_site']);
        $vo->setCompanyEmail($params['company_email']);
        $vo->setPersonEmail($params['person_email']);

        if(isset($params['hobbies_ids']) && is_array($params['hobbies_ids'])){
            $vo->setHobbiesIds(implode(',',$params['hobbies_ids']));
        }else{
            $vo->setHobbiesIds('');
        }

        $vo->setCustomerDetail($params['customer_detail']);

        $time = time();
        $vo->setAddUid($this->getUserId());
        $vo->setEditUid($this->getUserId());
        $vo->setAddTime($time);
        $vo->setEditTime($time);

        $logic = new CrmCustomerLogic();
        $result = $logic->add($vo);

        return $result;
    }

    public function add_batch_save(){
        $result = new Result();
        $result->msg = '添加失败！';
        $result->success = false;

        if(!$this->request->isPost()){
            $result->msg = '添加失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->post();

        if(strlen(trim($params['batch_customer'])) == 0){
            $result->success = false;
            $result->msg = '客户文本不能为空！';
            return $result;
        }

        $customer_list = explode("\n",$params['batch_customer']);

        $time = time();
        $dataList = array();
        if(is_array($customer_list) && count($customer_list) > 0){
            foreach($customer_list as $k=>$v){
                $customer_split = explode('=',$v);
                $data = array();
                $data['customer_name'] = $customer_split[0];
                $data['mobile'] = $customer_split[1];
                $data['sex'] = 2;
                $data['add_uid'] = $this->getUserId();
                $data['edit_uid'] = $this->getUserId();
                $data['add_time'] = $time;
                $data['edit_time'] = $time;
                $data['customer_class'] = 'D';

                array_push($dataList,$data);
            }
        }

        $logic = new CrmCustomerLogic();
        $result = $logic->addBatch($dataList);

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
        }

        !isset($model['mobile'][0]) && $model['mobile'][0] = '';
        !isset($model['mobile'][1]) && $model['mobile'][1] = '';
        !isset($model['mobile'][2]) && $model['mobile'][2] = '';

        if($model['birthdate'] > 0){
            $model['birthdate'] = date('Y-m-d',$model['birthdate']);
        }else{
            $model['birthdate'] = '';
        }

        $model['hobbies_ids'] = letu_array_filter_empty(explode(',',$model['hobbies_ids']));

        $model['industry_name'] = '';
        $model['position_name'] = '';
        $model['post_name'] = '';
        $model['specialty_name'] = '';
        $model['school_name'] = '';

        $logic = new IndustryLogic();
        $mapVo = new Industry();
        $mapVo->setIndustryId($model['industry_id']);
        $data = $logic->find($mapVo);
        if($data){
            $model['industry_name'] = $data['industry_name'];
        }

        $logic = new WorkPositionLogic();
        $mapVo = new WorkPosition();
        $mapVo->setPositionId($model['position_id']);
        $data = $logic->find($mapVo);
        if($data){
            $model['position_name'] = $data['position_name'];
        }

        $logic = new WorkPostLogic();
        $mapVo = new WorkPost();
        $mapVo->setPostId($model['post_id']);
        $data = $logic->find($mapVo);
        if($data){
            $model['post_name'] = $data['post_name'];
        }

        $logic = new SpecialtyLogic();
        $mapVo = new Specialty();
        $mapVo->setSpecialtyId($model['specialty_id']);
        $data = $logic->find($mapVo);
        if($data){
            $model['specialty_name'] = $data['specialty_name'];
        }

        $logic = new SchoolLogic();
        $mapVo = new School();
        $mapVo->setSchoolId($model['school_id']);
        $data = $logic->find($mapVo);
        if($data){
            $model['school_name'] = $data['school_name'];
        }

        $this->assign('model',$model);

        return $this->fetch();
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

        $mapVo = new CrmCustomer();
        $mapVo->setCustomerId($id);

        $logic = new CrmCustomerLogic();
        $model = $logic->find($mapVo);

        $mobileList = $model['mobile'];
        $model['mobile'] = array();
        if(!string_empty($mobileList)){
            $model['mobile'] = explode(',',$mobileList);
        }

        !isset($model['mobile'][0]) && $model['mobile'][0] = '';
        !isset($model['mobile'][1]) && $model['mobile'][1] = '';
        !isset($model['mobile'][2]) && $model['mobile'][2] = '';

        if($model['birthdate'] > 0){
            $model['birthdate'] = date('Y-m-d',$model['birthdate']);
        }else{
            $model['birthdate'] = '';
        }

        $model['hobbies_ids'] = letu_array_filter_empty(explode(',',$model['hobbies_ids']));

        $model['industry_name'] = '';
        $model['position_name'] = '';
        $model['post_name'] = '';
        $model['specialty_name'] = '';
        $model['school_name'] = '';

        $logic = new IndustryLogic();
        $mapVo = new Industry();
        $mapVo->setIndustryId($model['industry_id']);
        $data = $logic->find($mapVo);
        if($data){
            $model['industry_name'] = $data['industry_name'];
        }

        $logic = new WorkPositionLogic();
        $mapVo = new WorkPosition();
        $mapVo->setPositionId($model['position_id']);
        $data = $logic->find($mapVo);
        if($data){
            $model['position_name'] = $data['position_name'];
        }

        $logic = new WorkPostLogic();
        $mapVo = new WorkPost();
        $mapVo->setPostId($model['post_id']);
        $data = $logic->find($mapVo);
        if($data){
            $model['post_name'] = $data['post_name'];
        }

        $logic = new SpecialtyLogic();
        $mapVo = new Specialty();
        $mapVo->setSpecialtyId($model['specialty_id']);
        $data = $logic->find($mapVo);
        if($data){
            $model['specialty_name'] = $data['specialty_name'];
        }

        $logic = new SchoolLogic();
        $mapVo = new School();
        $mapVo->setSchoolId($model['school_id']);
        $data = $logic->find($mapVo);
        if($data){
            $model['school_name'] = $data['school_name'];
        }

        $sexList = config('sex');
        $somatotypeList = config('somatotype');
        $maritalStatusList = config('marital_status');
        $customerRelationsList = config('customer_relations');
        $customerClassList = config('customer_class');
        $educationIdList = config('education_id');
        $hobbiesIdsList = config('hobbies_ids');

        $this->assign('sexList',$sexList);
        $this->assign('somatotypeList',$somatotypeList);
        $this->assign('maritalStatusList',$maritalStatusList);
        $this->assign('customerRelationsList',$customerRelationsList);
        $this->assign('customerClassList',$customerClassList);
        $this->assign('educationIdList',$educationIdList);
        $this->assign('hobbiesIdsList',$hobbiesIdsList);

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

        if(strlen(trim($params['customer_name'])) == 0){
            $result->success = false;
            $result->msg = '客户名称不能为空！';
            return $result;
        }

        $id = think_decrypt($params['customer_id']);
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $vo = new CrmCustomer();
        $vo->setCustomerName($params['customer_name']);

        if(isset($params['mobile']) && is_array($params['mobile'])){
            $vo->setMobile(implode(',',letu_array_filter_empty($params['mobile'])));
        }

        $vo->setSex($params['sex']);
        $vo->setStature($params['stature']);
        $vo->setWeight($params['weight']);
        $vo->setSomatotype($params['somatotype']);
        $vo->setMaritalStatus($params['marital_status']);

        if(isset($params['birthdate']) && strlen($params['birthdate']) > 0){
            $vo->setBirthdate(strtotime($params['birthdate']));
        }

        $vo->setCustomerRelations($params['customer_relations']);
        $vo->setCustomerClass($params['customer_class']);
        $vo->setIndustryId($params['industry_id']);
        $vo->setPositionId($params['position_id']);
        $vo->setPostId($params['post_id']);
        $vo->setEducationId($params['education_id']);
        $vo->setSpecialtyId($params['specialty_id']);
        $vo->setSchoolId($params['school_id']);
        $vo->setBirthProvinceId($params['birth_province_id']);
        $vo->setBirthCityId($params['birth_city_id']);
        $vo->setBirthRegionId($params['birth_region_id']);
        $vo->setBirthProvinceName($params['birth_province_name']);
        $vo->setBirthCityName($params['birth_city_name']);
        $vo->setBirthRegionName($params['birth_region_name']);
        $vo->setBirthAddress($params['birth_address']);
        $vo->setResideProvinceId($params['reside_province_id']);
        $vo->setResideCityId($params['reside_city_id']);
        $vo->setResideRegionId($params['reside_region_id']);
        $vo->setResideProvinceName($params['reside_province_name']);
        $vo->setResideCityName($params['reside_city_name']);
        $vo->setResideRegionName($params['reside_region_name']);
        $vo->setResideAddress($params['reside_address']);
        $vo->setCompanyName($params['company_name']);
        $vo->setOfficeAddress($params['office_address']);
        $vo->setOfficePhone($params['office_phone']);
        $vo->setFax($params['fax']);
        $vo->setCompanySite($params['company_site']);
        $vo->setPersonSite($params['person_site']);
        $vo->setCompanyEmail($params['company_email']);
        $vo->setPersonEmail($params['person_email']);

        if(isset($params['hobbies_ids']) && is_array($params['hobbies_ids'])){
            $vo->setHobbiesIds(implode(',',$params['hobbies_ids']));
        }else{
            $vo->setHobbiesIds('');
        }

        $vo->setCustomerDetail($params['customer_detail']);

        $time = time();
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime($time);

        $mapVo = new CrmCustomer();
        $mapVo->setCustomerId($id);

        $logic = new CrmCustomerLogic();
        $result = $logic->update($vo,$mapVo);

        return $result;
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

        $logic = new CrmCustomerLogic();
        $vo = new CrmCustomer();
        $mapVo = new CrmCustomer();

        $vo->setIsDel(1);
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime(time());

        $mapVo->setCustomerId($id);

        $result = $logic->update($vo,$mapVo);
        if($result->success){
            $result->msg = '删除成功！';
            $result->url = url('index');
        }

        return $result;
    }
}
