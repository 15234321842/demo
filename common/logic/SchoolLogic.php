<?php
/**
* 学校 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-04
* Time: 14:08:00
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\School;
use common\model\Result;
use common\dao\SchoolDao;

class SchoolLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new SchoolDao();
    }

    /**
    * 添加
    * @param School $vo
    * @return Result
    */
    public function add(School $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '学校添加失败！';

        $map = new School();
        $map->setSchoolName($vo->getSchoolName());

        $school = $this->find($map);
        if($school){
            $result->success = false;
            $result->msg = '学校名称已存在！';
            return $result;
        }

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '学校添加成功！';
        }

        return $result;
    }

    /**
    * 更新
    * @param School $vo
    * @param School $mapVo
    * @return Result
    */
    public function update(School $vo,School $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->update($vo,$mapVo);

        return $result;
    }

    /**
    * 删除
    * @param School $mapVo
    * @return Result
    */
    public function delete(School $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param School $mapVo
    * @return array
    */
    public function find(School $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param School $mapVo
    * @return array_list
    */
    public function findList(School $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 查找学校列表
     * @param School $mapVo
     * @return array_list
     */
    public function schoolList(School $mapVo){
        return $this->dao->schoolList($mapVo);
    }
}