<?php
/**
* 日程 Model 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-21
* Time: 16:50:13
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\model;

class WorkSchedule
{
    const TABLE_NAME = 'work_schedule';
    const PRIMARY_KEY = 'schedule_id';

    /**
    * 设置字段-值集合
    */
    private $set_data_list = array();

    private $schedule_id;
    private $user_id;
    private $schedule_date;
    private $add_uid;
    private $add_time;
    private $edit_uid;
    private $edit_time;
    private $is_del;
    
    /**
    * 字段属性 - 日程ID
    * @return $schedule_id
    */
    public function getScheduleId(){
        return $this->schedule_id;
    }
    
    /**
    * 字段属性 - 用户ID
    * @return $user_id
    */
    public function getUserId(){
        return $this->user_id;
    }
    
    /**
    * 字段属性 - 日程日期
    * @return $schedule_date
    */
    public function getScheduleDate(){
        return $this->schedule_date;
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
    * 字段方法 - 日程ID
    * @param $schedule_id
    * @return void
    */
    public function setScheduleId($schedule_id){
        $this->schedule_id = $schedule_id;
        $this->set_data_list['schedule_id'] = &$this->schedule_id;
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
    * 字段方法 - 日程日期
    * @param $schedule_date
    * @return void
    */
    public function setScheduleDate($schedule_date){
        $this->schedule_date = $schedule_date;
        $this->set_data_list['schedule_date'] = &$this->schedule_date;
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