<?php
/**
* 销售记录 Model 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-18
* Time: 21:37:48
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\model;

class SellRecord
{
    const TABLE_NAME = 'sell_record';
    const PRIMARY_KEY = 'record_id';

    /**
    * 设置字段-值集合
    */
    private $set_data_list = array();

    private $record_id;
    private $customer_id;
    private $sell_date;
    private $sell_money;
    private $sell_explain;
    private $add_uid;
    private $add_time;
    private $edit_uid;
    private $edit_time;
    private $is_del;
    
    /**
    * 字段属性 - 销售记录ID
    * @return $record_id
    */
    public function getRecordId(){
        return $this->record_id;
    }
    
    /**
    * 字段属性 - 客户ID
    * @return $customer_id
    */
    public function getCustomerId(){
        return $this->customer_id;
    }
    
    /**
    * 字段属性 - 销售日期
    * @return $sell_date
    */
    public function getSellDate(){
        return $this->sell_date;
    }
    
    /**
    * 字段属性 - 销售金额
    * @return $sell_money
    */
    public function getSellMoney(){
        return $this->sell_money;
    }
    
    /**
    * 字段属性 - 销售说明
    * @return $sell_explain
    */
    public function getSellExplain(){
        return $this->sell_explain;
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
    * 字段方法 - 销售记录ID
    * @param $record_id
    * @return void
    */
    public function setRecordId($record_id){
        $this->record_id = $record_id;
        $this->set_data_list['record_id'] = &$this->record_id;
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
    * 字段方法 - 销售日期
    * @param $sell_date
    * @return void
    */
    public function setSellDate($sell_date){
        $this->sell_date = $sell_date;
        $this->set_data_list['sell_date'] = &$this->sell_date;
    }
    
    /**
    * 字段方法 - 销售金额
    * @param $sell_money
    * @return void
    */
    public function setSellMoney($sell_money){
        $this->sell_money = $sell_money;
        $this->set_data_list['sell_money'] = &$this->sell_money;
    }
    
    /**
    * 字段方法 - 销售说明
    * @param $sell_explain
    * @return void
    */
    public function setSellExplain($sell_explain){
        $this->sell_explain = $sell_explain;
        $this->set_data_list['sell_explain'] = &$this->sell_explain;
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