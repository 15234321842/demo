<?php
/**
* 销售记录 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-18
* Time: 21:37:52
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\CrmCustomer;
use common\model\SellRecord;
use common\model\Result;
use common\dao\SellRecordDao;
use think\Db;
use think\db\Expression;

class SellRecordLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new SellRecordDao();
    }

    /**
    * 添加
    * @param SellRecord $vo
    * @return Result
    */
    public function add(SellRecord $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '销售记录添加失败！';

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '销售记录添加成功！';

            $customer_id = $vo->getCustomerId();

            //更新客户标识为“老”客户
            $vo = new CrmCustomer();
            $vo->setRegularCustomer(1);

            $mapVo = new CrmCustomer();
            $mapVo->setCustomerId($customer_id);

            $logic = new CrmCustomerLogic();
            $logic->update($vo,$mapVo);
        }

        return $result;
    }

    /**
    * 更新
    * @param SellRecord $vo
    * @param SellRecord $mapVo
    * @return Result
    */
    public function update(SellRecord $vo,SellRecord $mapVo){
        $result = new Result();
        $result->success = false;
        $result->msg = '更新失败！';

        //获取原记录
        $model = $this->find($mapVo);

        $success = false;
        $success = $this->dao->update($vo,$mapVo);
        if($success !== false){
            $result->success = true;
            $result->msg = '更新成功！';

            $logic = new CrmCustomerLogic();
            //更新新的客户的“新，老”客户标识
            $mapVo = new CrmCustomer();
            $mapVo->setCustomerId($vo->getCustomerId());

            $vo = new CrmCustomer();
            $vo->setRegularCustomer(1);

            $logic->update($vo,$mapVo);

            //判断原记录的客户是否存在销售记录，更新“新，老”客户标识
            $sellRecordCount = $this->sellRecordCount($model['customer_id']);

            $regular_customer = 0;
            if($sellRecordCount > 0){
                $regular_customer = 1;
            }

            $mapVo = new CrmCustomer();
            $mapVo->setCustomerId($model['customer_id']);

            $vo = new CrmCustomer();
            $vo->setRegularCustomer($regular_customer);

            $logic->update($vo,$mapVo);
        }

        return $result;
    }

    /**
    * 删除
    * @param SellRecord $mapVo
    * @return Result
    */
    public function delete(SellRecord $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param SellRecord $mapVo
    * @return array
    */
    public function find(SellRecord $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param SellRecord $mapVo
    * @return array_list
    */
    public function findList(SellRecord $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 客户的销售记录数量
     * @param int $customer_id
     * @return int
     */
    public function sellRecordCount($customer_id){
        return $this->dao->sellRecordCount($customer_id);
    }

    /**
     * 分页查询
     * @param array $params
     * @return null|\think\paginator\Collection
     */
    public function paginate($params = array())
    {
        $list = null;
        $map = array();
        $whereExp = '1=1 ';
        $config['query'] = array();

        if(isset($params['customer_name']) && !string_empty($params['customer_name'])){
            $map['c.customer_name'] = array('like','%'.$params['customer_name'].'%');
            $config['query']['customer_name'] = $params['customer_name'];
        }

        if(isset($params['customer_id']) && $params['customer_id'] > 0){
            $map['r.customer_id'] = $params['customer_id'];
        }
        if(isset($params['sell_date']) && !string_empty($params['sell_date'])){
            $whereExp .= "and (unix_timestamp(from_unixtime(r.sell_date, '%Y-%m-1')) = ".strtotime($params['sell_date'].'-1').')';
        }
        if(isset($params['is_del']) && !string_empty($params['is_del'])){
            $map['r.is_del'] = $params['is_del'];
        }
        if(isset($params['add_uid']) && !string_empty($params['add_uid'])){
            $map['r.add_uid'] = $params['add_uid'];
        }
        if(isset($params['last_time']) && $params['last_time'] > 0){
            $map['r.edit_time'] = new Expression('<='.$params['last_time']);
        }

        $list = Db::name('sell_record r')
            ->field("r.record_id,r.customer_id,r.sell_date,r.sell_money,r.sell_explain
            ,c.customer_name")
            ->join('crm_customer c','c.customer_id=r.customer_id','left')
            ->order(['r.sell_date'=>'desc','r.record_id'=>'desc'])
            ->where($map)->whereRaw($whereExp)
            ->paginate(null,false,$config);

        return $list;
    }
}