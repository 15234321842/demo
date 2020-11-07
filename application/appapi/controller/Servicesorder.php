<?php
/**
 * Created by 比谷网络.
 * User: kongBo
 * Date: 2018/3/27
 * Time: 10:06
 */

namespace app\appapi\controller;

use data\model\AlbumPictureModel;
use data\model\NfxShopConfigModel;
use data\model\NsCartModel;
use data\model\NsExpressShippingModel;
use data\model\NsServicesModel as NsGoodsModel;
use data\model\NsOrderServicesGoodsModel as NsOrderGoodsModel;
use data\model\NsOrderExpressCompanyModel;
use data\model\NsOrderServicesModel as NsOrderModel;
use data\model\UserModel;
use data\service\Address;
use data\service\Config;
use data\service\Express;
use data\service\Services as Goods;
use data\service\Member;
use data\service\User as UserService;
use data\service\Member as MemberService;
use data\service\NfxShopConfig;
use data\service\Order\Order as OrderOrderService;
use data\service\ServicesOrder\OrderGoods as OrderGoods;
use data\service\ServicesOrder as OrderService;
use data\service\Servicespromotion\GoodsExpress as GoodsExpressService;
use data\service\Servicespromotion\GoodsMansong as GoodsMansong;
use data\service\ServicesPromotion as Promotion;
use data\service\Servicespromotion\GoodsPreference as GoodsPreference;
use data\service\Shop;
use data\model\NsServicesSkuModel as NsGoodsSkuModel;
use data\service\promotion\PromoteRewardRule;
use think\Log;
use data\service\WebSite;
use data\model\XianxiaModel;
use think\Db;
use data\service\Services as GoodsService;

class Servicesorder extends BaseController
{
    protected $uid;
    protected $instance_id;
    public function __construct()
    {
        parent::__construct();
        $this->uid=$this->getUserId();
      $this->instance_id=0;
    }


    /**
     * 待付款订单
     */
public function paymentOrder(){

    /*if(empty( $this->uid)){
        $this->error('请先登录！');

    }*/
    $tag = request()->post('tag', '');
    $tag = 'buy_now';
    if (empty($tag)) {
        $this->error('参数错误！');
    }
    if ($tag == 'cart') {
        $order_tag = 'cart';
        $cart_list= request()->post('cart_id');
    }
    if ($tag == 'buy_now') {
        $order_tag = 'buy_now';
        $order_sku_list = request()->post('sku_id','') . ':' . request()->post('num','');
        $order_goods_type = request()->post("goods_type"); // 实物类型标识
    }
    if ($tag == 'combination_packages') {
        $order_tag = 'combination_packages';
        $order_sku = request()->post("data");
        $combo_id = request()->post("combo_id", "");
        $combo_buy_num = request()->post("buy_num", "");
    }
    $goods_sku_model=new NsGoodsSkuModel();
    $goods_model=new NsGoodsModel();
    $member = new MemberService();
    $goods_id=  $goods_sku_model->getInfo(['sku_id'=>request()->post('sku_id')],"goods_id");
    $goods_type=$goods_model->getInfo(['goods_id'=>$goods_id['goods_id']],"goods_type");


    $unpaid_goback = isset($unpaid_goback) ? $unpaid_goback : '';
    $order_create_flag = isset($order_create_flag) ? $order_create_flag : '';

    // 订单创建标识，表示当前生成的订单详情已经创建好了。用途：订单创建成功后，返回上一个界面的路径是当前创建订单的详情，而不是首页
    if (! empty($order_create_flag)) {
        $order_create_flag = "";
        $this->error($unpaid_goback);
    }

    // 判断实物类型：实物商品，虚拟商品
    $order_tag = isset($order_tag) ? $order_tag : "";
    if (empty($order_tag)) {
      // 没有商品返回到首页
        $this->error('参数错误');

    }
  //$this->assign("order_tag", $order_tag); // 标识：立即购买还是购物车中进来的
    $order_goods_type = isset($order_goods_type) ? $order_goods_type : "";

    if ($order_tag == "buy_now" && $order_goods_type === "0") {
//         虚拟商品
//        $this->virtualOrderInfo();
//        return view($this->style . 'Order/paymentVirtualOrder');
    } elseif ($order_tag == "combination_packages") {
        // 组合套餐
       // $this->comboPackageorderInfo();
       // return view($this->style . 'Order/paymentComboPackageOrder');
    } else {
        // 实物商品
        $info=   $this->orderInfo($order_tag,$order_sku_list,$cart_list);
        $is_paypassword = $member -> isPayPassword($this->userId);
        $info['order_tag']=$order_tag;
        $info['is_paypassword']=$is_paypassword;
        if($goods_type['goods_type'] == 0){
            $info['goods_type']=true;
        }
        $this->success('创建成功',$info);
        //return view($this->style . 'Order/paymentOrder');
    }

}

