<?php
/**
* 工作职位 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-04
* Time: 10:16:52
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\WorkPosition;
use common\model\Result;
use common\dao\WorkPositionDao;

class WorkPositionLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new WorkPositionDao();
    }

    /**
    * 添加
    * @param WorkPosition $vo
    * @return Result
    */
    public function add(WorkPosition $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '职位添加失败！';

        $map = new WorkPosition();
        $map->setPositionName($vo->getPositionName());

        $position = $this->find($map);
        if($position){
            $result->success = false;
            $result->msg = '职位名称已存在！';
            return $result;
        }

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '职位添加成功！';
        }

        return $result;
    }

    /**
    * 更新
    * @param WorkPosition $vo
    * @param WorkPosition $mapVo
    * @return Result
    */
    public function update(WorkPosition $vo,WorkPosition $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->update($vo,$mapVo);

        return $result;
    }

    /**
    * 删除
    * @param WorkPosition $mapVo
    * @return Result
    */
    public function delete(WorkPosition $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param WorkPosition $mapVo
    * @return array
    */
    public function find(WorkPosition $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param WorkPosition $mapVo
    * @return array_list
    */
    public function findList(WorkPosition $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 查找职位列表
     * @param WorkPosition $mapVo
     * @return array_list
     */
    public function positionList(WorkPosition $mapVo){
        return $this->dao->positionList($mapVo);
    }
}