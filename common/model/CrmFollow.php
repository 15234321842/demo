<?php
/**
* 客户跟进 Model 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-18
* Time: 15:44:10
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\model;

class CrmFollow
{
    const TABLE_NAME = 'crm_follow';
    const PRIMARY_KEY = 'follow_id';

    /**
    * 设置字段-值集合
    */
    private $set_data_list = array();

    private $follow_id;
    private $customer_id;
    private $follow_time;
    private $follow_log;
    private $follow_type;
    private $follow_uid;
    private $add_uid;
    private $add_time;
    private $edit_uid;
    private $edit_time;
    private $is_del;
    
    /**
    * 字段属性 - 跟进ID
    * @return $follow_id
    */
    public function getFollowId(){
        return $this->follow_id;
    }
    
    /**
    * 字段属性 - 客户ID
    * @return $customer_id
    */
    public function getCustomerId(){
        return $this->customer_id;
    }
    
    /**
    * 字段属性 - 跟进时间
    * @return $follow_time
    */
    public function getFollowTime(){
        return $this->follow_time;
    }
    
    /**
    * 字段属性 - 跟进日志
    * @return $follow_log
    */
    public function getFollowLog(){
        return $this->follow_log;
    }
    
    /**
    * 字段属性 - 跟进类型：0 拜访 1 跟进 2 促成 3 成交 4 回访
    * @return $follow_type
    */
    public function getFollowType(){
        return $this->follow_type;
    }
    
    /**
    * 字段属性 - 跟进人员
    * @return $follow_uid
    */
    public function getFollowUid(){
        return $this->follow_uid;
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
    * 字段方法 - 跟进ID
    * @param $follow_id
    * @return void
    */
    public function setFollowId($follow_id){
        $this->follow_id = $follow_id;
        $this->set_data_list['follow_id'] = &$this->follow_id;
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
    * 字段方法 - 跟进时间
    * @param $follow_time
    * @return void
    */
    public function setFollowTime($follow_time){
        $this->follow_time = $follow_time;
        $this->set_data_list['follow_time'] = &$this->follow_time;
    }
    
    /**
    * 字段方法 - 跟进日志
    * @param $follow_log
    * @return void
    */
    public function setFollowLog($follow_log){
        $this->follow_log = $follow_log;
        $this->set_data_list['follow_log'] = &$this->follow_log;
    }
    
    /**
    * 字段方法 - 跟进类型：0 拜访 1 跟进 2 促成 3 成交 4 回访
    * @param $follow_type
    * @return void
    */
    public function setFollowType($follow_type){
        $this->follow_type = $follow_type;
        $this->set_data_list['follow_type'] = &$this->follow_type;
    }
    
    /**
    * 字段方法 - 跟进人员
    * @param $follow_uid
    * @return void
    */
    public function setFollowUid($follow_uid){
        $this->follow_uid = $follow_uid;
        $this->set_data_list['follow_uid'] = &$this->follow_uid;
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