    /**
     * 实物商品待付款订单需要的数据
     * dy
     */
    public function orderInfo($order_tag,$order_sku_list,$cart_list)
    {
        $member = new MemberService();
        $order = new OrderService();
        $goods_mansong = new GoodsMansong();
        $Config = new Config();
        $Config = new Config();
        $promotion = new Promotion();
        $shop_service = new Shop();
        // 检测购物车
        if(empty($order_sku_list)){

            $this->error('参数错误！');
        }


        switch ($order_tag) {
            // 立即购买
            case "buy_now":
                $res = $this->buyNowSession($order_sku_list);

                $goods_sku_list = $res["goods_sku_list"];
                $list = $res["list"];
                break;
            case "cart":

                // 加入购物车
                $res = $this->addShoppingCartSession($cart_list);
                $goods_sku_list = $res["goods_sku_list"];
                $list = $res["list"];
                break;
        }
     //   $this->assign('goods_sku_list', $goods_sku_list);

        $data['goods_sku_list']=$goods_sku_list;

        $address = $member->getDefaultExpressAddress($this->uid); // 获取默认收货地址
        $express = 0;

        $express_company_list = array();
        $goods_express_service = new GoodsExpressService();
        if (! empty($address)) {
            // 物流公司
            $express_company_list = $goods_express_service->getExpressCompany($this->instance_id, $goods_sku_list, $address['province'], $address['city'], $address['district']);

            if (! empty($express_company_list)) {
                foreach ($express_company_list as $v) {
                    $express = $v['express_fee']; // 取第一个运费，初始化加载运费
                    break;
                }
            }
            $data['address_is_have']=1;
          //  $this->assign("address_is_have", 1);
        } else {
            $data['address_is_have']=0;
            //$this->assign("address_is_have", 0);
        }
        $count = $goods_express_service->getExpressCompanyCount($this->instance_id);
       /* $this->assign("express_company_count", $count); // 物流公司数量
        $this->assign("express", sprintf("%.2f", $express)); // 运费
        $this->assign("express_company_list", $express_company_list); // 物流公司*/

        $data['express_company_count']=$count; // 物流公司数量
        $data['express']=sprintf("%.2f", $express);// 运费
        $data['express_company_list']=$express_company_list;

        $discount_money = $goods_mansong->getGoodsMansongMoney($goods_sku_list);
      //  $this->assign("discount_money", sprintf("%.2f", $discount_money));

        $data['discount_money']=sprintf("%.2f", $discount_money); // 总优惠

        $count_money = $order->getGoodsSkuListPrice($goods_sku_list);
     //   $this->assign("count_money", sprintf("%.2f", $count_money));




        $pick_up_money = $order->getPickupMoney($count_money);
      //  $this->assign("pick_up_money", $pick_up_money);
        $data['pick_up_money']=$pick_up_money;
        $count_point_exchange = 0;
        $count_point_use = 0;
        $domain_name = \think\Request::instance()->domain();
        foreach ($list as $k => $v) {
            $list[$k]['price'] = sprintf("%.2f", $list[$k]['price']);
            $list[$k]['subtotal'] = sprintf("%.2f", $list[$k]['price'] * $list[$k]['num']);
            $list[$k]['picture_info']['pic_cover'] = $domain_name.'/'.$v['picture_info']['pic_cover'];
            $list[$k]['picture_info']['pic_cover_big'] = $domain_name.'/'.$v['picture_info']['pic_cover_big'];
            $list[$k]['picture_info']['pic_cover_micro'] = $domain_name.'/'.$v['picture_info']['pic_cover_micro'];
            $list[$k]['picture_info']['pic_cover_mid'] = $domain_name.'/'.$v['picture_info']['pic_cover_mid'];
            $list[$k]['picture_info']['pic_cover_small'] = $domain_name.'/'.$v['picture_info']['pic_cover_small'];
            if ($v["point_exchange_type"] == 1) {
                if ($v["point_exchange"] > 0) {
                    $count_point_exchange += $v["point_exchange"] * $v["num"];
                }
            }
            //获取总的使用积分
            if ($v["point_exchange_type"] == 2) {
                if ($v["point_exchange"] > 0) {
                    $count_point_use += $v["point_exchange"] ;
                }
            }
        }
       /* $this->assign("count_point_exchange", $count_point_exchange); // 总积分
        $this->assign("itemlist", $list);*/

        $data['count_point_exchange']=$count_point_exchange;// 总积分
        $data['count_point_use']=$count_point_use;
        $data['itemlist']=$list;
        //  print_r($list);die();
        $shop_id = $this->instance_id;
        $shop_config = $Config->getShopConfig($shop_id);

        $order_invoice_content = explode(",", $shop_config['order_invoice_content']);
        $shop_config['order_invoice_content_list'] = array();
        foreach ($order_invoice_content as $v) {
            if (! empty($v)) {
                array_push($shop_config['order_invoice_content_list'], $v);
            }
        }
       // $this->assign("shop_config", $shop_config); // 后台配置

        $data['shop_config']=$shop_config;

        $member_account = $member->getMemberAccount($this->uid, $this->instance_id);

        if ($member_account['balance'] == '' || $member_account['balance'] == 0) {
            $member_account['balance'] = '0.00';
        }
       // $this->assign("member_account", $member_account); // 用户现金


        //积分低现金

        $goods_info=explode(':',$goods_sku_list);
        $goods_sku_model=new NsGoodsSkuModel();
        $goods_model=new NsGoodsModel();


        $goods_id=  $goods_sku_model->getInfo(['sku_id'=>$goods_info[0]],"goods_id");
        $goods_type=$goods_model->getInfo(['goods_id'=>$goods_id['goods_id']],"point_exchange_type,point_exchange");

//        if($goods_type['point_exchange_type']==2){
//            if($member_account['point'] >= $goods_type['point_exchange']){
//                //查询用户的积分
//                $integral=$goods_type['point_exchange'];
//            }else{
//                $integral=$member_account['point'];
//            }
//
//            $count_money-=$integral;
//            $data['count_money']=sprintf("%.2f", $count_money);// 商品金额
//        }else{
//
//            $data['count_money']=sprintf("%.2f", $count_money);// 商品金额
//        }


        $data['count_money']=sprintf("%.2f", $count_money);// 商品金额
        $data['member_account']=$member_account;// 用户现金

        $coupon_list = $order->getMemberCouponList($goods_sku_list,$this->uid);
       // $this->assign("coupon_list", $coupon_list); // 获取红包

        foreach ($coupon_list as $k=>$v){
            $v['end_time']=date("Y-m-d H:i:s",$v['end_time']) ;
            $v['start_time']=date("Y-m-d H:i:s",$v['start_time']) ;
        }

        $data['coupon_list']=$coupon_list;
        $promotion_full_mail = $promotion->getPromotionFullMail($this->instance_id);
        if (! empty($address)) {
            $no_mail = checkIdIsinIdArr($address['city'], $promotion_full_mail['no_mail_city_id_array']);
            if ($no_mail) {
                $promotion_full_mail['is_open'] = 0;
            }
        }
      //  $this->assign("promotion_full_mail", $promotion_full_mail); // 满额包邮
        $data['promotion_full_mail']=$promotion_full_mail;// 满额包邮

        $pickup_point_list = $shop_service->getPickupPointList();
      //  $this->assign("pickup_point_list", $pickup_point_list); // 自提地址列表

       // $this->assign("address_default", $address);

        $goods_mansong_gifts = $this->getOrderGoodsMansongGifts($goods_sku_list);
       // $this->assign("goods_mansong_gifts", $goods_mansong_gifts); // 赠品列表

        $data['pickup_point_list']=$pickup_point_list;// 自提地址列表
        $data['goods_mansong_gifts']=$goods_mansong_gifts;
        $data['address_default']=$address;

        return $data;
    }

