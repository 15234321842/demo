<?php
/**
 * 日程 Controller 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-10-21
 * Time: 16:50:21
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace app\houtai\controller;


use common\logic\WorkScheduleLogic;
use common\logic\WorkScheduleTaskLogic;
use common\model\Result;
use common\model\WorkSchedule as ModelWorkSchedule;
use common\model\WorkScheduleTask;

class WorkSchedule extends Base {
    public function index(){
        $params = array();
        $params = $this->request->param();


        $params['user_id'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['schedule_date'] = date('Y-m-d',time());

        $logic = new WorkScheduleLogic();
        $list = $logic->getScheduleTaskList($params);

        $this->assign('list',$list);

        return $this->fetch();
    }

    public function pull_to_refresh(){
        $result = new Result();

        $params = array();
        $params = $this->request->param();

        $params['user_id'] = $this->getUserId();
        $params['is_del'] = 0;

        $logic = new WorkScheduleLogic();
        $list = $logic->getScheduleTaskList($params);

        foreach($list as $k=>&$v){
            $v['task_level_text'] = config('app.task_level.'.$v['task_level']);

            $v['task_time_format'] = '';
            if($v['task_time'] > 0){
                $v['task_time_format'] = date('H:i',$v['task_time']);
            }

            $v['encrypt_id'] = think_encrypt($v['task_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));

            unset($v);
        }
        $result->success = true;
        $result->data = $list;

        return $result;
    }

    public function add(){
        $taskLevelList = config('app.task_level');
        $taskStatusList = config('app.task_status');
        $this->assign('taskLevelList',$taskLevelList);
        $this->assign('taskStatusList',$taskStatusList);

        return $this->fetch();
    }

    public function add_save(){
        $result = new Result();
        $result->msg = '添加失败！';
        $result->success = false;

        if(!$this->request->isPost()){
            $result->msg = '添加失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->post();

        if(string_empty($params['schedule_date'])){
            $result->success = false;
            $result->msg = '请选择日程日期！';
            return $result;
        }

        if(string_empty($params['task_time'])){
            $result->success = false;
            $result->msg = '请选择任务时间！';
            return $result;
        }

        if(string_empty($params['task_title'])){
            $result->success = false;
            $result->msg = '请填写任务标题！';
            return $result;
        }

        if(string_empty($params['task_content'])){
            $result->success = false;
            $result->msg = '请填写任务内容！';
            return $result;
        }

        $time = time();
        $voSchedule = new ModelWorkSchedule();

        if(isset($params['schedule_date']) && strlen($params['schedule_date']) > 0){
            $voSchedule->setScheduleDate(strtotime($params['schedule_date']));
        }
        $voSchedule->setUserId($this->getUserId());
        $voSchedule->setAddUid($this->getUserId());
        $voSchedule->setEditUid($this->getUserId());
        $voSchedule->setAddTime($time);
        $voSchedule->setEditTime($time);

        $voTask = new WorkScheduleTask();
        if(isset($params['task_time']) && strlen($params['task_time']) > 0){
            $voTask->setTaskTime(strtotime($params['schedule_date'].' '.$params['task_time'].':00'));
        }

        $voTask->setTaskTitle($params['task_title']);
        $voTask->setTaskContent($params['task_content']);
        $voTask->setTaskStatus($params['task_status']);
        $voTask->setTaskLevel($params['task_level']);

        $logic = new WorkScheduleLogic();
        $result = $logic->addScheduleTask($voSchedule,$voTask);

        return $result;
    }

    public function edit(){
        $id = 0;
        $params = $this->request->param();
        if(!isset($params['id'])){
            $this->error('非法参数！');
            exit;
        }
        $id = think_decrypt($params['id']);
        if(string_empty($id)){
            $this->error('非法参数！');
            exit;
        }

        $mapVo = new WorkScheduleTask();
        $mapVo->setTaskId($id);
        $mapVo->setIsDel(0);

        $logic = new WorkScheduleTaskLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('日程任务不存在！');
            return false;
        }
        $model['schedule_date'] = '';

        $mapVo = new ModelWorkSchedule();
        $mapVo->setScheduleId($model['schedule_id']);

        $logic = new WorkScheduleLogic();
        $schedule = $logic->find($mapVo);
        if($schedule){
            $model['schedule_date'] = $schedule['schedule_date'];
        }

        $taskLevelList = config('app.task_level');
        $taskStatusList = config('app.task_status');
        $this->assign('taskLevelList',$taskLevelList);
        $this->assign('taskStatusList',$taskStatusList);

        $this->assign('model',$model);

        return $this->fetch();
    }

    public function edit_save(){
        $result = new Result();
        $result->msg = '编辑失败！';
        $result->success = false;

        if(!$this->request->isPost()){
            $result->msg = '编辑失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->post();

        if(string_empty($params['schedule_date'])){
            $result->success = false;
            $result->msg = '请选择日程日期！';
            return $result;
        }

        if(string_empty($params['task_time'])){
            $result->success = false;
            $result->msg = '请选择任务时间！';
            return $result;
        }

        if(string_empty($params['task_title'])){
            $result->success = false;
            $result->msg = '请填写任务标题！';
            return $result;
        }

        if(string_empty($params['task_content'])){
            $result->success = false;
            $result->msg = '请填写任务内容！';
            return $result;
        }

        $id = think_decrypt($params['task_id']);
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $time = time();
        $voSchedule = new ModelWorkSchedule();

        if(isset($params['schedule_date']) && strlen($params['schedule_date']) > 0){
            $voSchedule->setScheduleDate(strtotime($params['schedule_date']));
        }

        $voSchedule->setEditUid($this->getUserId());
        $voSchedule->setEditTime($time);

        $voTask = new WorkScheduleTask();
        if(isset($params['task_time']) && strlen($params['task_time']) > 0){
            $voTask->setTaskTime(strtotime($params['schedule_date'].' '.$params['task_time'].':00'));
        }

        $voTask->setTaskTitle($params['task_title']);
        $voTask->setTaskContent($params['task_content']);
        $voTask->setTaskStatus($params['task_status']);
        $voTask->setTaskLevel($params['task_level']);

        $mapVoTask = new WorkScheduleTask();
        $mapVoTask->setTaskId($id);

        $logic = new WorkScheduleLogic();
        $result = $logic->updateScheduleTask($voSchedule,$voTask,$mapVoTask);

        return $result;
    }

    public function view(){
        $id = 0;
        $params = $this->request->param();
        if(!isset($params['id'])){
            $this->error('非法参数！');
            exit;
        }
        $id = think_decrypt($params['id']);
        if(string_empty($id)){
            $this->error('非法参数！');
            exit;
        }

        $mapVo = new WorkScheduleTask();
        $mapVo->setTaskId($id);
        $mapVo->setIsDel(0);

        $logic = new WorkScheduleTaskLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('日程任务不存在！');
            return false;
        }
        $model['schedule_date'] = '';

        $mapVo = new ModelWorkSchedule();
        $mapVo->setScheduleId($model['schedule_id']);

        $logic = new WorkScheduleLogic();
        $schedule = $logic->find($mapVo);
        if($schedule){
            $model['schedule_date'] = $schedule['schedule_date'];
        }

        $this->assign('model',$model);

        return $this->fetch();
    }

    public function del(){
        $result = new Result();
        $result->msg = '删除失败！';
        $result->success = false;

        if($this->request->isPost() == false){
            $result->msg = '删除失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->param();
        if(!isset($params['id'])){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }
        $id = think_decrypt($params['id']);
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $logic = new WorkScheduleTaskLogic();
        $vo = new WorkScheduleTask();
        $mapVo = new WorkScheduleTask();

        $vo->setIsDel(1);

        $mapVo->setTaskId($id);

        $result = $logic->update($vo,$mapVo);

        if($result->success){
            $result->msg = '删除成功！';
            $result->url = url('index');
        }

        return $result;
    }

    public function set_task_status(){
        $result = new Result();
        $result->msg = '操作失败！';
        $result->success = false;

        if($this->request->isPost() == false){
            $result->msg = '操作失败：非法操作！';
            $result->success = false;
            return $result;
        }

        $params = $this->request->param();
        if(!isset($params['id'])){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }
        $id = think_decrypt($params['id']);
        if(string_empty($id)){
            $result->msg = '非法操作！';
            $result->success = false;
            return $result;
        }

        $logic = new WorkScheduleTaskLogic();
        $vo = new WorkScheduleTask();
        $mapVo = new WorkScheduleTask();

        $vo->setTaskStatus($params['task_status']);

        $mapVo->setTaskId($id);

        $result = $logic->update($vo,$mapVo);

        if($result->success){
            $result->msg = '任务状态设置成功！';
            $result->url = url('index');
        }

        return $result;
    }
}