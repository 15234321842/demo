<?php
/**
* 学校 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-04
* Time: 14:07:52
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\School;
use think\Db;

class SchoolDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(School::TABLE_NAME);
    }

    /**
    * 添加
    * @param School $vo
    * @return int
    */
    public function add(School $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param School $vo
    * @param School $mapVo
    * @return bool
    */
    public function update(School $vo,School $mapVo){
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
    * @param School $mapVo
    * @return bool
    */
    public function delete(School $mapVo){
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
    * @param School $mapVo
    * @return array
    */
    public function find(School $mapVo){
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
    * @param School $mapVo
    * @return array_list
    */
    public function findList(School $mapVo){
        $list = array();

        $map = $mapVo->getSetDataList();
        $this->db->removeOption();
        $list = $this->db->where($map)->select();
        if(!$list){
            $list = array();
        }

        return $list;
    }

    /**
     * 查找学校列表
     * @param School $mapVo
     * @return array_list
     */
    public function schoolList(School $mapVo){
        $list = array();

        $map = $mapVo->getSetDataList();
        $this->db->removeOption();
        $list = $this->db->field('school_id,school_name,name_first_letter')->where($map)
            ->order('add_time','asc')->select();
        if(!$list){
            $list = array();
        }

        return $list;
    }
}