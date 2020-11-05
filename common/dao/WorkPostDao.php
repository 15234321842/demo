<?php
/**
* 工作岗位 Dao 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-04
* Time: 11:11:42
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\dao;

use common\model\WorkPost;
use think\Db;

class WorkPostDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(WorkPost::TABLE_NAME);
    }

    /**
    * 添加
    * @param WorkPost $vo
    * @return int
    */
    public function add(WorkPost $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
    * 更新
    * @param WorkPost $vo
    * @param WorkPost $mapVo
    * @return bool
    */
    public function update(WorkPost $vo,WorkPost $mapVo){
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
    * @param WorkPost $mapVo
    * @return bool
    */
    public function delete(WorkPost $mapVo){
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
    * @param WorkPost $mapVo
    * @return array
    */
    public function find(WorkPost $mapVo){
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
    * @param WorkPost $mapVo
    * @return array_list
    */
    public function findList(WorkPost $mapVo){
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
     * 查找岗位列表
     * @param WorkPost $mapVo
     * @return array_list
     */
    public function postList(WorkPost $mapVo){
        $list = array();

        $map = $mapVo->getSetDataList();
        $this->db->removeOption();
        $list = $this->db->field('post_id,post_name,name_first_letter')->where($map)
            ->order('add_time','asc')->select();
        if(!$list){
            $list = array();
        }

        return $list;
    }
}