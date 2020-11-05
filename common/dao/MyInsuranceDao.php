<?php
/**
* 保险单 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-22
* Time: 19:53:44
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\MyInsurance;
use think\Db;

class MyInsuranceDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(MyInsurance::TABLE_NAME);
    }

    /**
    * 添加
    * @param MyInsurance $vo
    * @return int
    */
    public function add(MyInsurance $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param MyInsurance $vo
    * @param MyInsurance $mapVo
    * @return bool
    */
    public function update(MyInsurance $vo,MyInsurance $mapVo){
        $success = false;

        $map = $mapVo->getSetDataList();
        if(count($map) > 0){
            $this->db->removeOption();
            $result = $this->db->where($map)->update($vo->getSetDataList());
            if($result !== false){
                $success = true;
            }
        }

        return $success;
    }

    /**
    * 删除
    * @param MyInsurance $mapVo
    * @return bool
    */
    public function delete(MyInsurance $mapVo){
        $success = false;

        $map = $mapVo->getSetDataList();
        if(count($map) > 0){
            $this->db->removeOption();
            $result = $this->db->where($map)->delete();
            if($result > 0){
                $success = true;
            }
        }

        return $success;
    }

    /**
    * 查找单条
    * @param MyInsurance $mapVo
    * @return array
    */
    public function find(MyInsurance $mapVo){
        $record = array();

        $map = $mapVo->getSetDataList();
        if(count($map) > 0){
            $this->db->removeOption();
            $record = $this->db->where($map)->find();
            if(!$record){
                $record = array();
            }
        }

        return $record;
    }

    /**
    * 查找列表
    * @param MyInsurance $mapVo
    * @return array_list
    */
    public function findList(MyInsurance $mapVo){
        $list = array();

        $map = $mapVo->getSetDataList();
        $this->db->removeOption();
        $list = $this->db->where($map)->select();
        if(!$list){
            $list = array();
        }

        return $list;
    }
}