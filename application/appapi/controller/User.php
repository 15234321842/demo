<?php
/**
 * Created by PhpStorm.
 * User: zhumeng
 * Date: 2018/3/12
 * Time: 9:54
 */
namespace app\appapi\controller;

use data\model\MessageModel;
use data\model\NfxPromoterModel;
use data\model\NsMemberLevelModel;
use data\model\UserModel;
use data\model\XianxiaModel;
use data\service\Member;
use data\service\UnifyPay;
use data\service\User as UserService;
use think\Validate;
use think\Session;
use data\service\Config;
use data\service\Member as MemberService;
use data\service\NfxPromoter;
use data\service\Order as OrderService;
use data\service\Member\MemberAccount;
use data\service\Promotion;
use data\model\VerifyicationModel;
use data\service\NfxUser as NfxUserService;
use data\service\NfxRegionAgent;
use data\service\Shop as ShopService;
use data\model\NfxCommissionDistributionModel;
use data\model\NsMemberAccountModel;
use data\model\NfxShopMemberAssociationModel;
use data\service\Config as WebConfig;
use data\extend\WchatOauth;
use data\extend\ThinkOauth as ThinkOauth;
use think\Db;

Class User extends BaseController{

    protected  $uid;
    protected  $shop_id;
    protected $login_verify_code;//$this->shop_id

    public function __construct()
    {
        parent::__construct();
   $this->uid=$this->getUserId();
















        $this->shop_id=0;
        $this->member = new MemberService();
    }

    /*
     * 修改密码
     * */
    public function editPassword(){
        $validate = new Validate([
            'oldpassword'=> 'require',
            'newpassword'=> 'require',
            'password'=> 'require',
        ]);
        $validate->message([
            'oldpassword.require' => '原始密码未填写!',
            'newpassword.require' => '新密码未填写!',
            'password.require' => '确认密码未填写!',
        ]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        if($data['password']!=$data['newpassword']){
            $this->error('确认密码与新密码不一致');
        }else{
            $user=new UserService();
            if($user->ratioPassword($this->uid,$data['oldpassword'])){
                if($user->editPwd($this->uid,$data['newpassword'])){
                    $user->userLogout($this->uid,$this->token,$this->deviceType);
                    $this->success('密码修改成功！请重新登录');
                }else{
                    $this->error('密码修改失败！');
                }
            }else{
                $this->error('原始密码输入错误！');
            }
        }
    }


    /*
     * 获取个人资料
     * get
     * */
    public function getUserInfo(){
        $user=new UserService();
        $param['url'] = \think\Request::instance()->domain().'/';
        $userinfo=$user->getUserInfoByUid($this->uid);
        $param['user_name']=$userinfo['user_name'];
        $param['nick_name']=$userinfo['nick_name'];
        $param['real_name']=$userinfo['real_name'];
        $param['user_headimg']=$userinfo['user_headimg'];
        $param['user_tel']=$userinfo['user_tel'];
        $param['user_tel_bind']=$userinfo['user_tel_bind'];
        $param['user_qq']=$userinfo['user_qq'];
        $param['wx_openid']=$userinfo['wx_openid'];
        $param['wx_info']=$userinfo['wx_info'];
        $param['paypwd']=0;
        if($userinfo['pay_password']){
            $param['paypwd']=1;
        }
        if($userinfo["qq_openid"]){
            $param["bind"]="qq";
        }else if($userinfo["wx_openid"]){
            $param["bind"]="wx";
        }else{
            $param["bind"]="";
        }

        $this->success('获取信息完成',$param);
    }

    /*
     * 修改用户信息
     * post参数：type(nickname,email,avatar,phone),value,code(如果有验证码)
     * */
    public function editUserInfo(){

        $type=request()->param('type');
        if(!$this->request->isPost()){
            $this->error('错误参数');
        }
        switch ($type){
            case 'realname':
                $validate = new Validate([
                    'value'=> 'require',
                ]);
                $validate->message([
                    'value.require' => '请检查您的真实姓名是否填写!',
                ]);
                $data = $this->request->param();
                if (!$validate->check($data)) {
                    $this->error($validate->getError());
                }
                $member=new MemberService();

                $status=$member->updateRealNameByUserid($this->uid,$data['value']);
                if($status){
                    $this->success('修改成功!');
                }else{
                    $this->error('修改失败!');
                }
                break;
            case 'nickname':
                $validate = new Validate([
                    'value'=> 'require',
                ]);
                $validate->message([
                    'value.require' => '请检查您的昵称!',
                ]);
                $data = $this->request->param();
                if (!$validate->check($data)) {
                    $this->error($validate->getError());
                }
                $member=new MemberService();
                $status=$member->updateNickNameByUid($this->uid,$data['value']);
                if($status){
                    $this->success('昵称修改成功!');
                }else{
                    $this->error('昵称修改失败!');
                }
                break;
            case 'qq':
                $validate = new Validate([
                    'value'=> 'number',
                ]);
                $validate->message([
                    'value.number' => '请检查您的qq!',
                ]);
                $data = $this->request->param();
                if (!$validate->check($data)) {
                    $this->error($validate->getError());
                }
                $member=new MemberService();
                $status=$member->updateQQByUid($this->uid,$data['value']);
                if($status){
                    $this->success('QQ设置成功!');
                }else{
                    $this->error('QQ设置失败!');
                }
                break;
            case 'avatar':
                $file = request()->file('value');
                $info = $file->validate(['size'=>1048576,'ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'upload' . DS . 'avatar');
                if($info){
                    $userService=new UserService();
                    $status=$userService->setUserInfo('user_headimg','upload' . DS . 'avatar'.DS.$info->getSaveName(),$this->uid);
                    if($status){
                        $this->success('修改头像成功！');
                    }else{
                        $this->error('修改头像失败！');
                    }
                }else{
                    $this->error($file->getError());
                }
                break;
            case 'phone':
                $validate = new Validate([
                    'value'=> 'require',
                    'code'=> 'require',
                ]);
                $validate->message([
                    'value.require' => '手机号未填写!',
                    'code.require' => '手机动态码未填写!',
                ]);
                $data = $this->request->param();

                if (!$validate->check($data)) {
                    $this->error($validate->getError());
                }
                $userService=new UserService();
                $info=$this->getCode($data['value'],3);
                if($info){
                    if ($data['code'] != $info) {
                        return $result = array(
                            "code" => - 1,
                            "message" => "动态验证码不一致"
                        );
                    }else{
                        $status=$userService->updateUsertelByUserid($this->uid,$data['value']);
                        if($status){
                            $this->success('绑定成功!');
                        }else{
                            $this->error('绑定失败！');
                        }
                    }
                }else{
                    $this->error('动态码已过期！');
                }

                break;
            default:
                $this->error('错误参数！');
                break;
        }
    }

    /*
     * 发送验证码
     * */
    public function sendPhoneSms(){
        $params['mobile'] = request()->post('mobile', '');
        $params['user_id'] =$this->uid;
        $vertification = request()->post('vertification', '');
        $params["shop_id"]=0;
        //$this->success('test',captcha_check($vertification,$this->token));
        if (0==1) {
            //! captcha_check($vertification)
            $this->error('验证码错误！');
        }else if(!$params['mobile']){
            $this->error('手机号未填写！');
        } else {
            //检测手机号码是否重复
            $user=new UserService();
            $phone=$user->getBindPhone($params['mobile']);
            if($phone){
                $this->error('手机号码已被绑定');
            }

            $hook = runhook('Notify', 'bindMobile', $params);
            if (empty($hook)) {
                $this->error('发送失败');
            }else{
                if ($hook["code"] != 0) {
                    $this->error('发送短信太频繁，请稍后再试！');
                }else if ($hook["code"] == 0){
                    $this->setCode($params['mobile'],$hook['param'],3);
                }
            }
            if (! empty($hook) && ! empty($hook['param'])) {
                $this->success('发送成功');
            } else {
                $this->error('发送失败');
            }
        }
    }

    //我的团队

    public function teamlist(){
        $type=$this->request->post('type','all');
        /*$nfx_promoter = new NfxPromoter();*/
      /*  $team_list = $nfx_promoter->getTeamList($this->uid,$type);*/
        $PromoterModel=new NfxPromoterModel();

        //根据获取我的下级人数全部的
      $res=$PromoterModel->getInfo(['uid'=>$this->uid],"promoter_path,promoter_id");
        $condition['promoter_path']=['like',$res['promoter_path'].'-%'];
      //  $team_list= $PromoterModel->where($condition)->select();


        $list=$PromoterModel->alias('pr')
          ->join('nfx_shop_member_association sma', 'sma.promoter_id=pr.promoter_id','left')
            ->join('nfx_promoter_level nprl','nprl.level_id=pr.promoter_level','left')
            ->join('nfx_partner np','np.promoter_id=pr.promoter_id','left')
            ->join('nfx_partner_level npl','npl.level_id=np.partner_level','left')

            ->join('nfx_promoter_region_agent npra','npra.promoter_id=pr.promoter_id','left')
            ->join('nfx_promoter_region_agent_level npral','npral.level_id=npra.level_id','left')
            ->join('sys_user us','pr.uid=us.uid','left')
            ->field('pr.is_yes,pr.promoter_id,pr.uid,pr.regidter_time,sma.id,sma.is_promoter,sma.is_partner,sma.region_center_id,us.nick_name,us.user_tel,us.user_headimg,us.reg_time,nprl.level_name as tgyname,npral.level_id,npl.level_name as gdname,npral.level_name  as dlname,npral.level_num as dlnum')
            ->where($condition)
            ->order('us.reg_time desc')
            ->select();
        //获取下级人员是一部还是2部
        $serviceuser=   new \data\service\User();
        $result= $serviceuser->parentnumber($res['promoter_id']);

        foreach ($list as $k=> &$item){
            $item['reg_time']= date("Y-m-d H:i:s",$item['reg_time']);
            if($item['level_id']){//获取代理的下级代理人数
                $promoterinfo=   $PromoterModel->getInfo(['promoter_id'=>$item['promoter_id']],"*");
                $array['pr.promoter_path']=['like',$promoterinfo['promoter_path'].'-%'];
                $array['ag.level_id']=$item['level_id'];
                $count=$PromoterModel->alias('pr')->join('nfx_promoter_region_agent ag','pr.promoter_id=ag.promoter_id')
                    ->where($array)-> count();
                $item['count']=$count;
            }
            $domain_name = \think\Request::instance()->domain();
            if(empty($item['user_headimg'])){
                $item['user_headimg'] = $domain_name.'/'.$item['user_headimg'];
            }
            if($result['array_one']){
                if(in_array($item['uid'],$result['array_one'])){
                    $item['class_name']='一部';
                }

            }
            if($result['array_two']){
                if(in_array($item['uid'],$result['array_two'])){
                    $item['class_name']='二部';
                }
            }


            if($type!='all'){
                if($item['is_yes']!=$type){
                    unset($list[$k]);
                }
            }
        }

        $this->success("获取成功！", $list);
    }

    //  我的推广二维码

    public function getQrcode(){
        $instance_id = 0;
            /*  if ($instance_id == 0) {
                  $url = __URL(__URL__ . '/wap?source_uid=' . $uid);
              } else {
                  $url = __URL(__URL__ . '/wap/shop/index?shop_id=' . $instance_id . '&source_uid=' . $uid);
              }*/


       // $url = __URL(__URL__ . 'lingshanApp/index1.html#/reg/=' . $this->uid);
      // $url = 'http://192.168.1.166:8020/lingshanApp/index.html#/reg/'. $this->uid;
       //正式部署时需要修改网址
       // $url = 'http://192.168.1.166:8020/lingshanApp/index.html#/reg/'. $this->uid;
        //获取用户的推广码
        $promoter_model=new NfxPromoterModel();
        $promoter_info= $promoter_model->getInfo(['uid'=>$this->uid],"promoter_no");
        $domain_name = \think\Request::instance()->domain();
        //http://127.0.0.1:8020/lingshanApp/html/reg.html?id=11
        //$url = $domain_name.'/lingshanApp/index1.html#/reg/'. $promoter_info['promoter_no'].'/';
       // $url = $domain_name.'/App/html/reg.html?id='. $promoter_info['promoter_no'];
       $url = $domain_name.'/lingshanApp/html/reg.html?id='. $promoter_info['promoter_no'];
        // 查询并生成二维码

        $upload_path = "upload/qrcode/promote_qrcode/shop"; // 后台推广二维码模版
        if (! file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        $path = $upload_path . '/shop_' . $this->uid . '_' . $instance_id . '.png';
        if (! file_exists($path)) {
            getQRcode($url, $upload_path, "shop_" .$this->uid . '_' . $instance_id);
        }

        // 定义中继二维码地址
        $thumb_qrcode = $upload_path . '/thumb_shop_' . 'qrcode_' . $this->uid . '_' . $instance_id . '.png';
        $image = \think\Image::open($path);
        // 生成一个固定大小为360*360的缩略图并保存为thumb_....jpg
        $image->thumb(331, 331, \think\Image::THUMB_CENTER)->save($thumb_qrcode);
        // 背景图片
        $dst = "public/static/images/qrcode_bg/shop_qrcode_bg.png";

        // $dst = "http://pic107.nipic.com/file/20160819/22733065_150621981000_2.jpg";
        // 生成画布
        list ($max_width, $max_height) = getimagesize($dst);
        $dests = imagecreatetruecolor($max_width, $max_height);
        $dst_im = getImgCreateFrom($dst);
        // if (substr($dst, - 3) == 'png') {
        // $dst_im = imagecreatefrompng($dst);
        // } elseif (substr($dst, - 3) == 'jpg') {
        // $dst_im = imagecreatefromjpeg($dst);
        // }
        imagecopy($dests, $dst_im, 0, 0, 0, 0, $max_width, $max_height);
        imagedestroy($dst_im);
        // 并入二维码
        // $src_im = imagecreatefrompng($thumb_qrcode);
        $src_im = getImgCreateFrom($thumb_qrcode);
        $src_info = getimagesize($thumb_qrcode);
        imagecopy($dests, $src_im, "209", "654", 0, 0, $src_info[0], $src_info[1]);
        imagedestroy($src_im);
        // 获取所在店铺信息



        // logo
        /*  if (! strstr($shop_logo, "http://") && ! strstr($shop_logo, "https://")) {
              if (! file_exists($shop_logo)) {
                  $shop_logo = "public/static/images/logo.png";
              }
          }*/
        // if (substr($shop_logo, - 3) == 'png') {
        // $src_im_2 = imagecreatefrompng($shop_logo);
        // } elseif (substr($shop_logo, - 3) == 'jpg') {
        // $src_im_2 = imagecreatefromjpeg($shop_logo);
        // }
        /*  $src_im_2 = getImgCreateFrom($shop_logo);
          $src_info_2 = getimagesize($shop_logo);
          imagecopy($dests, $src_im_2, "10px" * 2, "380px" * 2, 0, 0, $src_info_2[0], $src_info_2[1]);
          imagedestroy($src_im_2);*/
        // 并入用户姓名
        $rgb = hColor2RGB("#333333");
        $bg = imagecolorallocate($dests, $rgb['r'], $rgb['g'], $rgb['b']);
        $name_top_size = "430px" * 2 + "23";
        /*    @imagefttext($dests, 23, 0, "10px" * 2, $name_top_size, $bg, "public/static/font/Microsoft.ttf", "店铺名称：" . $shop_name);
        @imagefttext($dests, 23, 0, "10px" * 2, $name_top_size + 50, $bg, "public/static/font/Microsoft.ttf", "电话号码：" . $shop_phone);
        @imagefttext($dests, 23, 0, "10px" * 2, $name_top_size + 100, $bg, "public/static/font/Microsoft.ttf", "店铺地址：" . $live_store_address);*/
        /* header("Content-type: image/jpeg");
         imagejpeg($dests);*/

        $thumb = $upload_path . '/thumb' . 'qrcode_' . $this->uid . '_' . $instance_id . '.png';
        imagejpeg($dests,$thumb);

        $this->success('二维码',['data'=>'http://'.$_SERVER['HTTP_HOST'].'/'.$thumb]);
    }

    /*
     * 个人中心首页
     * */
    public function user(){
        $user=new UserService();
        $np=new NfxPromoter();
        $userinfo=$user->getUserInfoById($this->uid);
        $user_account=$np->getUserLevel($this->uid);
        if(empty($user_account['name'])){
            $user_account['name']='推广员';
        }

        //获取系统通知
        $message=new MessageModel();
//        $condition['read_member_id']=['not like','%'.$this->uid.'%'];
        $uid=(string)$this->uid;
        $res=$message->where('read_member_id','NOT LIKE','%'.$uid.'%')->count();

        if($res){
            $userinfo['read']=$res;
        }else{
            $userinfo['read']=0;
        }
        $userinfo['level_name'] = '推广员';

        if($user_account['name'] == '省级代理'){
            $userinfo['level4'] = 1;
        }elseif($user_account['name'] == '市级代理'){
            $userinfo['level3'] = 1;
        }elseif($user_account['name'] == '县级代理'){
            $userinfo['level2'] = 1;
        }else{
            $userinfo['level1'] = 1;
        }
//        $userinfo['level_name']=$user_account['name'];
        $userinfo['sign']=$user_account['sign'];//超越标记

        //获取用户可用红包数量
        $couponCount=new Promotion($uid);
        $res=$couponCount->getUserCouponCount($uid);
        $userinfo['couponCount']=$res;
        $this->success('获取完成',$userinfo);
    }
    //获取管理费
    public  function UserAccountexchange(){
        //获取管理费
        $nfx_user = new NfxUserService();
        $user_account = $nfx_user->getNfxUserAccount($this->uid, 0);
        $this->success('获取完成',$user_account);
    }


    /*
     * 获取现金/积分记录
     * */
    public function getAccountLog(){
        $type=request()->param('type');
        $page=request()->post('page/d',1);
        $array=[1,2,3];
        if (empty($type) || !in_array($type, $array)) {
            $this->error('错误参数！');
        }
        $account=new MemberAccount();
        $log=$account->getPageAccountList($page,15,$this->uid,$type);
        $this->success('获取成功',$log);
    }

    /*
     * 获取地址
     * */
    public function getUserAddress(){
        $type=$this->request->param('type','1');
        $id=$this->request->post('id','');
        $member=new Member();
        if($type==1){
            $member_info=$member->getDefaultExpressAddressById($this->uid);
        }else if($type==2){
            if($id){
                $member_info=$member->getMemberExpressAddressListById(1,0,['uid'=>$this->uid,'id'=>$id],'');
            }else{
                $member_info=$member->getMemberExpressAddressListById(1,0,['uid'=>$this->uid],'');
            }

        }else{
            $this->error('错误参数');
        }

        $this->success('获取完成',$member_info);
    }

    /*
     * 删除地址
     * */
    public function delAddress(){
        $member=new Member();
        $id=$this->request->post('id');
        if($id){
            $retval=$member->memberAddressDeleteById($id,$this->uid);
            if($retval>0){
                $this->success('操作成功！');
            }else{
                $this->error('操作失败！',$retval);
            }
        }else{
            $this->error('错误参数！');
        }
    }

    /*
     * 添加，修改地址
     * */
    public function addOrSetAddress(){
        $type=$this->request->post('type');
        $id=request()->post('id/d','0');
        $consigner=request()->post('consigner');
        $mobile=request()->post('mobile','');
        $phone=request()->post('phone');
        $province=request()->post('province/d',0);
        $city=request()->post('city/d',0);
        $district=request()->post('district');
        $address=request()->post('address');
        $zip_code=request()->post('zip_code');
        $alias=request()->post('alias');

        $member=new MemberService;
        if($type=='update'){
            $retval=$member->updateMemberExpressAddressById($this->uid,$id, $consigner, $mobile, $phone, $province, $city, $district, $address, $zip_code, $alias);
        }else if($type=='add'){
            $retval=$member->addMemberExpressAddressById($this->uid,$consigner, $mobile, $phone, $province, $city, $district, $address, $zip_code, $alias);
        }
        if($retval>0){
            $this->success('操作成功！');
        }else{
            $this->error('操作失败！');
        }
    }

    /*
     * 设置默认地址
     * */
    public function DefaultAddress(){
        $member = new MemberService();
        $type=$this->request->post('type','get');
        if($type=='set'){
            $id=$this->request->post('id/d',0);
            $retval=$member->updateAddressDefaultById($id,$this->uid);
            if($retval>0){
                $this->success('操作成功！');
            }else{
                $this->error('操作失败！');
            }
        }else if($type=='get'){
            $retval=$member->getMemberExpressAddressListById(1,0,[ 'uid' => $this->uid,'is_default'=>1],'id desc');
            $this->success('查询完成',$retval);
        }
    }


    /**
     * 删除账户信息
     */
    public function delAccount()
    {
        if (request()->isPost()) {
            $member = new MemberService();
            $account_id = request()->post('id/d', 0);
            $retval = $member->delMemberBankAccountById($account_id,$this->uid);
            if($retval>0){
                $this->success('操作成功！');
            }else{
                $this->error('操作失败！');
            }
        }
    }

    /**
     * 修改账户信息
     */
    public function updateAccount()
    {
        $member = new MemberService();
        if (request()->isPost()) {
            $account_id = request()->post('id', '');
            $realname = request()->post('realname', '');
            $mobile = request()->post('mobile', '');
            $account_type = request()->post('account_type', '1');
            $account_type_name = request()->post('account_type_name', '银行卡');
            $account_number = request()->post('account_number', '');
            $branch_bank_name = request()->post('branch_bank_name', '');
            $retval = $member->updateMemberBankAccountById($this->uid,$account_id, $account_type, $account_type_name, $branch_bank_name, $realname, $account_number, $mobile);
            if($retval>0){
                $this->success('操作成功！');
            }else{
                $this->error('操作失败！');
            }
        }
    }

    /*
     * 获取，设置默认提现账号
     * */
    public function defaultAccount(){
        $member = new MemberService();
        $type=$this->request->post('type');
        $id=$this->request->post('id','');
        if($type=='get'){
            $retval=$member->getDefauteMemberBankAccountDetailById($this->uid);
        }else if($type=='set'){
            $retval=$member->setMemberBankAccountDefault($this->uid,$id);
        }
        $this->success('操作完成',$retval);
    }

    /*
     * get提现账号列表,post添加提现账号
     * */
    public function UserAccount(){
        $member = new MemberService();
        $type=$this->request->post('type');
        if($type=='add'){
            $id=$this->request->post('id','');
            $realname = request()->post('realname', '');
            $mobile = request()->post('mobile', '');
            $account_type = request()->post('account_type', '1');
            $account_type_name = request()->post('account_type_name', '银行卡');
            $account_number = request()->post('account_number', '');
            $branch_bank_name = request()->post('branch_bank_name', '');
            if($id){
                $retval=$member->updateMemberBankAccountById($this->uid,$id, $account_type, $account_type_name, $branch_bank_name, $realname, $account_number, $mobile);
            }else{
                $retval = $member->addMemberBankAccount($this->uid, $account_type, $account_type_name, $branch_bank_name, $realname, $account_number, $mobile);
            }
            if($retval>0){
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else if($type=='get'){
            $id=$this->request->post('id','');
            if($id){
                $info=$member->getMemberBankAccountDetailById($id,$this->uid);
            }else{
                $info = $member->getMemberBankAccountById(0,$this->uid);
            }
            $this->success('获取完成',$info);
        }
    }

    //我的收益转积分
    public function addAccountexchange(){
        $money=$this->request->post('money','');
        if(empty($money)){
            $this->error('请填写收益');
        }
        $nfx_user = new NfxUserService();
        $user_account = $nfx_user->getNfxUserAccount($this->uid, 0);
        if($money>$user_account['commission_cash']){
            $this->error('请确认填写是否正确！');
        }

        $nfx_user = new NfxUserService();
        $withdraw_no = $nfx_user->createWidthdrawNo(0);

        DB::startTrans();
        try{
            $nfx_user->addNfxUserAccountRecords($this->uid, 0, - $money, 10, 10, 1, 1, "收益转积分", $withdraw_no);//减少收益金额
            $member_account = new MemberService\MemberAccount();
            $res= $member_account->addMemberAccountData(0, 1, $this->uid, 1, $money, 35, 0, '收益转积分');//添加积分

        Db::commit();
            $this->success('成功！');
        } catch (\Exception $e) {
            Db::rollback();

            return   $this->error($e->getMessage());
        }







    }

    /*
     * 积分,现金日志
     * */
    public function getBalanceLog(){
        $shop_id = 0;
        $condition['nmar.shop_id'] = $shop_id;
        $condition['nmar.uid'] =$this->uid;
        $type=$this->request->param('type');
        $page=$this->request->param('page',1);
        switch ($type){
            case 1:
                $condition['nmar.account_type'] = 1;
                break;
            case 2:
                $condition['nmar.account_type'] = 2;
                break;
            default:
                $this->error('错误参数');
        }

        $member = new MemberService();
        // 查看用户在该商铺下的积分消费流水
        $param['info']=$member->getBalance($this->uid);
        $field='nmar.id,nmar.data_id,nmar.sign, nmar.number,nmar.text, nmar.create_time,nmar.from_type';
        $param['list'] = $member->getAccountList($page, 10, $condition,'',$field);
        $this->success('获取完成！',$param);
    }

    /*
     * 现金提现记录
     * */
    public function balancewithdraw(){
        $member = new MemberService();
        $shopid =0;
        $condition['uid'] = $this->uid;
        $condition['shop_id'] = $shopid;
        $page=$this->request->param('page',1);
        /* $condition['status'] = 1; */
        $withdraw_list = $member->getMemberBalanceWithdraw($page, 20, $condition,'ask_for_date desc');
        foreach ($withdraw_list['data'] as $k => $v) {
            if ($v['status'] == 1) {
                $withdraw_list['data'][$k]['status'] = '已同意';
            } else{
                if ($v['status'] == 0) {
                    $withdraw_list['data'][$k]['status'] = '已申请';
                } else {
                    $withdraw_list['data'][$k]['status'] = '已拒绝';
                }
            }
            $withdraw_list['data'][$k]['account_number']=substr($withdraw_list['data'][$k]['account_number'],-4);
        }
        $this->success('获取完成',$withdraw_list);
    }

    public function getTypeConfig(){
        $config = new Config();
        $data['withdraw_info'] = $config->getBalanceWithdrawConfig(0);
        $promotion=new Promotion;
        $data['promotioninfo']=$promotion->getPointConfig();
        return $data;
    }


    /*
     * 现金提现
     * */
    public function toWithdraw(){
        $type=$this->request->post('type',1);
        if($type==1){

            // 得到本店的提线设置
            $data=$this->getTypeConfig();
            $members = new MemberAccount();
            $param['balance'] = $members->getMemberBalance($this->uid);
            $param['withdraw_cash_min']=$data['withdraw_info']['value']["withdraw_cash_min"];
            $param['withdraw_multiple']= $data['withdraw_info']['value']["withdraw_multiple"];
            //$param['tax']=$data['promotioninfo']['tax']/100;
            $param['poundage']=$data['promotioninfo']['poundage'];
            $this->success('获取完成',$param);
        }else if($type==2){
            $member = new MemberService();
            $promotion=new Promotion;
            $promotioninfo=$promotion->getPointConfig();
            $withdraw_no = time() . rand(111, 999);
            $bank_account_id = request()->post('bank_account_id',0);
            $cash = request()->post('cash', 0);
            if(!$bank_account_id){
                $this->error('请填写银行账户');
            }
            if(!$cash){
                $this->error('请填写金额');
            }

            $shop_id = 0;

            $retval = $member->addMemberBalanceWithdraw($shop_id, $withdraw_no, $this->uid, $bank_account_id, $cash);
            if($retval>0){
                $this->success('申请提现成功！');
            }else{
                $this->error('申请提现失败！');
            }
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

    /*
     * 写入验证码
     * */
    public function setCode($phone,$result,$type=''){
        $Verifyication=new VerifyicationModel();
        $info=$Verifyication->getInfo(['account'=>$phone]);
        $time=time();
        $where['account']=$phone;
        if(!empty($type)){
            $where['type']=$type;

        }
        if($info){
            $Verifyication->save(['send_time'=>$time,'expire_time'=>$time+120,'code'=>$result,'count'=>$info['count']+1,'account'=>$phone],$where);
        }else{
            $Verifyication->save(['send_time'=>$time,'expire_time'=>$time+120,'code'=>$result,'count'=>1,'type'=>3,'account'=>$phone]);
        }
    }

    public function getCointWithdrawInfo(){
        $data=$this->getTypeConfig();

        $nfx_user = new NfxUserService();
        $user_account = $nfx_user->getNfxUserAccount($this->uid, 0);
        $param['commission_cash']=$user_account['commission_cash'];
        $param['commission']=$user_account['commission'];
        $param['withdraw_cash_min']=$data['withdraw_info']['value']["withdraw_cash_min"];
        $param['withdraw_multiple']= $data['withdraw_info']['value']["withdraw_multiple"];
        $param['tax']=$data['promotioninfo']['tax'];
        $param['poundage']=$data['promotioninfo']['poundage'];
        $this->success('获取完成！',$param);
    }


    //提交管理费提现
    public function addCointowithdraw(){

        $nfx_user = new NfxUserService();
        $config_service = new Config();
        $withdraw_info = $config_service->getBalanceWithdrawConfig($this->shop_id);
        $withdraw_no = 789;
        $bank_account_id = $this->request->param('bank_account_id/d','0');
        $cash = $this->request->param('cash/d','0');

        $shop_id =  $this->shop_id;
        if(empty($bank_account_id)){
            $this->error('请填写银行账户');
        }
        if(empty($cash)){
            $this->error('请填写金额');
        }
        if($withdraw_info['value']['withdraw_cash_min'] > $cash){
            $this->error('最低体现金额为'.$withdraw_info['value']['withdraw_cash_min']);


        }
        if($cash % $withdraw_info['value']['withdraw_multiple']!=0){

            $this->error('体现金额为'.$withdraw_info['value']['withdraw_cash_min'].'的倍数！');
        }
        $retval = $nfx_user->addNfxCommissionWithdraw($shop_id, $withdraw_no,   $this->uid, $bank_account_id, $cash);
        if($retval){
            $this->success('提交成功',$retval);

        }else{
            $this->error('保存错误，请稍后再试！',$retval);
        }

    }


    //区域代理页面信息

    public function applyRegionalAgentinfo(){
        $nfx_region_agent = new NfxRegionAgent();

        $region_config = $nfx_region_agent->getShopRegionAgentConfig($this->shop_id);
        // var_dump($region_config);
        if (empty($region_config)) {
            $this->error("当前店铺未设置区域代理");
        }
        $region_agent_info = $nfx_region_agent->getPromoterRegionAgentValidDetail($this->shop_id, $this->uid);
        $agent_type = empty($region_agent_info) ? '-1' : $region_agent_info['is_audit'];
        /*  $this->assign('agent_type', $agent_type);*/
        $shop = new ShopService();
        $shop_user_account = $shop->getShopUserConsume($this->shop_id, $this->uid);
        /* $this->assign("shop_sale_money", $shop_user_account);
         $this->assign("region_config", $region_config);*/
        $data['agent_type']=$agent_type;
        $data['shop_sale_money']=$shop_user_account;
        $data['region_config']=$region_config;
        $this->success('代理页面信息',$data);
    }

    public function applyRegionalAgent()
    {
        $nfx_region_agent = new NfxRegionAgent();

        $shop_id = 0;
        $agent_type = isset($_POST['agent_type']) ? $_POST['agent_type'] : '';
        $real_name = isset($_POST['real_name']) ? $_POST['real_name'] : '';
        $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';
        $address = isset($_POST['address']) ? $_POST['address'] : '';

        if(empty($agent_type)){

            $this->error('请传入正确的参数！');
        }
        if(empty($mobile)){

            $this->error('请输入手机号！');
        }
        if(empty($real_name)){

            $this->error('请输入真实姓名！');
        }
        if(empty($address)){

            $this->error('请输入地址！');
        }

        $retval = $nfx_region_agent->PromoterRegionAgentApplay($shop_id, $this->uid, $agent_type, $real_name, $mobile, $address);
        if($retval){
            $this->success('提交成功！',$retval);
        }else{
            $this->error('提交失败！',$retval);
        }

    }

    //检测密码是否正确
    public function checkPayPassword(){
        $paypwd=$this->request->post('paypassword/d',0);
        if(!$paypwd){
            $this->error('支付密码不能为空');
        }else{
            $user=new UserService();
            $status=$user->ratioPayPassword($this->uid,$paypwd);
            if($status){
                $this->success('验证通过');
            }else {
                $this->error('支付密码错误');
            }
        }
    }

    //修改支付密码
    public function editPayPassword(){
        $validate = new Validate([
            'oldpaypassword'=> 'require',
            'newpaypassword'=> 'require',
        ]);
        $validate->message([
            'oldpaypassword.require' => '原始密码未填写!',
            'newpaypassword.require' => '新密码未填写!',
        ]);
        $data = $this->request->param();

        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $user=new UserService();
        if($user->ratioPayPassword($this->uid,$data['oldpaypassword'])){
            if($user->editPayPwd($this->uid,$data['newpaypassword'])){

                $this->success('密码修改成功！');
            }else{
                $this->error('密码修改失败！');
            }
        }else{
            $this->error('原始密码输入错误！');
        }
    }

    //添加支付密码
    public function addPayPassword(){
        $validate = new Validate([

            'newpaypassword'=> 'require',
        ]);
        $validate->message([
            'newpaypassword.require' => '支付密码未填写!',
        ]);
        $data = $this->request->param();

        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $user=new UserService();
        if($user->editPayPwd($this->uid,$data['newpaypassword'])){

            $this->success('支付密码添加成功！');

        }else{
            $this->error('添加支付密码错误，请稍后再试！');
        }
    }

    /*
     * 获取提现详情
     * */
    public function getWithdrawLog(){

        $id=$this->request->post('id/d',0);
        $type=$this->request->post('type','');
        if(!$type||!$id){$this->error('参数不正确！');}
        $member=new Member();
        $info=$member->getWithdrawLog($this->uid,$id,$type);
        $this->success('获取完成！',$info);
    }

    /*
     * 获取团队个人订单
     * */
    public function getTeamOrderInfo(){
        $id=$this->request->post('id/d',0);
        $user=new UserService();
        $userinfo=$user->getTeamUserInfoBypath($id);
        $promoter_model=new NfxPromoterModel();
       $res=$promoter_model->getInfo(['promoter_id'=>$id],'promoter_no');

        if($userinfo){
            $userinfo['promoter_no']=$res['promoter_no'];
            $this->success('获取完成',$userinfo);
        }else{
            $this->error('无权查看此用户信息');
        }

    }

    /*
     * 获取团队个人订单列表
     * */
    public function getTeamOrderList(){
        $status=$this->request->post('status','all');
        $id=$this->request->post('id/d','0');
        $page=$this->request->param('page/d',1);
        $size=$this->request->param('size/d',10);
        $user=new UserService();
        $data=$user->getTeamOrderList($this->uid,$id,$status,$page,$size);
        if($data['code']==1){
            $this->success('获取完成',$data['data']);
        }else{
            $this->error('无记录');
        }
    }

    //管理费订单明细
    public function userAccountRecordsOrderDetail(){

        $type_alis_id = request()->post('type_alis_id', '');
        $type=request()->post('type', '');
        if(empty($type)){
            $this->error('没有找到订单！');
        }

        if(empty($type_alis_id)){
            $this->error('没有找到订单！');
        }
        if($type==1){
            $info=   $this->Agentcommission($type_alis_id);//一部
        }
        if($type==2){
            $info=   $this->Agentcommission($type_alis_id);//二部
        }
        if($type==3){
            $info=   $this->Mamagercommission($type_alis_id);//代理商
        }

        //$info=   $this->Partnercommission($type_alis_id);//合伙人
        $this->success('管理费订单明细',$info);
    }
    public function Partnercommission($type_alis_id){ //合伙人

        $condition['co.type_alis_id']=$type_alis_id;
        $CommissionDistribution= new  NfxCommissionDistributionModel();
        $info= $CommissionDistribution->CommissionPartnerView( $condition);
        if($info){
            $info['url']='http://'.$_SERVER['HTTP_HOST'].'/';
            $info['create_time']= date("Y-m-d H:i:s",$info['create_time']);
            $info['finish_time']= date("Y-m-d H:i:s",$info['finish_time']);
            $info['goods_commission']=$info['goods_money']*$info['goods_commission_rate']/100;
            if($info['is_issue']==1){
                $info['is_issue']='是';
            }else{
                $info['is_issue']='否';
            }
            return $info;
        }


    }


    public function Agentcommission($type_alis_id){ //推广员

        $condition['co.type_alis_id']=$type_alis_id;
        $CommissionDistribution= new  NfxCommissionDistributionModel();
        $info= $CommissionDistribution->CommissionAgentView( $condition);
        if($info){
            $info['url']='http://'.$_SERVER['HTTP_HOST'].'/';
            $info['create_time']= date("Y-m-d H:i:s",$info['create_time']);
            $info['finish_time']= date("Y-m-d H:i:s",$info['finish_time']);
            $info['goods_commission']=$info['goods_money']*$info['goods_commission_rate']/100;
            if($info['is_issue']==1){
                $info['is_issue']='是';
            }else{
                $info['is_issue']='否';
            }
            return $info;
        }


    }

    //代理商管理费明细
public function Mamagercommission($type_alis_id){

    $condition['co.type_alis_id']=$type_alis_id;
    $CommissionDistribution= new  NfxCommissionDistributionModel();
    $info= $CommissionDistribution->CommissionDistributionView( $condition);
    if($info){
        $info['url']='http://'.$_SERVER['HTTP_HOST'].'/';
        $info['create_time']= date("Y-m-d H:i:s",$info['create_time']);
        $info['finish_time']= date("Y-m-d H:i:s",$info['finish_time']);
        $info['goods_commission']=$info['goods_money']*$info['goods_commission_rate']/100;
        if($info['is_issue']==1){
            $info['is_issue']='是';
        }else{
            $info['is_issue']='否';
        }
      return $info;
    }

}

    //获取现金/积分变动详情
    public function getBalanceDetail(){
        $id=$this->request->post('id',0);
        $member=new MemberService;
        $info=$member->getBalanceDetail($this->uid,$id);
        $this->success('获取完成',$info);
    }

    //积分转账
    public function transferAccounts(){
        $type=$this->request->post('type','get');
        if($type=='get'){
            $account=new NsMemberAccountModel();
            $info=$account->getInfo(['uid'=>$this->uid],'balance,point');
            $user=new UserModel();
            $pay_password=$user->getInfo(['uid'=>$this->uid],'pay_password');
            $info['is_pay_pwd']=false;
            if(trim($pay_password['pay_password'])){
                $info['is_pay_pwd']=true;
            }
            $this->success('获取完成',$info);
        }else if($type=='tran'){
            $validate = new Validate([
                'num'=>'require',
                'realname'=> 'require',
                'account'=>'require',
                'password'=>'require'
            ]);
            $validate->message([
                'num.require' => '积分数量未填写!',
                'realname.require' => '真实姓名未填写!',
                'account.require' => '账号未填写!',
                'password.require' => '支付密码未填写!',
            ]);
            $data = $this->request->param();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $user=new UserService();
            $status=$user->ratioPayPassword($this->uid,$data['password']);
            if(!$status){
                $this->error('支付密码错误');
            }
            $userinfo=$user->checkAccountName($data['account'],$data['realname']);
            if($userinfo['uid']==$this->uid){
                $this->error('不能转账给自己！');
            }
            if($userinfo){
                $member=new MemberAccount();
                if($member->transferAccounts($userinfo,$this->uid,$data['num'])){
                    $this->success('转账成功');
                }else{
                    $this->error('转账失败');
                }
            }else{
                $this->error('用户信息不匹配！');
            }
        }
    }

    public function exchange(){
        $validate = new Validate([
            'num'=>'require',
            'password'=>'require'
        ]);
        $validate->message([
            'num.require' => '积分数量未填写!',
            'password.require' => '支付密码未填写!',
        ]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $user=new UserService();
        $status=$user->ratioPayPassword($this->uid,$data['password']);
        if(!$status){
            $this->error('支付密码错误');
        }
        $member=new MemberAccount();
        if($member->exchange($this->uid,$data['num'])){
            $this->success('兑换成功');
        }else{
            $this->error('兑换失败');
        }
    }

    //在公众号内绑定微信操作
    public function bindwx(){
        $token = "";
        $is_bind = 1;
        $info = "";
        $wx_unionid = "";
        $domain_name = \think\Request::instance()->domain();
        $url = $domain_name.'/lingshanapp/#!/userinfo/';
        // 微信浏览器自动登录
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $config = new WebConfig();
            $wchat_config = $config->getInstanceWchatConfig(0);

            if (empty($wchat_config['value']['appid'])) {
                $this->get_error_content("暂不支持此功能",$url);
            }

            $wchat_oauth = new WchatOauth();
            $token = $wchat_oauth->get_member_access_token();

            if (empty($token['access_token'])) {
                $this->get_error_content("获取微信授权失败",$url);
            }

            if (! empty($token['openid'])) {
                if (! empty($token['unionid'])) {
                    $wx_unionid = $token['unionid'];
                    $retval = $this->member->wxUnionLogin($wx_unionid);
                    if(is_array($retval)  && !empty($retval['uid'])){
                        $this->get_error_content("该微信已绑定!",$url);
                    }else{
//                        $info = $wchat_oauth->get_oauth_member_info($token);
//                        $bind_message = array(
//                            "token" => $token,
//                            "is_bind" => $is_bind,
//                            "info" => $info,
//                            "wx_unionid" => $wx_unionid
//                        );
//                        $this->wchatBindMember($this->userId,$bind_message);
                        $bind_message = array(
                            "is_bind" => $is_bind,
                            "wx_openid" => $token['openid'],
                            "wx_unionid" => $wx_unionid
                        );
                        $user=new UserModel();
                        $user ->save($bind_message,['uid'=>$this->userId]);
                        $this->get_success_content($this->token,$url,'绑定成功！');
                    }
                } else {
                    $wx_unionid = '';
                    $retval = $this->member->wxLogin($token['openid']);
                    if(is_array($retval)  && !empty($retval['uid'])){
                        $this->get_error_content("该微信已绑定!",$url);
                    }else{
//                        $info = $wchat_oauth->get_oauth_member_info($token);
//                        $bind_message = array(
//                            "token" => $token,
//                            "is_bind" => $is_bind,
//                            "info" => $info,
//                            "wx_unionid" => $wx_unionid
//                        );
//                        $this->wchatBindMember($this->userId,$bind_message);
                        $bind_message = array(
                            "is_bind" => $is_bind,
                            "wx_openid" => $token['openid'],
                            "wx_unionid" => $wx_unionid
                        );
                        $user=new UserModel();
                        $user ->save($bind_message,['uid'=>$this->userId]);
                        $this->get_success_content($this->token,$url,'绑定成功！');
                    }
                }
            }
        }else{
            $this->get_error_content("此功能在微信中有效",$url);
        }
    }

    /**
     * 微信绑定用户
     */
    public function wchatBindMember($uid, $bind_message_info)
    {
        if (! empty($bind_message_info)) {
            $token = $bind_message_info["token"];
            if (! empty($token['openid'])) {
                $this->member->updateUserWxInfo($uid, $token['openid'], $bind_message_info['info'], $bind_message_info['wx_unionid']);
            }
        }
    }

    /**
     * 解除wx绑定
     */
    public function removeBindWx()
    {
        $retval = $this->member->removeBindWx();
        if($retval){
            $this->success('解除绑定成功');
        }else{
            $this->error('解除绑定失败');
        }
    }

    /**
     * 解除QQ绑定
     */
    public function removeBindQQ()
    {
        $retval = $this->member->removeBindQQ();
        if($retval){
            $this->success('解除绑定成功');
        }else{
            $this->error('解除绑定失败');
        }
    }


    //上传大额转账记录

    public function addbanktransfer(){

        $bank_transfer=new XianxiaModel();
        $transaction_id=$this->request->post('transaction_id','');
        $path=$this->request->post('path','');
        $num=$this->request->post('num','');

        $uid=$this->uid;
        if(empty($transaction_id)){
            $this->error('参数错误！');
        }
        if(empty($path)){
            $this->error('参数错误！');
        }
        if(empty($num)){
            $this->error('参数错误！');
        }

        $data['transaction_id']=$transaction_id;
        $data['path']=$path;
        $data['create_time']=time();
        $data['status']=0;
        $data['uid']=$uid;
        $data['type']=2;
        $data['num']=$num;

        $res=$bank_transfer->save($data);
        if($res){
            $this->success('提交成功，正在处理！');
        }else{
            $this->error('提交错误，请稍后再试！');

        }


    }

    public function pay_no()
    {
        $pay = new UnifyPay();
        $pay_no = $pay->createOutTradeNo();
      return $pay_no;
    }

    /**
     * 创建充值订单
     */
    public function createRechargeOrder()
    {
        $recharge_money = request()->post('recharge_money', '');
        $out_trade_no = $this->pay_no();
        if (empty($recharge_money) || empty($out_trade_no)) {
           $this->error('参数有误！');
        } else {
            $member = new MemberService();
            $retval = $member->createMemberRecharge($recharge_money, $this->uid, $out_trade_no);
            if($retval){
                $this->success('创建充值订单',$out_trade_no);
            }else{

                $this->error('创建充值订单有误！');
            }

        }
    }


    /*
     * 获取是否存在支付密码
     * */
    public function getpaypwd(){
        $user=new UserModel();
        $info=$user->getInfo(['uid'=>$this->uid],'pay_password');
        if(trim($info['pay_password'])){
            $this->success('获取完成',true);
        }else{
            $this->success('获取完成',false);
        }
    }

    /*
   系统通知
   * */
    public function noticelist(){
        $user=new UserModel();
        $member=new MessageModel();
        $uid=(string)$this->uid;
       $res= $member->where('del_member_id','NOT LIKE','%'.$uid.'%')->order('message_time desc')->select();
       foreach ($res as $v)
       {
           $v['message_time']=date("Y-m-d H:i:s",$v['message_time']) ;
           if(stristr($v['read_member_id'],(string)$this->uid)){
               $v['read']=0;
           }else{
               $v['read']=1;
           }
       }
        if($res){
            $this->success('获取完成',$res);
        }else{
            $this->success('获取完成',$res);
        }
    }
//系统公告详情
    public function noticeinfo(){
        $user=new UserModel();
        $message_id= request()->post('message_id', '');
        if(empty($message_id)){
            $this->error('参数错误！');
        }
        $Message=new MessageModel();
        $condition['message_id']=$message_id;
        $res= $Message->where($condition)->find();
        if(!stristr($res['read_member_id'],(string)$this->uid)){
            if($res['read_member_id']){
                $data['read_member_id']=$res['read_member_id'].','.$this->uid;
            }else{
                $data['read_member_id']=$this->uid;
            }
            $Message->save($data,['message_id'=>$message_id]);
        }
        foreach ($res as $v)
        {
            $v['message_time']=date("Y-m-d H:i:s",$v['message_time']) ;
        }
        if($res){
            $this->success('获取完成',$res);
        }else{
            $this->success('获取完成',$res);
        }
    }

    //标为已读
    public function read(){

        $message_id= request()->post('message_id', '');
        if(empty($message_id)){
            $this->error('参数错误！');
        }

        $Message=new MessageModel();
        $condition['message_id']=$message_id;
        $res= $Message->where($condition)->find();
        if(!stristr($res['read_member_id'],(string)$this->uid)){
            if($res['read_member_id']){
                $data['read_member_id']=$res['read_member_id'].','.$this->uid;
            }else{
                $data['read_member_id']=$this->uid;
            }
            $Message->save($data,['message_id'=>$message_id]);
        }
        if($res){
            $this->success('操作成功！',$res);
        }else{
            $this->success('操作错误，请稍后再试',$res);
        }

    }

    public function delnoticeinfo(){
        $message_id= request()->post('message_id', '');
        if(empty($message_id)){
            $this->error('参数错误！');
        }
        $Message=new MessageModel();
        $condition['message_id']=$message_id;
        $res= $Message->where($condition)->find();
        if(!stristr($res['del_member_id'],(string)$this->uid)){
            if($res['del_member_id']){
                $data['del_member_id']=$res['del_member_id'].','.$this->uid;
            }else{
                $data['del_member_id']=$this->uid;
            }
          $re=  $Message->save($data,['message_id'=>$message_id]);
            if($re){
                $this->success('删除成功',$re);
            }else{
                $this->error('删除错误，请稍后再试',$re);
            }
        }else{
            $this->success('删除成功',1);

        }

    }




}