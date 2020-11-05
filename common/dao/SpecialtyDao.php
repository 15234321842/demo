<?php
/**
* 专业 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-04
* Time: 14:07:37
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\Specialty;
use think\Db;

class SpecialtyDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(Specialty::TABLE_NAME);
    }

    /**
    * 添加
    * @param Specialty $vo
    * @return int
    */
    public function add(Specialty $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param Specialty $vo
    * @param Specialty $mapVo
    * @return bool
    */
    public function update(Specialty $vo,Specialty $mapVo){
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
    * @param Specialty $mapVo
    * @return bool
    */
    public function delete(Specialty $mapVo){
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
    * @param Specialty $mapVo
    * @return array
    */
    public function find(Specialty $mapVo){
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
    * @param Specialty $mapVo
    * @return array_list
    */
    public function findList(Specialty $mapVo){
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
     * 查找专业列表
     * @param Specialty $mapVo
     * @return array_list
     */
    public function specialtyList(Specialty $mapVo){
        $list = array();

        $map = $mapVo->getSetDataList();
        $this->db->removeOption();
        $list = $this->db->field('specialty_id,specialty_name,name_first_letter')->where($map)
            ->order('add_time','asc')->select();
        if(!$list){
            $list = array();
        }

        return $list;
    }
}