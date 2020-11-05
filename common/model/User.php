<?php
/**
 * 用户 Model 类
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-10-30
 * Time: 16:53:22
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

namespace common\model;

class User
{
    const TABLE_NAME = 'user';
    const PRIMARY_KEY = 'user_id';

    /**
     * 设置字段-值集合
     */
    private $set_data_list = array();

    private $user_id;
    private $user_name;
    private $nickname;
    private $reg_mobile;
    private $reg_email;
    private $reg_source;
    private $reg_time;
    private $thirdparty_bind;
    private $login_password;
    private $sex;
    private $birthdate;
    private $login_fail;
    private $login_datetime;
    private $verify_code;
    private $verify_type;
    private $code_sendtime;
    private $user_status;
    private $add_uid;
    private $add_time;
    private $edit_uid;
    private $edit_time;
    private $is_del;
    private $user_logo;
    private $vip_level;
    private $user_type;
    private $lecoin;
    private $init_password;
    private $init_password_change;

    /**
     * 字段属性 - 用户ID
     * @return $user_id
     */
    public function getUserId(){
        return $this->user_id;
    }

    /**
     * 字段属性 - 名称
     * @return $user_name
     */
    public function getUserName(){
        return $this->user_name;
    }

    /**
     * 字段属性 - 昵称
     * @return $nickname
     */
    public function getNickname(){
        return $this->nickname;
    }

    /**
     * 字段属性 - 注册手机号
     * @return $reg_mobile
     */
    public function getRegMobile(){
        return $this->reg_mobile;
    }

    /**
     * 字段属性 - 注册邮箱
     * @return $reg_email
     */
    public function getRegEmail(){
        return $this->reg_email;
    }

    /**
     * 字段属性 - 注册来源：0 default 1 web 2 mobile 3 android 4 ios
     * @return $reg_source
     */
    public function getRegSource(){
        return $this->reg_source;
    }

    /**
     * 字段属性 - 注册时间
     * @return $reg_time
     */
    public function getRegTime(){
        return $this->reg_time;
    }

    /**
     * 字段属性 - 第三方绑定登录：0 nil 1 weixin 2 qq 3 alipay 4 taobao 5 weibo
     * @return $thirdparty_bind
     */
    public function getThirdpartyBind(){
        return $this->thirdparty_bind;
    }

    /**
     * 字段属性 - 登录密码
     * @return $login_password
     */
    public function getLoginPassword(){
        return $this->login_password;
    }

    /**
     * 字段属性 - 性别：0 女 1 男 2 保密
     * @return $sex
     */
    public function getSex(){
        return $this->sex;
    }

    /**
     * 字段属性 - 出生日期
     * @return $birthdate
     */
    public function getBirthdate(){
        return $this->birthdate;
    }

    /**
     * 字段属性 - 登录失败次数：一天登录失败5次，就自动禁用登录账号
     * @return $login_fail
     */
    public function getLoginFail(){
        return $this->login_fail;
    }

    /**
     * 字段属性 - 登录日期
     * @return $login_datetime
     */
    public function getLoginDatetime(){
        return $this->login_datetime;
    }

    /**
     * 字段属性 - 验证码：短信，邮件
     * @return $verify_code
     */
    public function getVerifyCode(){
        return $this->verify_code;
    }

    /**
     * 字段属性 - 验证类型：0 短信 1 邮件
     * @return $verify_type
     */
    public function getVerifyType(){
        return $this->verify_type;
    }

    /**
     * 字段属性 - 验证码发送时间
     * @return $code_sendtime
     */
    public function getCodeSendtime(){
        return $this->code_sendtime;
    }

    /**
     * 字段属性 - 用户状态：0 禁用 1 激活
     * @return $user_status
     */
    public function getUserStatus(){
        return $this->user_status;
    }

    /**
     * 字段属性 - 添加用户ID
     * @return $add_uid
     */
    public function getAddUid(){
        return $this->add_uid;
    }

    /**
     * 字段属性 - 添加时间
     * @return $add_time
     */
    public function getAddTime(){
        return $this->add_time;
    }

    /**
     * 字段属性 - 编辑用户ID
     * @return $edit_uid
     */
    public function getEditUid(){
        return $this->edit_uid;
    }

    /**
     * 字段属性 - 编辑时间
     * @return $edit_time
     */
    public function getEditTime(){
        return $this->edit_time;
    }

    /**
     * 字段属性 - 是否删除：0 正常 1 删除
     * @return $is_del
     */
    public function getIsDel(){
        return $this->is_del;
    }

    /**
     * 字段属性 - 用户头像
     * @return $user_logo
     */
    public function getUserLogo(){
        return $this->user_logo;
    }

    /**
     * 字段属性 - 会员等级：0 青铜 1 白银 2 黄金 3 铂金 4 黑金 33 创始人
     * @return $vip_level
     */
    public function getVipLevel(){
        return $this->vip_level;
    }

    /**
     * 字段属性 - 用户类型：0 会员 1 管理员
     * @return $user_type
     */
    public function getUserType(){
        return $this->user_type;
    }

    /**
     * 字段属性 - 乐币
     * @return $lecoin
     */
    public function getLecoin(){
        return $this->lecoin;
    }

    /**
     * 字段属性 - 初始密码
     * @return $init_password
     */
    public function getInitPassword(){
        return $this->init_password;
    }

    /**
     * 字段属性 - 初始密码更改：0 否 1 是
     * @return $init_password_change
     */
    public function getInitPasswordChange(){
        return $this->init_password_change;
    }

    /**
     * 字段方法 - 用户ID
     * @param $user_id
     * @return void
     */
    public function setUserId($user_id){
        $this->user_id = $user_id;
        $this->set_data_list['user_id'] = &$this->user_id;
    }

    /**
     * 字段方法 - 名称
     * @param $user_name
     * @return void
     */
    public function setUserName($user_name){
        $this->user_name = $user_name;
        $this->set_data_list['user_name'] = &$this->user_name;
    }

    /**
     * 字段方法 - 昵称
     * @param $nickname
     * @return void
     */
    public function setNickname($nickname){
        $this->nickname = $nickname;
        $this->set_data_list['nickname'] = &$this->nickname;
    }

    /**
     * 字段方法 - 注册手机号
     * @param $reg_mobile
     * @return void
     */
    public function setRegMobile($reg_mobile){
        $this->reg_mobile = $reg_mobile;
        $this->set_data_list['reg_mobile'] = &$this->reg_mobile;
    }

    /**
     * 字段方法 - 注册邮箱
     * @param $reg_email
     * @return void
     */
    public function setRegEmail($reg_email){
        $this->reg_email = $reg_email;
        $this->set_data_list['reg_email'] = &$this->reg_email;
    }

    /**
     * 字段方法 - 注册来源：0 default 1 web 2 mobile 3 android 4 ios
     * @param $reg_source
     * @return void
     */
    public function setRegSource($reg_source){
        $this->reg_source = $reg_source;
        $this->set_data_list['reg_source'] = &$this->reg_source;
    }

    /**
     * 字段方法 - 注册时间
     * @param $reg_time
     * @return void
     */
    public function setRegTime($reg_time){
        $this->reg_time = $reg_time;
        $this->set_data_list['reg_time'] = &$this->reg_time;
    }

    /**
     * 字段方法 - 第三方绑定登录：0 nil 1 weixin 2 qq 3 alipay 4 taobao 5 weibo
     * @param $thirdparty_bind
     * @return void
     */
    public function setThirdpartyBind($thirdparty_bind){
        $this->thirdparty_bind = $thirdparty_bind;
        $this->set_data_list['thirdparty_bind'] = &$this->thirdparty_bind;
    }

    /**
     * 字段方法 - 登录密码
     * @param $login_password
     * @return void
     */
    public function setLoginPassword($login_password){
        $this->login_password = $login_password;
        $this->set_data_list['login_password'] = &$this->login_password;
    }

    /**
     * 字段方法 - 性别：0 女 1 男 2 保密
     * @param $sex
     * @return void
     */
    public function setSex($sex){
        $this->sex = $sex;
        $this->set_data_list['sex'] = &$this->sex;
    }

    /**
     * 字段方法 - 出生日期
     * @param $birthdate
     * @return void
     */
    public function setBirthdate($birthdate){
        $this->birthdate = $birthdate;
        $this->set_data_list['birthdate'] = &$this->birthdate;
    }

    /**
     * 字段方法 - 登录失败次数：一天登录失败5次，就自动禁用登录账号
     * @param $login_fail
     * @return void
     */
    public function setLoginFail($login_fail){
        $this->login_fail = $login_fail;
        $this->set_data_list['login_fail'] = &$this->login_fail;
    }

    /**
     * 字段方法 - 登录日期
     * @param $login_datetime
     * @return void
     */
    public function setLoginDatetime($login_datetime){
        $this->login_datetime = $login_datetime;
        $this->set_data_list['login_datetime'] = &$this->login_datetime;
    }

    /**
     * 字段方法 - 验证码：短信，邮件
     * @param $verify_code
     * @return void
     */
    public function setVerifyCode($verify_code){
        $this->verify_code = $verify_code;
        $this->set_data_list['verify_code'] = &$this->verify_code;
    }

    /**
     * 字段方法 - 验证类型：0 短信 1 邮件
     * @param $verify_type
     * @return void
     */
    public function setVerifyType($verify_type){
        $this->verify_type = $verify_type;
        $this->set_data_list['verify_type'] = &$this->verify_type;
    }

    /**
     * 字段方法 - 验证码发送时间
     * @param $code_sendtime
     * @return void
     */
    public function setCodeSendtime($code_sendtime){
        $this->code_sendtime = $code_sendtime;
        $this->set_data_list['code_sendtime'] = &$this->code_sendtime;
    }

    /**
     * 字段方法 - 用户状态：0 禁用 1 激活
     * @param $user_status
     * @return void
     */
    public function setUserStatus($user_status){
        $this->user_status = $user_status;
        $this->set_data_list['user_status'] = &$this->user_status;
    }

    /**
     * 字段方法 - 添加用户ID
     * @param $add_uid
     * @return void
     */
    public function setAddUid($add_uid){
        $this->add_uid = $add_uid;
        $this->set_data_list['add_uid'] = &$this->add_uid;
    }

    /**
     * 字段方法 - 添加时间
     * @param $add_time
     * @return void
     */
    public function setAddTime($add_time){
        $this->add_time = $add_time;
        $this->set_data_list['add_time'] = &$this->add_time;
    }

    /**
     * 字段方法 - 编辑用户ID
     * @param $edit_uid
     * @return void
     */
    public function setEditUid($edit_uid){
        $this->edit_uid = $edit_uid;
        $this->set_data_list['edit_uid'] = &$this->edit_uid;
    }

    /**
     * 字段方法 - 编辑时间
     * @param $edit_time
     * @return void
     */
    public function setEditTime($edit_time){
        $this->edit_time = $edit_time;
        $this->set_data_list['edit_time'] = &$this->edit_time;
    }

    /**
     * 字段方法 - 是否删除：0 正常 1 删除
     * @param $is_del
     * @return void
     */
    public function setIsDel($is_del){
        $this->is_del = $is_del;
        $this->set_data_list['is_del'] = &$this->is_del;
    }

    /**
     * 字段方法 - 用户头像
     * @param $user_logo
     * @return void
     */
    public function setUserLogo($user_logo){
        $this->user_logo = $user_logo;
        $this->set_data_list['user_logo'] = &$this->user_logo;
    }

    /**
     * 字段方法 - 会员等级：0 青铜 1 白银 2 黄金 3 铂金 4 黑金 33 创始人
     * @param $vip_level
     * @return void
     */
    public function setVipLevel($vip_level){
        $this->vip_level = $vip_level;
        $this->set_data_list['vip_level'] = &$this->vip_level;
    }

    /**
     * 字段方法 - 用户类型：0 会员 1 管理员
     * @param $user_type
     * @return void
     */
    public function setUserType($user_type){
        $this->user_type = $user_type;
        $this->set_data_list['user_type'] = &$this->user_type;
    }

    /**
     * 字段方法 - 乐币
     * @param $lecoin
     * @return void
     */
    public function setLecoin($lecoin){
        $this->lecoin = $lecoin;
        $this->set_data_list['lecoin'] = &$this->lecoin;
    }

    /**
     * 字段方法 - 初始密码
     * @param $init_password
     * @return void
     */
    public function setInitPassword($init_password){
        $this->init_password = $init_password;
        $this->set_data_list['init_password'] = &$this->init_password;
    }

    /**
     * 字段方法 - 初始密码更改：0 否 1 是
     * @param $init_password_change
     * @return void
     */
    public function setInitPasswordChange($init_password_change){
        $this->init_password_change = $init_password_change;
        $this->set_data_list['init_password_change'] = &$this->init_password_change;
    }

    /**
     * 获取设置字段-值集合，标记添加、更新的字段集合
     */
    public function getSetDataList(){
        return $this->set_data_list;
    }

    /**
     * 清空设置字段-值集合
     */
    public function clearSetDataList(){
        $this->set_data_list = array();
    }
}