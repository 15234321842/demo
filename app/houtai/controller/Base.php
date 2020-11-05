<?php
/**
 * 控制器 Base 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-09-27
 * Time: 17:46:11
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace app\houtai\controller;

use common\logic\MessageLogic;
use common\model\Result;
use HttpResponseException;
use think\Controller;
use think\facade\Request;
use think\facade\Response;
use think\facade\Session;
use think\facade\Url;

class Base extends Controller
{
    private $user_id = 0;
    private $user_type = 0;
    private $vip_level = 0;
    private $user_name = '';
    private $nickname = '';
    private $userInfo = null;

    protected $notNeedLogin = array();
    protected $needPermission = array();

    public function __construct(){
        parent::__construct();

        $this->userInfo = Session::get('userInfo');
        if($this->userInfo){
            $this->user_id = $this->userInfo['user_id'];
            $this->user_name = $this->userInfo['user_name'];
            $this->nickname = $this->userInfo['nickname'];
            $this->user_type = $this->userInfo['user_type'];
            $this->vip_level = $this->userInfo['vip_level'];
        }

        //检测用户是否需要登录
        $this->checkUserLogin();

        //获取未读消息数
        $notReadMessageCount = 0;
        if($this->user_id > 0){
            $logic = new MessageLogic();
            $notReadMessageCount = $logic->notReadMessageCount($this->user_id);
        }
        $this->assign('notReadMessageCount',$notReadMessageCount);

        $this->assign('userId',$this->user_id);
        $this->assign('userName',$this->user_name);
        $this->assign('nickName',$this->nickname);
    }

    /**
     * 检测用户是否需要登录
     */
    private function checkUserLogin(){
        $doLogin = true;

        if(in_array($this->request->action(),$this->notNeedLogin)) {
            $doLogin = false;
        }

        if($doLogin  && !($this->user_id > 0)){

            if($this->request->isAjax()){
                $result = new Result();
                $result->success = false;
                $result->msg = '请先登录，再继续操作！';
                $result->needLogin = true;

                $response = Response::create($result,'json');
                $response->send();
                exit;
            }else{
                $this->redirect('/houtai/login/index');
            }
        }
    }

    /**
     * 判断用户权限
     */
    public function checkPermission(){
        if(in_array($this->request->action(),$this->needPermission)) {
            if(!($this->user_type > 0)){
                $result = new Result();
                $result->success = false;
                $result->msg = '没有权限操作！';
                $result->needPermission = true;

                $this->letuResponse($result);
            }
        }
    }

    public function letuResponse(Result $result){
        if($this->request->isAjax()){
            $response = Response::create($result,'json');
            $response->send();
            exit;
        }else{
            $this->error($result->msg);
        }
    }

    public function getUserId(){
        return $this->user_id;
    }

    public function getUserName(){
        return $this->user_name;
    }

    public function getNickname(){
        return $this->nickname;
    }

    public function getUserInfo(){
        return $this->userInfo;
    }

    public function setUserId($user_id){
        $this->user_id = $user_id;
    }

    public function setUserName($user_name){
        $this->user_name = $user_name;
    }

    public function setNickname($nickname){
        $this->nickname = $nickname;
    }

    public function setUserInfo($userInfo){
        $this->userInfo = $userInfo;
    }
}