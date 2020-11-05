<?php
/**
* 保险单 Model 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-22
* Time: 19:53:38
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\model;

class MyInsurance
{
    const TABLE_NAME = 'my_insurance';
    const PRIMARY_KEY = 'insurance_id';

    /**
    * 设置字段-值集合
    */
    private $set_data_list = array();

    private $insurance_id;
    private $user_id;
    private $insurance_company;
    private $service_telephone;
    private $policy_holder;
    private $customer_id;
    private $insured;
    private $people_relation;
    private $first_pay_date;
    private $next_pay_date;
    private $effective_date;
    private $first_premium;
    private $insurance_main;
    private $pay_year;
    private $insurance_detail;
    private $policy_type;
    private $link_phone;
    private $link_address;
    private $add_uid;
    private $add_time;
    private $edit_uid;
    private $edit_time;
    private $is_del;
    
    /**
    * 字段属性 - 保险单ID
    * @return $insurance_id
    */
    public function getInsuranceId(){
        return $this->insurance_id;
    }
    
    /**
    * 字段属性 - 用户ID
    * @return $user_id
    */
    public function getUserId(){
        return $this->user_id;
    }
    
    /**
    * 字段属性 - 保险公司
    * @return $insurance_company
    */
    public function getInsuranceCompany(){
        return $this->insurance_company;
    }
    
    /**
    * 字段属性 - 客服电话
    * @return $service_telephone
    */
    public function getServiceTelephone(){
        return $this->service_telephone;
    }
    
    /**
    * 字段属性 - 投保人
    * @return $policy_holder
    */
    public function getPolicyHolder(){
        return $this->policy_holder;
    }
    
    /**
    * 字段属性 - 客户ID
    * @return $customer_id
    */
    public function getCustomerId(){
        return $this->customer_id;
    }
    
    /**
    * 字段属性 - 被保险人
    * @return $insured
    */
    public function getInsured(){
        return $this->insured;
    }
    
    /**
    * 字段属性 - 人员关系：0 本人 1 丈夫 2 妻子 3 儿子 4 女儿 5 父亲 6 母亲 7 员工
    * @return $people_relation
    */
    public function getPeopleRelation(){
        return $this->people_relation;
    }
    
    /**
    * 字段属性 - 首次缴费日期
    * @return $first_pay_date
    */
    public function getFirstPayDate(){
        return $this->first_pay_date;
    }
    
    /**
    * 字段属性 - 下次缴费日期
    * @return $next_pay_date
    */
    public function getNextPayDate(){
        return $this->next_pay_date;
    }
    
    /**
    * 字段属性 - 生效日期
    * @return $effective_date
    */
    public function getEffectiveDate(){
        return $this->effective_date;
    }
    
    /**
    * 字段属性 - 首年保费
    * @return $first_premium
    */
    public function getFirstPremium(){
        return $this->first_premium;
    }
    
    /**
    * 字段属性 - 主险名称
    * @return $insurance_main
    */
    public function getInsuranceMain(){
        return $this->insurance_main;
    }
    
    /**
    * 字段属性 - 缴费年限
    * @return $pay_year
    */
    public function getPayYear(){
        return $this->pay_year;
    }
    
    /**
    * 字段属性 - 保险明细：险种名称/保障期限/交费期限/保额
    * @return $insurance_detail
    */
    public function getInsuranceDetail(){
        return $this->insurance_detail;
    }
    
    /**
    * 字段属性 - 保单类别：0 我的 1 客户
    * @return $policy_type
    */
    public function getPolicyType(){
        return $this->policy_type;
    }
    
    /**
    * 字段属性 - 联系电话
    * @return $link_phone
    */
    public function getLinkPhone(){
        return $this->link_phone;
    }
    
    /**
    * 字段属性 - 联系地址
    * @return $link_address
    */
    public function getLinkAddress(){
        return $this->link_address;
    }
    
    /**
    * 字段属性 - 添加用户ID
    * @return $add_uid
    */
    public function getAddUid(){
        return $this->add_uid;
    }
    
    /**
    * 字段属性 - 添加时间
    * @return $add_time
    */
    public function getAddTime(){
        return $this->add_time;
    }
    
    /**
    * 字段属性 - 编辑用户ID
    * @return $edit_uid
    */
    public function getEditUid(){
        return $this->edit_uid;
    }
    
    /**
    * 字段属性 - 编辑时间
    * @return $edit_time
    */
    public function getEditTime(){
        return $this->edit_time;
    }
    
    /**
    * 字段属性 - 是否删除：0 正常 1 删除
    * @return $is_del
    */
    public function getIsDel(){
        return $this->is_del;
    }
    
    /**
    * 字段方法 - 保险单ID
    * @param $insurance_id
    * @return void
    */
    public function setInsuranceId($insurance_id){
        $this->insurance_id = $insurance_id;
        $this->set_data_list['insurance_id'] = &$this->insurance_id;
    }
    
    /**
    * 字段方法 - 用户ID
    * @param $user_id
    * @return void
    */
    public function setUserId($user_id){
        $this->user_id = $user_id;
        $this->set_data_list['user_id'] = &$this->user_id;
    }
    
    /**
    * 字段方法 - 保险公司
    * @param $insurance_company
    * @return void
    */
    public function setInsuranceCompany($insurance_company){
        $this->insurance_company = $insurance_company;
        $this->set_data_list['insurance_company'] = &$this->insurance_company;
    }
    
    /**
    * 字段方法 - 客服电话
    * @param $service_telephone
    * @return void
    */
    public function setServiceTelephone($service_telephone){
        $this->service_telephone = $service_telephone;
        $this->set_data_list['service_telephone'] = &$this->service_telephone;
    }
    
    /**
    * 字段方法 - 投保人
    * @param $policy_holder
    * @return void
    */
    public function setPolicyHolder($policy_holder){
        $this->policy_holder = $policy_holder;
        $this->set_data_list['policy_holder'] = &$this->policy_holder;
    }
    
    /**
    * 字段方法 - 客户ID
    * @param $customer_id
    * @return void
    */
    public function setCustomerId($customer_id){
        $this->customer_id = $customer_id;
        $this->set_data_list['customer_id'] = &$this->customer_id;
    }
    
    /**
    * 字段方法 - 被保险人
    * @param $insured
    * @return void
    */
    public function setInsured($insured){
        $this->insured = $insured;
        $this->set_data_list['insured'] = &$this->insured;
    }
    
    /**
    * 字段方法 - 人员关系：0 本人 1 丈夫 2 妻子 3 儿子 4 女儿 5 父亲 6 母亲 7 员工
    * @param $people_relation
    * @return void
    */
    public function setPeopleRelation($people_relation){
        $this->people_relation = $people_relation;
        $this->set_data_list['people_relation'] = &$this->people_relation;
    }
    
    /**
    * 字段方法 - 首次缴费日期
    * @param $first_pay_date
    * @return void
    */
    public function setFirstPayDate($first_pay_date){
        $this->first_pay_date = $first_pay_date;
        $this->set_data_list['first_pay_date'] = &$this->first_pay_date;
    }
    
    /**
    * 字段方法 - 下次缴费日期
    * @param $next_pay_date
    * @return void
    */
    public function setNextPayDate($next_pay_date){
        $this->next_pay_date = $next_pay_date;
        $this->set_data_list['next_pay_date'] = &$this->next_pay_date;
    }
    
    /**
    * 字段方法 - 生效日期
    * @param $effective_date
    * @return void
    */
    public function setEffectiveDate($effective_date){
        $this->effective_date = $effective_date;
        $this->set_data_list['effective_date'] = &$this->effective_date;
    }
    
    /**
    * 字段方法 - 首年保费
    * @param $first_premium
    * @return void
    */
    public function setFirstPremium($first_premium){
        $this->first_premium = $first_premium;
        $this->set_data_list['first_premium'] = &$this->first_premium;
    }
    
    /**
    * 字段方法 - 主险名称
    * @param $insurance_main
    * @return void
    */
    public function setInsuranceMain($insurance_main){
        $this->insurance_main = $insurance_main;
        $this->set_data_list['insurance_main'] = &$this->insurance_main;
    }
    
    /**
    * 字段方法 - 缴费年限
    * @param $pay_year
    * @return void
    */
    public function setPayYear($pay_year){
        $this->pay_year = $pay_year;
        $this->set_data_list['pay_year'] = &$this->pay_year;
    }
    
    /**
    * 字段方法 - 保险明细：险种名称/保障期限/交费期限/保额
    * @param $insurance_detail
    * @return void
    */
    public function setInsuranceDetail($insurance_detail){
        $this->insurance_detail = $insurance_detail;
        $this->set_data_list['insurance_detail'] = &$this->insurance_detail;
    }
    
    /**
    * 字段方法 - 保单类别：0 我的 1 客户
    * @param $policy_type
    * @return void
    */
    public function setPolicyType($policy_type){
        $this->policy_type = $policy_type;
        $this->set_data_list['policy_type'] = &$this->policy_type;
    }
    
    /**
    * 字段方法 - 联系电话
    * @param $link_phone
    * @return void
    */
    public function setLinkPhone($link_phone){
        $this->link_phone = $link_phone;
        $this->set_data_list['link_phone'] = &$this->link_phone;
    }
    
    /**
    * 字段方法 - 联系地址
    * @param $link_address
    * @return void
    */
    public function setLinkAddress($link_address){
        $this->link_address = $link_address;
        $this->set_data_list['link_address'] = &$this->link_address;
    }
    
    /**
    * 字段方法 - 添加用户ID
    * @param $add_uid
    * @return void
    */
    public function setAddUid($add_uid){
        $this->add_uid = $add_uid;
        $this->set_data_list['add_uid'] = &$this->add_uid;
    }
    
    /**
    * 字段方法 - 添加时间
    * @param $add_time
    * @return void
    */
    public function setAddTime($add_time){
        $this->add_time = $add_time;
        $this->set_data_list['add_time'] = &$this->add_time;
    }
    
    /**
    * 字段方法 - 编辑用户ID
    * @param $edit_uid
    * @return void
    */
    public function setEditUid($edit_uid){
        $this->edit_uid = $edit_uid;
        $this->set_data_list['edit_uid'] = &$this->edit_uid;
    }
    
    /**
    * 字段方法 - 编辑时间
    * @param $edit_time
    * @return void
    */
    public function setEditTime($edit_time){
        $this->edit_time = $edit_time;
        $this->set_data_list['edit_time'] = &$this->edit_time;
    }
    
    /**
    * 字段方法 - 是否删除：0 正常 1 删除
    * @param $is_del
    * @return void
    */
    public function setIsDel($is_del){
        $this->is_del = $is_del;
        $this->set_data_list['is_del'] = &$this->is_del;
    }
    
    /**
    * 获取设置字段-值集合，标记添加、更新的字段集合
    */
    public function getSetDataList(){
        return $this->set_data_list;
    }

    /**
    * 清空设置字段-值集合
    */
    public function clearSetDataList(){
        $this->set_data_list = array();
    }
}