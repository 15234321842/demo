<?php
/**
* 销售流程 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-07
* Time: 21:54:54
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\CrmCustomer;
use common\model\SellFlow;
use common\model\Result;
use common\dao\SellFlowDao;
use think\Db;

class SellFlowLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new SellFlowDao();
    }

    /**
    * 添加
    * @param SellFlow $vo
    * @return Result
    */
    public function add(SellFlow $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '线索添加失败！';
        $result->data = 0;

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->data = $pkId;
            $result->success = true;
            $result->msg = '线索添加成功！';
        }

        return $result;
    }

    /**
    * 更新
    * @param SellFlow $vo
    * @param SellFlow $mapVo
    * @return Result
    */
    public function update(SellFlow $vo,SellFlow $mapVo){
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
    * @param SellFlow $mapVo
    * @return Result
    */
    public function delete(SellFlow $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param SellFlow $mapVo
    * @return array
    */
    public function find(SellFlow $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param SellFlow $mapVo
    * @return array_list
    */
    public function findList(SellFlow $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 添加删除目标名单
     * @param string|int $aim_list
     * @param CrmCustomer $mapVo
     * @return Result
     */
    public function addDelAimList($aim_list,CrmCustomer $mapVo){
        $result = new Result();
        $result->success = false;
        $result->msg = '操作失败！';

        Db::startTrans();

        //先更新客户资料
        $vo = new CrmCustomer();
        $vo->setAimList($aim_list);

        $logic = new CrmCustomerLogic();
        $result = $logic->update($vo,$mapVo);
        if(!$result->success){
            Db::rollback();
            return $result;
        }

        if($aim_list > 0){
            $result->msg = '添加目标名单成功！';

            //添加销售流程记录
            $customerId = $mapVo->getCustomerId();

            $mapVo = new SellFlow();
            $mapVo->setCustomerId($customerId);

            $vo = $this->find($mapVo);
            //没有存在销售流程记录，就添加新的销售流程记录
            if(!$vo){
                $vo = new SellFlow();
                $vo->setCustomerId($customerId);
                $this->add($vo);
            }

        }else{
            $result->msg = '删除目标名单成功！';
        }

        Db::commit();

        return $result;
    }
}