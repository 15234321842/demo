<?php
/**
 * Member.php
 * shop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 河南比谷网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: http://www.easybigu.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * @author : hujinteam
 * @date : 2015.1.17
 * @version : v1.0.0.0
 */
namespace app\admin\controller;

use data\model\NfxPromoterModel;
use data\model\NfxPromoterRegionAgentLevelModel;
use data\model\NfxPromoterRegionAgentModel;
use data\model\NfxShopMemberAssociationModel;
use data\model\NsOrderModel;
use data\model\NsOrderPaymentModel;
use data\model\UserModel;
use data\model\XianxiaModel;
use data\service\Member as MemberService;
use data\service\NfxRegionAgent;
use data\service\User;
use data\service\Weixin;
use data\service\Supplier;
use data\service\Config as WebConfig;
use think\Db;
/**
 * 会员管理
 *
 * @author Administrator
 *        
 */
class Member extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 会员列表主页
     *
     * @return Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function memberList()
    {
        $member = new MemberService();
        if (request()->isAjax()) {
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $search_text = request()->post('search_text', '');
            $level_id = request()->post('levelid', - 1);
            $condition = [
                'su.is_member' => 1,
                'su.nick_name|su.user_tel|su.user_email' => array(
                    'like',
                    '%' . $search_text . '%'
                ),
                
                'su.is_system' => 0
            ];
            if ($level_id != - 1) {
                $condition['nml.level_id'] = $level_id;
            }
            $list = $member->getMemberList($page_index, $page_size, $condition, 'su.reg_time desc');
            
            return $list;
        } else {
            // 查询会员等级
            $list = $member->getMemberLevelList(1, 0);
            $this->assign('level_list', $list);
            return view($this->style . 'Member/fx_memberList');
        }
    }

    //大额转账记录

    public function bank_transfer(){

        $member = new MemberService();
        if (request()->isAjax()) {

            $page_index = request()->post("page_index", 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $search_text = request()->post('search_text', '');
            $level_id = request()->post('levelid','');
           /* if(!empty($level_id)){
                $condition['xx.type']=$level_id;
            }*/
            if($level_id!='all'){
                $condition['xx.type']=$level_id;
            }
            if(!empty($search_text)) {

                $condition['su.nick_name|su.user_tel|su.user_email']=['like','%' . $search_text . '%'];
            }

            $list = $member->getbanktransferList($page_index, $page_size, $condition, 'xx.xx_id desc');

            return $list;
        } else {
            /*// 查询会员等级
            $list = $member->getMemberLevelList(1, 0);
            $this->assign('level_list', $list);*/
            return view($this->style . 'Member/xx_bank_transfer');
        }


    }

    //确认转账到账
    public function verifymoney(){

        $Xianxia= new XianxiaModel();
        $member = new MemberService();
        $promoter_region_rate=new NfxPromoterRegionAgentLevelModel();

        $xx_id = request()->post("xx_id", '');
         $sort = request()->post("sort", '');
        if(empty($sort)){
            $this->error('请选择操作类别！');
        }
        if(empty($xx_id)){
            $this->error('参数错误，请稍后再试！');
        }
        $res= $Xianxia->getInfo(['xx_id'=>$xx_id],"*");
        if(empty($res)){
            $this->error('参数错误，请稍后再试！');
        }
        if($res['status']==1){
            $this->error('请勿多次操作！');
        }

        if($sort==1){
            //添加金额到现金
            $member_account = new MemberService\MemberAccount();
       //  $res= $member->addMemberAccount(0, 2, $res['uid'], $res['num'], "充值");
         $resu= $member_account->addMemberAccountData(0, 2, $res['uid'], 1, $res['num'], 45, 0, "线下现金充值");
          if($resu<=0){
              $this->error('操作错误！');
          }
//1线下转账，2代理商，3服务订单线下支付4,充值
            $result=$Xianxia->save(['status'=>1,'type'=>4],['xx_id'=>$xx_id]);
            //修改支付订单
            $OrderPayment=new NsOrderPaymentModel();
            $OrderPayment->save(['pay_status'=>1,'pay_type'=>45,'pay_time'=>time()],['out_trade_no'=>$res['out_trade_no']]);

        }
        if($sort==2){
            //添加积分并授予代理商权限
            $result=$promoter_region_rate->order('level_id ASC')->find();
          $num=  $this->parentnumber($res['uid']);
            if($result['level_num']>$num){
                $this->error('代理人数不符合要求！');
            }
            if($result['level_money']>$res['num']){
                $this->error('代理金额不符合要求！');
            }
          $res1=  $this->addresult($xx_id,$res['num']);//设置代理
            if($res1['code']<=0){
                $this->error('操作错误！');
            }
            $result=$Xianxia->save(['status'=>2],['xx_id'=>$xx_id]);
            //修改支付订单
            $OrderPayment=new NsOrderPaymentModel();
            $OrderPayment->save(['pay_status'=>1,'pay_type'=>30,'pay_time'=>time()],['out_trade_no'=>$res['out_trade_no']]);


        }

        if($result){

            $this->success('操作成功！');
        }else{

            $this->error('操作错误，请稍后再试！');
        }

    }

    public function paymentList(){
  if (request()->isAjax()) {
            $member = new MemberService();
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $search_text = request()->post('search_text', '');
            $pay_type = request()->post('pay_type','');
            $start_date = request()->post('start_date') == "" ? 0 : request()->post('start_date');
            $end_date = request()->post('end_date') == "" ? 0 : request()->post('end_date');
            $condition['pay_type'] = ['in','1,2'];
            $condition['pay_status'] =1;
            if(!empty($pay_type)){
                $condition['pay_type'] = intval($pay_type)- 1;
            }
//            if(!empty($search_text)){
//                $condition['su.nick_name'] = [
//                    'like',
//                    '%' . $search_text . '%'
//                ];
//            }
            if($start_date != 0 && $end_date != 0){
                $condition["pay_time"] = [
                    [
                        ">",
                        getTimeTurnTimeStamp($start_date)
                    ],
                    [
                        "<",
                        getTimeTurnTimeStamp($end_date)
                    ]
                ];
            }elseif($start_date != 0 && $end_date == 0){
                $condition["pay_time"] = [
                    [
                        ">",
                        getTimeTurnTimeStamp($start_date)
                    ]
                ];
            }elseif($start_date == 0 && $end_date != 0){
                $condition["pay_time"] = [
                    [
                        "<",
                        getTimeTurnTimeStamp($end_date)
                    ]
                ];
            }
            $list = $member->getPaymentList($page_index, $page_size, $condition, $order = '', $field = '*');
        $payment=new NsOrderPaymentModel();
        $list['money']= $payment->where($condition)->sum('pay_money');

            return $list;
    }

    return view($this->style . 'Member/paymentList');
    }



    //获取下级人员数量

    public function parentnum(){

            $xx_id = request()->post("xx_id", '');
            if(empty($xx_id)) {
                return $date['code'] = -1;
            }
        $xianxia= New XianxiaModel();
        $info=  $xianxia->getInfo(['xx_id'=>$xx_id],"*");
        if(empty($info['uid'])) {
            return $date['code'] = -1;
        }
          $count=   $this->parentnumber($info['uid']);
            $date['code']=1;
            $date['count']=$count;
            //是否购买商品
        $order_model=new NsOrderModel();
        $res=$order_model->where(['buyer_id'=>$info['uid']])->find();
        if($res){
            $date['yes']= '是';
        }else{
            $date['yes']= '否';
        }
            return $date;
    }

    public function parentnumber($uid){
        $promoter= new NfxPromoterModel();

        if(empty($uid)){
            return -2;
        }
        $info=  $promoter->getInfo(['uid'=>$uid],"promoter_path");
        if(empty($info)){
            return -2;
        }
        $data['promoter_path']=['like','%'.$info['promoter_path'].'-%'];
        $data['is_yes']=1;
        $count=  $promoter->where($data)->count();

        return $count;

    }




    //代理商通过审核
    public function addresult($xx_id,$momey){

        if(empty($xx_id)){
            $data['msg']='参数错误';
           $data['code']=-1;
            return $data;
        }
        if(empty($momey)){
            $data['msg']='参数错误';
            $data['code']=-1;
            return $data;
        }
        $promoter=new NfxPromoterModel();
        $user=new UserModel();
        $xianxia= New XianxiaModel();
        $PromoterRegionAgent=new NfxPromoterRegionAgentModel();
        $ShopMemberAssociation= new NfxShopMemberAssociationModel();
        $PromoterRegionAgentLevel=new NfxPromoterRegionAgentLevelModel();

        $info=  $xianxia->getInfo(['xx_id'=>$xx_id],"*");
        $result=$PromoterRegionAgentLevel->order('level_id ASC')->select();
        if(empty($info['uid'])){
            $data['code']=-1;
        //  $data['msg']='查询错误，请稍后再试！';
            $data['msg']=$xx_id;
            return $data;
        }
        if($info['status']==2){
            $data['code']=-1;
            $data['msg']='请勿重复操作！';
            return $data;
        }
        $res=$PromoterRegionAgent->getInfo(['uid'=>$info['uid']],"*");
        if($res['uid']){
            $data['code']=-1;
            $data['msg']='已经为代理商，请勿重复操作！';
            return $data;
        }
       $nums= $this->parentnumber($info['uid']);//下级人数
        if($nums<0){

            $data['code']=-1;
            $data['msg']='查询错误，请稍后再试！';
            return $data;
        }
        if($result[0]['level_num']>$nums){
            $data['code']=-1;
            //$data['msg']='人数不符合升级标准！';
            $data['msg']=$nums;

            return $data;
        }

        Db::startTrans();
        try {
        $xianxia->save(['status'=>2],['xx_id'=>$xx_id]);
        $text='成功升级为县级代理商';
        //添加客户的积分
            $member_account = new MemberService\MemberAccount();
        $member = new MemberService();
     // $member->addMemberAccount(0, 1, $info['uid'], $momey, $text);
            $res= $member_account->addMemberAccountData(0, 1, $info['uid'], 1, $momey, 30, 0, $text);
        $promoterinfo=  $promoter->getInfo(['uid'=>$info['uid']],"*");
        $userinfo=   $user->getInfo(['uid'=>$info['uid']],'user_tel,real_name');
        $ShopMemberAssociation->save(['region_center_id'=>$promoterinfo['promoter_id']],['promoter_id'=>$promoterinfo['promoter_id']]);
        $data['shop_id']=0;
        $data['promoter_id']=$promoterinfo['promoter_id'];
        $data['uid']=$info['uid'];
        $data['agent_type']=4;
        $data['agent_provinceid']=0;
        $data['agent_cityid']=0;
        $data['agent_districtid']=0;
        $data['is_audit']=1;
        $data['remark']='成功升级为县级代理商';
        $data['audit_time']=time();
        $data['reg_time']=time();
        $data['real_name']=$userinfo['real_name'];
        $data['agent_mobile']=$userinfo['user_tel'];
        $data['agent_address']=0;
        $data['level_id']=$result[0]['level_id'];//默认为一级
        $res= $PromoterRegionAgent->save($data);
        //查询父级的id
            $this->parentid($info['uid']);

            Db::commit();
            $data['code']=$res;
            return  $data;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }


    }


//升级代理级别  level_id:1,2,4 县，市，省
    public function parentid($uid){

        $promoter=new NfxPromoterModel();
        $PromoterRegionAgent=new NfxPromoterRegionAgentModel();
        $PromoterRegionAgentLevel=new NfxPromoterRegionAgentLevelModel();
        $promoterinfo=  $promoter->getInfo(['uid'=>$uid],"*");
        $promoter_path=  $promoterinfo['promoter_path'];
        $promoterarray= explode('-',$promoter_path);
        $promoter_ids=array_reverse($promoterarray);
        $data['pr.promoter_id']=['in',$promoter_ids];
        $info=  $promoter->alias('pr')->join('nfx_promoter_region_agent ag','pr.promoter_id=ag.promoter_id')
          ->where($data)->select();
        $result=array();
        //严格按照path路径，倒序
        foreach( $promoter_ids as $k=>$v){
            foreach ($info as $key=>$val){
                if($val['promoter_id'] ==$v){
                    $result[$k]= $val;
                }
            }
        }
        if(empty($result)){
            return 1;
        }
       // print_r($info);die();
        Db::startTrans();
        try {
        foreach($result as $k=>$v){
             if($v['is_audit']==1 ){
                 $promoterinfo=   $promoter->getInfo(['promoter_id'=>$v['promoter_id']],"*");
                 $array['pr.promoter_path']=['like',$promoterinfo['promoter_path'].'%'];
               /*  if($v['agent_type']==4){
                     $array['ag.agent_type']=4;
                 }else if($v['agent_type']==5){
                     $array['ag.agent_type']=5;
                 }else{
                     $array['ag.agent_type']=6;
                 }*/
                 $array['ag.level_id']=$v['level_id'];
                 $count=$promoter->alias('pr')->join('nfx_promoter_region_agent ag','pr.promoter_id=ag.promoter_id')
                     ->where($array)-> count();
                 //如果是省代，被超越标记
                 $count=$count-1;
                 if($v['level_id']==4){
                     if($count>0){
                       $res=  $PromoterRegionAgent->save(['sign'=>1,'sign_time'=>time()],['promoter_id'=>$v['promoter_id']]);
                       if($res){
                           break;
                       }else{
                           throw new \think\Exception('保存错误，请稍后再试', 100004);
                       }
                     }else{
                         break;
                     }
                 }

                 if($v['level_id']==1){
                     $condition['level_id']=2;
                 }else if($v['level_id']==2){
                     $condition['level_id']=4;
                 }
                 $result=$PromoterRegionAgentLevel->where($condition)->find(); //查询等级标准
                    if(empty($result)){
                        throw new \think\Exception('参数错误，请稍后再试', 100005);
                    }
                 if($count >=$result['level_num']){ //符合条件的升级
                     $date['level_id']=$result['level_id'];
                     $date['remark']=$result['level_name'];
                     $date['audit_time']=time();

                     $res=$PromoterRegionAgent->save($date,['promoter_id'=>$v['promoter_id']]);
                     if($res<=0){
                         throw new \think\Exception('保存错误，请稍后再试', 100006);
                     }
                 }
             }
        }
            Db::commit();
            return $res;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 区域代理取消资格
     *
     * @return multitype:number unknown |Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function removePromoterRegionAgent()
    {

        $xx_id = request()->post("xx_id", 0);
        if($xx_id==0){
            return -1;
        }
        $xianxia=new XianxiaModel();
        $PromoterRegionAgent= new NfxPromoterRegionAgentModel();
        $xianxiainfo= $xianxia->getInfo(['xx_id'=>$xx_id],"uid");
        if(empty($xianxiainfo['uid'])){
            return -1;
        }
        Db::startTrans();
        try {

        $xianxia->save(['status'=>-1],['xx_id'=>$xx_id]);
        $region_agentinfo= $PromoterRegionAgent->getInfo(['uid'=>$xianxiainfo['uid']],"region_agent_id");
        $region_agent = new NfxRegionAgent();
        $retval = $region_agent->removePromoterRegionAgent(0, $region_agentinfo['region_agent_id']);
            Db::commit();
            return $retval;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }

    }

    /**
     * 查询单个 会员
     *
     * @return multitype:unknown
     */
    public function getMemberDetail()
    {
        $user = new User();
        $uid = request()->post("uid", 0);
        $info = $user->getUserInfoDetail($uid);
        return $info;
    }

    /**
     * 修改会员
     */
    public function updateMember()
    {
        if (request()->isAjax()) {
            $member = new MemberService();
            $uid = request()->post('uid', '');
            $user_name = request()->post('user_name', '');
            $member_level = request()->post('level_name', '');
            $password = request()->post('user_password', '');
            $mobile = request()->post('tel', '');
            $email = request()->post('email', '');
            $nick_name = request()->post('nick_name', '');
            $sex = request()->post('sex', '');
            $status = request()->post('status', '');
            $res = $member->updateMemberByAdmin($uid, $user_name, $email, $sex, $status, $mobile, $nick_name, $member_level);
            return AjaxReturn($res);
        }
    }

    /**
     * 修改密码
     */
    public function updateMemberPassword()
    {
        $user = new User();
        $userid = request()->post('uid', '');
        $password = request()->post('user_password', '');
        $result = $user->updateUserInfoByUserid($userid, $password);
        return AjaxReturn($result);
    }

    /**
     * 删除 会员
     *
     * @return multitype:unknown
     */
    public function deleteMember()
    {
        $member = new MemberService();
        $uid = request()->post("uid", 0);
        $res = $member->deleteMember($uid);
        return AjaxReturn($res);
    }

    /**
     * 获取数据库中用户列表
     */
    public function check_username()
    {
        if (request()->isAjax()) {
            // 获取数据库中的用户列表
            $username = request()->get('username','');
            $exist = false;
            $member = new MemberService();
            $exist = $member -> judgeUserNameIsExistence($username);
            return $exist;
        }
    }
    
    /**
     * 判断用户信息是否存在
     * @return boolean
     */
    public function checkUserInfoIsExist(){
        if (request()->isAjax()) {
            $info = request()->post('info', '');
            $type = request()->post('type', '');
            //是否存在
            $exist = false;
            $member = new MemberService();
            
            switch ($type) {
                case "email":
                    $exist = $member -> memberIsEmail($info);
                break;
                case "mobile":
                    $exist = $member -> memberIsMobile($info);
                break;
            }
            return $exist;
        }
    }
    
    /**
     * 添加会员信息
     */
    public function addMember()
    {
        $member = new MemberService();
        $user_name = request()->post('username', '');
        $nick_name = request()->post('nickname', '');
        $password = request()->post('password', '');
        $member_level = request()->post('level_name', '');
        $mobile = request()->post('tel', '');
        $email = request()->post('email', '');
        $sex = request()->post('sex', '');
        $status = request()->post('status', '');
        $retval = $member->addMember($user_name, $password, $email, $sex, $status, $mobile, $member_level);
        $result = $member->updateNickNameByUid($retval, $nick_name);
        return AjaxReturn($retval);
    }

    /**
     * 会员积分明细
     */
    public function pointdetail()
    {
        if (request()->isAjax()) {
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $member_id = request()->post('member_id', '');
            $search_text = request()->post('search_text');
            $start_date = request()->post('start_date') == "" ? 0 : request()->post('start_date');
            $end_date = request()->post('end_date') == "" ? 0 : request()->post('end_date');
            $condition['nmar.uid'] = $member_id;
            $condition['nmar.account_type'] = 1;
            if($start_date != 0 && $end_date != 0){
                $condition["nmar.create_time"] = [
                    [
                        ">",
                        getTimeTurnTimeStamp($start_date)
                    ],
                    [
                        "<",
                        getTimeTurnTimeStamp($end_date)
                    ]
                ];
            }elseif($start_date != 0 && $end_date == 0){
                $condition["nmar.create_time"] = [
                    [
                        ">",
                        getTimeTurnTimeStamp($start_date)
                    ]
                ];
            }elseif($start_date == 0 && $end_date != 0){
                $condition["nmar.create_time"] = [
                    [
                        "<",
                        getTimeTurnTimeStamp($end_date)
                    ]
                ];
            }
            $condition['su.nick_name'] = [
                'like',
                '%' . $search_text . '%'
            ];
            
            $member = new MemberService();
            $list = $member->getPointList($page_index, $page_size, $condition, $order = '', $field = '*');
            return $list;
        }
        $member_id = request()->get('member_id','');
        $this->assign('member_id', $member_id);
        return view($this->style . 'Member/pointDetailList');
    }

    /**
     * 会员积分管理
     */
    public function pointlist()
    {
        if (request()->isAjax()) {
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $search_text = request()->post('search_text', '');
            $form_type = request()->post('form_type');
            $start_date = request()->post('start_date') == "" ? 0 : request()->post('start_date');
            $end_date = request()->post('end_date') == "" ? 0 : request()->post('end_date');
            $condition['nmar.account_type'] = 1;
            if ($form_type != '') {
                if($form_type==111){
                    $condition['nmar.from_type'] = ['in','1,2'];
                }elseif ($form_type==222){
                    $condition['nmar.from_type'] = ['in','21,22'];
                }elseif ($form_type==555){
                    $condition['nmar.from_type'] = ['in','21,22'];
                }

                else{
                    $condition['nmar.from_type'] = $form_type;
                }

            }
            if($start_date != 0 && $end_date != 0){
                $condition["nmar.create_time"] = [
                    [
                        ">",
                        getTimeTurnTimeStamp($start_date)
                    ],
                    [
                        "<",
                        getTimeTurnTimeStamp($end_date)
                    ]
                ];
            }elseif($start_date != 0 && $end_date == 0){
                $condition["nmar.create_time"] = [
                    [
                        ">",
                        getTimeTurnTimeStamp($start_date)
                    ]
                ];
            }elseif($start_date == 0 && $end_date != 0){
                $condition["nmar.create_time"] = [
                    [
                        "<",
                        getTimeTurnTimeStamp($end_date)
                    ]
                ];
            }
            $condition['su.nick_name'] = [
                'like',
                '%' . $search_text . '%'
            ];
            
            $member = new MemberService();
            $list = $member->getPointList($page_index, $page_size, $condition, $order = '', $field = '*');

            return $list;
        }
        return view($this->style . 'Member/pointList');
    }

    /**
     * 会员现金明细
     */
    public function accountdetail()
    {
        if (request()->isAjax()) {
            $member = new MemberService();
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $member_id = request()->post('member_id');
            $search_text = request()->post('search_text');
            $start_date = request()->post('start_date') == "" ? 0 : request()->post('start_date');
            $end_date = request()->post('end_date') == "" ? 0 : request()->post('end_date');
            $condition['nmar.uid'] = $member_id;
            $condition['nmar.account_type'] = 2;
            if($start_date != 0 && $end_date != 0){
                $condition["nmar.create_time"] = [
                    [
                        ">",
                        getTimeTurnTimeStamp($start_date)
                    ],
                    [
                        "<",
                        getTimeTurnTimeStamp($end_date)
                    ]
                ];
            }elseif($start_date != 0 && $end_date == 0){
                $condition["nmar.create_time"] = [
                    [
                        ">",
                        getTimeTurnTimeStamp($start_date)
                    ]
                ];
            }elseif($start_date == 0 && $end_date != 0){
                $condition["nmar.create_time"] = [
                    [
                        "<",
                        getTimeTurnTimeStamp($end_date)
                    ]
                ];
            }
            $condition['su.nick_name'] = [
                'like',
                '%' . $search_text . '%'
            ];
            
            $list = $member->getAccountList($page_index, $page_size, $condition, $order = '', $field = '*');
            return $list;
        }
        $member_id = request()->get('member_id','');
        $this->assign('member_id', $member_id);
        return view($this->style . 'Member/accountDetailList');
    }

    /**
     * 会员现金管理
     */
    public function accountlist()
    {
        if (request()->isAjax()) {
            $member = new MemberService();
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $search_text = request()->post('search_text', '');
            $form_type = request()->post('form_type');
            $start_date = request()->post('start_date') == "" ? 0 : request()->post('start_date');
            $end_date = request()->post('end_date') == "" ? 0 : request()->post('end_date');
            
            $condition['nmar.account_type'] = 2;
            $condition['su.nick_name'] = [
                'like',
                '%' . $search_text . '%'
            ];
        if($start_date != 0 && $end_date != 0){
                $condition["nmar.create_time"] = [
                    [
                        ">",
                        getTimeTurnTimeStamp($start_date)
                    ],
                    [
                        "<",
                        getTimeTurnTimeStamp($end_date)
                    ]
                ];
            }elseif($start_date != 0 && $end_date == 0){
                $condition["nmar.create_time"] = [
                    [
                        ">",
                        getTimeTurnTimeStamp($start_date)
                    ]
                ];
            }elseif($start_date == 0 && $end_date != 0){
                $condition["nmar.create_time"] = [
                    [
                        "<",
                        getTimeTurnTimeStamp($end_date)
                    ]
                ];
            }
            if ($form_type != '') {
                if($form_type=='111'){
                    $condition['nmar.from_type'] = ['in','1,2'];
                }else{
                    $condition['nmar.from_type'] = $form_type;
                }

            }
            $list = $member->getAccountList($page_index, $page_size, $condition, $order = '', $field = '*');
            return $list;
        }
        
        return view($this->style . 'Member/accountList');
    }

    /**
     * 用户锁定
     */
    public function memberLock()
    {
        $uid = request()->post('id','');
        $retval = 0;
        if (! empty($uid)) {
            $member = new MemberService();
            $retval = $member->userLock($uid);
        }
        return AjaxReturn($retval);
    }

    /**
     * 用户解锁
     */
    public function memberUnlock()
    {
        $uid = request()->post('id','');
        $retval = 0;
        if (! empty($uid)) {
            $member = new MemberService();
            $retval = $member->userUnlock($uid);
        }
        return AjaxReturn($retval);
    }

    /**
     * 粉丝列表
     *
     * @return multitype:number unknown |Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function weixinFansList()
    {
        if (request()->isAjax()) {
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $search_text = request()->post('search_text','');
            $condition = array(
                'nickname_decode' => array(
                    'like',
                    '%' . $search_text . '%'
                )
            );
            $weixin = new Weixin();
            $list = $weixin->getWeixinFansList($page_index, $page_size, $condition);
            return $list;
        } else {
            return view($this->style . 'Member/weixinFansList');
        }
    }

    /**
     * 积分、现金调整
     */
    public function addMemberAccount()
    {
        $member = new MemberService();
        $uid = request()->post('id','');
        $type = request()->post('type','');
        $num = request()->post('num','');
        $text = request()->post('text','');
        $retval = $member->addMemberAccount(0, $type, $uid, $num, $text);
        return AjaxReturn($retval);
    }

    /**
     * 积分、现金调整//线下充值
     */
    public function addMemberAccountOffline()
    {

        $member = new MemberService();
        $xx_id = request()->post('id','');
        $type = request()->post('type','');
        $num = request()->post('num','');
       // $text = request()->post('text','');
        if(empty($xx_id)){
            $retval=-1;
        }

        $xianxia_model=New XianxiaModel();

        $xianxia_model->startTrans();
        try{
            $res=$xianxia_model->getInfo(['xx_id'=>$xx_id],'uid');
            if($res){
                $retval = $member->addMemberAccount(0, $type, $res['uid'], $num, "充值");

                $xianxia_model->save(['status'=>5],['xx_id'=>$xx_id]);
            }



            $xianxia_model->commit();

            return AjaxReturn($retval);
        } catch (\Exception $e) {
            $xianxia_model->rollback();
            return $e->getMessage();
        }



    }

    /**
     * 会员等级列表
     */
    public function memberLevelList()
    {
        $member = new MemberService();
        if (request()->isAjax()) {
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $list = $member->getMemberLevelList($page_index, $page_size);
            return $list;
        }
        return view($this->style . 'Member/memberLevelList');
    }

    /**
     * 添加会员等级
     */
    public function addMemberLevel()
    {
        $member = new MemberService();
        if (request()->isAjax()) {
            $level_name = request()->post("level_name", '');
            $min_integral = request()->post("min_integral", '');
            $quota = request()->post("quota", '');
            $upgrade = request()->post("upgrade", '');
            $goods_discount = request()->post("goods_discount", '');
            $goods_discount = $goods_discount / 100;
            $desc = request()->post("desc", '');
            $relation = request()->post("relation", '');
            $res = $member->addMemberLevel($this->instance_id, $level_name, $min_integral, $quota, $upgrade, $goods_discount, $desc, $relation);
            return AjaxReturn($res);
        }
        return view($this->style . 'Member/addMemberLevel');
    }

    /**
     * 修改会员等级
     */
    public function updateMemberLevel()
    {
        $member = new MemberService();
        if (request()->isAjax()) {
            $level_id = request()->post("level_id", 0);
            $level_name = request()->post("level_name", '');
            $min_integral = request()->post("min_integral", '');
            $quota = request()->post("quota", '');
            $upgrade = request()->post("upgrade", '');
            $goods_discount = request()->post("goods_discount", '');
            $goods_discount = $goods_discount / 100;
            $desc = request()->post("desc", '');
            $relation = request()->post("relation", '');
            $res = $member->updateMemberLevel($level_id, $this->instance_id, $level_name, $min_integral, $quota, $upgrade, $goods_discount, $desc, $relation);
            return AjaxReturn($res);
        }
        $level_id = request()->get("level_id", 0);
        $info = $member->getMemberLevelDetail($level_id);
        $info['goods_discount'] = $info['goods_discount'] * 100;
        $this->assign('info', $info);
        return view($this->style . 'Member/updateMemberLevel');
    }

    /**
     * 删除 会员等级
     *
     * @return multitype:unknown
     */
    public function deleteMemberLevel()
    {
        $member = new MemberService();
        $level_id = request()->post("level_id", 0);
        $res = $member->deleteMemberLevel($level_id);
        return AjaxReturn($res);
    }

    /**
     * 修改 会员等级 单个字段
     *
     * @return multitype:unknown
     */
    public function modityMemberLevelField()
    {
        $member = new MemberService();
        $level_id = request()->post("level_id", 0);
        $field_name = request()->post("field_name", '');
        $field_value = request()->post("field_value", '');
        $res = $member->modifyMemberLevelField($level_id, $field_name, $field_value);
        return AjaxReturn($res);
    }

    /**
     * 会员提现列表
     */
    public function userCommissionWithdrawList()
    {
        if (request()->isAjax()) {
            $member = new MemberService();
            $pageindex = request()->post('pageIndex','');
            $user_phone = request()->post('user_phone','');
            if ($user_phone != "") {
                $condition["mobile"] = array(
                    "like",
                    "" . $user_phone . "%"
                );
            }
            $uid_string = "";
            $where = array();
            if ($user_phone != "") {
                $where["nick_name"] = array(
                    "like",
                    "%" . $user_phone . "%"
                );
            }
            if (! empty($where)) {
                $uid_string = $this->getUserUids($where);
                if ($uid_string != "") {
                    $condition["uid"] = array(
                        "in",
                        $uid_string
                    );
                } else {
                    $condition["uid"] = 0;
                }
            }
            $condition["shop_id"] = $this->instance_id;
            $list = $member->getMemberBalanceWithdraw($pageindex, PAGESIZE, $condition, 'ask_for_date desc');
            return $list;
        } else {
            $config_service = new WebConfig();
            $data1 = $config_service->getTransferAccountsSetting($this->instance_id, 'wechat');
            $data2 = $config_service->getTransferAccountsSetting($this->instance_id, 'alipay');
            if (! empty($data1)) {
                $wechat = json_decode($data1['value'], true);
            }
            if (! empty($data2)) {
                $alipay = json_decode($data2['value'], true);
            }
            $this->assign("wechat", $wechat);
            $this->assign("alipay", $alipay);
            
            $child_menu_list = array(
                array(
                    'url' => "Member/userCommissionWithdrawList",
                    'menu_name' => "会员提现列表",
                    "active" => 1
                ),
                array(
                    'url' => "Config/memberwithdrawsetting",
                    'menu_name' => "会员提现设置",
                    "active" => 0
                )
            );
            $this->assign("child_menu_list", $child_menu_list);
            return view($this->style . "Member/userCommissionWithdrawList");
        }
    }

    /**
     * 用户提现审核
     *
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    public function userCommissionWithdrawAudit()
    {
        $id = request()->post('id','');
        $status = request()->post('status','');
        $transfer_type = request()->post('transfer_type','');
        $transfer_name = request()->post('transfer_name','');
        $transfer_money = request()->post('transfer_money','');
        $transfer_remark = request()->post('transfer_remark','');
        $transfer_no = request()->post('transfer_no','');
        $transfer_account_no = request()->post('transfer_account_no','');
        $type_id = request()->post('type_id','');
        $member = new MemberService();
        $retval = $member->MemberBalanceWithdrawAudit($this->instance_id, $id, $status, $transfer_type, $transfer_name, $transfer_money, $transfer_remark, $transfer_no, $transfer_account_no, $type_id);
        return $retval;
    }

    /**
     * 拒绝提现请求
     *
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    public function userCommissionWithdrawRefuse()
    {
        $id = request()->post('id','');
        $status = request()->post('status','');
        $remark = request()->post('remark','');
        $member = new MemberService();
        $retval = $member->userCommissionWithdrawRefuse($this->instance_id, $id, $status, $remark);
        return AjaxReturn($retval);
    }

    /**
     * 查寻符合条件的数据并返回id （多个以“,”隔开）
     *
     * @param unknown $condition            
     * @return string
     */
    public function getUserUids($condition)
    {
        $member = new MemberService();
        $list = $member->getMemberAll($condition);
        $uid_string = "";
        foreach ($list as $k => $v) {
            $uid_string = $uid_string . "," . $v["uid"];
        }
        if ($uid_string != "") {
            $uid_string = substr($uid_string, 1);
        }
        return $uid_string;
    }

    /**
     * 获取提现详情
     *
     * @return unknown
     */
    public function getWithdrawalsInfo()
    {
        $id = request()->post('id','');
        $member = new MemberService();
        $retval = $member->getMemberWithdrawalsDetails($id);
        return $retval;
    }
    /**
     * 供货商列表
     */
    public function supplierList(){
        $supplier = new Supplier();
        if(request()->isAjax()){
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $search_text = request()->post('search_text', '');
            $condition['supplier_name'] = array('like', '%'.$search_text.'%');
            $list = $supplier->getSupplierList($page_index, $page_size, $condition);
            return $list;
        }
        return view($this->style.'Member/supplierList');
    }
    /**
     * 添加供货商
     * @return multitype:unknown
     */
    public function addSupplier(){
        $supplier = new Supplier();
        if(request()->isAjax()){
            $uid = request()->post('uid', 0);
            $supplier_name = request()->post('supplier_name', '');
            $linkman_name = request()->post('linkman_name', '');
            $linkman_tel = request()->post('linkman_tel', '');
            $linkman_address = request()->post('linkman_address', '');
            $desc = request()->post('desc', '');
            $res = $supplier->addSupplier($uid, $supplier_name, $linkman_name, $linkman_tel, $linkman_address, $desc);
            return AjaxReturn($res);
        }
        return view($this->style.'Member/addSupplier');
    }
    /**
     * 修改代理商
     * @return multitype:unknown
     */
    public function updateSupplier(){
        $supplier = new Supplier();
        if(request()->isAjax()){
            $supplier_id = request()->post('supplier_id', 0);
            $supplier_name = request()->post('supplier_name', '');
            $linkman_name = request()->post('linkman_name', '');
            $linkman_tel = request()->post('linkman_tel', '');
            $linkman_address = request()->post('linkman_address', '');
            $desc = request()->post('desc', '');
            $res = $supplier->updateSupplier($supplier_id, $supplier_name, $linkman_name, $linkman_tel, $linkman_address,  $desc);
            return AjaxReturn($res);
        }
        $supplier_id = request()->get('supplier_id', 0);
        $info = $supplier->getSupplierInfo($supplier_id);
        $this->assign('supplier_id', $supplier_id);
        $this->assign('info', $info);
        return view($this->style.'Member/updateSupplier');
    }
    /**
     * 删除代理商
     * @return multitype:unknown
     */
    public function deleteSupplier(){
        $supplier = new Supplier();
        $supplier_id_array = request()->post('supplier_id', 0);
        $res = $supplier->deleteSupplier($supplier_id_array);
        return AjaxReturn($res);
    }
    
    /**
     * 订单数据excel导出
     */
    public function memberDataExcel(){
        $xlsName  = "会员数据列表";
        $xlsCell  = array(
            array('user_name','用户名'),
            array('nick_name','昵称'),
            array('user_tel','手机'),
            array('user_email','邮箱'),
            array('level_name','会员等级'),
            array('point','积分'),
            array('balance','账户现金'),
            array('reg_time','注册时间'),
            array('current_login_time','最后登录时间'),
        );
        $search_text = request()->get('search_text', '');
        $level_id = request()->get('levelid', -1);
        $condition = [
            'su.is_member' => 1,
            'su.nick_name|su.user_tel|su.user_email' => array(
                'like',
                '%' . $search_text . '%'
            ),
        
            'su.is_system' => 0
        ];
        if ($level_id != - 1) {
            $condition['nml.level_id'] = $level_id;
        }
        $member = new MemberService();
        $list = $member->getMemberList(1, 0, $condition, 'su.reg_time desc');
        $list = $list["data"];
        foreach($list as $k=>$v){
            $list[$k]["reg_time"] = getTimeStampTurnTime($v["reg_time"]);
            $list[$k]["current_login_time"] = getTimeStampTurnTime($v["current_login_time"]);
        }
        dataExcel($xlsName,$xlsCell,$list);
    }
    /**
     * 更新粉丝信息
     */
    public function updateWchatFansList(){
        $page_index = request()->post("page_index", 0);
        $page_size = 50;
        if($page_index == 0)
        {
            //建立连接，同时获取所有用户openid
            $weixin = new Weixin();
            $openid_list = $weixin -> getWeixinOpenidList();
            
            if(!empty($openid_list['total']))
            {
                $_SESSION['wchat_openid_list'] = $openid_list['openid_list'];
                if ($openid_list['total'] % $page_size == 0) {
                    $page_count = $openid_list['total'] / $page_size;
                } else {
                    $page_count = (int) ($openid_list['total'] / $page_size) + 1;
                }
                return array(
                    'total' => $openid_list['total'],
                    'page_count' => $page_count,
                    'errcode'  => '0',
                    'errorMsg' => ''
                );
            }else{
                return $openid_list;
            }
        }else{
            //对应页数更新用户粉丝信息
           $get_fans_openid_list = $_SESSION['wchat_openid_list'];
           if(!empty($get_fans_openid_list))
           {
               $start = ($page_index -1) * $page_size;
               $page_fans_openid_list = array_slice($get_fans_openid_list, $start, $page_size);
               if(!empty($page_fans_openid_list))
               {
                    $str = '{ "user_list" : [';
                    foreach ($page_fans_openid_list as $key => $value) {
                   	    $str .= ' {"openid" : "'.$value.'"},';
               	    }
                   	$openidlist = substr($str,0,strlen($str)-1);
                   	$openidlist .= ']}';
                    $weixin = new Weixin();
                    $result = $weixin -> UpdateWchatFansList($openidlist);
                    return $result;
               }else{
                   return array(
                        'errcode'  => '0',
                        'errorMsg' => 'success'
                    );
               }
            
           }
        }
    }
    
    /**
     * 获取用户日志列表
     */
    public function userOperationLogList(){
        if(request()->isAjax()){
            $member = new MemberService();
            $start_date = request()->post('start_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('start_date'));
            $end_date = request()->post('end_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('end_date'));
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post("page_size", PAGESIZE);
            $user_name = request()->post("search_text", "");
            
            if ($start_date != 0 && $end_date != 0) {
                $condition["create_time"] = [
                    [
                        ">",
                        $start_date
                    ],
                    [
                        "<",
                        $end_date
                    ]
                ];
            } elseif ($start_date != 0 && $end_date == 0) {
                $condition["create_time"] = [
                    [
                        ">",
                        $start_date
                    ]
                ];
            } elseif ($start_date == 0 && $end_date != 0) {
                $condition["create_time"] = [
                    [
                        "<",
                        $end_date
                    ]
                ];
            }
            if(!empty($user_name)){
                $condition["user_name"] = [
                    [
                        "like",
                        "%$user_name%"
                    ]
                ];
            }
            
            $list = $member -> getUserOperationLogList($page_index, $page_size, $condition, "create_time desc");
            return $list;
        }
        return view($this->style. "Member/userOperationLogList");
    }
}