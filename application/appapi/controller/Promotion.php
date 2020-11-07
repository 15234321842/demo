<?php

/* 
 * Created by 比谷网络
 * User jt
 */
namespace app\appapi\controller;

use data\model\ConfigModel;
use data\model\NsCouponTypeModel;
use data\service\Promotion as PromotionService;
use data\service\Goods as GoodsService;
use data\model\NsPromotionDiscountGoodsModel;
use data\model\NsCouponModel;
use think\Db;
use think\Validate;
use think\Request;
use data\service\User;
use data\model\NsRewardRuleModel;
use data\service\Config;
use data\service\promotion\PromoteRewardRule;
use data\service\Member;
use data\service\Platform;

class Promotion extends BaseController{
    /**
     * 获取限时折扣列表
     */
    public function getDiscountList(){
        $discount = new PromotionService();
        $status=request()->post('status','1');
        if ($status !== '-1') {
            $condition['status'] = $status;
            $list = $discount->getPromotionDiscountList('','', $condition);
        } else {
            $list = $discount->getPromotionDiscountList('','');
        }
        if($list[data]){
            $this->success('获取限时折扣活动列表成功', $list[data]);
        }else{
            $this->error('获取限时折扣活动列表失败');
        }
    }

    
    /**
     * 获取限时折扣分类下的限时秒杀列表
     */
    public function getDiscountDetail(){

        $lunbo_id=6668;
        $lunbo = new Platform();
        $domain_name = \think\Request::instance()->domain();
        $slide = $lunbo -> getPlatformAdvPositionDetail($lunbo_id);
        foreach($slide['adv_list'] as $k => $v){
            $slide['adv_list'][$k]['adv_image'] = $domain_name.'/'.$v['adv_image'];
        }

        $page_index = request()->post("page_index", 1);
        $page_size = request()->post('page_size', PAGESIZE);
        $discount = new PromotionService();
        $condition['status'] = 1;
        $list = $discount->getPromotionDiscountList($page_index,$page_size, $condition);
        foreach($list[data] as $k=>$v){
            $discount_id[]=$list[data][$k]['discount_id'];
        }

        $param=['slide'=>$slide];
        if (!$discount_id) {
            $this->success("没有获取到折扣信息",$param);
        }

        foreach ($discount_id as $k => $v) {
            $discount = new PromotionService();
            $detail[] = $discount->getPromotionDiscountDetail($v);
        }
        if($detail){
            $param['detail']=$detail;
            $this->success("获取活动详情成功", $param);
        }else{

            $this->success("未获取活动详情",$param);
        }
    }

    
    /**
     * 限时秒杀商品详情
     */
    public function getDetail(){
        $goodsId= request()->post('goods_id');
        $goods_detail = new GoodsService();
        $goods = $goods_detail->getGoodsDetail($goodsId);
        //根据商品id查询折扣信息
        if($goods){
            $res=Db::table('ns_promotion_discount_goods')->where("goods_id",$goodsId)->select();
            $goods[discount]=$res[0];
        }
        if($goods){
            $this->success("秒杀商品详情获取成功", $goods);
        }else{
            $this->error("秒杀商品详情获取失败");
        }
    }
    
    /**
     * 所有红包列表
     */
    public function couponTypeList(){
        $page_index = request()->post("page_index", 1);
        $page_size = request()->post("page_size", PAGESIZE);
        $search_text = request()->post('search_text', '');
        $coupon = new PromotionService();
        $condition = array(
//            'shop_id' => $this->instance_id,
            'coupon_name' => array(
                'like',
                '%' . $search_text . '%'
            )
        );
        $list = $coupon->getCouponTypeList($page_index,$page_size,$condition, 'start_time desc');
        if($list[data]){
            $this->success("列表获取成功", $list);
        }else{
            $this->error("列表获取失败");
        }
    }

    /**
     * 会员中心红包列表
     * @param uid
     * @return
     */
    public function getUidCoupon(){
        $uid=$this->getUserId();
        $page_index = request()->post("page_index", 1);
        $page_size = request()->post("page_size", PAGESIZE);
        //根据用户id查询用户的已有的红包信息
        $condition = array(
            'uid'=>$uid,
        );
        $coupon=new PromotionService();
        $condition["nc.state"] = 1;
        $uidCoupon=$coupon->getCouponDetail($page_index,$page_size,$condition,'fetch_time desc');
        foreach($uidCoupon[data] as $k=>$v){
            $uidCoupon[data][$k]['start_time']=date("Y-m-d ", $v['start_time']);
            $uidCoupon[data][$k]['end_time']=date("Y-m-d ", $v['end_time']);
            if($v['range_type'] == 1){
                $uidCoupon[data][$k]['range_type']="全场商品可用";
            }elseif ($v['range_type'] == 0){
                $uidCoupon[data][$k]['range_type']="部分商品可用";
            }
        }
        if($uidCoupon[data]){
            $this->success("该用户红包获取成功",$uidCoupon);
        }else{
            $this->success("该用户红包获取成功",$uidCoupon);
        }
    }


//    /**
//     * 注册送红包
//     */
//    public function regPostCoupon($uid){
//        $page_index = request()->post("page_index", 1);
//        $page_size = request()->post("page_size", PAGESIZE);
//        //先判断是否开启注册送红包
//        $configModel=new ConfigModel();
//        $regdata=$configModel->where('key',REGISTER_COUPON)->find();
//        if($regdata['is_use']==1) {
//            //获取注册送红包类型id
//            $reward_rule = new NsRewardRuleModel();
//            $couTpyeId = $reward_rule->field('reg_coupon')->find();
//
//            //获取是否有可用的红包
//            $coupon = new PromotionService();
//            $condition["nc.state"] = 0;
//            $condition["nc.coupon_type_id"] = $couTpyeId['reg_coupon'];
//            $couponList = $coupon->getCouponDetail($page_index, $page_size, $condition);//未领取红包列表
//            if ($couponList && $uid) {
//                $coupon_id = $couponList[data][0]['coupon_id'];//注册送查询未领取红包列表的第一张
//                $res = $coupon->saveUidCoupon($coupon_id, $uid);//自动给注册会员发送红包，并返回红包信息
//            }
//        }
        // 注册成功送红包
//        $Config = new Config();
//        $integralConfig = $Config->getIntegralConfig($this->instance_id);
//        if ($integralConfig['register_coupon'] == 1) {
//            $rewardRule = new PromoteRewardRule();
//            $coupon_result = $rewardRule->getRewardRuleDetail($this->instance_id);
//            if ($coupon_result['reg_coupon'] != 0) {
//                $member = new Member();
//                $retval = $member->memberGetCoupon($uid, $coupon_result['reg_coupon'], 2);
//            }
//        }
//    }
}