    /**
     * 实物商品立即购买
     */
    public function buyNowSession($order_sku_list)
    {

        if (empty($order_sku_list)) {
          /*  $this->redirect(__URL__); // 没有商品返回到首页*/
            $this->error('参数错误！');
        }

        $cart_list = array();
        $order_sku_list = explode(":", $order_sku_list);
        $sku_id = $order_sku_list[0];
        $num = $order_sku_list[1];

        // 获取商品sku信息
        $goods_sku = new NsGoodsSkuModel();
        $sku_info = $goods_sku->getInfo([
            'sku_id' => $sku_id
        ], '*');

        // 查询当前商品是否有SKU主图
        $order_goods_service = new OrderGoods();
        $picture = $order_goods_service->getSkuPictureBySkuId($sku_info);

        // 清除非法错误数据
        $cart = new NsCartModel();
        if (empty($sku_info)) {
            $cart->destroy([
                'buyer_id' => $this->uid,
                'sku_id' => $sku_id
            ]);

            $this->error('没有商品信息！');
        }
        $goods = new NsGoodsModel();
        $goods_info = $goods->getInfo([
            'goods_id' => $sku_info["goods_id"]
        ], 'max_buy,state,point_exchange_type,point_exchange,picture,goods_id,goods_name');

        $cart_list["stock"] = $sku_info['stock']; // 库存
        $cart_list["sku_id"] = $sku_info["sku_id"];
        $cart_list["sku_name"] = $sku_info["sku_name"];

        $goods_preference = new GoodsPreference();
        $member_price = $goods_preference->getGoodsSkuMemberPrice($sku_info['sku_id'], $this->uid);
        $cart_list["price"] = $member_price < $sku_info['promote_price'] ? $member_price : $sku_info['promote_price'];

        $cart_list["goods_id"] = $goods_info["goods_id"];
        $cart_list["goods_name"] = $goods_info["goods_name"];
        $cart_list["max_buy"] = $goods_info['max_buy']; // 限购数量
        $cart_list['point_exchange_type'] = $goods_info['point_exchange_type']; // 积分兑换类型 0 非积分兑换 1 只能积分兑换
        $cart_list['point_exchange'] = $goods_info['point_exchange']; // 积分兑换
        if ($goods_info['state'] != 1) {
            // 商品状态 0下架，1正常，10违规（禁售）
            $this->error('商品下架！');
        }
        $cart_list["num"] = $num;
        // 如果购买的数量超过限购，则取限购数量
        if ($goods_info['max_buy'] != 0 && $goods_info['max_buy'] < $num) {
            $num = $goods_info['max_buy'];
        }
        // 如果购买的数量超过库存，则取库存数量
        if ($sku_info['stock'] < $num) {
            $num = $sku_info['stock'];
        }
        // 获取图片信息
        $album_picture_model = new AlbumPictureModel();
        $picture_info = $album_picture_model->get($picture == 0 ? $goods_info['picture'] : $picture);
        $cart_list['picture_info'] = $picture_info;

        // 获取商品阶梯优惠信息
        $goods_service = new Goods();
        $cart_list["price"] = $goods_service->getGoodsLadderPreferentialInfo($goods_info["goods_id"], $num, $cart_list["price"]);

        if (count($cart_list) == 0) {
            // 没有商品返回到首页
            $this->error('没有获取商品！');
        }
        $list[] = $cart_list;
        $goods_sku_list = $sku_id . ":" . $num; // 商品skuid集合
        $res["list"] = $list;
        $res["goods_sku_list"] = $goods_sku_list;
        return $res;
    }

