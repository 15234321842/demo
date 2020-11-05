<?php
/**
* 日程任务 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-21
* Time: 16:50:30
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\WorkScheduleTask;
use common\model\Result;
use common\dao\WorkScheduleTaskDao;

class WorkScheduleTaskLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new WorkScheduleTaskDao();
    }

    /**
    * 添加
    * @param WorkScheduleTask $vo
    * @return Result
    */
    public function add(WorkScheduleTask $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '日程任务添加失败！';

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '日程任务添加成功！';
        }

        return $result;
    }

    /**
    * 更新
    * @param WorkScheduleTask $vo
    * @param WorkScheduleTask $mapVo
    * @return Result
    */
    public function update(WorkScheduleTask $vo,WorkScheduleTask $mapVo){
        $result = new Result();
        $result->success = false;
        $result->msg = '更新失败！';

        $success = false;
        $success = $this->dao->update($vo,$mapVo);
        if($success !== false){
            $result->success = true;
            $result->msg = '更新成功！';
        }

        return $result;
    }

    /**
    * 删除
    * @param WorkScheduleTask $mapVo
    * @return Result
    */
    public function delete(WorkScheduleTask $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param WorkScheduleTask $mapVo
    * @return array
    */
    public function find(WorkScheduleTask $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param WorkScheduleTask $mapVo
    * @return array_list
    */
    public function findList(WorkScheduleTask $mapVo){
        return $this->dao->findList($mapVo);
    }
}