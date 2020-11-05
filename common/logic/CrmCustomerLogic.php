<?php
/**
* 客户资料 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-05
* Time: 23:51:39
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\CrmCustomer;
use common\model\Result;
use common\dao\CrmCustomerDao;
use think\Db;
use think\db\Expression;

class CrmCustomerLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new CrmCustomerDao();
    }

    /**
    * 添加
    * @param CrmCustomer $vo
    * @return Result
    */
    public function add(CrmCustomer $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '添加客户失败！';

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '添加客户成功！';
        }

        return $result;
    }

    /**
     * 批量添加
     * @param array $dataList
     * @return Result
     */
    public function addBatch($dataList){
        $result = new Result();
        $result->success = false;
        $result->msg = '批量添加失败！';

        Db::startTrans();
        try{
            $count = $this->dao->addBatch($dataList);
            if($count > 0){
                $result->success = true;
                $result->msg = '批量添加成功！';
            }
        }catch (\Exception $e){
            $result->success = false;
            $result->msg = $e->getMessage();
            Db::rollback();
        }
        Db::commit();

        return $result;
    }

    /**
    * 更新
    * @param CrmCustomer $vo
    * @param CrmCustomer $mapVo
    * @return Result
    */
    public function update(CrmCustomer $vo,CrmCustomer $mapVo){
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
    * @param CrmCustomer $mapVo
    * @return Result
    */
    public function delete(CrmCustomer $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param CrmCustomer $mapVo
    * @return array
    */
    public function find(CrmCustomer $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param CrmCustomer $mapVo
    * @return array_list
    */
    public function findList(CrmCustomer $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 获取客户列表
     * @param CrmCustomer $mapVo
     * @return array_list
     */
    public function customerListShortFields(CrmCustomer $mapVo){
        return $this->dao->customerListShortFields($mapVo);
    }

    /**
     * 获取提前几天过生日的客户
     * @param $user_id 用户ID
     * @param $advance_days 提前的天数
     * @return array|null|\PDOStatement|string|\think\Collection
     */
    public function getAllBirthdate($user_id,$advance_days){
        $list = array();
        $map = array();
        $whereExp = '1=1 ';

        if($advance_days >= 0){
            $whereExp .= "AND DATEDIFF(FROM_UNIXTIME(birthdate, CONCAT(YEAR(CURDATE()),'-','%m-%d')),CURDATE()) > 0 AND DATEDIFF(FROM_UNIXTIME(birthdate, CONCAT(YEAR(CURDATE()),'-','%m-%d')),CURDATE()) <=$advance_days";

            $map['add_uid'] = $user_id;
            $map['is_del'] = 0;
            $map['birthdate'] = new Expression('> 0');

            $list = Db::name('crm_customer')
                ->field('customer_id,customer_name,birthdate')
                ->order(['add_time'=>'asc','customer_id'=>'desc'])
                ->where($map)->whereRaw($whereExp)
                ->select();
        }

        return $list;
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
        $config['query'] = array();

        if(isset($params['customer_name']) && !string_empty($params['customer_name'])){
            $map['c.customer_name'] = new Expression("like '%".$params['customer_name']."%'");
            $config['query']['customer_name'] = $params['customer_name'];
        }
        if(isset($params['mobile']) && !string_empty($params['mobile'])){
            $map['c.mobile'] = new Expression("like '%".$params['mobile']."%'");
            $config['query']['mobile'] = $params['mobile'];
        }
        if(isset($params['sex']) && !string_empty($params['sex'])){
            $map['c.sex'] = $params['sex'];
            $config['query']['sex'] = $params['sex'];
        }
        if(isset($params['customer_relations']) && !string_empty($params['customer_relations'])){
            $map['c.customer_relations'] = $params['customer_relations'];
            $config['query']['customer_relations'] = $params['customer_relations'];
        }
        if(isset($params['customer_class']) && !string_empty($params['customer_class'])){
            $map['c.customer_class'] = $params['customer_class'];
            $config['query']['customer_class'] = $params['customer_class'];
        }

        if(isset($params['add_uid']) && $params['add_uid'] > 0){
            $map['c.add_uid'] = $params['add_uid'];
        }
        if(isset($params['aim_list']) && !string_empty($params['aim_list'])){
            $map['c.aim_list'] = $params['aim_list'];
        }
        if(!string_empty($params['is_del'])){
            $map['c.is_del'] = $params['is_del'];
        }
        if(isset($params['last_time']) && $params['last_time'] > 0){
            $map['c.edit_time'] = new Expression('<='.$params['last_time']);
        }

        $list = Db::name('crm_customer c')
            ->field('c.customer_id,c.customer_name,c.mobile,c.customer_relations,c.regular_customer,
            c.aim_list')
            ->order(['c.add_time'=>'desc','c.customer_id'=>'desc'])
            ->where($map)
            ->paginate(null,false,$config);

        return $list;
    }
}