    /**
     * 获取订单商品满就送赠品，重复赠品累加数量

     */
    public function getOrderGoodsMansongGifts($goods_sku_list)
    {
        $res = array();
        $gift_id_arr = array();
        $goods_mansong = new GoodsMansong();
        $mansong_array = $goods_mansong->getGoodsSkuListMansong($goods_sku_list);
        if (! empty($mansong_array)) {
            foreach ($mansong_array as $k => $v) {
                foreach ($v['discount_detail'] as $discount_k => $discount_v) {
                    $v = $discount_v[0]['gift_id'];
                    array_push($gift_id_arr, $v);
                }
            }
        }
        // 统计每个赠品的数量
        $statistical = array_count_values($gift_id_arr);
        $promotion = new Promotion();
        foreach ($statistical as $k => $v) {
            $detail = $promotion->getPromotionGiftDetail($k);
            $detail['count'] = $v;
            array_push($res, $detail);
        }
        return $res;
    }


    /**
     * 加入购物车
     *
     * @return unknown
     */
    public function addShoppingCartSession($cart_list)
    {
        // 加入购物车
        $session_cart_list = isset($cart_list) ? $cart_list : ""; // 用户所选择的商品
        if ($session_cart_list == "") {
            // 没有商品返回到首页
            $this->error('没有商品！');
        }

        $cart_id_arr = explode(",", $session_cart_list);
        $goods = new Goods();
        $cart_list = $goods->getCartList($session_cart_list);
        if (count($cart_list) == 0) {
            $this->error('没有商品！');// 没有商品返回到首页
        }
        $list = Array();
        $str_cart_id = ""; // 购物车id
        $goods_sku_list = ''; // 商品skuid集合
        for ($i = 0; $i < count($cart_list); $i ++) {
            if ($cart_id_arr[$i] == $cart_list[$i]["cart_id"]) {
                $list[] = $cart_list[$i];
                $str_cart_id .= "," . $cart_list[$i]["cart_id"];
                $goods_sku_list .= "," . $cart_list[$i]['sku_id'] . ':' . $cart_list[$i]['num'];
            }
        }
        $goods_sku_list = substr($goods_sku_list, 1); // 商品sku列表
        $res["list"] = $list;
        $res["goods_sku_list"] = $goods_sku_list;
        return $res;
    }

