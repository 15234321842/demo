<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/14
 * Time: 17:41
 */
namespace app\appapi\controller;
use data\service\NfxPromoter;
use data\model\NfxPromoterModel;
use data\service\NfxUser as NfxUserService;
use data\service\User as UserService;


//管理费
Class Commission extends BaseController{

    /*
     * 获取我的管理费首页信息
     * */

    public function index(){
        $uid=$this->getUserId();
        $page=$this->request->param('page',1);
        $condition['uid'] = $uid;
        $condition['account_type'] = ['in','1,2'];
        $account_records_list= $this->ManagerCommisionlist($page,$condition);

        $this->success('获取完成',$account_records_list);
    }

    public function getCommission(){
        $uid=$this->getUserId();
        $nfx_user = new NfxUserService();
        $user_account = $nfx_user->getNfxUserAccount($uid, 0);
        $PromoterModel= new NfxPromoterModel();
        $promoterinfo=$PromoterModel->getInfo(['uid'=>$uid],'promoter_id');
        $ids= $this->parentnumber($promoterinfo['promoter_id']);
       $condition['or.buyer_id']=['in',$ids['array_one']];
        $condition['uid']=$uid;
        $commission_promoter = $nfx_user->getNfxUserAccountids($condition);
        $array['uid']=$uid;
        $array['or.buyer_id']=['in',$ids['array_two']];
        $commission_promoter1 = $nfx_user->getNfxUserAccountids($array);

        if(!$user_account){
            $user_account['commission_cash']='0.00';
            $user_account['commission']='0.00';
            $user_account['commission_locked']='0.00';
            $user_account['commission_withdraw']='0.00';
            $user_account['commission_promoter']='0.00';
            $user_account['commission_region_agent']='0.00';
        }
        $user=new UserService();

        $np=new NfxPromoter();
        $user_account['team_num']=count( $np->getTeamCount($uid));
        $user_account['info']=$np->getUserLevel($uid);
        $info=$user->getTeamInfo($uid);


        $user_account['level']=$info['level'];
        $user=new UserService();
        $userinfo=$user->getUserInfoByUid($uid);
        $user_account['user_name']=$userinfo['user_name'];
        $user_account['nick_name']=$userinfo['nick_name'];
        $user_account['user_headimg']=$userinfo['user_headimg'];
        $user_account['url']=\think\Request::instance()->domain().'/';
        $user_account['commission_promoter']=$commission_promoter;
        $user_account['commission_promoter1']=$commission_promoter1;

        $this->success('获取完成',$user_account);
    }

    /*
     * 获取管理费明细
     * type_id:1 分销管理费，2 区域代理 ， 3 渠道代理
     * */
    public function getCommissionDetail(){
        $uid=$this->getUserId();
        $PromoterModel= new NfxPromoterModel();
        $nfx_user = new NfxUserService();
        $page=$this->request->param('page',1);
        $type_id = $this->request->param('type_id','');
        $promoterinfo=$PromoterModel->getInfo(['uid'=>$uid],'promoter_id');
        $ids= $this->parentnumber($promoterinfo['promoter_id']);
        $condition['ar.uid']=$uid;
        if($type_id==1){//一部
            if(empty($ids['array_one'])){
                $this->success('获取完成',0);
            }
            $condition['account_type'] = 1;
            $condition['or.buyer_id']=['in',$ids['array_one']];
            $account_records_list= $nfx_user->getNfxUserAccountidsClass($page, 20, $condition);
        }
        if($type_id==2){//er部
            if(empty($ids['array_two'])){
                $this->success('获取完成',0);
            }
            $condition['account_type'] = 1;
            $condition['or.buyer_id']=['in',$ids['array_two']];
            $account_records_list= $nfx_user->getNfxUserAccountidsClass($page, 20, $condition);


        }
        if($type_id==3)
        {//区域代理
            $array['account_type'] = 2;
            $array['uid'] = $uid;
            $account_records_list= $this->ManagerCommisionlist($page,$array);
        }
        $this->success('获取完成',$account_records_list);
    }


    //代理商管理费列表

    public  function ManagerCommisionlist($page,$condition){

        $nfx_user = new NfxUserService();
        $field='nuar.id,nuar.type_alis_id,nuar.money,nuar.text,nuar.create_time, nuat.type_name, nuat.sign';
        $account_records_list = $nfx_user->getNfxUserAccountRecordsList($page, 10, $condition, 'create_time desc',$field);
        foreach ($account_records_list['data'] as &$item){
            $item['create_time']= date("Y-m-d H:i:s",$item['create_time']);

         if($item['type_name']!="提现"){
                $item['type']=false;
            }else{
                $item['type']=true;
            }
            if($item['type_name']=='推广员'){
                $item['class']=3;
            }
            if($item['type_name']=='销售佣金'){
                $item['class']=1;
            }



        }
        $account_records_list['page_count']=ceil($account_records_list['total_count']/10);
        return $account_records_list;
    }

    //获取下级人员
    public function parentnumber($promoter_id){
     $Promoter=   new NfxPromoter();
     $PromoterModel=new NfxPromoterModel();
        $array_one = array();
        $array_two = array();
        //下一级关系者
        $array_one = $Promoter->getPromoterChildren($promoter_id);
        if(!empty($array_one)){
            $array_one_ids = array();
            foreach ($array_one as $k=>$v){
                $array_one_ids[]=$v['promoter_id'];
                $array_one_uids[]=$v['uid'];

            }
            $condition['parent_promoter']=['in',$array_one_ids];
            $array_two=  $PromoterModel->where($condition)->select();
            if(!empty($array_two)){
                $array_two_uids = array();
                foreach ($array_two as $k=>$v){
                    $array_two_uids[]=$v['uid'];
                }
            }
        }
        $data['array_one']=$array_one_uids;
        $data['array_two']=$array_two_uids;

return $data;


    }


    /**
     * 提现记录
     */
    public function ajaxUserCommissionWithdraw()
    {
        $uid=$this->getUserId();
        $page=$this->request->param('page',1);
        $nfx_user = new NfxUserService();
        $shop_id = 0;
        $condition['shop_id'] = $shop_id;
        $condition['uid'] = $uid;
        $commission_withdraw_list = $nfx_user->getUserCommissionWithdraw($page, 10, $condition, 'ask_for_date desc');
        foreach ($commission_withdraw_list['data'] as &$item){
            $item['ask_for_date']= date("Y-m-d H:i:s",$item['ask_for_date']);
        }
        $this->success('获取成功！',$commission_withdraw_list);
    }

}