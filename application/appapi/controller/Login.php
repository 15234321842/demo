<?php
/**
 * Created by 比谷网络.
 * User: niuchao
 * Date: 2018-03-06
 * Time: 15:31
 */
namespace app\appapi\controller;

use data\model\NfxPromoterModel;
use data\model\NfxShopConfigModel;
use data\model\NfxShopMemberAssociationModel;
use data\model\NsMemberAccountModel;
use data\model\NsOrderModel;
use data\model\UserModel as UserModel;
use data\model\VerifyicationModel;
use data\model\WebSiteModel;
use data\service\NfxShopConfig;
use think\Db;
use think\Validate;
use data\service\Config as WebConfig;
use data\service\Member as Member;
use data\extend\WchatOauth;

class Login extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->member = new Member();
        $this->userModel = new UserModel();
    }

    // 用户登录 TODO 增加最后登录信息记录,如 ip
    public function login()
    {
        $validate = new Validate([
            'username' => 'require',
            'password' => 'require'
        ]);
        $validate->message([
            'username.require' => '请输入您的账号!',
            'password.require' => '请输入您的密码!'
        ]);

        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        $findUserWhere = [];

        if (Validate::is($data['username'], 'email')) {
            $findUserWhere['user_email'] = $data['username'];
        } else if (preg_match('/(^(13\d|15[^4\D]|17[013678]|18\d)\d{8})$/', $data['username'])) {
            $findUserWhere['user_tel'] = $data['username'];
        } else {
            $findUserWhere['user_name'] = $data['username'];
        }

        $user_name = trim($data['username']);
        $password = md5(trim($data['password']));
        $findUserWhere['user_password'] = $password;
        if (! empty($user_name) && !empty($password)) {
            $findUser = $this->userModel->getInfo($findUserWhere, $field = 'uid,user_status,user_name,is_system,instance_id, is_member');
        }else{
            $this->error("请输入您的账号及密码！");
        }

        $allowedDeviceTypes = ['mobile', 'android', 'iphone', 'ipad', 'web', 'pc', 'mac', 'wxapp'];

        if (empty($data['device_type']) || !in_array($data['device_type'], $allowedDeviceTypes)) {
            $this->error("请求错误,未知设备!");
        }
        if (! empty($findUser)) {
            if ($findUser['user_status'] == 0) {
                $this->error(getErrorInfo(USER_LOCK));
            } else {
                //登录成功后增加用户的登录次数
                $this->userModel->where("user_name|user_tel|user_email", "eq", $user_name)
                    ->setInc('login_num', 1);
                $userTokenQuery = Db::name("sys_user_token")
                    ->where('user_id', $findUser['uid'])
                    ->where('device_type', $data['device_type']);
                $findUserToken  = $userTokenQuery->find();
                $currentTime    = time();
                $expireTime     = $currentTime + 24 * 3600 * 180;
                $token          = md5(uniqid()) . md5(uniqid());
                if (empty($findUserToken)) {
                    $result = $userTokenQuery->insert([
                        'token'       => $token,
                        'user_id'     => $findUser['uid'],
                        'expire_time' => $expireTime,
                        'create_time' => $currentTime,
                        'device_type' => $data['device_type']
                    ]);
                } else {
                    $result = $userTokenQuery
                        ->where('user_id', $findUser['uid'])
                        ->where('device_type', $data['device_type'])
                        ->update([
                            'token'       => $token,
                            'expire_time' => $expireTime,
                            'create_time' => $currentTime
                        ]);
                }
                //修改客户的父级id ,在保护期外
                $pid = request()->post('pid', '');
                if(!empty($pid)){
                    $this-> updatepid($pid,$findUser['uid']);
                }
                if (empty($result)) {
                    $this->error("登录失败!");
                }
                $wx_openid = request()->post('wx_openid', '');
                $wx_info = request()->post('wx_info', '');//微信接口返回的数据集
                $qq_openid = request()->post('qq_openid', '');
                $qq_info = request()->post('qq_info', '');//qq接口返回的数据集
                $unionid = request()->post('unionid', '');
                $this->member->updateUserWchat($user_name, $password, $wx_openid, $wx_info, $unionid);
                $this->member->updateUserQQInfo($user_name, $password, $qq_openid, $qq_info);
                $this->success("登录成功!", ['token' => $token, 'user' => $findUser]);
            }
        } else{
            $this->error(getErrorInfo(USER_ERROR));
        }
    }

