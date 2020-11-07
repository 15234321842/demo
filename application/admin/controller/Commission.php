<?php
/**
 * Commission.php
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

use data\service\NfxPartner;
use data\service\NfxPromoter;
use data\service\NfxRegionAgent;
use data\service\NfxUser;
use data\service\Member;
use data\service\Shop;
use SebastianBergmann\GlobalState\Blacklist;

/**
 * 管理费控制器
 */
class Commission extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 三级分销管理费列表
     * 
     * @return Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function commissionDistributionList()
    {

            return view($this->style ."Commission/userAccountList");
    }

    /**
     * 合伙人分红管理费列表
     * 
     * @return Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function commissionPartnerList()
    {

            return view($this->style . "Commission/userAccountList");

    }

    /**
     * 区域代理管理费
     * 
     * @return multitype:number unknown |Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function commissionRegionAgentList()
    {

            return view($this->style . "Commission/userAccountList");

    }
//区域经理管理费
    public function commissionRegionAgentManageList()
    {

            return view($this->style . "Commission/userAccountList");

    }

    /**
     * 全球分红发放列表
     */
    public function commissionPartnerGlobalList()
    {

            return view($this->style . "Commission/userAccountList");

    }

    /**
     * 推广员管理费列表
     * 
     * @return Ambigous <multitype:number unknown , unknown>|Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function userAccountList()
    {

            return view($this->style . "Commission/userAccountList");

    }
    
    /**
     * 会员提现列表
     */
    public function userCommissionWithdrawList()
    {

            return view($this->style . "Commission/userAccountList");

    }

    /**
     * 用户提现审核
     * 
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    public function userCommissionWithdrawAudit()
    {

        $id = request()->post("id", 0);
        $status = request()->post("status", 1);
        $user = new NfxUser();
        $retval = $user->UserCommissionWithdrawAudit($this->instance_id, $id, $status);
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
        $member = new Member();
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
     * 查询 合伙人 返回id 已,隔开
     * 
     * @param unknown $condition            
     * @return string
     */
    public function getPartnerUids($condition)
    {
        $partner = new NfxPartner();
        $list = $partner->getPartnerAll($condition);
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
     * 查询 推广员 返回id 已,隔开
     * 
     * @param unknown $condition            
     * @return string
     */
    public function getPromoterUids($condition)
    {
        $promoter = new NfxPromoter();
        $list = $promoter->getPromoterAll($condition);
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
     * 查询 代理 返回id 已,隔开
     * 
     * @param unknown $condition            
     * @return string
     */
    public function getPromoterRegionAgentUids($condition)
    {
        $region_agent = new NfxRegionAgent();
        $list = $region_agent->getPromoterRegionAgentAll($condition);
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
     * 会员提现设置
     *
     * @return multitype:number unknown |Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function memberWithdrawSetting()
    {
        $shop = new Shop();
        if (request()->isAjax()) {
            $id = isset($_POST['id']) ? $_POST['id'] : '';
            if($id == ''){
                $withdraw_cash_min = isset($_POST['cash_min']) ? $_POST['cash_min'] : '';
                $withdraw_multiple = isset($_POST['multiple']) ? $_POST['multiple'] : '';
                $withdraw_poundage = isset($_POST['poundage']) ? $_POST['poundage'] : '';
                $withdraw_message = isset($_POST['message']) ? $_POST['message'] : '';
                $list = $shop->addMemberWithdrawSetting($this->instance_id, $withdraw_cash_min, $withdraw_multiple, $withdraw_poundage, $withdraw_message, "银联卡");
                return $list;
            }else {
                $withdraw_cash_min = isset($_POST['cash_min']) ? $_POST['cash_min'] : '';
                $withdraw_multiple = isset($_POST['multiple']) ? $_POST['multiple'] : '';
                $withdraw_poundage = isset($_POST['poundage']) ? $_POST['poundage'] : '';
                $withdraw_message = isset($_POST['message']) ? $_POST['message'] : '';
                $list = $shop->updateMemberWithdrawSetting($this->instance_id, $withdraw_cash_min, $withdraw_multiple, $withdraw_poundage, $withdraw_message, "银联卡",$id);
                return $list;
            }
        }else {
            $list = $shop->getWithdrawInfo($this->instance_id);
            if(empty($list)){
                $list['id'] = '';
                $list['withdraw_cash_min'] = '';
                $list['withdraw_multiple'] = '';
                $list['withdraw_poundage'] = '';
                $list['withdraw_message'] = '';
            }
            $this->assign("list", $list);
            return view($this->style . "Commission/memberWithdrawSetting");
        }
           
    }
    
    /**
     * 具体项的管理费明细
     */
    public function userAccountRecordsDetail()
    {
        $nfx_user = new NfxUser();
        if(request()->isAjax())
        {
            $pageindex = request()->post('pageIndex','');
            $condition['shop_id'] = $this->instance_id;
            $uid = request()->post('uid','');
            if(!empty($uid)){
                $condition['uid'] = $uid;
            } 
            $type_id = request()->post('type_id','');
            if(!empty($type_id)){
                $condition['account_type'] = $type_id;
            }
            $account_records_detail = $nfx_user->getPcNfxUserAccountRecordsList($pageindex, PAGESIZE, $condition, 'create_time desc');
          //  print_r($account_records_detail);die();
            return $account_records_detail;
        }else{
            $type_id = request()->get('type_id','');
            $uid = request()->get('uid','');
            switch ($type_id)
            {
                case 1:
                    $type_name = '分销管理费';
                    $view= 'Commission/userAccountRecordsDetail';
                    break;
                case 2:
                    $type_name = '代理管理费';
                    $view= 'Commission/userRegionAgentDetail';
                    break;
                case 4:
                    $type_name = '合伙人分红';
                    $view= 'Commission/userPartnerDetail';
                    break;
                case 5:
                    $type_name = '全球分红';
                    $view= 'Commission/userPartnerGlobalDetail';
                    break;
            }
    
            $this->assign('type_name', $type_name);
            $this->assign('type_id', $type_id);
            $this->assign('uid', $uid);
    
            return view($this->style . $view);
    
        }
    
    }
    
    
}