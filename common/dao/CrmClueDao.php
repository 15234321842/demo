<?php
/**
* 客户线索 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-16
* Time: 20:11:19
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\CrmClue;
use think\Db;
use think\db\Expression;

class CrmClueDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(CrmClue::TABLE_NAME);
    }

    /**
    * 添加
    * @param CrmClue $vo
    * @return int
    */
    public function add(CrmClue $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param CrmClue $vo
    * @param CrmClue $mapVo
    * @return bool
    */
    public function update(CrmClue $vo,CrmClue $mapVo){
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
    * @param CrmClue $mapVo
    * @return bool
    */
    public function delete(CrmClue $mapVo){
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
    * @param CrmClue $mapVo
    * @return array
    */
    public function find(CrmClue $mapVo){
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
    * @param CrmClue $mapVo
    * @return array_list
    */
    public function findList(CrmClue $mapVo){
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
     * 加载其它线索
     * @param int $customer_id
     * @param int $cule_id
     * @param int $uid
     * @return array_list
     */
    public function load_other($customer_id,$cule_id,$uid){
        $list = array();

        $map = array();
        $map['is_del'] = 0;
        $map['add_uid'] = $uid;
        $map['customer_id'] = $customer_id;
        $map['clue_id'] = new Expression('<>'.$cule_id);

        $this->db->removeOption();
        $list = $this->db->field('clue_id,customer_id,clue_time,clue_content')
            ->where($map)->order('clue_time','desc')->select();
        if(!$list){
            $list = array();
        }

        return $list;
    }
}