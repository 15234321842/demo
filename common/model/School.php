<?php
/**
* 学校 Model 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-04
* Time: 14:07:56
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\model;

class School
{
    const TABLE_NAME = 'school';
    const PRIMARY_KEY = 'school_id';

    /**
    * 设置字段-值集合
    */
    private $set_data_list = array();

    private $school_id;
    private $school_name;
    private $name_first_letter;
    private $add_uid;
    private $add_time;
    private $edit_uid;
    private $edit_time;
    private $is_del;
    
    /**
    * 字段属性 - 学校ID
    * @return $school_id
    */
    public function getSchoolId(){
        return $this->school_id;
    }
    
    /**
    * 字段属性 - 学校名称
    * @return $school_name
    */
    public function getSchoolName(){
        return $this->school_name;
    }
    
    /**
    * 字段属性 - 名称首字母
    * @return $name_first_letter
    */
    public function getNameFirstLetter(){
        return $this->name_first_letter;
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
    * 字段方法 - 学校ID
    * @param $school_id
    * @return void
    */
    public function setSchoolId($school_id){
        $this->school_id = $school_id;
        $this->set_data_list['school_id'] = &$this->school_id;
    }
    
    /**
    * 字段方法 - 学校名称
    * @param $school_name
    * @return void
    */
    public function setSchoolName($school_name){
        $this->school_name = $school_name;
        $this->set_data_list['school_name'] = &$this->school_name;
    }
    
    /**
    * 字段方法 - 名称首字母
    * @param $name_first_letter
    * @return void
    */
    public function setNameFirstLetter($name_first_letter){
        $this->name_first_letter = $name_first_letter;
        $this->set_data_list['name_first_letter'] = &$this->name_first_letter;
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