    /**
     * 创建订单（实物商品）
     */
    public function orderCreate()
    {
        $order = new OrderService();
        // 获取支付编号
        $ExpressCompany=new NsOrderExpressCompanyModel();
        $info= $ExpressCompany->getInfo(['is_default'=>1],"*");
        $out_trade_no = $order->getOrderTradeNo();
        $use_coupon = request()->post('use_coupon', 0); // 红包
        $integral = request()->post('integral', 0); // 积分
        $goods_sku_list = request()->post('goods_sku_list', ''); // 商品列表
        $leavemessage = request()->post('leavemessage', ''); // 留言
        $user_money = request()->post("account_balance", 0); // 使用现金
        $pay_type = request()->post("pay_type", 5); // 支付方式
        $buyer_invoice = request()->post("buyer_invoice", ""); // 发票
        $pick_up_id = request()->post("pick_up_id", 0); // 自提点
     //   $shipping_company_id = request()->post("shipping_company_id", 0); // 物流公司
        $shipping_company_id = $info['co_id']; // 物流公司
        $shipping_time = request()->post("shipping_time", 0); // 配送时间
        $address1 = request()->post("address", 1); // 是否需要寄存
        $paypwd = request()->post("paypwd", ''); // 是否需要寄存
        if(empty($goods_sku_list)){
            $this->error('参数错误！');

        }
        if($use_coupon !== 0 && $integral !== 0 && $user_money !== 0){
            $user=new UserService();
            $status=$user->ratioPayPassword($this->uid,$paypwd);
            if($status == false){
                $this->error('支付密码错误');
            }
        }

        //查询商品的支付类型，只要是积分充抵金额
        $goods_info=explode(':',$goods_sku_list);
        $goods_sku_model=new NsGoodsSkuModel();
        $goods_model=new NsGoodsModel();
        $member = new MemberService();
        $goods_id=  $goods_sku_model->getInfo(['sku_id'=>$goods_info[0]],"goods_id");
        $goods_type=$goods_model->getInfo(['goods_id'=>$goods_id['goods_id']],"point_exchange_type,point_exchange,goods_type");
        $member_account = $member->getMemberAccount($this->uid, $this->instance_id);//获得用户的积分
        if($goods_type['point_exchange_type']==2){
            if($integral!=0){
                if($goods_type['point_exchange']>$member_account['point']){
                    if($integral>$member_account['point']){
                        $integral=$member_account['point'];
                    }
                }else{
                    if($integral>$goods_type['point_exchange']){
                        $integral=$goods_type['point_exchange'];
                    }
                }
            }
        }
        if($goods_type['goods_type']==0){
            $info = request()->post('info','');
            $order_type = 2;
            $this -> virtualOrderCreate($goods_type['goods_type'],$out_trade_no,$use_coupon,$integral,$goods_sku_list,$leavemessage,$user_money,$pay_type,$buyer_invoice,$info);
            return;
        }else{
            $order_type = 1;
        }
        $shipping_type = 1; // 配送方式，1：物流，2：自提
        if ($pick_up_id != 0) {
            $shipping_type = 2;
        }
        $member = new Member();
        if($address1==1){
           // $address = 0;
            $address['address']='客户选择寄存';
            $shipping_type = 0;
            $is_address = 2;
        }else{
            $is_address = 1;
            $address = $member->getDefaultExpressAddress($this->uid);
            if(empty($address)){
                $this->error('请填写或选择地址！');
            }
        }

        $coin = 0; // 积分
        $buyer_ip = request()->ip(0,false);
        // 查询商品限购
        $purchase_restriction = $order->getGoodsPurchaseRestrictionForOrder($goods_sku_list);
        if (! empty($purchase_restriction)) {
            $res = array(
                "code" => 0,
                "message" => $purchase_restriction
            );
            return $res;
        } else {
            $order_id = $order->orderCreate($order_type, $out_trade_no, $pay_type, $shipping_type, '1', $buyer_ip, $leavemessage, $buyer_invoice, $shipping_time, $address['mobile'], $address['province'], $address['city'], $address['district'], $address['address'], $address['zip_code'], $address['consigner'], $integral, $use_coupon, 0, $goods_sku_list, $user_money, $pick_up_id, $shipping_company_id, $coin, $address["phone"],$this->uid,$is_address);
//            $_SESSION['unpaid_goback'] = __URL(__URL__ . "/wap/order/orderdetail?orderId=" . $order_id);
//            // 订单创建标识，表示当前生成的订单详情已经创建好了。用途：订单创建成功后，返回上一个界面的路径是当前创建订单的详情，而不是首页
//            $_SESSION['order_create_flag'] = 1;

            if ($order_id > 0) {
                $order->deleteCart($goods_sku_list, $this->uid);
          //      $_SESSION['order_tag'] = ""; // 生成订单后，清除购物车
             //   return AjaxReturn($out_trade_no);
                $this->success('操作成功！',$out_trade_no);
            } else {
             //   return AjaxReturn($order_id);
                $this->error('生成错误，请稍后再试！',$order_id);
            }
        }
    }

    /**
     * 创建订单（虚拟商品）
     */
    public function virtualOrderCreate($order_type,$out_trade_no,$use_coupon,$integral,$goods_sku_list,$leavemessage,$user_money,$pay_type,$buyer_invoice,$info)
    {
        $order = new OrderService();
        $uid = $this -> uid;
        // 获取支付编号
//        $out_trade_no = $order->getOrderTradeNo();
//        $use_coupon = request()->post('use_coupon', 0); // 红包
//        $integral = request()->post('integral', 0); // 积分
//        $goods_sku_list = request()->post('goods_sku_list', ''); // 商品列表
//        $leavemessage = request()->post('leavemessage', ''); // 留言
//        $user_money = request()->post("account_balance", 0); // 使用现金
//        $pay_type = request()->post("pay_type", 1); // 支付方式
//        $buyer_invoice = request()->post("buyer_invoice", ""); // 发票
        $user_telephone = request()->post("user_telephone", ""); // 电话号码
        $pick_up_id = 0; // 自提点
        $shipping_type = 1; // 配送方式，1：物流，2：自提
        $express_company_id = 0; // 物流公司
        $buyer_ip = request()->ip(0,false);
        $member = new Member();
        $shipping_time = date("Y-m-d H::i:s", time());
        $address = $member->getDefaultExpressAddress($uid);
        // 查询商品限购
        $purchase_restriction = $order->getGoodsPurchaseRestrictionForOrder($goods_sku_list);
        if (! empty($purchase_restriction)) {
            $res = array(
                "code" => 0,
                "message" => $purchase_restriction
            );
            return $res;
        } else {
            $order_id = $order->orderCreateVirtual('2', $out_trade_no, $pay_type, $shipping_type, '1', $buyer_ip, $leavemessage, $buyer_invoice, $shipping_time, $integral, $use_coupon, 0, $goods_sku_list, $user_money, $pick_up_id, $express_company_id, $address['mobile'],'',$uid,$info);
            // Log::write($order_id);
            if ($order_id > 0) {
                $order->deleteCart($goods_sku_list, $this->uid);
//                $_SESSION['order_tag'] = ""; // 订单创建成功会把购物车中的标记清楚
//                return AjaxReturn($out_trade_no);
                $this -> success('虚拟订单创建成功',$out_trade_no);
            } else {
                $this->error('生成错误，请稍后再试！',$order_id);
            }
        }
    }

