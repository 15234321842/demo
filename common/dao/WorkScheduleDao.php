<?php
/**
* 日程 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-21
* Time: 16:50:21
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\WorkSchedule;
use think\Db;

class WorkScheduleDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(WorkSchedule::TABLE_NAME);
    }

    /**
    * 添加
    * @param WorkSchedule $vo
    * @return int
    */
    public function add(WorkSchedule $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param WorkSchedule $vo
    * @param WorkSchedule $mapVo
    * @return bool
    */
    public function update(WorkSchedule $vo,WorkSchedule $mapVo){
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
    * @param WorkSchedule $mapVo
    * @return bool
    */
    public function delete(WorkSchedule $mapVo){
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
    * @param WorkSchedule $mapVo
    * @return array
    */
    public function find(WorkSchedule $mapVo){
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
    * @param WorkSchedule $mapVo
    * @return array_list
    */
    public function findList(WorkSchedule $mapVo){
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