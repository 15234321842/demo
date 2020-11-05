<?php
/**
* 流量统计 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-11-05
* Time: 19:43:48
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\FlowInfo;
use think\Db;

class FlowInfoDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(FlowInfo::TABLE_NAME);
    }

    /**
    * 添加
    * @param FlowInfo $vo
    * @return int
    */
    public function add(FlowInfo $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param FlowInfo $vo
    * @param FlowInfo $mapVo
    * @return bool
    */
    public function update(FlowInfo $vo,FlowInfo $mapVo){
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
    * @param FlowInfo $mapVo
    * @return bool
    */
    public function delete(FlowInfo $mapVo){
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
    * @param FlowInfo $mapVo
    * @return array
    */
    public function find(FlowInfo $mapVo){
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
    * @param FlowInfo $mapVo
    * @return array_list
    */
    public function findList(FlowInfo $mapVo){
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