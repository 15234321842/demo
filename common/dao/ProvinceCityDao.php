<?php
/**
* 省份城市 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-04
* Time: 20:49:12
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\ProvinceCity;
use think\Db;

class ProvinceCityDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(ProvinceCity::TABLE_NAME);
    }

    /**
    * 添加
    * @param ProvinceCity $vo
    * @return int
    */
    public function add(ProvinceCity $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param ProvinceCity $vo
    * @param ProvinceCity $mapVo
    * @return bool
    */
    public function update(ProvinceCity $vo,ProvinceCity $mapVo){
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
    * @param ProvinceCity $mapVo
    * @return bool
    */
    public function delete(ProvinceCity $mapVo){
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
    * @param ProvinceCity $mapVo
    * @return array
    */
    public function find(ProvinceCity $mapVo){
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
    * @param ProvinceCity $mapVo
    * @return array_list
    */
    public function findList(ProvinceCity $mapVo){
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
     * 查找省份城市列表
     * @param ProvinceCity $mapVo
     * @return array_list
     */
    public function provinceCityList(ProvinceCity $mapVo){
        $list = array();

        $map = $mapVo->getSetDataList();
        $this->db->removeOption();
        $list = $this->db->field('pcity_id,parent_id,pcity_name,pcity_level')->where($map)
            ->select();
        if(!$list){
            $list = array();
        }

        return $list;
    }
}