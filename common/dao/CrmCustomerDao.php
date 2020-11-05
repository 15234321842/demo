<?php
/**
* 客户资料 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-05
* Time: 23:51:27
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\CrmCustomer;
use think\Db;

class CrmCustomerDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(CrmCustomer::TABLE_NAME);
    }

    /**
    * 添加
    * @param CrmCustomer $vo
    * @return int
    */
    public function add(CrmCustomer $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
     * 批量添加
     * @param array $dataList
     * @return int
     */
    public function addBatch($dataList){
        $count = 0;
        if(count($dataList) > 0){
            $count = $this->db->insertAll($dataList);
        }

        return $count;
    }

    /**
    * 更新
    * @param CrmCustomer $vo
    * @param CrmCustomer $mapVo
    * @return bool
    */
    public function update(CrmCustomer $vo,CrmCustomer $mapVo){
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
    * @param CrmCustomer $mapVo
    * @return bool
    */
    public function delete(CrmCustomer $mapVo){
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
    * @param CrmCustomer $mapVo
    * @return array
    */
    public function find(CrmCustomer $mapVo){
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
    * @param CrmCustomer $mapVo
    * @return array_list
    */
    public function findList(CrmCustomer $mapVo){
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
     * 获取客户列表
     * @param CrmCustomer $mapVo
     * @return array_list
     */
    public function customerListShortFields(CrmCustomer $mapVo){
        $list = array();

        $map = $mapVo->getSetDataList();
        $this->db->removeOption();
        $list = $this->db->field('customer_id,customer_name,mobile,sex,regular_customer,aim_list')->where($map)->select();
        if(!$list){
            $list = array();
        }

        return $list;
    }
}