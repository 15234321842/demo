<?php
/**
 * Promotion.php
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

use data\model\NsCouponTypeModel;
use data\service\Address;
use data\service\Config;
use data\service\promotion\PromoteRewardRule;
use data\service\Promotion as PromotionService;
use data\service\GoodsCategory as GoodsCategory;
use data\service\Member;

/**
 * 营销控制器
 *
 * @author Administrator
 *        
 */
class Promotion extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 红包类型列表
     *
     * @return multitype:number unknown |Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function couponTypeList()
    {
        if (request()->isAjax()) {
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post("page_size", PAGESIZE);
            $search_text = request()->post('search_text', '');
            $coupon = new PromotionService();
            $condition = array(
                'shop_id' => $this->instance_id,
                'coupon_name' => array(
                    'like',
                    '%' . $search_text . '%'
                )
            );
            $list = $coupon->getCouponTypeList($page_index, $page_size, $condition, 'start_time desc');
            return $list;
        } else {
            return view($this->style . "Promotion/couponTypeList");
        }
    }

    /**
     * 红包详情
     */
     public function couponDetailList(){
         if (request()->isAjax()) {
             $page_index = request()->post("page_index", 1);
             $page_size = request()->post("page_size", PAGESIZE);
             $state = request()->post('state', '');
             $couponType = request()->post('couponType', '');

             $coupon = new PromotionService();
             if ($state != "") {
                 $condition["nc.state"] = $state;
             }
             if ($couponType != "") {
                 $condition["nc.coupon_type_id"] = $couponType;
             }
             $list = $coupon->getCouponDetail($page_index, $page_size, $condition);

             return $list;
         } else {
             $couponType = new PromotionService();
             $res=$couponType->getCouponTypeList();
             $state=array("未领用","已领用","已使用","已过期");
             $this->assign('couponType',$res[data]);
             $this->assign('state',$state);
             return view($this->style . "Promotion/couponDetailList");
         }
     }

    /**
     * 删除红包类型
     */
    public function delcoupontype()
    {
        $coupon_type_id = request()->post('coupon_type_id', '');
        if (empty($coupon_type_id)) {
            $this->error("没有获取到红包信息");
        }
        $coupon = new PromotionService();
        $res = $coupon->deletecouponType($coupon_type_id);
        return AjaxReturn($res);
    }

    /**
     * 添加红包类型
     */
    public function addCouponType()
    {
        if (request()->isAjax()) {
            $coupon_name = request()->post('coupon_name', '');
            $money = request()->post('money', '');
            $money1 = request()->post('money1', '');
            $count = request()->post('count', '');
            $max_fetch = request()->post('max_fetch', '');
            $at_least = request()->post('at_least', '');
            $need_user_level = request()->post('need_user_level', '');
            $range_type = request()->post('range_type', '');
            $start_time = request()->post('start_time', '');
            $end_time = request()->post('end_time', '');
            $is_show = request()->post('is_show', '');
            $goods_list = request()->post('goods_list', '');
            $coupon = new PromotionService();
            if(!$money1){
                //添加定额红包
                $retval = $coupon->addCouponType($coupon_name, $money, $count, $max_fetch, $at_least, $need_user_level, $range_type, $start_time, $end_time, $is_show, $goods_list);
            }else{
                //添加随机面额红包
                $retval = $coupon->addCouponType1($coupon_name, $money,$money1, $count, $max_fetch, $at_least, $need_user_level, $range_type, $start_time, $end_time, $is_show, $goods_list);
            }
            return AjaxReturn($retval);
        } else {
            $goodsCategory = new GoodsCategory();
            $oneGoodsCategory = $goodsCategory->getGoodsCategoryListByParentId(0);
            $this->assign("oneGoodsCategory", $oneGoodsCategory);
            return view($this->style . "Promotion/addCouponType");
        }
    }

    public function updateCouponType()
    {
        $coupon = new PromotionService();
        if (request()->isAjax()) {
            $coupon_type_id = request()->post('coupon_type_id', '');
            $coupon_name = request()->post('coupon_name', '');
            $money = request()->post('money', '');
            $money1 = request()->post('money1', '');
            $count = request()->post('count', '');
            $repair_count = request()->post('repair_count', '');
            $max_fetch = request()->post('max_fetch', '');
            $at_least = request()->post('at_least', '');
            $need_user_level = request()->post('need_user_level', '');
            $range_type = request()->post('range_type', '');
            $start_time = request()->post('start_time', '');
            $end_time = request()->post('end_time', '');
            $is_show = request()->post('is_show', '');
            $goods_list = request()->post('goods_list', '');
            if(!$money1){
                $retval = $coupon->updateCouponType($coupon_type_id, $coupon_name, $money, $count, $repair_count, $max_fetch, $at_least, $need_user_level, $range_type, $start_time, $end_time, $is_show, $goods_list);
            }else{

                $retval = $coupon->updateCouponType1($coupon_type_id, $coupon_name, $money, $money1, $count, $repair_count, $max_fetch, $at_least, $need_user_level, $range_type, $start_time, $end_time, $is_show, $goods_list);
            }

            return AjaxReturn($retval);
        } else {
            $coupon_type_id = request()->get('coupon_type_id', 0);
            if ($coupon_type_id == 0) {
                $this->error("没有获取到类型");
            }
            $coupon_type_data = $coupon->getCouponTypeDetail($coupon_type_id);
            $str=$coupon_type_data['money'];
            if($coupon_type_data['coupon_type']==1){
                $str=explode('-', $str);
                $coupon_type_data['money']=$str[0];
                $coupon_type_data['money1']=$str[1];
            }
            $goods_id_array = array();
            foreach ($coupon_type_data['goods_list'] as $k => $v) {
                $goods_id_array[] = $v['goods_id'];
            }
            $coupon_type_data['goods_id_array'] = $goods_id_array;
            $this->assign("coupon_type_info", $coupon_type_data);
            
            return view($this->style . "Promotion/updateCouponType");
        }
    }

    /**
     * 获取红包详情
     */
    public function getCouponTypeInfo()
    {
        $coupon = new PromotionService();
        $coupon_type_id = request()->post('coupon_type_id', '');
        $coupon_type_data = $coupon->getCouponTypeDetail($coupon_type_id);
        return $coupon_type_data;
    }

    /**
     * 功能：积分管理
     * 创建：左骐羽
     * 时间：2016年12月8日15:02:16
     */
    public function pointConfig()
    {
        $pointConfig = new PromotionService();
        if (request()->isAjax()) {
            $convert_rate = request()->post('convert_rate', '');
            $is_open = request()->post('is_open', 0);
            $desc = request()->post('desc', 0);
            $tax = request()->post('tax', 0);
            $poundage = request()->post('poundage', 0);
            $retval = $pointConfig->setPointConfig($convert_rate, $is_open, $desc,$tax,$poundage);
            return AjaxReturn($retval);
        }
        $pointconfiginfo = $pointConfig->getPointConfig();
        $this->assign("pointconfiginfo", $pointconfiginfo);
        return view($this->style . "Promotion/pointConfig");
    }

    /**
     * 赠品列表
     * wzy
     */
    public function giftList()
    {
        $child_menu_list = array(
            array(
                'url' => "Promotion/giftList",
                'menu_name' => "赠品列表",
                "active" => 1
            ),
            array(
                'url' => "promotion/giftGrantRecordsList",
                'menu_name' => "赠品发放记录",
                "active" => 0
            )
        );
        
        $this->assign("child_menu_list", $child_menu_list);
        
        if (request()->isAjax()) {
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post("page_size", PAGESIZE);
            $search_text = request()->post('search_text');
            $type = request()->post("type", 0);
            $gift = new PromotionService();
            $condition = array(
                'shop_id' => $this->instance_id,
                'gift_name' => array(
                    'like',
                    '%' . $search_text . '%'
                )
            );
            
            if ($type == 1) {
                $condition["start_time"] = [
                    "LT",
                    time()
                ];
                $condition["end_time"] = [
                    "GT",
                    time()
                ];
            }
            
            $list = $gift->getPromotionGiftList($page_index, $page_size, $condition);
            return $list;
        }
        return view($this->style . "Promotion/giftList");
    }

    /**
     * 赠品发放记录列表
     * 创建时间：2018年1月25日16:11:39 全栈小学生
     *
     * @return Ambigous <\data\model\multitype:unknown, multitype:unknown number >|Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function giftGrantRecordsList()
    {
        $child_menu_list = array(
            array(
                'url' => "Promotion/giftList",
                'menu_name' => "赠品列表",
                "active" => 0
            ),
            array(
                'url' => "promotion/giftGrantRecordsList",
                'menu_name' => "赠品发放记录",
                "active" => 1
            )
        );
        
        $this->assign("child_menu_list", $child_menu_list);
        if (request()->isAjax()) {
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post("page_size", PAGESIZE);
            $search_text = request()->post("search_text", "");
            $condition['pgr.gift_name'] = [
                'like',
                "%$search_text%"
            ];
            $gift = new PromotionService();
            $list = $gift->getPromotionGiftGrantRecordsList($page_index, $page_size, $condition, "pgr.id desc");
            return $list;
        }
        return view($this->style . "Promotion/giftGrantRecordsList");
    }

    /**
     * 添加赠品
     *
     * @return \think\response\View
     */
    public function addGift()
    {
        if (request()->isAjax()) {
            $shop_id = $this->instance_id;
            $gift_name = request()->post('gift_name', ''); // 赠品活动名称
            $start_time = request()->post('start_time', ''); // 赠品活动开始时间
            $end_time = request()->post('end_time', ''); // 赠品活动结束时间
            $goods_id = request()->post('goods_id', ''); // 要赠送的商品id
            $days = request()->post('days', ''); // 领取有效期/天（0表示不限），2.0版本不用
            $max_num = request()->post('max_num', ''); // 领取限制(次/人 (0表示不限领取次数))，2.0版本不用
            $gift = new PromotionService();
            $res = $gift->addPromotionGift($shop_id, $gift_name, $start_time, $end_time, $days, $max_num, $goods_id);
            return AjaxReturn($res);
        }
        return view($this->style . "Promotion/addGift");
    }

    /**
     * 修改赠品
     *
     * @return \think\response\View
     */
    public function updateGift()
    {
        $gift = new PromotionService();
        if (request()->isAjax()) {
            $gift_id = request()->post('gift_id', '');
            $shop_id = $this->instance_id;
            $gift_name = request()->post('gift_name', '');
            $start_time = request()->post('start_time', '');
            $end_time = request()->post('end_time', '');
            $days = request()->post('days', '');
            $max_num = request()->post('max_num', '');
            $goods_id = request()->post('goods_id', '');
            $res = $gift->updatePromotionGift($gift_id, $shop_id, $gift_name, $start_time, $end_time, $days, $max_num, $goods_id);
            return AjaxReturn($res);
        } else {
            $gift_id = request()->get('gift_id', 0);
            if (! is_numeric($gift_id)) {
                $this->error('未获取到信息');
            }
            $info = $gift->getPromotionGiftDetail($gift_id);
            $this->assign('info', $info);
            return view($this->style . "Promotion/updateGift");
        }
    }

    /**
     * 获取赠品 详情
     *
     * @param unknown $gift_id            
     */
    public function getGiftInfo($gift_id)
    {
        $gift = new PromotionService();
        $info = $gift->getPromotionGiftDetail($gift_id);
        return $info;
    }

    /**
     * 删除赠品
     *
     * @return unknown[]
     */
    public function deleteGift()
    {
        $gift_id = request()->post("gift_id", 0);
        $gift = new PromotionService();
        $res = $gift->deletePromotionGift($gift_id);
        return $res;
    }

    /**
     * 满减送 列表
     */
    public function mansongList()
    {
        if (request()->isAjax()) {
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $search_text = request()->post('search_text', '');
            $status = request()->post('status', '');
            $condition = array(
                'shop_id' => $this->instance_id,
                'mansong_name' => array(
                    'like',
                    '%' . $search_text . '%'
                )
            );
            $mansong = new PromotionService();
            if ($status !== '-1') {
                $condition['status'] = $status;
                $list = $mansong->getPromotionMansongList($page_index, $page_size, $condition);
            } else {
                $list = $mansong->getPromotionMansongList($page_index, $page_size, $condition);
            }
            return $list;
        }
        
        $status = request()->get('status', - 1);
        $this->assign("status", $status);
        $child_menu_list = array(
            array(
                'url' => "promotion/mansonglist",
                'menu_name' => "全部",
                "active" => $status == '-1' ? 1 : 0
            ),
            array(
                'url' => "promotion/mansonglist?status=0",
                'menu_name' => "未发布",
                "active" => $status == 0 ? 1 : 0
            ),
            array(
                'url' => "promotion/mansonglist?status=1",
                'menu_name' => "进行中",
                "active" => $status == 1 ? 1 : 0
            ),
            array(
                'url' => "promotion/mansonglist?status=3",
                'menu_name' => "已关闭",
                "active" => $status == 3 ? 1 : 0
            ),
            array(
                'url' => "promotion/mansonglist?status=4",
                'menu_name' => "已结束",
                "active" => $status == 4 ? 1 : 0
            )
        );
        $this->assign('child_menu_list', $child_menu_list);
        return view($this->style . "Promotion/mansongList");
    }

    /**
     * 添加满减送活动
     *
     * @return \think\response\View
     */
    public function addMansong()
    {
        $mansong = new PromotionService();
        if (request()->isAjax()) {
            $mansong_name = request()->post('mansong_name', '');
            $start_time = request()->post('start_time', '');
            $end_time = request()->post('end_time', '');
            $shop_id = $this->instance_id;
            $type = request()->post('type', '');
            $range_type = request()->post('range_type', '');
            $rule = request()->post('rule', '');
            $goods_id_array = request()->post('goods_id_array', '');
            $res = $mansong->addPromotionMansong($mansong_name, $start_time, $end_time, $shop_id, '', $type, $range_type, $rule, $goods_id_array);
            return AjaxReturn($res);
        } else {
            return view($this->style . "Promotion/addMansong");
        }
    }

    /**
     * 修改 满减送活动
     */
    public function updateMansong()
    {
        $mansong = new PromotionService();
        if (request()->isAjax()) {
            $mansong_id = request()->post('mansong_id', '');
            $mansong_name = request()->post('mansong_name', '');
            $start_time = request()->post('start_time', '');
            $end_time = request()->post('end_time', '');
            $shop_id = $this->instance_id;
            $type = request()->post('type', '');
            $range_type = request()->post('range_type', '');
            $rule = request()->post('rule', '');
            $goods_id_array = request()->post('goods_id_array', '');
            $res = $mansong->updatePromotionMansong($mansong_id, $mansong_name, $start_time, $end_time, $shop_id, '', $type, $range_type, $rule, $goods_id_array);
            return AjaxReturn($res);
        } else {
            $mansong_id = request()->get('mansong_id', '');
            if (! is_numeric($mansong_id)) {
                $this->error('未获取到信息');
            }
            $info = $mansong->getPromotionMansongDetail($mansong_id);
            $condition = array(
                'shop_id' => $this->instance_id
            );
            $coupon_type_list = $mansong->getCouponTypeList(1, 0, $condition);
            $gift_list = $mansong->getPromotionGiftList(1, 0, $condition);
            $this->assign('coupon_type_list', $coupon_type_list);
            $this->assign('gift_list', $gift_list);
            $this->assign('mansong_info', $info);
            return view($this->style . "Promotion/updateMansong");
        }
    }

    /**
     * 获取限时折扣；列表
     */
    public function getDiscountList()
    {
        if (request()->isAjax()) {
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $search_text = request()->post('search_text', '');
            $status = request()->post('status', '');
            $discount = new PromotionService();
            
            $condition = array(
                'shop_id' => $this->instance_id,
                'discount_name' => array(
                    'like',
                    '%' . $search_text . '%'
                )
            );
            if ($status !== '-1') {
                $condition['status'] = $status;
                $list = $discount->getPromotionDiscountList($page_index, $page_size, $condition);
            } else {
                $list = $discount->getPromotionDiscountList($page_index, $page_size, $condition);
            }
            
            return $list;
        }
        
        $status = request()->get('status', - 1);
        $this->assign("status", $status);
        $child_menu_list = array(
            array(
                'url' => "promotion/getdiscountList",
                'menu_name' => "全部",
                "active" => $status == '-1' ? 1 : 0
            ),
            array(
                'url' => "promotion/getdiscountList?status=0",
                'menu_name' => "未发布",
                "active" => $status == 0 ? 1 : 0
            ),
            array(
                'url' => "promotion/getdiscountList?status=1",
                'menu_name' => "进行中",
                "active" => $status == 1 ? 1 : 0
            ),
            array(
                'url' => "promotion/getdiscountList?status=3",
                'menu_name' => "已关闭",
                "active" => $status == 3 ? 1 : 0
            ),
            array(
                'url' => "promotion/getdiscountList?status=4",
                'menu_name' => "已结束",
                "active" => $status == 4 ? 1 : 0
            )
        );
        $this->assign('child_menu_list', $child_menu_list);
        
        return view($this->style . "Promotion/getDiscountList");
    }

    /**
     * 添加限时折扣
     */
    public function addDiscount()
    {
        // 查找一级商品分类
        $goodsCategory = new GoodsCategory();
        $oneGoodsCategory = $goodsCategory->getGoodsCategoryListByParentId(0);
        $this->assign("oneGoodsCategory", $oneGoodsCategory);
        if (request()->isAjax()) {
            $discount = new PromotionService();
            $discount_name = request()->post('discount_name', '');
            $start_time = request()->post('start_time', '');
            $end_time = request()->post('end_time', '');
            $remark = '';
            $goods_id_array = request()->post('goods_id_array', '');
            $retval = $discount->addPromotiondiscount($discount_name, $start_time, $end_time, $remark, $goods_id_array);
            return AjaxReturn($retval);
        }
        return view($this->style . "Promotion/addDiscount");
    }

    /**
     * 修改限时折扣
     */
    public function updateDiscount()
    {
        if (request()->isAjax()) {
            $discount = new PromotionService();
            $discount_id = request()->post('discount_id', '');
            $discount_name = request()->post('discount_name', '');
            $start_time = request()->post('start_time', '');
            $end_time = request()->post('end_time', '');
            $remark = '';
            $goods_id_array = request()->post('goods_id_array', '');
            $retval = $discount->updatePromotionDiscount($discount_id, $discount_name, $start_time, $end_time, $remark, $goods_id_array);
            return AjaxReturn($retval);
        }
        $info = $this->getDiscountDetail();
        if (! empty($info['goods_list'])) {
            foreach ($info['goods_list'] as $k => $v) {
                $goods_id_array[] = $v['goods_id'];
            }
        }
        $info['goods_id_array'] = $goods_id_array;
        $this->assign("info", $info);
        return view($this->style . "Promotion/updateDiscount");
    }

    /**
     * 获取限时折扣详情
     */
    public function getDiscountDetail()
    {
        $discount_id = request()->get('discount_id', '');
        if (! is_numeric($discount_id)) {
            $this->error("没有获取到折扣信息");
        }
        $discount = new PromotionService();
        $detail = $discount->getPromotionDiscountDetail($discount_id);
        return $detail;
    }

    /**
     * 获取满减送详情
     */
    public function getMansongDetail()
    {
        $mansong_id = request()->get('mansong_id', '');
        if (! is_numeric($mansong_id)) {
            $this->error("没有获取到满减送信息");
        }
        $mansong = new PromotionService();
        $detail = $mansong->getPromotionMansongDetail($mansong_id);
        return $detail;
    }

    /**
     * 删除限时折扣
     */
    public function delDiscount()
    {
        $discount_id = request()->post('discount_id', '');
        if (empty($discount_id)) {
            $this->error("没有获取到折扣信息");
        }
        $discount = new PromotionService();
        $res = $discount->delPromotionDiscount($discount_id);
        return AjaxReturn($res);
    }

    /**
     * 关闭正在进行的限时折扣
     */
    public function closeDiscount()
    {
        $discount_id = request()->post('discount_id', '');
        if (! is_numeric($discount_id)) {
            $this->error("没有获取到折扣信息");
        }
        $discount = new PromotionService();
        $res = $discount->closePromotionDiscount($discount_id);
        return AjaxReturn($res);
    }

    /**
     * 删除满减送活动
     *
     * @return unknown[]
     */
    public function delMansong()
    {
        $mansong_id = request()->post('mansong_id', '');
        if (empty($mansong_id)) {
            $this->error("没有获取到满减送信息");
        }
        $mansong = new PromotionService();
        $res = $mansong->delPromotionMansong($mansong_id);
        return AjaxReturn($res);
    }

    /**
     * 关闭满减送活动
     *
     * @return unknown[]
     */
    public function closeMansong()
    {
        $mansong_id = request()->post('mansong_id', '');
        if (! is_numeric($mansong_id)) {
            $this->error("没有获取到满减送信息");
        }
        $mansong = new PromotionService();
        $res = $mansong->closePromotionMansong($mansong_id);
        return AjaxReturn($res);
    }

    /**
     * 满额包邮
     */
    public function fullShipping()
    {
        $full = new PromotionService();
        if (request()->isAjax()) {
            $is_open = request()->post('is_open', '');
            $full_mail_money = request()->post('full_mail_money', '');
            $no_mail_province_id_array = request()->post('no_mail_province_id_array', '');
            $no_mail_city_id_array = request()->post("no_mail_city_id_array", '');
            $res = $full->updatePromotionFullMail(0, $is_open, $full_mail_money, $no_mail_province_id_array, $no_mail_city_id_array);
            return AjaxReturn($res);
        } else {
            $info = $full->getPromotionFullMail($this->instance_id);
            $this->assign("info", $info);
            $existing_address_list['province_id_array'] = explode(',', $info['no_mail_province_id_array']);
            $existing_address_list['city_id_array'] = explode(',', $info['no_mail_city_id_array']);
            $address = new Address();
            // 目前只支持省市，不支持区县，在页面上不会体现 2017年9月14日 19:18:08 王永杰
            $address_list = $address->getAreaTree($existing_address_list);
            $this->assign("address_list", $address_list);
            $no_mail_province_id_array = '';
            if (! empty($existing_address_list['province_id_array'])) {
                foreach ($existing_address_list['province_id_array'] as $v) {
                    $no_mail_province_id_array[] = $address->getProvinceName($v);
                }
            }
            $no_mail_province = implode(',', $no_mail_province_id_array);
            $this->assign("no_mail_province", $no_mail_province);
            return view($this->style . "Promotion/fullShipping");
        }
    }

    /**
     * 单店基础版积分奖励
     */
    public function integral()
    {
        $rewardRule = new PromoteRewardRule();
        if (request()->isAjax()) {
            $shop_id = $this->instance_id;
            $sign_point = request()->post('sign_point', 0);
            $share_point = request()->post('share_point', 0);
            $reg_member_self_point = request()->post('reg_member_self_point', 0);
            $reg_member_one_point = 0;
            $reg_member_two_point = 0;
            $reg_member_three_point = 0;
            $reg_promoter_self_point = 0;
            $reg_promoter_one_point = 0;
            $reg_promoter_two_point = 0;
            $reg_promoter_three_point = 0;
            $reg_partner_self_point = 0;
            $reg_partner_one_point = 0;
            $reg_partner_two_point = 0;
            $reg_partner_three_point = 0;
            $click_point = request()->post("click_point", 0);
            $comment_point = request()->post("comment_point", 0);
            
            $reg_coupon = request()->post("reg_coupon", 0);
            $click_coupon = request()->post("click_coupon", 0);
            $comment_coupon = request()->post("comment_coupon", 0);
            $sign_coupon = request()->post("sign_coupon", 0);
            $share_coupon = request()->post("share_coupon", 0);
            
            $res = $rewardRule->setPointRewardRule($shop_id, $sign_point, $share_point, $reg_member_self_point, $reg_member_one_point, $reg_member_two_point, $reg_member_three_point, $reg_promoter_self_point, $reg_promoter_one_point, $reg_promoter_two_point, $reg_promoter_three_point, $reg_partner_self_point, $reg_partner_one_point, $reg_partner_two_point, $reg_partner_three_point, $click_point, $comment_point, $reg_coupon, $click_coupon, $comment_coupon, $sign_coupon, $share_coupon);
            return AjaxReturn($res);
        }
        $res = $rewardRule->getRewardRuleDetail($this->instance_id);
        $Config = new Config();
        $integralConfig = $Config->getIntegralConfig($this->instance_id);
        $coupon = new PromotionService();
        $condition = array(
            'shop_id' => $this->instance_id,
            'start_time' => array(
                'ELT',
                time()
            ),
            'end_time' => array(
                'EGT',
                time()
            )
        );
        $couponlist = $coupon->getCouponTypeList($page_index, $page_size, $condition, 'start_time desc');
        $this->assign("res", $res);
        $this->assign("integralConfig", $integralConfig);
        $this->assign("couponlist", $couponlist['data']);
        return view($this->style . "Promotion/integral");
    }

    /**
     *
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    public function setIntegralAjax()
    {
        $register = request()->post('register', 0);
        $sign = request()->post('sign', 0);
        $share = request()->post('share', 0);
        $reg_coupon = request()->post('reg_coupon', 0);
        $click_coupon = request()->post('click_coupon', 0);
        $comment_coupon = request()->post('comment_coupon', 0);
        $sign_coupon = request()->post('sign_coupon', 0);
        $share_coupon = request()->post('share_coupon', 0);
        $Config = new Config();
        $retval = $Config->SetIntegralConfig($this->instance_id, $register, $sign, $share, $reg_coupon, $click_coupon, $comment_coupon, $sign_coupon, $share_coupon);
        return AjaxReturn($retval);
    }

    /**
     * 组合套餐列表
     * 创建时间：2017年12月4日 17:55:38 王永杰
     */
    public function comboPackagePromotionList()
    {
        if (request()->isAjax()) {
            $promotionService = new PromotionService();
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post("page_size", PAGESIZE);
            $combo_package_name = request()->post("search_text", "");
            $condition["combo_package_name"] = array(
                "like",
                "%$combo_package_name%"
            );
            $list = $promotionService->getComboPackageList($page_index, $page_size, $condition);
            return $list;
        }
        return view($this->style . "Promotion/comboPackagePromotionList");
    }

    /**
     * 组合套餐编辑
     * 创建时间：2017年12月4日 18:05:19 王永杰
     */
    public function comboPackagePromotionEdit()
    {
        $id = request()->get("id", 0);
        $promotionService = new PromotionService();
        $info = $promotionService->getComboPackageDetail($id);
        $this->assign("info", $info);
        $this->assign("id", $id);
        return view($this->style . "Promotion/comboPackagePromotionEdit");
    }

    /**
     * 添加或编辑组合套餐
     */
    public function addOrEditComboPackage()
    {
        $promotionService = new PromotionService();
        $id = request()->post("id", 0);
        $combo_package_name = request()->post("combo_package_name", "");
        $combo_package_price = request()->post("combo_package_price", "");
        $goods_id_array = request()->post("goods_id_array", "");
        $is_shelves = request()->post("is_shelves", 1);
        $original_price = request()->post("original_price", "");
        $save_the_price = request()->post("save_the_price", "");
        
        $res = $promotionService->addOrEditComboPackage($id, $combo_package_name, $combo_package_price, $goods_id_array, $is_shelves, $this->instance_id, $original_price, $save_the_price);
        return AjaxReturn($res);
    }

    /**
     * 删除组合套餐
     */
    public function deleteComboPackage()
    {
        $promotionService = new PromotionService();
        $ids = request()->post("ids", "");
        $res = $promotionService->deleteComboPackage($ids);
        return AjaxReturn($res);
    }

    /**
     * 营销活动列表
     */
    public function promotionGamesList()
    {
        if (request()->isAjax()) {
            
            $promotionService = new PromotionService();
            
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post("page_size", PAGESIZE);
            $search_text = request()->post("search_text", '');
            
            $condition = array();
            if (! empty($search_text)) {
                $condition['name'] = array(
                    'like',
                    '%' . $search_text . '%'
                );
            }
            $promotion_games_list = $promotionService->getPromotionGamesList($page_index, $page_size, $condition);
            return $promotion_games_list;
        }
        return view($this->style . "Games/promotionGamesList");
    }

    /**
     * 营销活动类型列表
     *
     * @return \think\response\View
     */
    public function promotionGameTypeList()
    {
        $promotionService = new PromotionService();
        $game_type_list = $promotionService->getPromotionGameTypeList(1, 0, '', 'is_complete desc');
        $this->assign('game_type_list', $game_type_list['data']);
        return view($this->style . "Games/promotionGameTypeList");
    }

    /**
     * 添加营销活动
     */
    public function addPromotionGame()
    {
        $this->promotionGameInit();
        $this->assign('game_id', 0);
        return view($this->style . "Games/addPromotionGame");
    }

    /**
     * 添加修改互动游戏
     */
    public function addUpdatePromotionGame()
    {
        if (request()->isAjax()) {
            
            $promotionService = new PromotionService();
            $game_id = request()->post('game_id', '');
            $name = request()->post('game_name', '');
            $type = request()->post('game_type', '');
            $member_level = request()->post('member_level', '');
            $points = request()->post('points', '');
            $start_time = request()->post('start_time', '');
            $end_time = request()->post('end_time', '');
            $remark = request()->post('remark', '');
            $winning_rate = request()->post('winning_rate', '');
            $no_winning_des = request()->post('no_winning_des', '');
            $rule_json = request()->post('rule_array', '');
            $activity_images = request()->post('activity_images', ''); // 活动图片
            
            $res = $promotionService->addUpdatePromotionGame($game_id, $this->instance_id, $name, $type, $member_level, $points, $start_time, $end_time, $remark, $winning_rate, $no_winning_des, $rule_json,$activity_images);
            return AjaxReturn($res);
        }
    }

    /**
     * 修改互动游戏
     */
    public function updatePromotionGame()
    {
        $this->promotionGameInit();
        
        $game_id = request()->get('game_id', '');
        $this->assign('game_id', $game_id);
        $promotionService = new PromotionService();
        $game_info = $promotionService->getPromotionGameDetail($game_id);
        $this->assign('game_info', $game_info);
        return view($this->style . "Games/updatePromotionGame");
    }

    /**
     * 修改添加互动游戏页面加载项
     */
    public function promotionGameInit()
    {
        $promotionService = new PromotionService();
        // 活动类型
        $game_type = request()->get('game_type', '');
        $game_type_info = $promotionService->getPromotionGameTypeInfo($game_type);
        $this->assign('game_type', $game_type);
        $this->assign('game_type_info', $game_type_info);
        
        // 会员等级
        $member_service = new Member();
        $member_level_list = $member_service->getMemberLevelList();
        $this->assign('level_list', $member_level_list['data']);
        
        // 优惠劵列表
        $coupon_condition = array(
            'start_time' => array(
                'lt',
                time()
            ),
            'end_time' => array(
                'gt',
                time()
            )
        );
        $coupon_type_list = $promotionService->getCouponTypeInfoList(1, 0, $coupon_condition);
        $this->assign('coupon_type_list', $coupon_type_list['data']);
        
        // 赠品列表
        $gift_condition = array(
            'start_time' => array(
                'lt',
                time()
            ),
            'end_time' => array(
                'gt',
                time()
            )
        );
        $gift_list = $promotionService->getPromotionGiftList(1, 0, $gift_condition);
        $this->assign('gift_list', $gift_list['data']);
    }

    /**
     * 删除互动游戏
     */
    public function delPromotionGame()
    {
        if (request()->isAjax()) {
            
            $promotionService = new PromotionService();
            $game_id = request()->post('game_id', '');
            $res = $promotionService->delPromotionGame($game_id);
            return AjaxReturn($res);
        }
    }

    /**
     * 关闭互动游戏
     */
    public function closePromotionGame()
    {
        if (request()->isAjax()) {
            $promotionService = new PromotionService();
            $game_id = request()->post('game_id', '');
            $res = $promotionService->closePromotionGame($game_id);
            return AjaxReturn($res);
        }
    }

    /**
     * 营销游戏奖项列表
     * 创建时间：2018年2月1日09:59:57 王永杰
     *
     * @return Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function promotionGamesAwardList()
    {
        $game_id = request()->get("game_id", "");
        if (empty($game_id)) {
            $this->error("缺少参数game_id");
        }
        $promotionService = new PromotionService();
        $game_detail = $promotionService->getPromotionGameDetail($game_id);
        $this->assign("game_detail", $game_detail);
        return view($this->style . "Games/promotionGamesAwardList");
    }

    /**
     * 获奖记录
     * 创建时间：2018年2月1日11:38:06
     *
     * @return Ambigous <\data\service\Ambigous, \data\model\unknown, \data\model\multitype:unknown, multitype:unknown number >|Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function promotionGamesAccessRecords()
    {
        if (request()->isAjax()) {
            $page_index = request()->post("page_index", 1);
            $page_size = request()->post("page_size", PAGESIZE);
            $search_text = request()->post("search_text", "");
            $is_winning = request()->post("is_winning", "");
            $game_id = request()->post("game_id", "");
            $condition = array();
            if (! empty($search_text)) {
                $condition['np_pgwr.nick_name'] = [
                    'like',
                    "%" . $search_text . "%"
                ];
            }
            if ($is_winning !== "") {
                
                $condition['np_pgwr.is_winning'] = $is_winning;
            }
            if ($game_id !== "") {
                
                $condition['np_pgwr.game_id'] = $game_id;
            }
            $promotionService = new PromotionService();
            $res = $promotionService->getUserPromotionGamesWinningRecords($page_index, $page_size, $condition);
            return $res;
        } else {
            
            $game_id = request()->get("game_id", "");
            if (empty($game_id)) {
                $this->error("缺少参数game_id");
            }
            $this->assign("game_id", $game_id);
        }
        
        return view($this->style . "Games/promotionGamesAccessRecords");
    }
}