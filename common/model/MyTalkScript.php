<?php
/**
* 话术 Model 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-22
* Time: 19:53:55
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\model;

class MyTalkScript
{
    const TABLE_NAME = 'my_talk_script';
    const PRIMARY_KEY = 'script_id';

    /**
    * 设置字段-值集合
    */
    private $set_data_list = array();

    private $script_id;
    private $user_id;
    private $script_type;
    private $script_title;
    private $script_content;
    private $add_uid;
    private $add_time;
    private $edit_uid;
    private $edit_time;
    private $is_del;
    
    /**
    * 字段属性 - 话术ID
    * @return $script_id
    */
    public function getScriptId(){
        return $this->script_id;
    }
    
    /**
    * 字段属性 - 用户ID
    * @return $user_id
    */
    public function getUserId(){
        return $this->user_id;
    }
    
    /**
    * 字段属性 - 话术类别：0 销售 1 增员 2 客服
    * @return $script_type
    */
    public function getScriptType(){
        return $this->script_type;
    }
    
    /**
    * 字段属性 - 话术标题
    * @return $script_title
    */
    public function getScriptTitle(){
        return $this->script_title;
    }
    
    /**
    * 字段属性 - 话术内容
    * @return $script_content
    */
    public function getScriptContent(){
        return $this->script_content;
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
    * 字段方法 - 话术ID
    * @param $script_id
    * @return void
    */
    public function setScriptId($script_id){
        $this->script_id = $script_id;
        $this->set_data_list['script_id'] = &$this->script_id;
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
    * 字段方法 - 话术类别：0 销售 1 增员 2 客服
    * @param $script_type
    * @return void
    */
    public function setScriptType($script_type){
        $this->script_type = $script_type;
        $this->set_data_list['script_type'] = &$this->script_type;
    }
    
    /**
    * 字段方法 - 话术标题
    * @param $script_title
    * @return void
    */
    public function setScriptTitle($script_title){
        $this->script_title = $script_title;
        $this->set_data_list['script_title'] = &$this->script_title;
    }
    
    /**
    * 字段方法 - 话术内容
    * @param $script_content
    * @return void
    */
    public function setScriptContent($script_content){
        $this->script_content = $script_content;
        $this->set_data_list['script_content'] = &$this->script_content;
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