    /**
     * 获取当前会员的订单列表
     */
    public function myOrderList()
    {
        $status = request()->post('status', 'all');
        $page_index = request()->post("page", 1);
        $pagesize = request()->post("pagesize", 5);
        $condition['a.buyer_id'] = $this->uid;
        $condition['a.is_deleted'] = 0;
        if (! empty($this->shop_id)) {
                $condition['a.shop_id'] = $this->shop_id;
            }

        if ($status != 'all') {
                switch ($status) {
                    case 0:
                        $condition['a.order_status'] = 0;
                        $condition['a.order_type'] = array(
                            "in",
                            "1,2"
                        );
                        break;
                    case 1:
                        $condition['a.order_status'] = 1;
                        $condition['a.order_type'] = array(
                            "in",
                            "1,2"
                        );
                        break;
                    case 2:
                        $condition['a.order_status'] = 2;
                        $condition['a.order_type'] = array(
                            "in",
                            "1,2"
                        );
                        break;
                    case 7:
                        $condition['a.order_status'] = 4;
                        $condition['a.order_type'] = 2;
                        $condition['b.use_status'] = 0;
                        break;
                    case 4:
                        $condition['a.order_status'] = 4;
                        $condition['a.order_type'] = array(
                            "in",
                            "1,2"
                        );
                        break;
                    case 5:
                        $condition['a.order_status'] = 5;
                        $condition['a.order_type'] = array(
                            "in",
                            "1,2"
                        );
                        break;
                    default:
                        break;
                }
            }
            // 还要考虑状态逻辑
            $order = new OrderService();
            $order_list = $order->getOrderListApi($page_index, $pagesize, $condition, 'a.create_time desc');
            $this->success('获取完成',$order_list);

    }

