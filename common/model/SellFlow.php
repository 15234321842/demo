<?php
/**
* 销售流程 Model 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-07
* Time: 21:54:35
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\model;

class SellFlow
{
    const TABLE_NAME = 'sell_flow';
    const PRIMARY_KEY = 'customer_id';

    /**
    * 设置字段-值集合
    */
    private $set_data_list = array();

    private $customer_id;
    private $sandler_trust;
    private $sandler_appoint;
    private $sandler_pain;
    private $sandler_budget;
    private $sandler_decision;
    private $sandler_scheme;
    private $sandler_deal;
    private $trust_content;
    private $appoint_content;
    private $pain_content;
    private $budget_content;
    private $decision_content;
    private $scheme_content;
    private $deal_content;
    
    /**
    * 字段属性 - 客户ID
    * @return $customer_id
    */
    public function getCustomerId(){
        return $this->customer_id;
    }
    
    /**
    * 字段属性 - 桑德拉_信任：0 否 1 是
    * @return $sandler_trust
    */
    public function getSandlerTrust(){
        return $this->sandler_trust;
    }
    
    /**
    * 字段属性 - 桑德拉_约定：0 否 1 是
    * @return $sandler_appoint
    */
    public function getSandlerAppoint(){
        return $this->sandler_appoint;
    }
    
    /**
    * 字段属性 - 桑德拉_痛点：0 否 1 是
    * @return $sandler_pain
    */
    public function getSandlerPain(){
        return $this->sandler_pain;
    }
    
    /**
    * 字段属性 - 桑德拉_预算：0 否 1 是
    * @return $sandler_budget
    */
    public function getSandlerBudget(){
        return $this->sandler_budget;
    }
    
    /**
    * 字段属性 - 桑德拉_决策：0 否 1 是
    * @return $sandler_decision
    */
    public function getSandlerDecision(){
        return $this->sandler_decision;
    }
    
    /**
    * 字段属性 - 桑德拉_方案：0 否 1 是
    * @return $sandler_scheme
    */
    public function getSandlerScheme(){
        return $this->sandler_scheme;
    }
    
    /**
    * 字段属性 - 桑德拉_后售：0 否 1 是
    * @return $sandler_deal
    */
    public function getSandlerDeal(){
        return $this->sandler_deal;
    }
    
    /**
    * 字段属性 - 信任_内容
    * @return $trust_content
    */
    public function getTrustContent(){
        return $this->trust_content;
    }
    
    /**
    * 字段属性 - 约定_内容
    * @return $appoint_content
    */
    public function getAppointContent(){
        return $this->appoint_content;
    }
    
    /**
    * 字段属性 - 痛点_内容
    * @return $pain_content
    */
    public function getPainContent(){
        return $this->pain_content;
    }
    
    /**
    * 字段属性 - 预算_内容
    * @return $budget_content
    */
    public function getBudgetContent(){
        return $this->budget_content;
    }
    
    /**
    * 字段属性 - 决策_内容
    * @return $decision_content
    */
    public function getDecisionContent(){
        return $this->decision_content;
    }
    
    /**
    * 字段属性 - 方案_内容
    * @return $scheme_content
    */
    public function getSchemeContent(){
        return $this->scheme_content;
    }
    
    /**
    * 字段属性 - 后售_内容
    * @return $deal_content
    */
    public function getDealContent(){
        return $this->deal_content;
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
    * 字段方法 - 桑德拉_信任：0 否 1 是
    * @param $sandler_trust
    * @return void
    */
    public function setSandlerTrust($sandler_trust){
        $this->sandler_trust = $sandler_trust;
        $this->set_data_list['sandler_trust'] = &$this->sandler_trust;
    }
    
    /**
    * 字段方法 - 桑德拉_约定：0 否 1 是
    * @param $sandler_appoint
    * @return void
    */
    public function setSandlerAppoint($sandler_appoint){
        $this->sandler_appoint = $sandler_appoint;
        $this->set_data_list['sandler_appoint'] = &$this->sandler_appoint;
    }
    
    /**
    * 字段方法 - 桑德拉_痛点：0 否 1 是
    * @param $sandler_pain
    * @return void
    */
    public function setSandlerPain($sandler_pain){
        $this->sandler_pain = $sandler_pain;
        $this->set_data_list['sandler_pain'] = &$this->sandler_pain;
    }
    
    /**
    * 字段方法 - 桑德拉_预算：0 否 1 是
    * @param $sandler_budget
    * @return void
    */
    public function setSandlerBudget($sandler_budget){
        $this->sandler_budget = $sandler_budget;
        $this->set_data_list['sandler_budget'] = &$this->sandler_budget;
    }
    
    /**
    * 字段方法 - 桑德拉_决策：0 否 1 是
    * @param $sandler_decision
    * @return void
    */
    public function setSandlerDecision($sandler_decision){
        $this->sandler_decision = $sandler_decision;
        $this->set_data_list['sandler_decision'] = &$this->sandler_decision;
    }
    
    /**
    * 字段方法 - 桑德拉_方案：0 否 1 是
    * @param $sandler_scheme
    * @return void
    */
    public function setSandlerScheme($sandler_scheme){
        $this->sandler_scheme = $sandler_scheme;
        $this->set_data_list['sandler_scheme'] = &$this->sandler_scheme;
    }
    
    /**
    * 字段方法 - 桑德拉_后售：0 否 1 是
    * @param $sandler_deal
    * @return void
    */
    public function setSandlerDeal($sandler_deal){
        $this->sandler_deal = $sandler_deal;
        $this->set_data_list['sandler_deal'] = &$this->sandler_deal;
    }
    
    /**
    * 字段方法 - 信任_内容
    * @param $trust_content
    * @return void
    */
    public function setTrustContent($trust_content){
        $this->trust_content = $trust_content;
        $this->set_data_list['trust_content'] = &$this->trust_content;
    }
    
    /**
    * 字段方法 - 约定_内容
    * @param $appoint_content
    * @return void
    */
    public function setAppointContent($appoint_content){
        $this->appoint_content = $appoint_content;
        $this->set_data_list['appoint_content'] = &$this->appoint_content;
    }
    
    /**
    * 字段方法 - 痛点_内容
    * @param $pain_content
    * @return void
    */
    public function setPainContent($pain_content){
        $this->pain_content = $pain_content;
        $this->set_data_list['pain_content'] = &$this->pain_content;
    }
    
    /**
    * 字段方法 - 预算_内容
    * @param $budget_content
    * @return void
    */
    public function setBudgetContent($budget_content){
        $this->budget_content = $budget_content;
        $this->set_data_list['budget_content'] = &$this->budget_content;
    }
    
    /**
    * 字段方法 - 决策_内容
    * @param $decision_content
    * @return void
    */
    public function setDecisionContent($decision_content){
        $this->decision_content = $decision_content;
        $this->set_data_list['decision_content'] = &$this->decision_content;
    }
    
    /**
    * 字段方法 - 方案_内容
    * @param $scheme_content
    * @return void
    */
    public function setSchemeContent($scheme_content){
        $this->scheme_content = $scheme_content;
        $this->set_data_list['scheme_content'] = &$this->scheme_content;
    }
    
    /**
    * 字段方法 - 后售_内容
    * @param $deal_content
    * @return void
    */
    public function setDealContent($deal_content){
        $this->deal_content = $deal_content;
        $this->set_data_list['deal_content'] = &$this->deal_content;
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