<?php
/**
* 日程任务 Model 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-21
* Time: 16:50:27
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\model;

class WorkScheduleTask
{
    const TABLE_NAME = 'work_schedule_task';
    const PRIMARY_KEY = 'task_id';

    /**
    * 设置字段-值集合
    */
    private $set_data_list = array();

    private $task_id;
    private $schedule_id;
    private $task_time;
    private $task_title;
    private $task_content;
    private $task_level;
    private $task_status;
    private $is_del;
    
    /**
    * 字段属性 - 日程任务ID
    * @return $task_id
    */
    public function getTaskId(){
        return $this->task_id;
    }
    
    /**
    * 字段属性 - 日程ID
    * @return $schedule_id
    */
    public function getScheduleId(){
        return $this->schedule_id;
    }
    
    /**
    * 字段属性 - 任务时间
    * @return $task_time
    */
    public function getTaskTime(){
        return $this->task_time;
    }
    
    /**
    * 字段属性 - 任务标题
    * @return $task_title
    */
    public function getTaskTitle(){
        return $this->task_title;
    }
    
    /**
    * 字段属性 - 任务内容
    * @return $task_content
    */
    public function getTaskContent(){
        return $this->task_content;
    }
    
    /**
    * 字段属性 - 任务级别：0 一般 1 重要 2 紧急
    * @return $task_level
    */
    public function getTaskLevel(){
        return $this->task_level;
    }
    
    /**
    * 字段属性 - 任务状态：0 未完成 1 已完成
    * @return $task_status
    */
    public function getTaskStatus(){
        return $this->task_status;
    }
    
    /**
    * 字段属性 - 是否删除：0 正常 1 删除
    * @return $is_del
    */
    public function getIsDel(){
        return $this->is_del;
    }
    
    /**
    * 字段方法 - 日程任务ID
    * @param $task_id
    * @return void
    */
    public function setTaskId($task_id){
        $this->task_id = $task_id;
        $this->set_data_list['task_id'] = &$this->task_id;
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
    * 字段方法 - 任务时间
    * @param $task_time
    * @return void
    */
    public function setTaskTime($task_time){
        $this->task_time = $task_time;
        $this->set_data_list['task_time'] = &$this->task_time;
    }
    
    /**
    * 字段方法 - 任务标题
    * @param $task_title
    * @return void
    */
    public function setTaskTitle($task_title){
        $this->task_title = $task_title;
        $this->set_data_list['task_title'] = &$this->task_title;
    }
    
    /**
    * 字段方法 - 任务内容
    * @param $task_content
    * @return void
    */
    public function setTaskContent($task_content){
        $this->task_content = $task_content;
        $this->set_data_list['task_content'] = &$this->task_content;
    }
    
    /**
    * 字段方法 - 任务级别：0 一般 1 重要 2 紧急
    * @param $task_level
    * @return void
    */
    public function setTaskLevel($task_level){
        $this->task_level = $task_level;
        $this->set_data_list['task_level'] = &$this->task_level;
    }
    
    /**
    * 字段方法 - 任务状态：0 未完成 1 已完成
    * @param $task_status
    * @return void
    */
    public function setTaskStatus($task_status){
        $this->task_status = $task_status;
        $this->set_data_list['task_status'] = &$this->task_status;
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