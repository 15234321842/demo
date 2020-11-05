<?php
/**
* 客户跟进 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-18
* Time: 15:44:18
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\CrmFollow;
use think\Db;
use think\db\Expression;

class CrmFollowDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(CrmFollow::TABLE_NAME);
    }

    /**
    * 添加
    * @param CrmFollow $vo
    * @return int
    */
    public function add(CrmFollow $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param CrmFollow $vo
    * @param CrmFollow $mapVo
    * @return bool
    */
    public function update(CrmFollow $vo,CrmFollow $mapVo){
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
    * @param CrmFollow $mapVo
    * @return bool
    */
    public function delete(CrmFollow $mapVo){
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
    * @param CrmFollow $mapVo
    * @return array
    */
    public function find(CrmFollow $mapVo){
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
    * @param CrmFollow $mapVo
    * @return array_list
    */
    public function findList(CrmFollow $mapVo){
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
     * 加载其它跟进日志
     * @param int $customer_id
     * @param int $follow_id
     * @param int $uid
     * @return array_list
     */
    public function load_other($customer_id,$follow_id,$uid){
        $list = array();

        $map = array();
        $map['is_del'] = 0;
        $map['add_uid'] = $uid;
        $map['customer_id'] = $customer_id;
        $map['follow_id'] = new Expression('<>'.$follow_id);

        $this->db->removeOption();
        $list = $this->db->field('follow_id,customer_id,follow_time,follow_log,follow_type')
            ->where($map)->order('follow_time','desc')->select();
        if(!$list){
            $list = array();
        }

        return $list;
    }
}