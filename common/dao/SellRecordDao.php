<?php
/**
* 销售记录 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-18
* Time: 21:37:55
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\SellRecord;
use think\Db;

class SellRecordDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(SellRecord::TABLE_NAME);
    }

    /**
    * 添加
    * @param SellRecord $vo
    * @return int
    */
    public function add(SellRecord $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param SellRecord $vo
    * @param SellRecord $mapVo
    * @return bool
    */
    public function update(SellRecord $vo,SellRecord $mapVo){
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
    * @param SellRecord $mapVo
    * @return bool
    */
    public function delete(SellRecord $mapVo){
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
    * @param SellRecord $mapVo
    * @return array
    */
    public function find(SellRecord $mapVo){
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
    * @param SellRecord $mapVo
    * @return array_list
    */
    public function findList(SellRecord $mapVo){
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
     * 客户的销售记录数量
     * @param int $customer_id
     * @return int
     */
    public function sellRecordCount($customer_id){
        $rowCount = 0;

        if($customer_id > 0){
            $map['customer_id'] = $customer_id;
            $map['is_del'] = 0;
            $this->db->removeOption();
            $rowCount = $this->db->where($map)->count();
        }

        return $rowCount;
    }
}