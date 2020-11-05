<?php
/**
* 目标 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-21
* Time: 08:53:32
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\WorkAim;
use common\model\Result;
use common\dao\WorkAimDao;
use think\Db;
use think\db\Expression;

class WorkAimLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new WorkAimDao();
    }

    /**
    * 添加
    * @param WorkAim $vo
    * @return Result
    */
    public function add(WorkAim $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '目标添加失败！';

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '目标添加成功！';
        }

        return $result;
    }

    /**
    * 更新
    * @param WorkAim $vo
    * @param WorkAim $mapVo
    * @return Result
    */
    public function update(WorkAim $vo,WorkAim $mapVo){
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
    * @param WorkAim $mapVo
    * @return Result
    */
    public function delete(WorkAim $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param WorkAim $mapVo
    * @return array
    */
    public function find(WorkAim $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param WorkAim $mapVo
    * @return array_list
    */
    public function findList(WorkAim $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 获取所有到期目标
     * @param $user_id 用户ID
     * @param $advance_days 提前的天数
     * @return array|null|\PDOStatement|string|\think\Collection
     */
    public function getAllExpireAim($user_id,$time){
        $list = array();
        $map = array();
        $whereExp = '1=1 ';

        if($time > 0){
            $date_end_time = strtotime(date('Y-m-d',$time));
            $whereExp .= "AND unix_timestamp(from_unixtime(date_end,'%Y-%m-%d'))=$date_end_time";

            $map['user_id'] = $user_id;
            $map['is_del'] = 0;
            $map['date_end'] = new Expression('> 0');

            $list = Db::name('work_aim')
                ->field('aim_id,user_id,aim_name,date_begin,date_end,finish_status')
                ->order(['add_time'=>'asc','aim_id'=>'desc'])
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
        $whereExp = '1=1 ';
        $config['query'] = array();

        if(isset($params['sel_year']) && !string_empty($params['sel_year'])){
            $year_time = strtotime($params['sel_year'].'-1-1');
            $whereExp .= "and (unix_timestamp(from_unixtime(a.date_begin, '%Y-1-1')) = $year_time or unix_timestamp(from_unixtime(a.date_end, '%Y-1-1')) = $year_time)";
        }

        if(!string_empty($params['user_id'])){
            $map['a.add_uid'] = $params['user_id'];
        }

        if(!string_empty($params['is_del'])){
            $map['a.is_del'] = $params['is_del'];
        }
        if(isset($params['last_time']) && $params['last_time'] > 0){
            $map['a.edit_time'] = new Expression('<='.$params['last_time']);
        }

        $list = Db::name('work_aim a')
            ->field("a.aim_id,a.user_id,a.aim_name,a.date_begin,a.date_end,a.finish_status
            ,u.user_name,u.nickname")
            ->join('user u','a.user_id=u.user_id','left')
            ->order(['a.date_begin'=>'desc','a.aim_id'=>'desc'])
            ->where($map)->whereRaw($whereExp)
            ->paginate(null,false,$config);

        return $list;
    }
}