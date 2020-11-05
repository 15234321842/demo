<?php
/**
* 消息 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-27
* Time: 16:00:53
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\Message;
use think\Db;

class MessageDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(Message::TABLE_NAME);
    }

    /**
    * 添加
    * @param Message $vo
    * @return int
    */
    public function add(Message $vo){
        $this->db->removeOption();
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param Message $vo
    * @param Message $mapVo
    * @return bool
    */
    public function update(Message $vo,Message $mapVo){
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
    * @param Message $mapVo
    * @return bool
    */
    public function delete(Message $mapVo){
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
    * @param Message $mapVo
    * @return array
    */
    public function find(Message $mapVo){
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
    * @param Message $mapVo
    * @return array_list
    */
    public function findList(Message $mapVo){
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