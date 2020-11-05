<?php
/**
* 行业 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-03
* Time: 20:20:32
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\Industry;
use think\Db;

class IndustryDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(Industry::TABLE_NAME);
    }

    /**
    * 添加
    * @param Industry $vo
    * @return int
    */
    public function add(Industry $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param Industry $vo
    * @param Industry $mapVo
    * @return bool
    */
    public function update(Industry $vo,Industry $mapVo){
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
    * @param Industry $mapVo
    * @return bool
    */
    public function delete(Industry $mapVo){
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
    * @param Industry $mapVo
    * @return array
    */
    public function find(Industry $mapVo){
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
    * @param Industry $mapVo
    * @return array_list
    */
    public function findList(Industry $mapVo){
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
     * 查找行业列表
     * @param Industry $mapVo
     * @return array_list
     */
    public function industryList(Industry $mapVo){
        $list = array();

        $map = $mapVo->getSetDataList();
        $this->db->removeOption();
        $list = $this->db->field('industry_id,industry_name,name_first_letter')->where($map)
            ->order('add_time','asc')->select();
        if(!$list){
            $list = array();
        }

        return $list;
    }
}