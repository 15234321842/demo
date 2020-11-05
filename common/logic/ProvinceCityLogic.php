<?php
/**
* 省份城市 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-04
* Time: 20:49:22
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\ProvinceCity;
use common\model\Result;
use common\dao\ProvinceCityDao;

class ProvinceCityLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new ProvinceCityDao();
    }

    /**
    * 添加
    * @param ProvinceCity $vo
    * @return Result
    */
    public function add(ProvinceCity $vo){
        $result = new Result();
        $pkId = $this->dao->add($vo);

        return $result;
    }

    /**
    * 更新
    * @param ProvinceCity $vo
    * @param ProvinceCity $mapVo
    * @return Result
    */
    public function update(ProvinceCity $vo,ProvinceCity $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->update($vo,$mapVo);

        return $result;
    }

    /**
    * 删除
    * @param ProvinceCity $mapVo
    * @return Result
    */
    public function delete(ProvinceCity $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param ProvinceCity $mapVo
    * @return array
    */
    public function find(ProvinceCity $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param ProvinceCity $mapVo
    * @return array_list
    */
    public function findList(ProvinceCity $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 查找省份城市列表
     * @param ProvinceCity $mapVo
     * @return array_list
     */
    public function provinceCityList(ProvinceCity $mapVo){
        return $this->dao->provinceCityList($mapVo);
    }
}