    /**
     * 订单详情
     *
     * @return Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function orderDetail()
    {
        $order_id = request()->post('orderId', 0);
        if (! is_numeric($order_id)) {
            $this->error("没有获取到订单信息");
        }

        $order_service = new OrderService();
        $detail = $order_service->getOrderDetail($order_id);
        if (empty($detail)) {
            $this->error("没有获取到订单信息");
        }
        // 通过order_id判断该订单是否属于当前用户
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->uid;
        $condition['order_type'] = array(
            "in",
            "1,2"
        );
        $order_count = $order_service->getOrderCount($condition);
        if ($order_count == 0) {
            $this->error("没有获取到订单信息");
        }

        $count = 0; // 计算包裹数量（不包括无需物流）
        $express_count = count($detail['goods_packet_list']);
        $express_name = "";
        $express_code = "";
        if ($express_count) {
            foreach ($detail['goods_packet_list'] as $v) {
                if ($v['is_express']) {
                    $count ++;
                    if (! $express_name) {
                        $express_name = $v['express_name'];
                        $express_code = $v['express_code'];
                    }
                }
            }
         /*   $this->assign('express_name', $express_name);
            $this->assign('express_code', $express_code);*/
            $data['express_name']=$express_name;
            $data['express_code']=$express_code;
        }
        /*$this->assign('express_count', $express_count);
        $this->assign('is_show_express_code', $count); // 是否显示运单号（无需物流不显示）

        $this->assign("order", $detail);*/

        $data['express_count']=$express_count;
        $data['is_show_express_code']=$count;
        $data['order']=$detail;

        // 美洽客服
        $config_service = new Config();
        $list = $config_service->getcustomserviceConfig($this->instance_id);
        $web_config = new WebSite();
        $web_info = $web_config->getWebSiteInfo();

        $list['value']['web_phone'] = '';
        if (empty($list)) {
            $list['id'] = '';
            $list['value']['service_addr'] = '';
        }

        if (! empty($web_info['web_phone'])) {
            $list['value']['web_phone'] = $web_info['web_phone'];
        }
       /* $this->assign("list", $list);*/
        $data['list']=$list;

      $this->success('订单详情',$data);
    }

    /**
     * 订单项退款详情
     */
    public function refundDetail()
    {

        $order_goods_id = request()->post('order_goods_id', '');

        if (! is_numeric($order_goods_id)) {
            $this->error("没有获取到退款信息");
        }
        $order_service = new OrderService();
        $detail = $order_service->getOrderGoodsRefundInfo($order_goods_id);
        unset($detail['picture_info']);
        //  $this->assign("order_refund", $detail);
        $refund_money = $order_service->orderGoodsRefundMoney($order_goods_id);
        //  $this->assign('refund_money', sprintf("%.2f", $refund_money));

        $data['order_refund']=$detail;
        $data['refund_money']=sprintf("%.2f", $refund_money);

        // 现金退款
        $order_goods_service = new OrderGoods();
        $refund_balance = $order_goods_service->orderGoodsRefundBalance($order_goods_id);
        //$this->assign("refund_balance", sprintf("%.2f", $refund_balance));

        //  $this->assign("detail", $detail);
        $data['refund_balance']=$refund_balance;
        /*  $data['detail']=$detail;*/
        // 查询店铺默认物流地址
        $express = new Express();
        $address = $express->getDefaultShopExpressAddress($this->instance_id);
        // 查询商家地址
        $shop_info = $order_service->getShopReturnSet($this->instance_id);
        /*  $this->assign("shop_info", $shop_info);
          $this->assign("address_info", $address);*/
        $data['shop_info']=$shop_info;
        $data['address_info']=$address;
        $this->success('退款申请信息',$data);
    }

    /**
     * 申请退款
     */
    public function orderGoodsRefundAskfor()
    {
        $order_id = request()->post('order_id', '');
        if(!empty($order_id)){
            $order_g=    new NsOrderGoodsModel();
            $orderino= $order_g->getInfo(['order_id'=>$order_id],"*");
        }else{

            $this->error('参数错误!');
        }
        $order_goods_id = $orderino['order_goods_id'];
        $refund_type = request()->post('refund_type', 1);
        $refund_require_money = request()->post('refund_require_money', 0);
        $refund_reason = request()->post('refund_reason', '');
        $order_service = new OrderService();
        $retval = $order_service->orderGoodsRefundAskfor($order_id, $order_goods_id, $refund_type, $refund_require_money, $refund_reason);
//        print_r($retval);die;

        if($retval){
            $this->success('申请退款成功！',$retval);

        }else{
            $this->success('申请退款错误！');
        }
    }

    /**
     * 收货
     */
    public function orderTakeDelivery()
    {
        $order_service = new OrderService();
        $order_id = request()->post('order_id', '149');
        if(empty($order_id)){
            $this -> error('订单id为空');
        }
        $order_info = $order_service -> getOrderInfo($order_id);
        if($order_info['buyer_id'] !== $this->uid || $order_info['order_status'] !== 2){
            $this -> error('非法操作!');
        }
        $res = $order_service->OrderTakeDelivery($order_id);


        if($res){
            $this->success('收货成功！');
        }else{
            $this->error('收货失败！');
        }
    }

    /**
     * 删除订单
     */
    public function deleteOrder()
    {

            $order_service = new OrderService();
            $order_id = request()->post("order_id", "");
            if($order_id == ''){
                $this->error('参数错误！');
            }
            $res = $order_service->deleteOrder($order_id, 2, $this->uid);
            if($res){
                $this->success('删除订单成功！');
            }else{
                $this->error('删除订单失败！');
            }


    }


