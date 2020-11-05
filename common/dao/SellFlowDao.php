<?php
/**
* 销售流程 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-07
* Time: 21:54:46
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\SellFlow;
use think\Db;

class SellFlowDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(SellFlow::TABLE_NAME);
    }

    /**
    * 添加
    * @param SellFlow $vo
    * @return int
    */
    public function add(SellFlow $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param SellFlow $vo
    * @param SellFlow $mapVo
    * @return bool
    */
    public function update(SellFlow $vo,SellFlow $mapVo){
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
    * @param SellFlow $mapVo
    * @return bool
    */
    public function delete(SellFlow $mapVo){
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
    * @param SellFlow $mapVo
    * @return array
    */
    public function find(SellFlow $mapVo){
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
    * @param SellFlow $mapVo
    * @return array_list
    */
    public function findList(SellFlow $mapVo){
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