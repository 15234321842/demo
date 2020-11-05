<?php
/**
* 行业 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-03
* Time: 20:22:39
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\Industry;
use common\model\Result;
use common\dao\IndustryDao;

class IndustryLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new IndustryDao();
    }

    /**
    * 添加
    * @param Industry $vo
    * @return Result
    */
    public function add(Industry $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '行业添加失败！';

        $map = new Industry();
        $map->setIndustryName($vo->getIndustryName());

        $industry = $this->find($map);
        if($industry){
            $result->success = false;
            $result->msg = '行业名称已存在！';
            return $result;
        }

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '行业添加成功！';
        }

        return $result;
    }

    /**
    * 更新
    * @param Industry $vo
    * @param Industry $mapVo
    * @return Result
    */
    public function update(Industry $vo,Industry $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->update($vo,$mapVo);

        return $result;
    }

    /**
    * 删除
    * @param Industry $mapVo
    * @return Result
    */
    public function delete(Industry $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param Industry $mapVo
    * @return array
    */
    public function find(Industry $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param Industry $mapVo
    * @return array_list
    */
    public function findList(Industry $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 查找行业列表
     * @param Industry $mapVo
     * @return array_list
     */
    public function industryList(Industry $mapVo){
        return $this->dao->industryList($mapVo);
    }
}