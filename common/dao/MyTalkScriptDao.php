<?php
/**
* 话术 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-22
* Time: 19:54:01
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\MyTalkScript;
use think\Db;

class MyTalkScriptDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(MyTalkScript::TABLE_NAME);
    }

    /**
    * 添加
    * @param MyTalkScript $vo
    * @return int
    */
    public function add(MyTalkScript $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param MyTalkScript $vo
    * @param MyTalkScript $mapVo
    * @return bool
    */
    public function update(MyTalkScript $vo,MyTalkScript $mapVo){
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
    * @param MyTalkScript $mapVo
    * @return bool
    */
    public function delete(MyTalkScript $mapVo){
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
    * @param MyTalkScript $mapVo
    * @return array
    */
    public function find(MyTalkScript $mapVo){
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
    * @param MyTalkScript $mapVo
    * @return array_list
    */
    public function findList(MyTalkScript $mapVo){
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