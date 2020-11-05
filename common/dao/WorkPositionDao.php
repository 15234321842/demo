<?php
/**
* 工作职位 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-04
* Time: 10:05:54
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\WorkPosition;
use think\Db;

class WorkPositionDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(WorkPosition::TABLE_NAME);
    }

    /**
    * 添加
    * @param WorkPosition $vo
    * @return int
    */
    public function add(WorkPosition $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param WorkPosition $vo
    * @param WorkPosition $mapVo
    * @return bool
    */
    public function update(WorkPosition $vo,WorkPosition $mapVo){
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
    * @param WorkPosition $mapVo
    * @return bool
    */
    public function delete(WorkPosition $mapVo){
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
    * @param WorkPosition $mapVo
    * @return array
    */
    public function find(WorkPosition $mapVo){
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
    * @param WorkPosition $mapVo
    * @return array_list
    */
    public function findList(WorkPosition $mapVo){
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
     * 查找职位列表
     * @param WorkPosition $mapVo
     * @return array_list
     */
    public function positionList(WorkPosition $mapVo){
        $list = array();

        $map = $mapVo->getSetDataList();
        $this->db->removeOption();
        $list = $this->db->field('position_id,position_name,name_first_letter')->where($map)
            ->order('add_time','asc')->select();
        if(!$list){
            $list = array();
        }

        return $list;
    }
}