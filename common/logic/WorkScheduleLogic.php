<?php
/**
* 日程 Logic 类
* Author: ls-huang
* Email: 282130106@qq.com
* Date: 2018-10-21
* Time: 16:50:18
* Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
*/

namespace common\logic;

use common\model\WorkSchedule;
use common\model\Result;
use common\dao\WorkScheduleDao;
use common\model\WorkScheduleTask;
use think\Db;

class WorkScheduleLogic
{
    private $dao;

    public function __construct(){
        $this->dao = new WorkScheduleDao();
    }

    /**
    * 添加
    * @param WorkSchedule $vo
    * @return Result
    */
    public function add(WorkSchedule $vo){
        $result = new Result();
        $result->success = false;
        $result->msg = '日程添加失败！';

        $pkId = $this->dao->add($vo);
        if($pkId > 0){
            $result->success = true;
            $result->msg = '日程添加成功！';
            $result->data = $pkId;
        }

        return $result;
    }

    /**
     * 添加日程任务
     * @param WorkSchedule $voSchedule
     * @param WorkScheduleTask $voTask
     * @return Result
     */
    public function addScheduleTask(WorkSchedule $voSchedule,WorkScheduleTask $voTask){
        $result = new Result();
        $result->success = false;
        $result->msg = '操作失败！';

        Db::startTrans();

        //如果已经存在当前日程，就只添加日程任务。否则先添加日程。
        $mapVo = new WorkSchedule();
        $mapVo->setUserId($voSchedule->getUserId());
        $mapVo->setScheduleDate($voSchedule->getScheduleDate());
        $mapVo->setIsDel(0);

        $schedule = $this->find($mapVo);

        try{
            $logic = new WorkScheduleTaskLogic();

            if($schedule){
                //添加日程任务
                $voTask->setScheduleId($schedule['schedule_id']);
                $logic->add($voTask);
            }else{
                //添加日程
                $result = $this->add($voSchedule);

                //添加日程任务
                if($result->success && $result->data > 0){
                    $voTask->setScheduleId($result->data);
                    $logic->add($voTask);
                }
            }
        }catch (\Exception $e){
            Db::rollback();
            $result->success = false;
            $result->msg = $e->getMessage();
            return $result;
        }

        $result->success = true;
        $result->msg = '添加日程任务成功！';
        Db::commit();

        return $result;
    }

    public function updateScheduleTask(WorkSchedule $voSchedule,WorkScheduleTask $voTask,WorkScheduleTask $mapVoTask){
        $result = new Result();
        $result->success = false;
        $result->msg = '操作失败！';

        Db::startTrans();

        try{
            $logic = new WorkScheduleTaskLogic();
            $task = $logic->find($mapVoTask);

            if($task){
                //更新日程
                $mapVo = new WorkSchedule();
                $mapVo->setScheduleId($task['schedule_id']);

                $this->update($voSchedule,$mapVo);

                //更新日程任务
                $logic->update($voTask,$mapVoTask);
            }
        }catch (\Exception $e){
            Db::rollback();
            $result->success = false;
            $result->msg = $e->getMessage();
            return $result;
        }

        $result->success = true;
        $result->msg = '更新日程任务成功！';
        Db::commit();

        return $result;
    }

    /**
    * 更新
    * @param WorkSchedule $vo
    * @param WorkSchedule $mapVo
    * @return Result
    */
    public function update(WorkSchedule $vo,WorkSchedule $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->update($vo,$mapVo);

        return $result;
    }

    /**
    * 删除
    * @param WorkSchedule $mapVo
    * @return Result
    */
    public function delete(WorkSchedule $mapVo){
        $result = new Result();
        $success = false;
        $success = $this->dao->delete($mapVo);

        return $result;
    }

    /**
    * 查找单条
    * @param WorkSchedule $mapVo
    * @return array
    */
    public function find(WorkSchedule $mapVo){
        return $this->dao->find($mapVo);
    }

    /**
    * 查找列表
    * @param WorkSchedule $mapVo
    * @return array_list
    */
    public function findList(WorkSchedule $mapVo){
        return $this->dao->findList($mapVo);
    }

    /**
     * 日程任务列表
     * @param array $params
     * @return null|\think\paginator\Collection
     */
    public function getScheduleTaskList($params = array())
    {
        $list = null;
        $map = array();

        if(isset($params['schedule_date']) && !string_empty($params['schedule_date'])){
            $schedule_date = strtotime($params['schedule_date']);
            $map['s.schedule_date'] = $schedule_date;
        }

        if(!string_empty($params['is_del'])){
            $map['t.is_del'] = $params['is_del'];
        }

        if(!string_empty($params['user_id'])){
            $map['s.user_id'] = $params['user_id'];
        }

        $list = Db::name('work_schedule s')
            ->field("t.task_id,t.schedule_id,t.task_time,t.task_title,t.task_content,t.task_level
            ,t.task_status,s.schedule_date,u.user_name,u.nickname")
            ->join('work_schedule_task t','t.schedule_id=s.schedule_id','right')
            ->join('user u','s.user_id=u.user_id','right')
            ->order(['t.task_time'=>'asc','t.task_id'=>'desc'])
            ->where($map)
            ->select();

        return $list;
    }
}