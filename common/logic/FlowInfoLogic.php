<?php
/**
* 流量统计 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-11-05
* Time: 19:43:45
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\FlowInfo;
use common\model\Result;
use common\dao\FlowInfoDao;

class FlowInfoLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new FlowInfoDao();
    }

    /**
    * 添加
    * @param FlowInfo $vo
    * @return Result
    */
    public function add(FlowInfo $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '访问统计添加失败！';

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '访问统计添加成功！';
        }

        return $result;
    }

    /**
    * 更新
    * @param FlowInfo $vo
    * @param FlowInfo $mapVo
    * @return Result
    */
    public function update(FlowInfo $vo,FlowInfo $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->update($vo,$mapVo);

        return $result;
    }

    /**
    * 删除
    * @param FlowInfo $mapVo
    * @return Result
    */
    public function delete(FlowInfo $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param FlowInfo $mapVo
    * @return array
    */
    public function find(FlowInfo $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param FlowInfo $mapVo
    * @return array_list
    */
    public function findList(FlowInfo $mapVo){
        return $this->dao->findList($mapVo);
    }
}