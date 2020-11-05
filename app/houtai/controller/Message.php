<?php
/**
 * 消息控制器 Message 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-10-07
 * Time: 10:16:00
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace app\houtai\controller;


use common\logic\MessageLogic;
use common\model\Result;
use common\model\Message as ModelMessage;

class Message extends Base{
    public function index(){
        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        $params['receive_uid'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new MessageLogic();
        $list = $logic->paginate($params);

        $this->assign('list',$list);
        $this->assign('lastTime',$lastTime);
        $this->assign('params',$params);

        $msgTypeList = config('msg_type');
        $this->assign('msgTypeList',$msgTypeList);

        $isReadList = config('is_read');
        $this->assign('isReadList',$isReadList);

        return $this->fetch();
    }

    public function scroll_loading(){
        $result = new Result();

        $params = array();
        $params = $this->request->param();

        $params['receive_uid'] = $this->getUserId();
        $params['is_del'] = 0;

        $logic = new MessageLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['is_read_text'] = config('app.is_read.'.$v['is_read']);
            $v['msg_type_text'] = config('app.msg_type.'.$v['msg_type']);
            $v['msg_time_format'] = '';

            if($v['msg_time'] > 0){
                $v['msg_time_format'] = date('Y-m-d H:i',$v['msg_time']);
            }

            $v['encrypt_id'] = think_encrypt($v['msg_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    public function pull_to_refresh(){
        $result = new Result();

        $lastTime = time();
        $params = array();
        $params = $this->request->param();

        $params['receive_uid'] = $this->getUserId();
        $params['is_del'] = 0;
        $params['last_time'] = $lastTime;

        $logic = new MessageLogic();
        $list = $logic->paginate($params);

        $result->totalPages = $list->lastPage();
        $result->page = $list->currentPage();
        $result->limit = $list->listRows();
        $result->success = true;
        $result->dataType = Result::DATATYPE_ARRAY;
        $result->time = $lastTime;
        $result->data = $list->items();
        foreach($result->data as $k=>&$v){
            $v['is_read_text'] = config('app.is_read.'.$v['is_read']);
            $v['msg_type_text'] = config('app.msg_type.'.$v['msg_type']);
            $v['msg_time_format'] = '';

            if($v['msg_time'] > 0){
                $v['msg_time_format'] = date('Y-m-d H:i',$v['msg_time']);
            }

            $v['encrypt_id'] = think_encrypt($v['msg_id']);
            $v['view_url'] = letu_url('view',array('id'=>$v['encrypt_id']));

            unset($v);
        }

        return $result;
    }

    /**
     * 生成消息
     * @return \think\Response
     */
    public function build_message(){
        $result = new Result();
        $result->success = false;
        $result->msg = '操作失败！';

        $message_advance_days = config('message_advance_days');

        $logic =  new MessageLogic();
        $result = $logic->buildMessage($this->getUserId(),$message_advance_days);

        return response($result,200,[],'json');
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

        $mapVo = new ModelMessage();
        $mapVo->setMsgId($id);

        $logic = new MessageLogic();
        $model = $logic->find($mapVo);
        if(!$model){
            $this->error('消息不存在！');
            return false;
        }

        $model['encrypt_id'] = think_encrypt($model['msg_id']);
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

        $logic = new MessageLogic();
        $vo = new ModelMessage();
        $mapVo = new ModelMessage();

        $vo->setIsDel(1);
        $vo->setEditUid($this->getUserId());
        $vo->setEditTime(time());

        $mapVo->setMsgId($id);

        $result = $logic->update($vo,$mapVo);
        if($result->success){
            $result->msg = '删除成功！';
            $result->url = url('index');
        }

        return response($result,200,[],'json');
    }

    /**
     * 设置已读
     * @return Result|\think\Response
     */
    public function do_read(){
        $result = new Result();
        $result->msg = '设置失败！';
        $result->success = false;

        if($this->request->isPost() == false){
            $result->msg = '设置失败：非法操作！';
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
        $is_read = 0;
        if(isset($params['is_read'])){
            $is_read = $params['is_read'];
        }

        if($is_read == 0){
            $logic = new MessageLogic();
            $vo = new ModelMessage();
            $mapVo = new ModelMessage();

            $vo->setIsRead(1);
            $vo->setReadTime(time());

            $mapVo->setMsgId($id);

            $result = $logic->update($vo,$mapVo);
            if($result->success){
                $result->msg = '阅读成功！';
                $result->url = url('index');
            }
        }

        return response($result,200,[],'json');
    }
}