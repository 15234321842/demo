<?php
/**
 * 控制器 Login 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-09-27
 * Time: 17:46:11
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace app\houtai\controller;

use common\logic\UserLogic;
use common\model\Result;
use common\model\User;
use think\captcha\Captcha;
use think\facade\Session;

class Login extends Base
{
    private $captchaConfig = array();

    public function __construct(){
        $this->notNeedLogin = array('index','login','logout','verify');

        parent::__construct();
        $this->captchaConfig = config('captcha');
    }

    /**
     * 登录页面
     * @return mixed
     */
    public function index(){
        if($this->getUserId() > 0){
            $this->redirect('/houtai/index/index');
            exit;
        }
        return $this->fetch();
    }

    /**
     * 验证码
     * @return \think\Response
     */
    public function verify(){
        $captcha = new Captcha($this->captchaConfig);
        return $captcha->entry();
    }

    /**
     * 登录
     * @return Result
     */
    public function login(){
        $result = new Result();
        $result->success = false;
        $result->msg = '登录失败！';

        $username = $this->request->post('username');
        $userpass = $this->request->post('userpass');
        $verify = $this->request->post('verify');

        if(strlen(trim($username)) == 0){
            $result->success = false;
            $result->msg = '账号不能为空！';
            return $result;
        }

        if(strlen(trim($userpass)) == 0){
            $result->success = false;
            $result->msg = '密码不能为空！';
            return $result;
        }

        if(strlen(trim($verify)) == 0){
            $result->success = false;
            $result->msg = '验证码不能为空！';
            return $result;
        }

        $captcha = new Captcha($this->captchaConfig);
        $verify_check = $captcha->check($verify);
        if(!$verify_check){
            $result->success = false;
            $result->msg = '验证码不正确！';
            return $result;
        }

        $logic = new UserLogic();
        $mapVo = new User();
        $mapVo->setUserName($username);
        $mapVo->setLoginPassword($userpass);

        $result = $logic->userLogin($mapVo);
        if($result->success){
            $userInfo = array();
            $userInfo['user_id'] = $result->data['user_id'];
            $userInfo['user_name'] = $result->data['user_name'];
            $userInfo['nickname'] = $result->data['nickname'];
            $userInfo['user_logo'] = $result->data['user_logo'];
            $userInfo['user_status'] = $result->data['user_status'];
            $userInfo['user_type'] = $result->data['user_type'];
            $userInfo['vip_level'] = $result->data['vip_level'];

            Session::set('userInfo',$userInfo);

            $result->msg = '登录成功！正在跳转...';
            $result->dataType = Result::DATATYPE_URL;
            $result->url = url('houtai/index/index');
        }

        return $result;
    }

    /*
     * 退出登录
     */
    public function logout(){
        Session::set('userInfo',null);

        if($this->request->isAjax()){
            $result = new Result();
            $result->success = true;
            $result->msg = '退出登录成功！';
            $result->dataType = Result::DATATYPE_URL;
            $result->url = url('houtai/login/index');

            return response($result,200,[],'json');
        }

        $this->redirect('/houtai/login/index');
    }
}