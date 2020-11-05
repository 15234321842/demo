<?php
/**
 * 消息 Model 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-10-27
 * Time: 18:08:51
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace common\model;

class Message
{
    const TABLE_NAME = 'message';
    const PRIMARY_KEY = 'msg_id';

    /**
     * 设置字段-值集合
     */
    private $set_data_list = array();

    private $msg_id;
    private $receive_uid;
    private $send_uid;
    private $msg_type;
    private $msg_title;
    private $msg_summary;
    private $msg_content;
    private $msg_time;
    private $is_read;
    private $read_time;
    private $add_uid;
    private $add_time;
    private $edit_uid;
    private $edit_time;
    private $is_del;
    private $source_id;

    /**
     * 字段属性 - 消息ID
     * @return $msg_id
     */
    public function getMsgId(){
        return $this->msg_id;
    }

    /**
     * 字段属性 - 接收者ID
     * @return $receive_uid
     */
    public function getReceiveUid(){
        return $this->receive_uid;
    }

    /**
     * 字段属性 - 发送者ID：0 代表系统发送者
     * @return $send_uid
     */
    public function getSendUid(){
        return $this->send_uid;
    }

    /**
     * 字段属性 - 消息类型：0 系统 1 客户 2 保单 3 目标
     * @return $msg_type
     */
    public function getMsgType(){
        return $this->msg_type;
    }

    /**
     * 字段属性 - 标题
     * @return $msg_title
     */
    public function getMsgTitle(){
        return $this->msg_title;
    }

    /**
     * 字段属性 - 消息摘要
     * @return $msg_summary
     */
    public function getMsgSummary(){
        return $this->msg_summary;
    }

    /**
     * 字段属性 - 消息内容
     * @return $msg_content
     */
    public function getMsgContent(){
        return $this->msg_content;
    }

    /**
     * 字段属性 - 消息时间
     * @return $msg_time
     */
    public function getMsgTime(){
        return $this->msg_time;
    }

    /**
     * 字段属性 - 是否阅读：0 未读 1 已读
     * @return $is_read
     */
    public function getIsRead(){
        return $this->is_read;
    }

    /**
     * 字段属性 - 阅读时间
     * @return $read_time
     */
    public function getReadTime(){
        return $this->read_time;
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
     * 字段属性 - 消息源ID
     * @return $source_id
     */
    public function getSourceId(){
        return $this->source_id;
    }

    /**
     * 字段方法 - 消息ID
     * @param $msg_id
     * @return void
     */
    public function setMsgId($msg_id){
        $this->msg_id = $msg_id;
        $this->set_data_list['msg_id'] = &$this->msg_id;
    }

    /**
     * 字段方法 - 接收者ID
     * @param $receive_uid
     * @return void
     */
    public function setReceiveUid($receive_uid){
        $this->receive_uid = $receive_uid;
        $this->set_data_list['receive_uid'] = &$this->receive_uid;
    }

    /**
     * 字段方法 - 发送者ID：0 代表系统发送者
     * @param $send_uid
     * @return void
     */
    public function setSendUid($send_uid){
        $this->send_uid = $send_uid;
        $this->set_data_list['send_uid'] = &$this->send_uid;
    }

    /**
     * 字段方法 - 消息类型：0 系统 1 客户 2 保单 3 目标
     * @param $msg_type
     * @return void
     */
    public function setMsgType($msg_type){
        $this->msg_type = $msg_type;
        $this->set_data_list['msg_type'] = &$this->msg_type;
    }

    /**
     * 字段方法 - 标题
     * @param $msg_title
     * @return void
     */
    public function setMsgTitle($msg_title){
        $this->msg_title = $msg_title;
        $this->set_data_list['msg_title'] = &$this->msg_title;
    }

    /**
     * 字段方法 - 消息摘要
     * @param $msg_summary
     * @return void
     */
    public function setMsgSummary($msg_summary){
        $this->msg_summary = $msg_summary;
        $this->set_data_list['msg_summary'] = &$this->msg_summary;
    }

    /**
     * 字段方法 - 消息内容
     * @param $msg_content
     * @return void
     */
    public function setMsgContent($msg_content){
        $this->msg_content = $msg_content;
        $this->set_data_list['msg_content'] = &$this->msg_content;
    }

    /**
     * 字段方法 - 消息时间
     * @param $msg_time
     * @return void
     */
    public function setMsgTime($msg_time){
        $this->msg_time = $msg_time;
        $this->set_data_list['msg_time'] = &$this->msg_time;
    }

    /**
     * 字段方法 - 是否阅读：0 未读 1 已读
     * @param $is_read
     * @return void
     */
    public function setIsRead($is_read){
        $this->is_read = $is_read;
        $this->set_data_list['is_read'] = &$this->is_read;
    }

    /**
     * 字段方法 - 阅读时间
     * @param $read_time
     * @return void
     */
    public function setReadTime($read_time){
        $this->read_time = $read_time;
        $this->set_data_list['read_time'] = &$this->read_time;
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
     * 字段方法 - 消息源ID
     * @param $source_id
     * @return void
     */
    public function setSourceId($source_id){
        $this->source_id = $source_id;
        $this->set_data_list['source_id'] = &$this->source_id;
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