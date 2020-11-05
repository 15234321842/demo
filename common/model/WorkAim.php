<?php
/**
* 目标 Model 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-21
* Time: 08:53:26
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\model;

class WorkAim
{
    const TABLE_NAME = 'work_aim';
    const PRIMARY_KEY = 'aim_id';

    /**
    * 设置字段-值集合
    */
    private $set_data_list = array();

    private $aim_id;
    private $user_id;
    private $aim_name;
    private $date_begin;
    private $date_end;
    private $realize_plan;
    private $finish_status;
    private $finish_summary;
    private $add_uid;
    private $add_time;
    private $edit_uid;
    private $edit_time;
    private $is_del;
    
    /**
    * 字段属性 - 目标ID
    * @return $aim_id
    */
    public function getAimId(){
        return $this->aim_id;
    }
    
    /**
    * 字段属性 - 用户ID
    * @return $user_id
    */
    public function getUserId(){
        return $this->user_id;
    }
    
    /**
    * 字段属性 - 目标名称
    * @return $aim_name
    */
    public function getAimName(){
        return $this->aim_name;
    }
    
    /**
    * 字段属性 - 目标开始日期
    * @return $date_begin
    */
    public function getDateBegin(){
        return $this->date_begin;
    }
    
    /**
    * 字段属性 - 目标结束日期
    * @return $date_end
    */
    public function getDateEnd(){
        return $this->date_end;
    }
    
    /**
    * 字段属性 - 目标实现计划
    * @return $realize_plan
    */
    public function getRealizePlan(){
        return $this->realize_plan;
    }
    
    /**
    * 字段属性 - 完成状态：0 未完成 1 已完成
    * @return $finish_status
    */
    public function getFinishStatus(){
        return $this->finish_status;
    }
    
    /**
    * 字段属性 - 目标完成总结
    * @return $finish_summary
    */
    public function getFinishSummary(){
        return $this->finish_summary;
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
    * 字段方法 - 目标ID
    * @param $aim_id
    * @return void
    */
    public function setAimId($aim_id){
        $this->aim_id = $aim_id;
        $this->set_data_list['aim_id'] = &$this->aim_id;
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
    * 字段方法 - 目标名称
    * @param $aim_name
    * @return void
    */
    public function setAimName($aim_name){
        $this->aim_name = $aim_name;
        $this->set_data_list['aim_name'] = &$this->aim_name;
    }
    
    /**
    * 字段方法 - 目标开始日期
    * @param $date_begin
    * @return void
    */
    public function setDateBegin($date_begin){
        $this->date_begin = $date_begin;
        $this->set_data_list['date_begin'] = &$this->date_begin;
    }
    
    /**
    * 字段方法 - 目标结束日期
    * @param $date_end
    * @return void
    */
    public function setDateEnd($date_end){
        $this->date_end = $date_end;
        $this->set_data_list['date_end'] = &$this->date_end;
    }
    
    /**
    * 字段方法 - 目标实现计划
    * @param $realize_plan
    * @return void
    */
    public function setRealizePlan($realize_plan){
        $this->realize_plan = $realize_plan;
        $this->set_data_list['realize_plan'] = &$this->realize_plan;
    }
    
    /**
    * 字段方法 - 完成状态：0 未完成 1 已完成
    * @param $finish_status
    * @return void
    */
    public function setFinishStatus($finish_status){
        $this->finish_status = $finish_status;
        $this->set_data_list['finish_status'] = &$this->finish_status;
    }
    
    /**
    * 字段方法 - 目标完成总结
    * @param $finish_summary
    * @return void
    */
    public function setFinishSummary($finish_summary){
        $this->finish_summary = $finish_summary;
        $this->set_data_list['finish_summary'] = &$this->finish_summary;
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