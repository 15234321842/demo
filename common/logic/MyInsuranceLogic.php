<?php
/**
* 保险单 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-22
* Time: 19:53:42
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\MyInsurance;
use common\model\Result;
use common\dao\MyInsuranceDao;
use think\Db;
use think\db\Expression;

class MyInsuranceLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new MyInsuranceDao();
    }

    /**
    * 添加
    * @param MyInsurance $vo
    * @return Result
    */
    public function add(MyInsurance $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '保单添加失败！';

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '保单添加成功！';
        }

        return $result;
    }

    /**
    * 更新
    * @param MyInsurance $vo
    * @param MyInsurance $mapVo
    * @return Result
    */
    public function update(MyInsurance $vo,MyInsurance $mapVo){
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
    * @param MyInsurance $mapVo
    * @return Result
    */
    public function delete(MyInsurance $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param MyInsurance $mapVo
    * @return array
    */
    public function find(MyInsurance $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param MyInsurance $mapVo
    * @return array_list
    */
    public function findList(MyInsurance $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 获取下次提前几天要交费的保单
     * @param $user_id 用户ID
     * @param $advance_days 提前的天数
     * @return array|null|\PDOStatement|string|\think\Collection
     */
    public function getAllNextPayInsurance($user_id,$advance_days){
        $list = array();
        $map = array();
        $whereExp = '1=1 ';

        if($advance_days >= 0){
            $whereExp .= "AND DATEDIFF(FROM_UNIXTIME(first_pay_date, CONCAT(YEAR(CURDATE()),'-','%m-%d')),CURDATE()) > 0 AND DATEDIFF(FROM_UNIXTIME(first_pay_date, CONCAT(YEAR(CURDATE()),'-','%m-%d')),CURDATE()) <=$advance_days";

            $map['user_id'] = $user_id;
            $map['is_del'] = 0;
            $map['first_pay_date'] = new Expression('> 0');

            $list = Db::name('my_insurance')
                ->field('insurance_id,user_id,policy_holder,policy_type,first_pay_date')
                ->order(['add_time'=>'asc','insurance_id'=>'desc'])
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
            $map['c.customer_name'] = array('like','%'.$params['customer_name'].'%');
            $config['query']['customer_name'] = $params['customer_name'];
        }
        if(isset($params['user_id']) && !string_empty($params['user_id'])){
            $map['e.user_id'] = $params['user_id'];
        }
        if(isset($params['policy_type']) && !string_empty($params['policy_type'])){
            $map['e.policy_type'] = $params['policy_type'];
        }
        if(isset($params['is_del']) && !string_empty($params['is_del'])){
            $map['e.is_del'] = $params['is_del'];
        }
        if(isset($params['last_time']) && $params['last_time'] > 0){
            $map['e.edit_time'] = new Expression('<='.$params['last_time']);
        }

        $list = Db::name('my_insurance e')
            ->field("e.insurance_id,e.user_id,e.customer_id,e.first_premium,e.insurance_main
            ,e.first_pay_date,e.effective_date,e.pay_year,c.customer_name")
            ->join('crm_customer c','c.customer_id=e.customer_id','left')
            ->order(['e.effective_date'=>'desc','e.insurance_id'=>'desc'])
            ->where($map)
            ->paginate(null,false,$config);

        return $list;
    }
}