<?php
/**
* 专业 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-04
* Time: 14:07:45
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\Specialty;
use common\model\Result;
use common\dao\SpecialtyDao;

class SpecialtyLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new SpecialtyDao();
    }

    /**
    * 添加
    * @param Specialty $vo
    * @return Result
    */
    public function add(Specialty $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '专业添加失败！';

        $map = new Specialty();
        $map->setSpecialtyName($vo->getSpecialtyName());

        $specialty = $this->find($map);
        if($specialty){
            $result->success = false;
            $result->msg = '专业名称已存在！';
            return $result;
        }

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '专业添加成功！';
        }

        return $result;
    }

    /**
    * 更新
    * @param Specialty $vo
    * @param Specialty $mapVo
    * @return Result
    */
    public function update(Specialty $vo,Specialty $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->update($vo,$mapVo);

        return $result;
    }

    /**
    * 删除
    * @param Specialty $mapVo
    * @return Result
    */
    public function delete(Specialty $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param Specialty $mapVo
    * @return array
    */
    public function find(Specialty $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param Specialty $mapVo
    * @return array_list
    */
    public function findList(Specialty $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 查找专业列表
     * @param Specialty $mapVo
     * @return array_list
     */
    public function specialtyList(Specialty $mapVo){
        return $this->dao->specialtyList($mapVo);
    }
}