//    /**
//     * 物流详情页
//     */
//    public function orderExpress()
//    {
//        $order_id = request()->post('orderId', 0);
//        if (! is_numeric($order_id)) {
//            $this->error("没有获取到订单信息");
//        }
//        $order_service = new OrderService();
//        $detail = $order_service->getOrderDetail($order_id);
//        if (empty($detail)) {
//            $this->error("没有获取到订单信息");
//        }
//        // 获取物流跟踪信息
//        $order_service = new OrderService();
//
//       $this->success('物流详信息',$detail);
//    }
    /**
     * 物流详情页
     */
    public function orderExpress()
    {
        $order_id = request()->post('orderid', 0);
        if (! is_numeric($order_id)) {
            $this->error("没有获取到订单信息");
        }
        $order_service = new OrderService();
        $detail = $order_service->getOrderDetail($order_id);
        $express_id=$detail['order_goods'][0]['express_info']['id'];
        $res=$this->getOrderGoodsExpressMessage($express_id);

        $data['goods_packet_list']=$detail['goods_packet_list'][0];
        $data['express_info']=$res;
        if (empty($data)) {
            $this->error("没有获取到订单信息");
        }
        // 获取物流跟踪信息
        $order_service = new OrderService();

        $this->success('物流详信息',$data);
    }
    /**
     * 查询包裹物流信息
     * 2017年6月24日 10:42:34 王永杰
     */
    public function getOrderGoodsExpressMessage($express_id)
    {
//        $express_id = request()->post("express_id", 0); // 物流包裹id
        $res = - 1;
        if ($express_id) {
            $order_service = new OrderService();
            $res = $order_service->getOrderGoodsExpressMessage($express_id);
            $res = array_reverse($res);
        }
        return $res;
    }
      //添加线下支付凭证
    public function AddTransaction()
    {
        $out_trade_no = request()->post("out_trade_no", '');
        $path = request()->post("path", '');
        $transaction_id = request()->post("transaction_id", '');
        $num = request()->post("num", '');
        $page_size = request()->post("page_size", PAGESIZE);
        if (empty($out_trade_no)) {
            $this->error('参数错误！');
        }
        if (empty($path)) {
            $this->error('参数错误！');
        }
        if (empty($transaction_id)) {
            $this->error('参数错误！');
        }
        $xianxia=    new XianxiaModel();
        $order_model=new NsOrderModel();
        $orderinfo=$order_model->getInfo(['out_trade_no'=>$out_trade_no],"order_id");
        Db::startTrans();
        try {
            $data=[
                'out_trade_no'=>$out_trade_no,
                'path'=>$path,
                'transaction_id'=>$transaction_id,
                'order_id'=>$orderinfo['order_id'],
                'create_time'=>time(),
                'uid'=>$this->uid,
                'type'=>3,
                'status'=>0,
                'num'=>$num,

            ];
            $res= $xianxia->save($data);
            //修改订单状态
            $order=  new NsOrderModel();
            $order_info=   $order->save(['order_status'=>6],['out_trade_no'=>$out_trade_no]);

            Db::commit();
            $this->success('提交成功，请等待审核');


        } catch (\Exception $e) {
            Db::rollback();
            $this->error('提交错误，请稍后再试', $e->getMessage());
            //  return $e->getMessage();
        }

    }

    /**
     * 转售商品
     */
    public function ResellerOrderList()
    {


        $status = request()->post('tostatus', 'all');
        $condition['buyer_id'] = $this->uid;
        $condition['is_deleted'] = 0;
        $condition['order_type'] = array(
            "in",
            "1,3"
        );
        if (! empty($this->shop_id)) {
            $condition['shop_id'] = $this->shop_id;
        }
        $condition['order_status'] = 4;
        if ($status != 'all') {
            switch ($status) {
                case 0:
                    $condition['tostatus'] = 0;
                    break;
                case 1:
                    $condition['tostatus'] = 1;
                    break;
                case 2:
                    $condition['tostatus'] = 2;
                    break;

                default:
                    break;
            }
        }
        $page_index = request()->post("page", 1);
        // 还要考虑状态逻辑
        $order = new OrderService();
        $order_list = $order->getOrderList($page_index, PAGESIZE, $condition, 'create_time desc');
        $this->success('获取完成',$order_list);
       // $this->success('获取成功！',$order_list);

    }


    //提交转让需要的信息

    public function getgoodsinfo(){

        $order_id = request()->post('order_id', 0);
        if ($order_id  == 0) {
            $this->error("没有获取到商品信息");
        }
      $goods = new GoodsService();
      $order_goods=  new NsOrderGoodsModel();
      $goods_info  = $order_goods->getInfo(['order_id'=>$order_id],'goods_id,num');
        $data['goods_detail'] = $goods->getBasisGoodsDetail($goods_info['goods_id']);
        $data['mun']=$goods_info['num'];

        $this->success('获取成功',$data);
    }

//提交转售信息
    public function addreseller(){
        $mobile = request()->post('mobile', 0);
        $real_name = request()->post('real_name', 0);
        $order_id = request()->post('order_id', 0);
        if ($mobile  == 0) {
            $this->error("没有获取到信息");
        }
        if ($order_id  == 0) {
            $this->error("没有获取到信息");
        }

        $user=  New UserModel();
        $touser_info=    $user->getInfo(['mobile'=>$mobile],"*");
        if($touser_info['real_name']!=$real_name){
            $this->error("姓名验证不正确，请确认后再提交");
        }
        $order=new  NsOrderModel();
        $data['touid']=$touser_info['uid'];
        $data['tostatus']=2;
        $data['tocreate_time']=time();
        $res=   $order->save($data,['order'=>$order_id,'buyer_id'=>$this->uid]);
        if($res){
            $this->success('保存成功!');
        }else{
            $this->error("保存失败！");
        }
    }
    /**
     * 交易关闭
     */
    public function orderClose()
    {
        $order_id = request()->post('order_id');
        if(empty($order_id)){
            $this -> error('参数错误！');
        }
        $order_service = new OrderService();
        $res = $order_service->orderClose($order_id);
        if($res){
            $this -> success('订单关闭成功！');
        }else{
            $this -> error('订单关闭失败！');
        }
    }

    /**
     * 买家退货
     *
     * @return Ambigous <multitype:unknown, multitype:unknown unknown string >
     */
    public function orderGoodsRefundExpress()
    {
        $order_id = request()->post('order_id', '');
        if(!empty($order_id)){
            $order_g=    new NsOrderGoodsModel();
            $orderino= $order_g->getInfo(['order_id'=>$order_id],"*");
        }else{

            $this->error('参数错误!');
        }
        $order_goods_id = $orderino['order_goods_id'];
        $refund_express_company = request()->post('refund_express_company', '');
        $refund_shipping_no = request()->post('refund_shipping_no', 0);
        $refund_reason = request()->post('refund_reason', '');
        $order_service = new OrderService();
        $retval = $order_service->orderGoodsReturnGoods($order_id, $order_goods_id, $refund_express_company, $refund_shipping_no);
        if($retval){
            $this->success('买家退货成功！',$retval);

        }else{
            $this->success('买家退货错误！');
        }
    }

}