//上级代理，在30天保护期外
    public function updatepid($pid,$uid){
        $ShopConfig= new NfxShopConfigModel();
        $Promoter= new NfxPromoterModel();
        $MemberAssociation=new NfxShopMemberAssociationModel();
        $shopinfo=$ShopConfig->find();
        $time=time();
        if($shopinfo['protection']==1){
            if(!empty($pid)){
                $promoterinfo= $Promoter->getInfo(['uid'=>$uid],'*');
                if($promoterinfo['is_yes']==0) {
                        if ($time > $promoterinfo['updatepid_time'] + (30 * 24 * 60)) {
                            $order = new NsOrderModel();
                            $orderinfo = $order->getInfo(['buyer_id' => $uid], "*");
                            Db::startTrans();
                            try {
                                if (empty($orderinfo)) {

                                  $promoter_path= str_replace($promoterinfo['parent_promoter'],$pid,$promoterinfo['promoter_path']);
                                    $Promoter->save(['parent_promoter' => $pid, 'updatepid_time' => $time,'promoter_path'=>$promoter_path], ['uid' => $uid]);
                                    $MemberAssociation->save(['source_uid' => $pid], ['uid' => $uid]);
                                } else {
                                    $Promoter->save(['is_yes' => 1], ['uid' => $uid]);
                                }

                                Db::commit();
                            } catch (\Exception $e) {
                                Db::rollback();
                                return $e->getMessage();
                            }
                        }
                    }
                }
            }

        }

    // 用户退出
    public function logout()
    {
        $userId = $this->getUserId();
        Db::name('sys_user_token')->where([
            'token'       => $this->token,
            'user_id'     => $userId,
            'device_type' => $this->deviceType
        ])->update(['token' => '']);
        $this->success("退出成功!");
    }


    //公众号内的微信登录
    public function wxLogin(){
        $is_bind = 1;
        $info = "";
        $wx_unionid = "";
        $domain_name = \think\Request::instance()->domain();
        $error_url = $domain_name.'/lingshanapp/#!/login/';
        $error_url2 = $domain_name.'/lingshanapp/#!/reg/';
        // 微信浏览器自动登录
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $config = new WebConfig();
            $wchat_config = $config->getInstanceWchatConfig(0);

            if (empty($wchat_config['value']['appid'])) {
                $this->get_error_content("暂不支持此功能",$error_url);
            }
            $wchat_oauth = new WchatOauth();
            $wxtoken = $wchat_oauth->get_member_access_token();

            if (empty($wxtoken['access_token'])) {
                $this->get_error_content("获取微信授权失败",$error_url);
            }
            if(!empty($wxtoken['openid'])){
                if(! empty($token['unionid'])){
                    $wx_unionid = $token['unionid'];
                    $retval = $this->member->wxUnionLogin($wx_unionid);
                }else{
                    $retval = $this->member->wxLogin($wxtoken['openid']);
                }
                    if (is_array($retval)  && !empty($retval['uid'])) {
                        //登录成功后增加用户的登录次数
                        $this->userModel->where("uid", "eq", $retval['uid'])
                            ->setInc('login_num', 1);
                        $userTokenQuery = Db::name("sys_user_token")
                            ->where('user_id', $retval['uid'])
                            ->where('device_type', 'mobile');
                        $findUserToken  = $userTokenQuery->find();
                        $currentTime    = time();
                        $expireTime     = $currentTime + 24 * 3600 * 180;
                        $token          = md5(uniqid()) . md5(uniqid());
                        if (empty($findUserToken)) {
                            $result = $userTokenQuery->insert([
                                'token'       => $token,
                                'user_id'     => $retval['uid'],
                                'expire_time' => $expireTime,
                                'create_time' => $currentTime,
                                'device_type' => 'mobile'
                            ]);
                        } else {
                            $result = $userTokenQuery
                                ->where('user_id', $retval['uid'])
                                ->where('device_type', 'mobile')
                                ->update([
                                    'token'       => $token,
                                    'expire_time' => $expireTime,
                                    'create_time' => $currentTime
                                ]);
                        }
                        //修改客户的父级id ,在保护期外
                        $pid = request()->post('pid', '');
                        if(!empty($pid)){
                            $this-> updatepid($pid,$retval['uid']);
                        }
                        if (empty($result)) {
                            $this->get_error_content("登录失败!",$error_url);
                        }
                        $success_url = $domain_name.'/lingshanapp/#!/my/';
                        $this->get_success_content($token,$success_url,'登录成功！');
                    } elseif ($retval == USER_LOCK) {
                        $this->get_error_content(getErrorInfo(USER_LOCK),$error_url);
                    } else {
                        $this->get_error_content("微信号未绑定用户",$error_url);
                    }
            }else{
                $this->get_error_content("获取微信openid失败",$error_url);
            }
        }else{
            $this->get_error_content("此功能在微信中有效",$error_url);
        }
    }

    /**
     * 第三方登录登录
     */
    public function oauthLogin()
    {
        $type = request()->post('type',1);
        $openid = request()->post('openid', '');
        $unionid = request()->post('unionid', '');
        $device_type = request()->post('device_type','');
        if(empty($openid) && empty($unionid)){
            $this->error('缺少必要参数！');
        }
        $device_type = !empty($data['device_type'])?$data['device_type']:$this->deviceType;
        /*1为微信，2为QQ*/
        if ($type == 1) {
            if(! empty($token['unionid'])){
                $retval = $this->member->wxUnionLogin($unionid);
            }else{
                $retval = $this->member->wxLogin($openid);
            }
            if (is_array($retval)  && !empty($retval['uid'])) {
                //登录成功后增加用户的登录次数
                $this->userModel->where("uid", "eq", $retval['uid'])
                    ->setInc('login_num', 1);
                $userTokenQuery = Db::name("sys_user_token")
                    ->where('user_id', $retval['uid'])
                    ->where('device_type', $device_type);
                $findUserToken  = $userTokenQuery->find();
                $currentTime    = time();
                $expireTime     = $currentTime + 24 * 3600 * 180;
                $token          = md5(uniqid()) . md5(uniqid());
                if (empty($findUserToken)) {
                    $result = $userTokenQuery->insert([
                        'token'       => $token,
                        'user_id'     => $retval['uid'],
                        'expire_time' => $expireTime,
                        'create_time' => $currentTime,
                        'device_type' => $device_type
                    ]);
                } else {
                    $result = $userTokenQuery
                        ->where('user_id', $retval['uid'])
                        ->where('device_type', $device_type)
                        ->update([
                            'token'       => $token,
                            'expire_time' => $expireTime,
                            'create_time' => $currentTime
                        ]);
                }
                //修改客户的父级id ,在保护期外
                $pid = request()->post('pid', '');
                if(!empty($pid)){
                    $this-> updatepid($pid,$retval['uid']);
                }
                if (empty($result)) {
                    $this->error('登录失败！');
                }
                $this->success("登录成功!", ['token' => $token, 'user' => $retval]);
            } elseif ($retval == USER_LOCK) {
                $this->error(getErrorInfo(USER_LOCK));
            } else {
//                $this->error("微信号未绑定用户");
                $this->error(['code' => 20001, 'msg' => '微信号未绑定用户']);
            }
        } elseif ($type == 2){
            $retval = $this->member->qq_Login($openid);
            if (is_array($retval)  && !empty($retval['uid'])) {
                //登录成功后增加用户的登录次数
                $this->userModel->where("uid", "eq", $retval['uid'])
                    ->setInc('login_num', 1);
                $userTokenQuery = Db::name("sys_user_token")
                    ->where('user_id', $retval['uid'])
                    ->where('device_type', $device_type);
                $findUserToken  = $userTokenQuery->find();
                $currentTime    = time();
                $expireTime     = $currentTime + 24 * 3600 * 180;
                $token          = md5(uniqid()) . md5(uniqid());
                if (empty($findUserToken)) {
                    $result = $userTokenQuery->insert([
                        'token'       => $token,
                        'user_id'     => $retval['uid'],
                        'expire_time' => $expireTime,
                        'create_time' => $currentTime,
                        'device_type' => $device_type
                    ]);
                } else {
                    $result = $userTokenQuery
                        ->where('user_id', $retval['uid'])
                        ->where('device_type', $device_type)
                        ->update([
                            'token'       => $token,
                            'expire_time' => $expireTime,
                            'create_time' => $currentTime
                        ]);
                }
                //修改客户的父级id ,在保护期外
                $pid = request()->post('pid', '');
                if(!empty($pid)){
                    $this-> updatepid($pid,$retval['uid']);
                }
                if (empty($result)) {
                    $this->error('登录失败！');
                }
                $this->success("登录成功!", ['token' => $token, 'user' => $retval]);
            } elseif ($retval == USER_LOCK) {
                $this->error(getErrorInfo(USER_LOCK));
            } else {
//                $this->error("QQ号未绑定用户");
                $this->error(['code' => 20001, 'msg' => 'QQ号未绑定用户']);
            }
        }
    }

    //未绑定的三方账号操作
    public function oauthBindLogin(){
        $validate = new Validate([
            'mobile'=> 'require',
            'code'=> 'require',
        ]);
        $validate->message([
            'mobile.require' => '手机号未填写!',
            'code.require' => '手机动态码未填写!',
        ]);
        $data = $this->request->param();
        $data['device_type'] = !empty($data['device_type'])?$data['device_type']:$this->deviceType;
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $info=$this->getCode($data['mobile'],3);
        if($info){
            if ($data['code'] != $info) {
                $this->error(['code'=>-1,'msg'=>"动态验证码不一致"]);
            }else{
                $status = '';
                $uid = '';
                $user_name = $data['mobile'];
                $findUser = $this->userModel->getInfo(['user_tel'=>$data['mobile']], $field = 'uid,user_status,user_name,is_system,instance_id, is_member');
                if(!empty($findUser)){
                    if($findUser['user_status'] == 0){
                        $this->error(getErrorInfo(USER_LOCK));
                    }
                    //登录成功后增加用户的登录次数
                    $this->userModel->where("user_name|user_tel|user_email", "eq", $user_name)
                        ->setInc('login_num', 1);
                    $userTokenQuery = Db::name("sys_user_token")
                        ->where('user_id', $findUser['uid'])
                        ->where('device_type', $data['device_type']);
                    $findUserToken  = $userTokenQuery->find();
                    $currentTime    = time();
                    $expireTime     = $currentTime + 24 * 3600 * 180;
                    $token          = md5(uniqid()) . md5(uniqid());
                    if (empty($findUserToken)) {
                        $result = $userTokenQuery->insert([
                            'token'       => $token,
                            'user_id'     => $findUser['uid'],
                            'expire_time' => $expireTime,
                            'create_time' => $currentTime,
                            'device_type' => $data['device_type']
                        ]);
                    } else {
                        $result = $userTokenQuery
                            ->where('user_id', $findUser['uid'])
                            ->where('device_type', $data['device_type'])
                            ->update([
                                'token'       => $token,
                                'expire_time' => $expireTime,
                                'create_time' => $currentTime
                            ]);
                    }
                    //修改客户的父级id ,在保护期外
                    $pid = request()->post('pid', '');
                    if(!empty($pid)){
                        $this-> updatepid($pid,$findUser['uid']);
                    }
                    if (empty($result)) {
                        $this->error("登录失败!");
                    }
                    $uid = $findUser['uid'];
                    $wx_openid = request()->post('wx_openid', '');
                    $wx_info = request()->post('wx_info', '');//微信接口返回的数据集
                    $qq_openid = request()->post('qq_openid', '');
                    $qq_info = request()->post('qq_info', '');//qq接口返回的数据集
                    $unionid = request()->post('unionid', '');
//                    $this->member->updateUserWxInfo($uid, $wx_openid, $wx_info, $unionid);
//                    $this->member->updateUserQQInfo($uid,$qq_openid, $qq_info);
                    if(!empty($wx_openid) || !empty($unionid)){
                        $this->member->updateUserWxInfo($uid, $wx_openid, $wx_info, $unionid);
                    }elseif(!empty($qq_openid)){
                        $this->member->updateUserQQInfo($uid,$qq_openid, $qq_info);
                    }else{
                        $this->error('未获取绑定信息');
                    }
                    $this->success("登录成功!", ['token' => $token, 'user' => $findUser]);
                }else{
                    $parentuid = 0;
                    $member = new Member();
                    $user = New UserModel();
                    $password = request()->post('password', '');
                    $parentid = request()->post('pid', '');//上级代码
                    $email = request()->post('email', '');
                    $mobile = $user_name;
                    $device_type = $data['device_type'];//设备类型
                    if (empty($password)) {
                        $this->error(['code' => 20001, 'msg' => '该手机未注册过，需填写密码']);
                    }
                    if(!empty($parentid)){
                        $PromoterModel=new NfxPromoterModel();
                        $promoter_info=$PromoterModel->getInfo(['promoter_no'=>$parentid],"uid");
                        if (empty($promoter_info)) {
                            $this->error("推荐码错误！");
                        }
                        $parentuid=$promoter_info['uid'];
                    }
                    $retval_id = $member->registerMemberRegister('', $password, $email, $mobile, '', '', '', '', '', $parentuid);
                    if ($retval_id > 0) {
                        $userTokenQuery = Db::name("sys_user_token");
                        $currentTime = time();
                        $expireTime = $currentTime + 24 * 3600 * 180;
                        $token = md5(uniqid()) . md5(uniqid());
                        $result = $userTokenQuery->insert([
                            'token' => $token,
                            'user_id' => $retval_id,
                            'expire_time' => $expireTime,
                            'create_time' => $currentTime,
                            'device_type' => $device_type
                        ]);
                        if(empty($result)){
                            $this->error('登录失败',['status'=>$result]);
                        }
                        $memberaccount = new NsMemberAccountModel();
                        $memberaccount->save(['uid' => $retval_id, 'shop_id' => 0]);
                        $user->save(['user_tel_bind' => 1], ['uid' => $retval_id]);
                        $wx_openid = request()->post('wx_openid', '');
                        $wx_info = request()->post('wx_info', '');//微信接口返回的数据集
                        $unionid = request()->post('unionid', '');
                        $qq_openid = request()->post('qq_openid', '');
                        $qq_info = request()->post('qq_info', '');//qq接口返回的数据集
                        if(!empty($wx_openid) || !empty($unionid)){
                            $this->member->updateUserWxInfo($retval_id, $wx_openid, $wx_info, $unionid);
                        }elseif(!empty($qq_openid)){
                            $this->member->updateUserQQInfo($retval_id,$qq_openid, $qq_info);
                        }else{
                            $this->error('未获取绑定信息');
                        }
                        $this->success("登录成功!", ['token' => $token, 'user_id' => $retval_id]);
                    } else {
                        $this->error("登录失败!", ['status' => $retval_id]);
                    }
                }

            }
        }else{
            $this->error('动态码已过期！');
        }
    }

    /*
 * 获取动态码
 * */
    public function getCode($phone,$type=1){
        $verify=new VerifyicationModel();
        $info=$verify->getInfo(['account'=>$phone,'type'=>$type]);
        if($info ){
            if($info['expire_time']>time()){
                return $info['code'];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    //获取app版本号
    public function check(){

        $pid = request()->post('vid', '');
        if(empty($pid)){
            $this->error('暂无更新');
        }
        $web_model=new WebSiteModel();
       $info= $web_model->find();
      $ver=$info['app_version'];
      if($ver>$pid){
          $url=$info['path_one'];
          $this->success('有更新',$url);
      }else{

          $this->error('暂无更新');
      }
    }


}