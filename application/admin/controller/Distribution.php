<?php
/**
 * Distribution.php
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

use data\service\NfxPromoter as NfxPromoterService;
use data\service\NfxPartner;
use data\service\NfxCommissionConfig;
use data\service\NfxPromoter;
use data\service\GoodsGroup as GoodsGroup;
use data\service\NfxShopConfig;
use data\service\NfxPartnerGlobalCalculate;
use data\service\NfxRegionAgent;
use data\service\NfxUser as NfxUserService;

use data\service\Address;
use data\service\Member;
use data\service\promotion\PromoteRewardRule;


/**
 * 分销控制器
 */
class Distribution extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 推广员列表
     */
    public function promoterList()
    {
        return view($this->style . "Distribution/promoterList");

    }

    /**
     * 推广员审核
     */
    public function promoterAudit()
    {
        $promoter_id = request()->post("promoter_id", 0);
        $is_audit = request()->post("is_audit", 0);
        $uid = request()->post("uid", 0);
        
//         $promoter_id = $_POST["promoter_id"];
//         $is_audit = $_POST["is_audit"];
//         $uid = $_POST['uid'];
        $promoter = new NfxPromoterService();
        $res = $promoter->promoterAudit($promoter_id, $is_audit, $this->instance_id);
        if ($is_audit == 1 && $res) {
            $promote_reward_rule = new PromoteRewardRule();
            $promote_reward_rule->RegisterPromoterSendPoint($this->instance_id, $uid);
            return AjaxReturn($res);
        }
    }

    public function test()
    {
        $promote_reward_rule = new PromoteRewardRule();
        $promote_reward_rule->RegisterPromoterSendPoint($this->instance_id, 31);
    }

    /**
     * 推广员等级
     */
    public function promoterLevelList()
    {
        if (request()->isAjax()) {
            $promoter = new NfxPromoterService();
            $pageindex = isset($_POST['pageIndex']) ? $_POST['pageIndex'] : '';
            $condition = array(
                'shop_id' => $this->instance_id
            );
            $list = $promoter->getPromoterLevelList($pageindex, PAGESIZE, $condition, '');
            return $list;
        } else {
            $child_menu_list = array(
                array(
                    'url' => "distribution/threeLeveldistributionconfig",
                    'menu_name' => "基本设置",
                    "active" => 0
                ),
                array(
                    'url' => "distribution/promoterlevelList",
                    'menu_name' => "推广员等级",
                    "active" => 1
                )
            );
            $this->assign('child_menu_list', $child_menu_list);
            return view($this->style . "Distribution/promoterLevelList");
        }
    }

    /**
     * 添加店铺推广员等级
     */
    public function addPromoterLevel()
    {
        if (request()->isAjax()) {
            $shop_id = $this->instance_id;
            
            $level_name = request()->post("level_name", 0);
            $level_money = request()->post("level_money", 0);
            $level_0 = request()->post("level_0", 0);
            $level_1 = request()->post("level_1", 0);
            $level_2 = request()->post("level_2", 0);
            
//             $level_name = $_POST["level_name"];
//             $level_money = $_POST["level_money"];
//             $level_0 = $_POST["level_0"];
//             $level_1 = $_POST["level_1"];
//             $level_2 = $_POST["level_2"];
            $promoter = new NfxPromoterService();
            $res = $promoter->addPromoterLevel($shop_id, $level_name, $level_money, $level_0, $level_1, $level_2);
            return AjaxReturn($res);
        } else {
            return view($this->style . "Distribution/addPromoterLevel");
        }
    }

    /**
     * 商品分销列表
     */
    public function goodsCommissionRateList()
    {
            return view($this->style . "Distribution/promoterList");
    }

    /**
     * 商品开启或关闭分销
     */
    public function modifyGoodsDistribution()
    {
        $is_open = request()->post("is_open", 0);
        $condition = request()->post("goods_ids", 0);
        $commission_config = new NfxCommissionConfig();
        $res = $commission_config->modifyGoodsIsOpenDistribution($condition, $is_open);
        return AjaxReturn($res);
    }

    /**
     * 获取商品分销信息
     */
    public function getGoodsCommissionRateDetail()
    {
        $goods_id = request()->post("goods_id", 0);
        $commission_config = new NfxCommissionConfig();
        $res = $commission_config->getGoodsCommissionRate($goods_id);
        return $res;
    }

    /**
     * 商品分销修改
     */
    public function updateGoodsCommissionRate()
    {
        if (request()->isAjax()) {
            $condition_type = request()->post("condition_type", 1);
            $condition = request()->post("condition", 0);
            $distribution_commission_rate = request()->post("distribution_commission_rate", 0);
            $partner_commission_rate = request()->post("partner_commission_rate", 0);
            $global_commission_rate = request()->post("global_commission_rate", 0);
            $distribution_team_commission_rate = request()->post("distribution_team_commission_rate", 0);
            $partner_team_commission_rate = request()->post("partner_team_commission_rate", 0);
            $regionagent_commission_rate = request()->post("regionagent_commission_rate", 0);
            $channelagent_commission_rate = request()->post("channelagent_commission_rate", 0);
            $shop_id = $this->instance_id;
            $commission_config = new NfxCommissionConfig();
            $retval = $commission_config->updateGoodsCommissionRate($condition, $condition_type, $distribution_commission_rate, $partner_commission_rate, $global_commission_rate, $distribution_team_commission_rate, $partner_team_commission_rate, $regionagent_commission_rate, $channelagent_commission_rate, $shop_id);
            return AjaxReturn($retval);
        } else {
            $goods_id = $_GET["goods_id"];
            $this->assign("goods_id", $goods_id);
            $commission_config = new NfxCommissionConfig();
            $goods_info = $commission_config->getGoodsCommissionRate($goods_id);
            $this->assign("goods_info", $goods_info);
            return view($this->style . "Distribution/updateGoodsCommissionRate");
        }
    }

    /**
     * 推广员详情
     */
    public function getPromoterDetail()
    {
        $promoter_id = request()->post("promoter_id", 0);
        $promoter = new NfxPromoter();
        $res = $promoter->getPromoterDetail($promoter_id);
        return $res;
    }

    /**
     * 修改推广员父级
     */
    public function modifyPromoterParent()
    {
        if (request()->isAjax()) {
            $promoter_id = request()->post("promoter_id", 0);
            $parent_promoter = request()->post("parent_promoter", 0);
            $promoter = new NfxPromoterService();
            $retval = $promoter->modifyPromoterParent($promoter_id, $parent_promoter, $this->instance_id);
            return AjaxReturn($retval);
        } else {
            $promoter_id = $_GET["promoter_id"];
            $parent_promoter = $_GET["parent_promoter"];
            $this->assign("promoter_id", $promoter_id);
            $this->assign("parent_promoter", $parent_promoter);
            return view($this->style . "Distribution/modifyPromoterParent");
        }
    }

    /**
     * 推广员冻结/解冻
     */
    public function modifyPromoterLock()
    {
        $is_lock = request()->post("is_lock", 0);
        $promoter_id = request()->post("promoter_id", 0);
        $promoter = new NfxPromoterService();
        $retval = $promoter->modifyPromoterLock($promoter_id, $is_lock);
        return AjaxReturn($retval);
    }

    /**
     * 修改推广员等级
     */
    public function updatePromoterLevel()
    {
        if (request()->isAjax()) {
            $level_id = request()->post("level_id", 0);
            $level_name = request()->post("level_name", '');
            $level_money = request()->post("level_money", 0);
            $level_0 = request()->post("level_0", 0);
            $level_1 = request()->post("level_1", 0);
            $level_2 = request()->post("level_2", 0);
            
//             $level_id = $_POST["level_id"];
//             $level_name = $_POST["level_name"];
//             $level_money = $_POST["level_money"];
//             $level_0 = $_POST["level_0"];
//             $level_1 = $_POST["level_1"];
//             $level_2 = $_POST["level_2"];
            $promoter = new NfxPromoterService();
            $retval = $promoter->updatePromoterLevel($level_id, $level_name, $level_money, $level_0, $level_1, $level_2);
            return AjaxReturn($retval);
        } else {
            $level_id = $_GET["level_id"];
            $promoter = new NfxPromoterService();
            $res = $promoter->getPromoterLevalDetail($level_id);
            $this->assign("premoter_level_info", $res);
            $this->assign("level_id", $level_id);
            return view($this->style . "Distribution/updatePromoterLevel");
        }
    }

    /**
     * 三级分销
     */
    public function threeLevelDistributionConfig()
    {

        return view($this->style . "Distribution/promoterList");
    }

    /**
     * 是否开启三级分销及是否开启推广员shenhe
     *
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    public function modifyShopConfigIsDistributionOrPromoterIsAudit()
    {

        $is_open = request()->post("is_open", 0);
        $is_audit = request()->post("is_audit", 0);
        $is_start = request()->post("is_start", 0);
        $is_set = request()->post("is_set", 0);
        $distribution_use = request()->post("distribution_use", 0);
        $protection = request()->post("protection", 0);
        $shop_config = new NfxShopConfig();
        $retval = $shop_config->modifyShopConfigIsDistributionOrPromoterIsAudit($this->instance_id, $is_open, $is_audit, $is_start, $is_set, $distribution_use,$protection);
        return AjaxReturn($retval);
    }

    /**
     * 区域代理
     */
    public function regionalAgent()
    {

        return view($this->style . "Distribution/promoterList");
    }

    /**
     * 店铺代理配置
     */
    public function UpdateShopRegionAgentConfig()
    {
        if (request()->isAjax()) {

            // 是否开启区域代理
            $is_open = request()->post("is_open", 0);
            $is_pass_open = request()->post("is_pass_open", 0);
            $manger_rate = request()->post("manger_rate", 0);
            $ragent_rate = request()->post("agent_rate", 0);
            $agent_rate_up = request()->post("agent_rate_up", 0);
            $manger_require = request()->post("manager_require", 0);
            // $shop_config = new NfxShopConfig();
            // $retval = $shop_config->modifyShopConfigIsRegionalAgent($this->instance_id,$is_open);

            // 修改区域代理配置
         /*   $province_rate = $_POST["province_rate"];
            $city_rate = $_POST["city_rate"];
            $district_rate = $_POST["district_rate"];
            $application_require_province = $_POST["application_require_province"];
            $application_require_city = $_POST["application_require_city"];
            $application_require_district = $_POST["application_require_district"];*/

            $province_rate = request()->post("province_rate", 0);
            $city_rate = request()->post("city_rate", 0);
            $district_rate= request()->post("district_rate", 0);
            $application_require_province= request()->post("application_require_province", 0);
            $application_require_city= request()->post("application_require_city", 0);
            $application_require_district= request()->post("application_require_district", 0);

       /*     $city_rate = $_POST["city_rate"];
            $district_rate = $_POST["district_rate"];
            $application_require_province = $_POST["application_require_province"];
            $application_require_city = $_POST["application_require_city"];
            $application_require_district = $_POST["application_require_district"];*/
            $region_agent = new NfxRegionAgent();
            $retval = $region_agent->updateShopRegionAgentConfig($this->instance_id, $province_rate, $city_rate, $district_rate, $application_require_province, $application_require_city, $application_require_district, $is_open,$is_pass_open,$manger_rate,$ragent_rate,$agent_rate_up,$manger_require);
            return ajaxReturn($retval);
        } else {
            $child_menu_list = array(
                array(
                    'url' => "Distribution/regionalAgent",
                    'menu_name' => "基本设置",
                    "active" => 0
                ),
                array(
                    'url' => "Distribution/promoterRegionAgentList",
                    'menu_name' => "人员管理",
                    "active" => 0
                )
            );
            $this->assign('child_menu_list', $child_menu_list);
            $region_agent = new NfxRegionAgent();
            $region_agent_info = $region_agent->getShopRegionAgentConfig($this->instance_id);
            // ($region_agent_info);
            $this->assign("region_agent_info", $region_agent_info);
            return view($this->style . "Distribution/UpdateShopRegionAgentConfig");
        }
    }

    /**
     * 获取区域列表
     *
     * @return multitype:unknown
     */
    public function getAddressList()
    {
        $address = new Address();
        $province_list = $address->getProvinceList();
        $city_list = $address->getCityList();
        $district_list = $address->getDistrictList();
        $address_list = array();
        $address_list["province_list"] = $province_list;
        $address_list["city_list"] = $city_list;
        $address_list["district_list"] = $district_list;
        return $address_list;
    }
    /**
     * 获得省级列表
     * @return multitype:
     */
    public function getProvinceList(){
        $address = new Address();
        $list = $address->getProvinceList();
        return $list;
    }
    /**
     *获得市
     * @return multitype:
     */
    public function getCityList(){
        $province_id = request()->post("province_id", 0);
        $address = new Address();
        $list = $address->getCityList($province_id);
        return $list;
    }
    /**
     * 获得县
     */
    public function getDistrictList(){
        $city_id = request()->post("city_id", 0);
        $address = new Address();
        $list = $address->getDistrictList($city_id);
        return $list;
    }
    /**
     * 区域代理审核
     *
     * @return multitype:number unknown |Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function modifyPromoterRegionAgentIsAudit()
    {
        $is_audit = isset($_POST["is_audit"]) ? $_POST["is_audit"] : "";
        // var_dump($is_audit);exit;
        $region_agent_id = isset($_POST["region_agent_id"]) ? $_POST["region_agent_id"] : "";
        
        $province_id = isset($_POST["province_id"]) ? $_POST["province_id"] : 0;
        $city_id = isset($_POST["city_id"]) ? $_POST["city_id"] : 0;
        $district_id = isset($_POST["district_id"]) ? $_POST["district_id"] : 0;
        
        $region_agent = new NfxRegionAgent();
        $retval = $region_agent->modifyPromoterRegionAgentIsAudit($this->instance_id, $is_audit, $region_agent_id, $province_id, $city_id, $district_id);
        return AjaxReturn($retval);
    }

    /**
     * 区域代理人员管理
     *
     * @return multitype:number unknown |Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    function promoterRegionAgentList()
    {
        if (request()->isAjax()) {
            $region_agent = new NfxRegionAgent();
            $pageindex = isset($_POST['pageIndex']) ? $_POST['pageIndex'] : '';
            $start_date = request()->post('start_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('start_date'));
            $end_date = request()->post('end_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('end_date'));
            $agent_type = ! empty($_POST['agent_type']) ? $_POST['agent_type'] : 0;
            $is_audit = ! empty($_POST['is_audit']) ? $_POST['is_audit'] : '10';
            
            $condition = array('shop_id' => $this->instance_id);
            
            if ($is_audit != 10) {
                $condition["is_audit"] = $is_audit;
            }
            
            if ($agent_type != 0) {
                if($agent_type == 11){
                    $condition["sign"] = 1;
                }else{
                    $condition["level_id"] = $agent_type;
                }

            }
            if ($start_date != 0 && $end_date != 0) {
                $condition["reg_time"] = [
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
                $condition["reg_time"] = [
                    [
                        ">",
                        $start_date
                    ]
                ];
            } elseif ($start_date == 0 && $end_date != 0) {
                $condition["reg_time"] = [
                    [
                        "<",
                        $end_date
                    ]
                ];
            }
            if ($_POST['user_name'] != "") {
                $uid_string = "";
                $where = array(
                    "user_name" => array(
                        "like",
                        "%" . $_POST['user_name'] . "%"
                    )
                );
                $uid_string = $this->getUserUids($where);
                if ($uid_string != "") {
                    $condition["uid"] = array(
                        "in",
                        $uid_string
                    );
                }
            }
            

            
            $list = $region_agent->getPromoterRegionAgent($pageindex, PAGESIZE, $condition, 'reg_time desc');

            return $list;
        } else {
            // $address = new Address();
            // $province_list = $address->getProvinceList();
            // $city_list = $address->getCityList();
            // $district_list = $address->getDistrictList();
            // $this->assign("province_list",$province_list);
            // $this->assign("city_list",$city_list);
            // $this->assign("district_list",$district_list);
            $child_menu_list = array(
               /* array(
                    'url' => "Distribution/regionalAgent",
                    'menu_name' => "基本设置",
                    "active" => 0
                ),*/
                array(
                    'url' => "Distribution/regionalAgentLevelList",
                    'menu_name' => "代理等级",
                    "active" => 0
                ),
                array(
                    'url' => "Distribution/promoterRegionAgentList",
                    'menu_name' => "人员管理",
                    "active" => 1
                )
            );
            $this->assign('child_menu_list', $child_menu_list);
            return view($this->style . "Distribution/promoterRegionAgentList");
        }
    }

    /**
     * 是否开启区域代理
     *
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    public function modifyShopConfigIsRegionalAgent()
    {
        $is_open = request()->post("is_open", 0);
        $shop_config = new NfxShopConfig();
        $retval = $shop_config->modifyShopConfigIsRegionalAgent($this->instance_id, $is_open);
        return AjaxReturn($retval);
    }

    /**
     * 合伙人分红
     */
    public function shareholderDividendsConfig()
    {

        return view($this->style . "Distribution/promoterList");
    }

    
    
    /**
     * 具体项的管理费明细
     */
    public function userAccountRecordsDetail()
    {
        $condition['uid'] = request()->post('uid','');
        $condition['shop_id'] = request()->post('shop_id','');
        $condition['account_type'] = request()->post('type_id','');
        $nfx_user = new NfxUserService();
        
        $account_records_detail = $nfx_user->getNfxUserAccountRecordsList(1, 0, $condition, 'create_time desc');
        return $account_records_detail;
    }    
    
    
    
    
    /**
     * 是否开启合伙人分红
     *
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    public function modifyShopConfigIsPartnerEnable()
    {
        $is_open = request()->post("is_open", 0);
        $shop_config = new NfxShopConfig();
        $retval = $shop_config->modifyShopConfigIsPartnerEnable($this->instance_id, $is_open);
        return AjaxReturn($retval);
    }

    /**
     * 全球分红
     */
    public function globalBonusPoolConfig()
    {
        $shop_config = new NfxShopConfig();
        $shop_config_info = $shop_config->getShopConfigDetail($this->instance_id);
        $this->assign("shop_config_info", $shop_config_info);
        $partner = new NfxPartner();
        $partner_level_list = $partner->getPartnerLevelAll($this->instance_id);
        $this->assign("partner_level_list", $partner_level_list);
        $child_menu_list = array(
            array(
                'url' => "Distribution/globalBonusPoolConfig",
                'menu_name' => "基本设置",
                "active" => 1
            ),
            array(
                'url' => "Distribution/globalBonusPoolPut",
                'menu_name' => "发放分红",
                "active" => 0
            ),
            array(
                'url' => "Distribution/commissionPartnerGlobalRecordsList",
                'menu_name' => "发放记录",
                "active" => 0
            )
        );
        $this->assign('child_menu_list', $child_menu_list);
        // $partner = new NfxPartner();
        // $partner_level_list = $partner->getPartnerLevelAll($this->instance_id);
        // $this->assign("partner_level_list",$partner_level_list);
        return view($this->style . "Distribution/globalBonusPoolConfig");
    }

    /**
     * 全球分红发放流水
     *
     * @return multitype:number unknown |Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function commissionPartnerGlobalRecordsList()
    {
        if (request()->isAjax()) {
            $partner = new NfxPartner();
            $pageindex = isset($_POST['pageIndex']) ? $_POST['pageIndex'] : '';
            $condition = array(
                'shop_id' => $this->instance_id
            );
            $list = $partner->getCommissionPartnerGlobalRecordsList($pageindex, PAGESIZE, $condition, '');
            return $list;
        } else {
            $child_menu_list = array(
                array(
                    'url' => "Distribution/globalBonusPoolConfig",
                    'menu_name' => "基本设置",
                    "active" => 0
                ),
                array(
                    'url' => "Distribution/globalBonusPoolPut",
                    'menu_name' => "发放分红",
                    "active" => 0
                ),
                array(
                    'url' => "Distribution/commissionPartnerGlobalRecordsList",
                    'menu_name' => "发放记录",
                    "active" => 1
                )
            );
            $this->assign('child_menu_list', $child_menu_list);
            return view($this->style . "Distribution/commissionPartnerGlobalRecordsList");
        }
    }

    /**
     * 分红权重设置
     */
    public function updateGlobalBonusPoolConfig()
    {
        if (request()->isAjax()) {
            $is_open = request()->post("is_open", 0);
            $partner_level_string = request()->post("partner_level_string", '');
            $level_array = array();
            $level_array = explode(';', $partner_level_string);
            foreach ($level_array as $k => $v) {
                $level_array[$k] = explode(",", $v);
            }
            $partner = new NfxPartner();
            $retval = $partner->updatePartnerGlobal($level_array, $this->instance_id, $is_open);
            return AjaxReturn($retval);
        } else {
            $child_menu_list = array(
                array(
                    'url' => "Distribution/globalBonusPoolConfig",
                    'menu_name' => "基本设置",
                    "active" => 0
                ),
                array(
                    'url' => "Distribution/globalBonusPoolPut",
                    'menu_name' => "发放分红",
                    "active" => 0
                ),
                array(
                    'url' => "Distribution/commissionPartnerGlobalRecordsList",
                    'menu_name' => "发放记录",
                    "active" => 0
                )
            );
            $this->assign('child_menu_list', $child_menu_list);
            $partner = new NfxPartner();
            $partner_level_list = $partner->getPartnerLevelAll($this->instance_id);
            $this->assign("partner_level_list", $partner_level_list);
            return view($this->style . "Distribution/updateGlobalBonusPoolConfig");
        }
    }

    /**
     * 是否开启全球分红
     *
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    public function modifyShopConfigIsGlobalEnable()
    {
        $is_open = $_POST["is_open"];
        $shop_config = new NfxShopConfig();
        $retval = $shop_config->modifyShopConfigIsGlobalEnable($this->instance_id, $is_open);
        return AjaxReturn($retval);
    }

    /**
     * ******************************************************合伙人 推广员分界线************************************************
     */
    /**
     * 合伙人列表
     */
    public function partnerList()
    {
        if (request()->isAjax()) {
            $partner = new NfxPartner();
            $pageindex = isset($_POST['pageIndex']) ? $_POST['pageIndex'] : '';
            $is_audit = isset($_POST['is_audit']) ? $_POST['is_audit'] : '';
            $condition = array(
                'shop_id' => $this->instance_id
            );
            if ($is_audit != "") {
                $condition["is_audit"] = $is_audit;
            }
            if ($_POST['user_name'] != "") {
                $uid_string = "";
                $where = array(
                    "user_name" => array(
                        "like",
                        "%" . $_POST['user_name'] . "%"
                    )
                );
                $uid_string = $this->getUserUids($where);
                if ($uid_string != "") {
                    $condition["uid"] = array(
                        "in",
                        $uid_string
                    );
                }
            }
            $list = $partner->getPartnerList($pageindex, PAGESIZE, $condition, 'register_time desc');
            return $list;
        } else {
            $child_menu_list = array(
                array(
                    'url' => "Distribution/shareholderDividendsConfig",
                    'menu_name' => "基本设置",
                    "active" => 0
                ),
                array(
                    'url' => "Distribution/partnerList",
                    'menu_name' => "人员管理",
                    "active" => 1
                )
            );
            $partner = new NfxPartner();
            $partner_level_list = $partner->getPartnerLevelAll($this->instance_id);
            $this->assign("partner_level_list", $partner_level_list);
            $this->assign('child_menu_list', $child_menu_list);
            return view($this->style . "Distribution/partnerList");
        }
    }

    /**
     * 合伙人等级列表
     */
    public function partnerLevelList()
    {
        if (request()->isAjax()) {
            $partner = new NfxPartner();
            $pageindex = isset($_POST['pageIndex']) ? $_POST['pageIndex'] : '';
            $condition = isset($_POST['condition']) ? $_POST['condition'] : '';
            $list = $partner->getPartnerLevelList($pageindex, PAGESIZE, $condition, '');
            return $list;
        } else {
            $child_menu_list = array(
                array(
                    'url' => "Distribution/shareholderDividendsConfig",
                    'menu_name' => "基本设置",
                    "active" => 0
                ),
                array(
                    'url' => "Distribution/partnerList",
                    'menu_name' => "人员管理",
                    "active" => 0
                )
            );
            $partner = new NfxPartner();
            $partner_level_list = $partner->getPartnerLevelAll($this->instance_id);
            $this->assign("partner_level_list", $partner_level_list);
            
            $shop_config = new NfxShopConfig();
            $shop_config_info = $shop_config->getShopConfigDetail($this->instance_id);
            $this->assign("shop_config_info", $shop_config_info);
            $this->assign('child_menu_list', $child_menu_list);
            return view($this->style . "Distribution/partnerLevelList");
        }
    }
    
    /**
     * 修改合伙人上级
     */    
    public function modifyPartherParnet()
    {
        $partner_id = request()->post('partner_id','');
        $parent_no = request()->post('parent_no','');
        $shop_id = $this->instance_id;
        $partner = new NfxPartner();
        $res = $partner->modifyPartherParnet($partner_id, $parent_no, $shop_id);
        return AjaxReturn($res);
    }
    
    
    
    
    /**
     * 合伙人审核
     */
    public function partnerAudit()
    {
        $partner_id = request()->post("partner_id", 0);
        $is_audit = request()->post("is_audit", 0);
        $uid = request()->post("uid", 0);

        $partner = new NfxPartner();
        $retval = $partner->partnerAudit($partner_id, $is_audit, $this->instance_id);
        if ($is_audit == 1 && $retval) {
            $promote_reward_rule = new PromoteRewardRule();
            $promote_reward_rule->RegisterPartnerSendPoint($this->instance_id, $uid);
            return AjaxReturn($retval);
        }else{
            return AjaxReturn($retval);
        }
    }

    /**
     * 获取合伙人等级详情
     */
    public function getPartnerLevelDetail()
    {
        $level_id = request()->post("level_id", 0);
        $partner = new NfxPartner();
        $res = $partner->getPartnerLevelDetail($level_id);
        return $res;
    }

    /**
     * 修改合伙人等级
     */
    public function updatePartnerLevel()
    {
        if (request()->isAjax()) {
            $level_id = request()->post("level_id", 0);
            $level_name = request()->post("level_name", '');
            $level_money = request()->post("level_money", '0');
            $commission_rate = request()->post("commission_rate", '0');
            
//             $level_id = $_POST["level_id"];
//             $level_name = $_POST["level_name"];
//             $level_money = $_POST["level_money"];
//             $commission_rate = $_POST["commission_rate"];
            // $global_value =$_POST["global_value"];
            // $global_weight =$_POST["global_weight"];
            $partner = new NfxPartner();
            $retval = $partner->updatePartnerLevel($level_id, $level_name, $level_money, $commission_rate, $this->instance_id);
            return AjaxReturn($retval);
        } else {
            $level_id = $_GET["level_id"];
            $partner = new NfxPartner();
            $partner_level_info = $partner->getPartnerLevelDetail($level_id);
            // var_dump($partner_level_info);
            $this->assign("level_id", $level_id);
            $this->assign("partner_level_info", $partner_level_info);
            return view($this->style . "Distribution/updatePartnerLevel");
        }
    }

    /**
     * 添加合伙人等级
     */
    public function addPartnerLevel()
    {
        if (request()->isAjax()) {
            $level_name = request()->post("level_name", ''); 
            $level_money = request()->post("level_money", '');
            $commission_rate = request()->post("commission_rate", 0);
//             $level_name = $_POST["level_name"];
//             $level_money = $_POST["level_money"];
//             $commission_rate = $_POST["commission_rate"];
            // $global_value =$_POST["global_value"];
            // $global_weight =$_POST["global_weight"];
            $partner = new NfxPartner();
            $retval = $partner->addPartnerLevel($level_money, $level_name, $commission_rate, $this->instance_id);
            return AjaxReturn($retval);
        } else {
            return view($this->style . "Distribution/addPartnerLevel");
        }
    }

    /**
     * 合伙人冻结/解冻
     */
    public function modifyPartnerLock()
    {
        $partner_id = request()->post("partner_id", 0);
        $is_lock = request()->post("is_lock", 0);
        $partner = new NfxPartner();
        $retval = $partner->modifyPartnerLock($partner_id, $is_lock);
        return AjaxReturn($retval);
    }

    /**
     * 发放分红
     */
    public function globalBonusPoolPut()
    {
        if (request()->isAjax()) {
            $start_time = request()->post('start_time') == "" ? 0 : getTimeTurnTimeStamp(request()->post('start_time'));
            $end_time = request()->post('end_time') == "" ? 0 : getTimeTurnTimeStamp(request()->post('end_time'));
            $global_money = ! empty($_POST['global_money']) ? $_POST['global_money'] : 0;
            $partner_global_calculate = new NfxPartnerGlobalCalculate();
            $retval = $partner_global_calculate->getPartnerGlobalCommission($this->instance_id, $start_time, $end_time, $global_money);
            return AjaxReturn($retval);
        } else {
            $child_menu_list = array(
                array(
                    'url' => "Distribution/globalBonusPoolConfig",
                    'menu_name' => "基本设置",
                    "active" => 0
                ),
                array(
                    'url' => "Distribution/globalBonusPoolPut",
                    'menu_name' => "发放分红",
                    "active" => 1
                ),
                array(
                    'url' => "Distribution/commissionPartnerGlobalRecordsList",
                    'menu_name' => "发放记录",
                    "active" => 0
                )
            );
            $this->assign("end_time", date('Y-m-d', time()));
            $this->assign('child_menu_list', $child_menu_list);
            $partner_global_calculate = new NfxPartnerGlobalCalculate();
            $partner_global_calculate_info = $partner_global_calculate->getPartnerGlobalLastInfo($this->instance_id);
            $partner = new NfxPartner();
            $partnerLevelGlobalList = $partner->getPartnerLevelGlobalList($this->instance_id);
            
            $shop_config = new NfxShopConfig();
            $shop_config_info = $shop_config->getShopConfigDetail($this->instance_id);
            $this->assign("is_global_enable", $shop_config_info["is_global_enable"]);
            if(!empty($partner_global_calculate_info)){
                $partner_global_calculate_info["end_time"] = date("Y-m-d", $partner_global_calculate_info["end_time"]);
            }
            $this->assign("partner_global_calculate_info", $partner_global_calculate_info);
            $this->assign("partner_level_global_list", $partnerLevelGlobalList);
            return view($this->style . "Distribution/globalBonusPoolPut");
        }
    }

    /**
     * 查询某个店铺指定之间内可分红金额
     */
    public function getPartnerGlobalLastInfo()
    {
        $start_time = request()->post('start_time') == "" ? 0 : getTimeTurnTimeStamp(request()->post('start_time'));
        $end_time = request()->post('end_time') == "" ? 0 : getTimeTurnTimeStamp(request()->post('end_time'));
//         $start_time = ! empty($_POST['start_time']) ? $_POST['start_time'] : '';
//         $end_time = ! empty($_POST['end_time']) ? $_POST['end_time'] : '';
        $partner_global_calculate = new NfxPartnerGlobalCalculate();
        $partner_global_last_info = $partner_global_calculate->getPartnerGlobalMoney($this->instance_id, $start_time, $end_time);
        return $partner_global_last_info;
    }

    /**
     * 修改所有合伙人等级
     *
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    public function updatePartnerLevelAll()
    {
        $partner_level_string = $_POST["partner_level_string"];
        $is_open = $_POST["is_open"];
        $level_array = array();
        $level_array = explode(';', $partner_level_string);
        foreach ($level_array as $k => $v) {
            $level_array[$k] = explode(",", $v);
        }
        $partner = new NfxPartner();
        $retval = $partner->updatePartnerLevelAll($level_array, $this->instance_id, $is_open);
        return AjaxReturn($retval);
    }

    /**
     * 删除合伙人等级
     *
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    function deletePartnerLevel()
    {
        $level_id = $_POST["level_id"];
        $partner = new NfxPartner();
        $retval = $partner->deletePartnerLevel($this->instance_id, $level_id);
        return ajaxReturn($retval);
    }

    /**
     * 修改合伙人用户等级
     *
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    public function modifyPartnerLevelNum()
    {
        $level_id = $_POST["level_id"];
        $uid = $_POST["uid"];
        $partner = new NfxPartner();
        $retval = $partner->modifyPartnerLevelNum($this->instance_id, $uid, $level_id);
        return ajaxReturn($retval);
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
     * 查寻符合条件的数据并返回id （多个以“,”隔开）
     *
     * @param unknown $condition            
     * @return string
     */
    public function getGoodsCommissionGoodsids($condition)
    {
        $commission_config = new NfxCommissionConfig();
        $list = $commission_config->getGoodsCommiddionAll($condition);
        $goodsid_string = "";
        foreach ($list as $k => $v) {
            $goodsid_string = $goodsid_string . "," . $v["goods_id"];
        }
        if ($goodsid_string != "") {
            $goodsid_string = substr($goodsid_string, 1);
        }
        return $goodsid_string;
    }

    /**
     * 删除推广员等级
     * 
     * @return Ambigous <number, unknown>
     */
    public function deletePromoterLevel()
    {
        $level_id = $_POST["level_id"];
        $promoter = new NfxPromoter();
        $retval = $promoter->deletePromoterLevel($this->instance_id, $level_id);
        return ajaxReturn($retval);
    }

    /**
     * 删除推广员
     * 
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    public function deletePromoter()
    {
        $promoter_id = $_POST["promoter_id"];
        $promoter = new NfxPromoter();
        $retval = $promoter->deletePromoter($this->instance_id, $promoter_id);
        return ajaxReturn($retval);
    }

    /**
     * 修改推广员的等级
     * 
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    public function modifyPromoterLevel()
    {
        $promoter_id = $_POST["promoter_id"];
        $level_id = $_POST["level_id"];
        $promoter = new NfxPromoter();
        $retval = $promoter->modifyPromoterLevel($this->instance_id, $promoter_id, $level_id);
        return ajaxReturn($retval);
    }
    
    /**
     * 修改店铺名称
     */
    public function modifyPromoterShopName(){
        $promoter_id = request()->post("promoter_id", 0);
        $promoter_shop_name = request()->post("promoter_shop_name", 0);
        $promoter = new NfxPromoter();
        $retval = $promoter->modifyShopName($this->instance_id, $promoter_id, $promoter_shop_name);
        return ajaxReturn($retval);
        
    }
    
    /**
     * 默认分销比例
     * @return multitype:unknown
     */
    public function defaultGoodsCommissionRate(){
        if (request()->isAjax()) {
            $default_distribution_commission_rate = request()->post("default_distribution_commission_rate", 0);
            $default_partner_commission_rate = request()->post("default_partner_commission_rate", 0);
            $default_global_commission_rate = request()->post("default_global_commission_rate", 0);
            $default_distribution_team_commission_rate = request()->post("default_distribution_team_commission_rate", 0);
            $default_partner_team_commission_rate = request()->post("default_partner_team_commission_rate", 0);
            $default_regionagent_commission_rate = request()->post("default_regionagent_commission_rate", 0);
            $default_channelagent_commission_rate = request()->post("default_channelagent_commission_rate", 0);
            $shop_id = $this->instance_id;
            $commission_config = new NfxCommissionConfig();
            $retval = $commission_config->setDefaultGoodsCommissionRate($default_distribution_commission_rate, $default_partner_commission_rate, $default_global_commission_rate, $default_distribution_team_commission_rate, $default_partner_team_commission_rate, $default_regionagent_commission_rate, $default_channelagent_commission_rate, $shop_id);
            return AjaxReturn($retval);
        }
        
    }
  
    
    
    /**
     * 我的团队 wyj
     *
     * @return \think\response\View
     */
    public function teamList()
    {
        $nfx_promoter = new NfxPromoter();

        if (request()->isAjax()) {
            $promoter_id = request()->post("promoter_id", 0);
            $team_list = $nfx_promoter->getAdminPromoterTeamList($promoter_id);
            return $team_list;
        }
       
    }
    
    
    /**
     * 验证是否存在该会员编号
     */
    public function  modifyPromoter(){
        $promoter_no = request()->post("promoter_no", '');
        $nfx_promoter = new NfxPromoter();
        $res = $nfx_promoter->modifyPromoterNo($promoter_no);
        return AjaxReturn($res);
    }
    
    /**
     * 修改会员
     */
    public function updateMember()
    {
        if (request()->isAjax()) {
            $nfx_promoter = new NfxPromoter();
            $uid = request()->post('uid', '');
            $promoter_no = request()->post('promoter_no', '');
            $res = $nfx_promoter->updateMemberByAdmin($uid, $promoter_no);
            return AjaxReturn($res);
        }
    }
    
    /**
     * 订单数据excel导出
     */
    public function memberDataExcel(){
        $promoter_id = request()->get("promoter_id", 0);
        $xlsName  = "团队数据列表";
        $xlsCell  = array(
            array('promoter_no','编号'),
            array('nick_name','姓名'),
            array('level_name','等级'),
            array('promoter_shop_name','店铺名称'),
            array('role_name','角色'),
            array('reg_time','申请时间'),
        );
      
        $nfx_promoter = new NfxPromoter();
        $team_list = $nfx_promoter->getAdminPromoterTeamList($promoter_id);
        $list = array();
        foreach($team_list as $k=>$v){
            $list[$k]["promoter_no"] = $v['promoter_info']['promoter_no'];
            $list[$k]["nick_name"] = $v['user_info']['nick_name'];
            $list[$k]["level_name"] = $v['promoter_info']['level_name'];
            $list[$k]["promoter_shop_name"] = $v['promoter_info']['promoter_shop_name'];
            $list[$k]["role_name"] = $v['role_name'];
            $list[$k]["reg_time"] = getTimeStampTurnTime($v['user_info']["reg_time"]);
        }
        dataExcel($xlsName,$xlsCell,$list);
    }
    /**
     * 推广员数据excel导出
     */
    public function promoterDataExcel()
    {
        $xlsName = "推广员列表";
        $xlsCell = array(
            array(
                'promoter_no',
                '推广员编号'
            ),
            array(
                'real_name',
                '姓名'
            ),
            array(
                'level_name',
                '等级'
            ),
            array(
                'promoter_shop_name',
                '店铺名称'
            ),
            array(
                'team',
                '团队'
            ),
            array(
                'order_total',
                '销售总额'
            ),
            array(
                'parent_realname',
                '上级推广员'
            ),
            array(
                'audit_status',
                '状态'
            ),
            array(
                'apply_date',
                '申请时间'
            )
        );
        
        $promoter = new NfxPromoterService();
        $is_audit = request()->get('is_audit', '');
        $start_date = request()->get('start_date') == "" ? 0 : getTimeTurnTimeStamp(request()->get('start_date'));
        $end_date = request()->get('end_date') == "" ? 0 : getTimeTurnTimeStamp(request()->get('end_date'));
        if ($start_date != 0 && $end_date != 0) {
            $condition["regidter_time"] = [
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
            $condition["regidter_time"] = [
                [
                    ">",
                    $start_date
                ]
            ];
        } elseif ($start_date == 0 && $end_date != 0) {
            $condition["regidter_time"] = [
                [
                    "<",
                    $end_date
                ]
            ];
        }
        if ($is_audit === "") {
            $condition["is_audit"] = [
                "<>",
                1
            ];
        } else {
            $condition["is_audit"] = $is_audit;
        }
        
        $uid_string = "";
        $where = array();
        if ($_GET['user_name'] != "") {
            $where["user_name"] = array(
                "like",
                "%" . trim($_GET['user_name']) . "%"
            );
        }
        if ($_GET['user_phone'] != "") {
            $where["user_tel"] = trim($_GET['user_phone']);
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
        $list = $promoter->getPromoterList(1, 0, $condition, 'regidter_time desc');
        $list = $list["data"];
        foreach ($list as $k => $v) {
            $list[$k]["apply_date"] = getTimeStampTurnTime($v["regidter_time"]); // 创建时间
            $list[$k]["team"] = "推广员数:".$v["promoter_num"]."<br/>粉丝数:".$v["fans_num"];  //团队
            $list[$k]["audit_status"] = $v["is_audit"] == 1 ? "已审核" : "已拒绝";
            $list[$k]["parent_realname"] = $v["parent_realname"] != null ? $v["parent_realname"] : "";
        }
        dataExcel($xlsName, $xlsCell, $list);
    }
    
    /**
     * 区域代理取消资格
     *
     * @return multitype:number unknown |Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function removePromoterRegionAgent()
    {
        $region_agent_id = request()->post("region_agent_id", 0);
        $region_agent = new NfxRegionAgent();
        $retval = $region_agent->removePromoterRegionAgent($this->instance_id, $region_agent_id);
        return AjaxReturn($retval);
    }

    /**
     * 晋升申请后台提交
     * @return \multitype
     * niuchao
     */
    public function applypartner(){
        $nfx_partner = new NfxPartner();
        $promoter = new NfxPromoter();
        if (request()->isAjax()) {
            $shop_id = isset($_POST['shop_id']) ? $_POST['shop_id'] : 0;
            $promoter_id = isset($_POST['promoter_id']) ? $_POST['promoter_id'] : '';
            $res = $promoter->getPromoterDetail($promoter_id);
            $retval = $nfx_partner->partnerApplay($shop_id, $res['uid']);
            return AjaxReturn($retval);
        }
    }

    /**
     * @return \multitype|\think\response\View
     * niuchao
     * 添加代理商等级
     */
    public function addRegionalAgentLevel()
    {
        if (request()->isAjax()) {
            $level_name = request()->post("level_name", '');
            $level_money = request()->post("level_money", '');
            $level_num = request()->post("level_num", '');
            $level_rate = request()->post("level_rate", 0);
            $condition = [];
            $condition['level_name'] = $level_name;
            $condition['level_money'] = $level_money;
            $condition['level_num'] = $level_num;
            $condition['level_rate'] = $level_rate;
            $condition['create_time'] = time();
            $RegionAgent = new NfxRegionAgent();
            $retval = $RegionAgent->addRegionalAgentLevel($condition);
            return AjaxReturn($retval);
        } else {
            return view($this->style . "Distribution/addRegionalAgentLevel");
        }
    }

    /**
     * 修改所有合伙人等级
     *
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    public function updateLevelAll()
    {
        $level_string = $_POST["level_string"];
        $level_array = array();
        $level_array = explode(';', $level_string);
        foreach ($level_array as $k => $v) {
            $level_array[$k] = explode(",", $v);
        }
        $partner = new NfxRegionAgent();
        $retval = $partner->updateLevelAll($level_array);
        return AjaxReturn($retval);
    }

    /**
     * 代理商的等级列表
     * @return \think\response\View
     * niuchao
     */
    public function regionalAgentLevelList(){
        $RegionAgent = new NfxRegionAgent();
        $regionagent_level_list = $RegionAgent->getregionalAgentLevelAll();
        $this->assign("regionagent_level_list", $regionagent_level_list);

        $child_menu_list = array(
         /*   array(
                'url' => "Distribution/regionalAgent",
                'menu_name' => "基本设置",
                "active" => 0
            ),*/
            array(
                'url' => "Distribution/regionalAgentLevelList",
                'menu_name' => "代理等级",
                "active" => 1
            ),
            array(
                'url' => "Distribution/promoterRegionAgentList",
                'menu_name' => "人员管理",
                "active" => 0
            )
        );
        $this->assign('child_menu_list', $child_menu_list);
        return view($this->style . "Distribution/regionalAgentLevelList");
    }

    /**
     * 删除代理等级
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     * niuchao
     */
    public function deleteRegionalAgentLevel()
    {
        $level_id = $_POST["level_id"];
        $partner = new NfxRegionAgent();
        $retval = $partner->deleteRegionalAgentLevel($level_id);
        return ajaxReturn($retval);
    }




}