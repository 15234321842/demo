<?php
/**
 * 用户 Dao 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-09-27
 * Time: 17:44:25
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace common\dao;

use common\model\User;
use think\Db;

class UserDao
{
    private $db;

    public function __construct(){
        $this->db = Db::name(User::TABLE_NAME);
    }

    /**
     * 添加
     * @param User $vo
     * @return int
     */
    public function add(User $vo){
        $pkId = $this->db->insertGetId($vo->getSetDataList());

        return $pkId;
    }

    /**
     * 更新
     * @param User $vo
     * @param User $mapVo
     * @return bool
     */
    public function update(User $vo,User $mapVo){
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
     * @param User $mapVo
     * @return bool
     */
    public function delete(User $mapVo){
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
     * @param User $mapVo
     * @return array
     */
    public function find(User $mapVo){
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
     * @param User $mapVo
     * @return array_list
     */
    public function findList(User $mapVo){
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