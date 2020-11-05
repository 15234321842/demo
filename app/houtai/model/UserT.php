<?php
/**
* 流量统计 Model 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-11-05
* Time: 19:43:42
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/
namespace APP\houtai\Model;

use common\model as Model;

namespace common\model;

class UserT
{
    const TABLE_NAME = 'flow_info';
    const PRIMARY_KEY = 'info_id';

    /**
    * 设置字段-值集合
    */
    private $set_data_list = array();

    private $info_id;
    private $user_id;
    private $visit_ip;
    private $visit_time;
    private $visit_url;
    private $visit_url_md5;
    private $visit_agent;
    
    /**
    * 字段属性 - 统计ID
    * @return $info_id
    */
    public function getInfoId(){
        return $this->info_id;
    }
    
    /**
    * 字段属性 - 用户ID
    * @return $user_id
    */
    public function getUserId(){
        return $this->user_id;
    }
    
    /**
    * 字段属性 - 访问IP
    * @return $visit_ip
    */
    public function getVisitIp(){
        return $this->visit_ip;
    }
    
    /**
    * 字段属性 - 访问时间
    * @return $visit_time
    */
    public function getVisitTime(){
        return $this->visit_time;
    }
    
    /**
    * 字段属性 - 访问地址
    * @return $visit_url
    */
    public function getVisitUrl(){
        return $this->visit_url;
    }
    
    /**
    * 字段属性 - 访问地址MD5
    * @return $visit_url_md5
    */
    public function getVisitUrlMd5(){
        return $this->visit_url_md5;
    }
    
    /**
    * 字段属性 - 访问Agent
    * @return $visit_agent
    */
    public function getVisitAgent(){
        return $this->visit_agent;
    }
    
    /**
    * 字段方法 - 统计ID
    * @param $info_id
    * @return void
    */
    public function setInfoId($info_id){
        $this->info_id = $info_id;
        $this->set_data_list['info_id'] = &$this->info_id;
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
    * 字段方法 - 访问IP
    * @param $visit_ip
    * @return void
    */
    public function setVisitIp($visit_ip){
        $this->visit_ip = $visit_ip;
        $this->set_data_list['visit_ip'] = &$this->visit_ip;
    }
    
    /**
    * 字段方法 - 访问时间
    * @param $visit_time
    * @return void
    */
    public function setVisitTime($visit_time){
        $this->visit_time = $visit_time;
        $this->set_data_list['visit_time'] = &$this->visit_time;
    }
    
    /**
    * 字段方法 - 访问地址
    * @param $visit_url
    * @return void
    */
    public function setVisitUrl($visit_url){
        $this->visit_url = $visit_url;
        $this->set_data_list['visit_url'] = &$this->visit_url;
    }
    
    /**
    * 字段方法 - 访问地址MD5
    * @param $visit_url_md5
    * @return void
    */
    public function setVisitUrlMd5($visit_url_md5){
        $this->visit_url_md5 = $visit_url_md5;
        $this->set_data_list['visit_url_md5'] = &$this->visit_url_md5;
    }
    
    /**
    * 字段方法 - 访问Agent
    * @param $visit_agent
    * @return void
    */
    public function setVisitAgent($visit_agent){
        $this->visit_agent = $visit_agent;
        $this->set_data_list['visit_agent'] = &$this->visit_agent;
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