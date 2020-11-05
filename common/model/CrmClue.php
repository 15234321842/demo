<?php
/**
* 客户线索 Model 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-16
* Time: 20:11:10
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\model;

class CrmClue
{
    const TABLE_NAME = 'crm_clue';
    const PRIMARY_KEY = 'clue_id';

    /**
    * 设置字段-值集合
    */
    private $set_data_list = array();

    private $clue_id;
    private $customer_id;
    private $clue_time;
    private $clue_content;
    private $clue_uid;
    private $add_uid;
    private $add_time;
    private $edit_uid;
    private $edit_time;
    private $is_del;
    
    /**
    * 字段属性 - 线索ID
    * @return $clue_id
    */
    public function getClueId(){
        return $this->clue_id;
    }
    
    /**
    * 字段属性 - 客户ID
    * @return $customer_id
    */
    public function getCustomerId(){
        return $this->customer_id;
    }
    
    /**
    * 字段属性 - 线索时间
    * @return $clue_time
    */
    public function getClueTime(){
        return $this->clue_time;
    }
    
    /**
    * 字段属性 - 线索内容
    * @return $clue_content
    */
    public function getClueContent(){
        return $this->clue_content;
    }
    
    /**
    * 字段属性 - 线索情报员ID
    * @return $clue_uid
    */
    public function getClueUid(){
        return $this->clue_uid;
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
    * 字段方法 - 线索ID
    * @param $clue_id
    * @return void
    */
    public function setClueId($clue_id){
        $this->clue_id = $clue_id;
        $this->set_data_list['clue_id'] = &$this->clue_id;
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
    * 字段方法 - 线索时间
    * @param $clue_time
    * @return void
    */
    public function setClueTime($clue_time){
        $this->clue_time = $clue_time;
        $this->set_data_list['clue_time'] = &$this->clue_time;
    }
    
    /**
    * 字段方法 - 线索内容
    * @param $clue_content
    * @return void
    */
    public function setClueContent($clue_content){
        $this->clue_content = $clue_content;
        $this->set_data_list['clue_content'] = &$this->clue_content;
    }
    
    /**
    * 字段方法 - 线索情报员ID
    * @param $clue_uid
    * @return void
    */
    public function setClueUid($clue_uid){
        $this->clue_uid = $clue_uid;
        $this->set_data_list['clue_uid'] = &$this->clue_uid;
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