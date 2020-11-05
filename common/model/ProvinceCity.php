<?php
/**
* 省份城市 Model 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-04
* Time: 20:49:19
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\model;

class ProvinceCity
{
    const TABLE_NAME = 'province_city';
    const PRIMARY_KEY = 'pcity_id';

    /**
    * 设置字段-值集合
    */
    private $set_data_list = array();

    private $pcity_id;
    private $parent_id;
    private $pcity_name;
    private $pcity_level;
    private $pinyin;
    private $first_letter;
    
    /**
    * 字段属性 - 省份城市ID
    * @return $pcity_id
    */
    public function getPcityId(){
        return $this->pcity_id;
    }
    
    /**
    * 字段属性 - 父ID
    * @return $parent_id
    */
    public function getParentId(){
        return $this->parent_id;
    }
    
    /**
    * 字段属性 - 省份城市名称
    * @return $pcity_name
    */
    public function getPcityName(){
        return $this->pcity_name;
    }
    
    /**
    * 字段属性 - 省分城市级别：0=>国家，1=>省，2=>市，3=>区或县，北京等直辖市也为1
    * @return $pcity_level
    */
    public function getPcityLevel(){
        return $this->pcity_level;
    }
    
    /**
    * 字段属性 - 拼音
    * @return $pinyin
    */
    public function getPinyin(){
        return $this->pinyin;
    }
    
    /**
    * 字段属性 - 前缀首字母
    * @return $first_letter
    */
    public function getFirstLetter(){
        return $this->first_letter;
    }
    
    /**
    * 字段方法 - 省份城市ID
    * @param $pcity_id
    * @return void
    */
    public function setPcityId($pcity_id){
        $this->pcity_id = $pcity_id;
        $this->set_data_list['pcity_id'] = &$this->pcity_id;
    }
    
    /**
    * 字段方法 - 父ID
    * @param $parent_id
    * @return void
    */
    public function setParentId($parent_id){
        $this->parent_id = $parent_id;
        $this->set_data_list['parent_id'] = &$this->parent_id;
    }
    
    /**
    * 字段方法 - 省份城市名称
    * @param $pcity_name
    * @return void
    */
    public function setPcityName($pcity_name){
        $this->pcity_name = $pcity_name;
        $this->set_data_list['pcity_name'] = &$this->pcity_name;
    }
    
    /**
    * 字段方法 - 省分城市级别：0=>国家，1=>省，2=>市，3=>区或县，北京等直辖市也为1
    * @param $pcity_level
    * @return void
    */
    public function setPcityLevel($pcity_level){
        $this->pcity_level = $pcity_level;
        $this->set_data_list['pcity_level'] = &$this->pcity_level;
    }
    
    /**
    * 字段方法 - 拼音
    * @param $pinyin
    * @return void
    */
    public function setPinyin($pinyin){
        $this->pinyin = $pinyin;
        $this->set_data_list['pinyin'] = &$this->pinyin;
    }
    
    /**
    * 字段方法 - 前缀首字母
    * @param $first_letter
    * @return void
    */
    public function setFirstLetter($first_letter){
        $this->first_letter = $first_letter;
        $this->set_data_list['first_letter'] = &$this->first_letter;
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