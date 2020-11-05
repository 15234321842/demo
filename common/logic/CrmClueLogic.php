<?php
/**
* 客户线索 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-16
* Time: 20:11:15
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\CrmClue;
use common\model\Result;
use common\dao\CrmClueDao;
use think\Db;
use think\db\Expression;

class CrmClueLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new CrmClueDao();
    }

    /**
    * 添加
    * @param CrmClue $vo
    * @return Result
    */
    public function add(CrmClue $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '线索添加失败！';

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '线索添加成功！';
        }

        return $result;
    }

    /**
    * 更新
    * @param CrmClue $vo
    * @param CrmClue $mapVo
    * @return Result
    */
    public function update(CrmClue $vo,CrmClue $mapVo){
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
    * @param CrmClue $mapVo
    * @return Result
    */
    public function delete(CrmClue $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param CrmClue $mapVo
    * @return array
    */
    public function find(CrmClue $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param CrmClue $mapVo
    * @return array_list
    */
    public function findList(CrmClue $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 加载其它线索
     * @param int $customer_id
     * @param int $cule_id
     * @param int $uid
     * @return array_list
     */
    public function load_other($customer_id,$cule_id,$uid){
        return $this->dao->load_other($customer_id,$cule_id,$uid);
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
            $map['cc.customer_id'] = $params['customer_id'];
        }
        if(isset($params['clue_time']) && !string_empty($params['clue_time'])){
            $whereExp .= "and (unix_timestamp(from_unixtime(cc.clue_time, '%Y-%m-%d')) = ".strtotime($params['clue_time']).')';
        }
        if(isset($params['is_del']) && !string_empty($params['is_del'])){
            $map['cc.is_del'] = $params['is_del'];
        }
        if(isset($params['add_uid']) && !string_empty($params['add_uid'])){
            $map['cc.add_uid'] = $params['add_uid'];
        }
        if(isset($params['last_time']) && $params['last_time'] > 0){
            $map['cc.edit_time'] = new Expression('<='.$params['last_time']);
        }

        $list = Db::name('crm_clue cc')
            ->field('cc.clue_id,cc.customer_id,cc.clue_time,cc.clue_content,cc.clue_uid
            ,c.customer_name')
            ->join('crm_customer c','c.customer_id=cc.customer_id','left')
            ->order(['cc.clue_time'=>'desc','cc.clue_id'=>'desc'])
            ->where($map)->whereRaw($whereExp)
            ->paginate(null,false,$config);

        return $list;
    }
}