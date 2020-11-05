<?php
/**
* 目标 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-21
* Time: 08:53:35
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\WorkAim;
use think\Db;

class WorkAimDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(WorkAim::TABLE_NAME);
    }

    /**
    * 添加
    * @param WorkAim $vo
    * @return int
    */
    public function add(WorkAim $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param WorkAim $vo
    * @param WorkAim $mapVo
    * @return bool
    */
    public function update(WorkAim $vo,WorkAim $mapVo){
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
    * @param WorkAim $mapVo
    * @return bool
    */
    public function delete(WorkAim $mapVo){
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
    * @param WorkAim $mapVo
    * @return array
    */
    public function find(WorkAim $mapVo){
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
    * @param WorkAim $mapVo
    * @return array_list
    */
    public function findList(WorkAim $mapVo){
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