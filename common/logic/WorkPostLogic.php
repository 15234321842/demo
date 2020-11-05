<?php
/**
* 工作岗位 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-04
* Time: 11:11:52
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\WorkPost;
use common\model\Result;
use common\dao\WorkPostDao;

class WorkPostLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new WorkPostDao();
    }

    /**
    * 添加
    * @param WorkPost $vo
    * @return Result
    */
    public function add(WorkPost $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '岗位添加失败！';

        $map = new WorkPost();
        $map->setPostName($vo->getPostName());

        $post = $this->find($map);
        if($post){
            $result->success = false;
            $result->msg = '岗位名称已存在！';
            return $result;
        }

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '岗位添加成功！';
        }

        return $result;
    }

    /**
    * 更新
    * @param WorkPost $vo
    * @param WorkPost $mapVo
    * @return Result
    */
    public function update(WorkPost $vo,WorkPost $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->update($vo,$mapVo);

        return $result;
    }

    /**
    * 删除
    * @param WorkPost $mapVo
    * @return Result
    */
    public function delete(WorkPost $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param WorkPost $mapVo
    * @return array
    */
    public function find(WorkPost $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param WorkPost $mapVo
    * @return array_list
    */
    public function findList(WorkPost $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 查找岗位列表
     * @param WorkPost $mapVo
     * @return array_list
     */
    public function postList(WorkPost $mapVo){
        return $this->dao->postList($mapVo);
    }
}