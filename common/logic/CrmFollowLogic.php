<?php
/**
* 客户跟进 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-18
* Time: 15:44:14
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\CrmFollow;
use common\model\Result;
use common\dao\CrmFollowDao;
use think\Db;
use think\db\Expression;

class CrmFollowLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new CrmFollowDao();
    }

    /**
    * 添加
    * @param CrmFollow $vo
    * @return Result
    */
    public function add(CrmFollow $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '跟进日志添加失败！';

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '跟进日志添加成功！';
        }

        return $result;
    }

    /**
    * 更新
    * @param CrmFollow $vo
    * @param CrmFollow $mapVo
    * @return Result
    */
    public function update(CrmFollow $vo,CrmFollow $mapVo){
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
    * @param CrmFollow $mapVo
    * @return Result
    */
    public function delete(CrmFollow $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param CrmFollow $mapVo
    * @return array
    */
    public function find(CrmFollow $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param CrmFollow $mapVo
    * @return array_list
    */
    public function findList(CrmFollow $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 加载其它跟进日志
     * @param int $customer_id
     * @param int $follow_id
     * @param int $uid
     * @return array_list
     */
    public function load_other($customer_id,$follow_id,$uid){
        return $this->dao->load_other($customer_id,$follow_id,$uid);
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
            $map['c.customer_name'] = new Expression("like '%".$params['customer_name']."%'");
            $config['query']['customer_name'] = $params['customer_name'];
        }

        if(isset($params['customer_id']) && $params['customer_id'] > 0){
            $map['f.customer_id'] = $params['customer_id'];
        }
        if(isset($params['follow_time']) && !string_empty($params['follow_time'])){
            $whereExp .= "and (unix_timestamp(from_unixtime(f.follow_time, '%Y-%m-%d')) = ".strtotime($params['follow_time']).')';
        }
        if(isset($params['follow_type']) && !string_empty($params['follow_type'])){
            $map['f.follow_type'] = $params['follow_type'];
        }
        if(isset($params['is_del']) && !string_empty($params['is_del'])){
            $map['f.is_del'] = $params['is_del'];
        }
        if(isset($params['add_uid']) && !string_empty($params['add_uid'])){
            $map['f.add_uid'] = $params['add_uid'];
        }
        if(isset($params['last_time']) && $params['last_time'] > 0){
            $map['f.edit_time'] = new Expression('<='.$params['last_time']);
        }

        $list = Db::name('crm_follow f')
            ->field('f.follow_id,f.customer_id,f.follow_time,f.follow_log,f.follow_type
            ,f.follow_uid,c.customer_name')
            ->join('crm_customer c','c.customer_id=f.customer_id','left')
            ->order(['f.follow_time'=>'desc','f.follow_id'=>'desc'])
            ->where($map)->whereRaw($whereExp)
            ->paginate(null,false,$config);

        return $list;
    }
}