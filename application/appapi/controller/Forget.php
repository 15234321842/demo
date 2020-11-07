<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/31
 * Time: 15:23
 */

namespace app\appapi\controller;


use data\model\UserModel;
use data\model\VerifyicationModel;
use data\service\Member;
use data\service\User as UserService;

use think\Validate;
class Forget extends BaseController
{

    //找回登陆密码
    public function password(){
        $member = new Member();
        $validate = new Validate([
            'onepassword'=> 'require',
            'twopassword'=> 'require',
            'verification'=> 'require',
            'mobile'=> 'require',
        ]);
        $validate->message([
            'onepassword.require' => '新密码未填写!',
            'twopassword.require' => '确认新密码未填写!',
            'verification.require' => '验证码未填写!',
            'mobile.require' => '手机号码未填写!',
        ]);
        $data = $this->request->param();

        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        if (! $member->memberIsMobile($data['mobile'])) {

            $this->error('该手机号未注册');
        }

        $ver=$this->getverification($data['mobile'],1);

        if($ver!=$data['verification']){
            $this->error('验证码错误！');
        }
        if($data['onepassword']!=$data['twopassword']){

            $this->error('请确认两次支付密码一致！');
        }
        $user=new UserService();
        if($user->forgetPwd($data['mobile'],$data['onepassword'])){

            $this->success('操作成功！');
        }else{
            $this->error('操作失败！');
        }
    }



    //获取验证码
    public function getverification($mobile,$type){

        $Verifyication= new VerifyicationModel();
        $info=$Verifyication->getInfo(['account'=>$mobile,'type'=>$type],"*");
        return $info['code'];
    }

//找回密码发送的短信
    public function sendpwd(){
        $params['send_param'] = request()->post('mobile', '');

        $params["send_type"]='sms';
        //   $params['user_id'] =$this->uid;
        $params["shop_id"]=0;


        if(! $params['send_param']){
            $this->error('手机号未填写！');
        } else {

            $hook = runhook("Notify", "forgotPassword", $params);

            if (empty($hook)) {
                $this->error('发送失败');
            }else{
                if ($hook["code"] != 0) {
                    $this->error('发送短信太频繁，请稍后再试！');
                }else if ($hook["code"] == 0){
                    $this->setCode($params['send_param'],$hook['param'],1);
                }
            }

            if (! empty($hook) && ! empty($hook['param'])) {
                $this->success('发送成功');
            } else {
                $this->error('发送失败');
            }
        }
    }


    /*
    * 写入验证码
    * */
    public function setCode($phone,$result,$type=''){
        $Verifyication=new VerifyicationModel();
        $where['account']=$phone;
        if(!empty($type)){
            $where['type']=$type;

        }else{
            $type=0;
        }
        $info=$Verifyication->getInfo($where,"*");
        $time=time();


        if($info){
            $Verifyication->save(['send_time'=>$time,'expire_time'=>$time+120,'code'=>$result,'count'=>$info['count']+1,'account'=>$phone],$where);
        }else{
            $Verifyication->save(['send_time'=>$time,'expire_time'=>$time+120,'code'=>$result,'count'=>1,'account'=>$phone,'type'=>$type]);

        }
    }

    //找回支付密码

    public function forgetpaypwd(){
        $uid=$this->getUserId();
        if(empty($uid)){
            $this->error('请先登录！');
        }
       $user_model= new UserModel();
        $res=$user_model->getInfo(['uid'=>$uid]);
        if(empty($res['user_tel'])){
            $this->error('请先绑定手机号！');
        }

        $member = new Member();
        $validate = new Validate([
            'verification'=> 'require',
        ]);
        $validate->message([
            'verification.require' => '验证码未填写!'
        ]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $user=New UserModel();
        $userinfo=$user->getInfo(['uid'=>$uid],"*");
        $ver=$this->getverification($res['user_tel'],2);
        if($ver!=$data['verification']){
            $this->error('验证码错误！');
        }
      $res=  $user->save(['pay_password'=>''],['uid'=>$uid]);
        if($res){
            $this->success('操作成功！');
        }else{
            $this->error('操作失败！');
        }
    }

    //找回支付密码发送的短信
    public function sendpaypwd(){

        $uid=$this->getUserId();
        if(empty($uid)){
            $this->error('请先登录！');
        }
        $user_model=new UserModel();
        $res=$user_model->getInfo(['uid'=>$uid]);
        if(empty($res['user_tel'])){

            $this->error('请先绑定手机号！');
        }
        $params['send_param'] = $res['user_tel'];
        $params["send_type"]='sms';
        //   $params['user_id'] =$this->uid;
        $params["shop_id"]=0;

            $hook = runhook("Notify", "forgotPassword", $params);
            if (empty($hook)) {
                $this->error('发送失败');
            }else{
                if ($hook["code"] != 0) {
                    $this->error('发送短信太频繁，请稍后再试！');
                }else if ($hook["code"] == 0){
                    $this->setCode($params['send_param'],$hook['param'],2);
                }
            }
            if (! empty($hook) && ! empty($hook['param'])) {
                $this->success('发送成功');
            } else {
                $this->error('发送失败');
            }

    }





}