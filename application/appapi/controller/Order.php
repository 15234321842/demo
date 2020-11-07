<?php
/**
 * Created by 比谷网络.
 * User: dy
 * Date: 2018/3/15
 * Time: 14:06
 */

namespace app\appapi\controller;

use data\model\AlbumPictureModel;
use data\model\ConfigModel;
use data\model\NfxShopConfigModel;
use data\model\NsCartModel;
use data\model\NsExpressShippingModel;
use data\model\NsGoodsModel;
use data\model\NsOrderGoodsModel;
use data\model\NsOrderExpressCompanyModel;
use data\model\NsOrderModel;
use data\model\NsPositionContentModel;
use data\model\NsToorderModel;
use data\model\UserModel;
use data\service\Address;
use data\service\Config;
use data\service\Express;
use data\service\Goods;
use data\service\Member;
use data\service\Member as MemberService;
use data\service\NfxShopConfig;
use data\service\Order\Order as OrderOrderService;
use data\service\Order\OrderGoods;
use data\service\Order as OrderService;
use data\service\promotion\GoodsExpress as GoodsExpressService;
use data\service\promotion\GoodsMansong;
use data\service\Promotion;
use data\service\promotion\GoodsPreference;
use data\service\Shop;
use data\model\NsGoodsSkuModel;
use data\service\promotion\PromoteRewardRule;
use think\Log;
use data\service\WebSite;
use data\model\XianxiaModel;
use think\Db;
use data\service\Goods as GoodsService;
use think\Validate;
use data\model\NsPointConfigModel;

class Order extends BaseController
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

    $tag = request()->post('tag', 'buy_now');
    if (empty($tag)) {
        $this->error('参数错误！');
    }

    if ($tag == 'cart') {
        $order_tag = 'cart';
        $cart_list= request()->post('cart_id');
    }
    if ($tag == 'buy_now') {
        $order_tag = 'buy_now';
       $order_sku_list = request()->post('sku_id') . ':' . request()->post('num');
        //$order_sku_list = 90 . ':' . 1;



        $order_goods_type = request()->post("goods_type"); // 实物类型标识
    }
    if ($tag == 'combination_packages') {
        $order_tag = 'combination_packages';
        $order_sku = request()->post("data");
        $combo_id = request()->post("combo_id", "");
        $combo_buy_num = request()->post("buy_num", "");
    }



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
        // 虚拟商品
       // $this->virtualOrderInfo();
     //   return view($this->style . 'Order/paymentVirtualOrder');
    } elseif ($order_tag == "combination_packages") {
        // 组合套餐
       // $this->comboPackageorderInfo();
       // return view($this->style . 'Order/paymentComboPackageOrder');
    } else {
        // 实物商品
        $info=   $this->orderInfo($order_tag,$order_sku_list,$cart_list);
        $info['order_tag']=$order_tag;
        $member = new MemberService();
        $is_paypassword = $member -> isPayPassword($this->userId);
        $info['is_paypassword']=$is_paypassword;
        //获取是否开启平台寄存
        $ConfigModel =new ConfigModel();
        $condition['key'] = 'IS_PLATFORM_DEPOSIT';
        $info['is_platform_deposit'] = $ConfigModel->where($condition)->value('value');
//        $pointConfig = new NsPointConfigModel();
//        $retval = $pointConfig->value('convert_rate');
//        $info['point_rate']=$retval;
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
        $promotion = new Promotion();
        $shop_service = new Shop();
        // 检测购物车
        if(empty($order_sku_list)&&empty($cart_list)){
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
        foreach ($list as $k => $v) {
            $list[$k]['price'] = sprintf("%.2f", $list[$k]['price']);
            $list[$k]['subtotal'] = sprintf("%.2f", $list[$k]['price'] * $list[$k]['num']);
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
        $data['count_point_use']=$count_point_use;// 总使用积分
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

    /*    if($goods_type['point_exchange_type']==2){
            if($member_account['point'] >= $goods_type['point_exchange']){
                //查询用户的积分
                $integral=$goods_type['point_exchange'];
            }else{
                $integral=$member_account['point'];
            }

          $count_money=$count_money-$integral;
            if($count_money<0){
                $count_money=0;
            }
            $data['count_money']=sprintf("%.2f", $count_money);// 商品金额

        }else{

            $data['count_money']=sprintf("%.2f", $count_money);// 商品金额
        }*/

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
$url=\think\Request::instance()->domain().'/';
        $data['pickup_point_list']=$pickup_point_list;// 自提地址列表
        $data['goods_mansong_gifts']=$goods_mansong_gifts;
        $data['address_default']=$address;
        $data['itemlist'][0]['picture_info']['pic_cover_small']=$url.$data['itemlist'][0]['picture_info']['pic_cover_small'];
        $data['itemlist'][0]['picture_info']['pic_cover']=$url.$data['itemlist'][0]['picture_info']['pic_cover'];
        $data['itemlist'][0]['picture_info']['pic_cover_big']=$url.$data['itemlist'][0]['picture_info']['pic_cover_big'];
        $data['itemlist'][0]['picture_info']['pic_cover_mid']=$url.$data['itemlist'][0]['picture_info']['pic_cover_mid'];
        $data['itemlist'][0]['picture_info']['pic_cover_micro']=$url.$data['itemlist'][0]['picture_info']['pic_cover_micro'];

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
        $goods_sku = new \data\model\NsGoodsSkuModel();
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
        $pay_type = request()->post("pay_type", 1); // 支付方式
        $buyer_invoice = request()->post("buyer_invoice", ""); // 发票
        $pick_up_id = request()->post("pick_up_id", 0); // 自提点
        $shipping_company_id = request()->post("shipping_company_id", 0); // 物流公司
//        $shipping_company_id = $info['co_id']; // 物流公司
        $shipping_time = request()->post("shipping_time", 0); // 配送时间
        $address1 = request()->post("address", 1); // 是否需要寄存
        $paypwd = request()->post("paypwd", ''); // 是否需要寄存

        if(empty($goods_sku_list)){
            $this->error('参数错误！');
        }
       if($user_money |$use_coupon |$integral ){//验证支付密码
         $user_model=new UserModel();
          $payinfo=  $user_model->getInfo(['uid'=>$this->uid],"pay_password");
          if(md5($paypwd)!=$payinfo['pay_password']){
              $this->error('密码不正确！');
          }
        }
        //查询商品的支付类型，只要是积分充抵金额
        $goods_info=explode(':',$goods_sku_list);
//        var_dump($goods_info);die;
        $goods_sku_model=new NsGoodsSkuModel();
        $goods_model=new NsGoodsModel();
        $member = new MemberService();

        $goods_id=  $goods_sku_model->getInfo(['sku_id'=>$goods_info[0]],"goods_id");
        $goods_type=$goods_model->getInfo(['goods_id'=>$goods_id['goods_id']],"point_exchange_type,point_exchange");
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


        $shipping_type = 1; // 配送方式，1：物流，2：自提
        if ($pick_up_id != 0) {
            $shipping_type = 2;
        }
        $member = new Member();
        if($address1==1){
           // $address = 0;
            $address['address']='客户选择寄存';
            $shipping_type = 0;
            $order_type = 3;
        }else{
            $order_type = 1;
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
//var_dump($goods_sku_list);die;
            $order_id = $order->orderCreate($order_type, $out_trade_no, $pay_type, $shipping_type, '1', $buyer_ip, $leavemessage, $buyer_invoice, $shipping_time, $address['mobile'], $address['province'], $address['city'], $address['district'], $address['address'], $address['zip_code'], $address['consigner'], $integral, $use_coupon, 0, $goods_sku_list, $user_money, $pick_up_id, $shipping_company_id, $coin, $address["phone"],$this->uid);
//            $_SESSION['unpaid_goback'] = __URL(__URL__ . "/wap/order/orderdetail?orderId=" . $order_id);
            // 订单创建标识，表示当前生成的订单详情已经创建好了。用途：订单创建成功后，返回上一个界面的路径是当前创建订单的详情，而不是首页
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
     * 获取当前会员的订单列表
     */
    public function myOrderList()
    {
            $status = request()->post('status', 'all');

            $condition['buyer_id'] = $this->uid;
            $condition['is_deleted'] = 0;
            $condition['order_type'] = array(
                "in",
                "1,3"
            );
            if (! empty($this->shop_id)) {
                $condition['shop_id'] = $this->shop_id;
            }

            if ($status != 'all') {
                switch ($status) {
                    case 0:
                        $condition['order_status'] = array(
                            'in',
                            '0,6'
                        );
                        break;
                    case 1:
                        $condition['order_status'] = 1;
                        break;
                    case 2:
                        $condition['order_status'] = 2;
                        break;
                    case 3:
                        $condition['order_status'] = array(
                            'in',
                            '3,4'
                        );
                        break;
                    case 4:
                        $condition['order_status'] = array(
                            'in',
                            [
                                - 1,
                                - 2
                            ]
                        );
                        break;
//                    case 5:
//                        $condition['order_status'] = array(
//                            'in',
//                            '3,4'
//                        );
//                        $condition['is_evaluate'] = array(
//                            'in',
//                            '0,1'
//                        );
                        break;
                    default:
                        break;
                }
            }
            $page_index = request()->post("page", 1);
            // 还要考虑状态逻辑
            $order = new OrderService();
            if($status==5){
                $refund_status=['in',[1,-1,4,5,-3]];
            }else{
                $refund_status=0;
            }
 
            $order_list = $order->getOrderList($page_index, PAGESIZE, $condition, 'create_time desc',$field='*',$refund_status);
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
            "1,3"
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
        //$order_goods_id =$this->request->post('order_goods_id/d',0);

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
        $order_goods_id = request()->post('order_goods_id', '');
        if(!empty($order_id)&&$order_goods_id){
            $order_g=    new NsOrderGoodsModel();
         $orderino= $order_g->getInfo(['order_id'=>$order_id],"*");
        }else{

            $this->error('参数错误!');
        }

//        $order_goods_id = $orderino['order_goods_id'];
        $refund_type = request()->post('refund_type', 1);
        $refund_require_money = request()->post('refund_require_money', 0);
        $refund_reason = request()->post('refund_reason', '');
        $order_service = new OrderService();
        $retval = $order_service->orderGoodsRefundAskfor($order_id, $order_goods_id, $refund_type, $refund_require_money, $refund_reason);


        if($retval){
            $this->success('申请退款成功！',$retval);

        }else{
            $this->success('申请退款错误！');
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
        $order_goods_id = request()->post('order_goods_id', '');
        if(!empty($order_id)&&$order_goods_id){
            $order_g=    new NsOrderGoodsModel();
            $orderino= $order_g->getInfo(['order_id'=>$order_id],"*");
        }else{

            $this->error('参数错误!');
        }
//        $order_goods_id = $orderino['order_goods_id'];
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



    /**
     * 收货
     */
    public function orderTakeDelivery()
    {
        $order_service = new OrderService();
        $order_id = request()->post('order_id', '');
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
            $res = $order_service->deleteOrder($order_id, 2, $this->uid);
            if($res){
                $this->success('删除订单成功！');
            }else{
                $this->error('删除订单失败！');
            }


    }


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
       $this->success('物流详信息',$data);
    }

    /**
     * 查询包裹物流信息
     * 2017年6月24日 10:42:34 王永杰
     */
    public function getOrderGoodsExpressMessage($express_id)
    {
        $express_id = request()->post("express_id", 0); // 物流包裹id
        $res = - 1;
        if ($express_id) {
            $order_service = new OrderService();
            $res = $order_service->getOrderGoodsExpressMessage($express_id);
            $res = array_reverse($res);
        }
        $this->success($res['Reason'],$res);
    }
      //添加线下支付凭证
     public function AddTransaction()
     {
         $out_trade_no = request()->post("out_trade_no", '');
         $path = request()->post("path", '');
         $path = '/'.$path;
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
                 'create_time'=>time(),
                 'order_id'=>$orderinfo['order_id'],
                 'uid'=>$this->uid,
                 'type'=>1,
                 'status'=>0,
                 'num'=>$num,

             ];
             $res= $xianxia->save($data);
             //修改订单状态
           $order=  new NsOrderModel();
          $order_info=   $order->save(['order_status'=>6],['out_trade_no'=>$out_trade_no]);

             Db::commit();
             runhook("Notify", "orderRemindBusiness", [
                 "shop_id" => 0,
                 "out_trade_no" => $out_trade_no,
//                 "uid" => $this->uid
             ]); // 订单商家提醒
                 $this->success('提交成功，请等待审核');


         } catch (\Exception $e) {
             Db::rollback();
             $this->error('提交错误，请稍后再试', $e->getMessage());
           //  return $e->getMessage();
         }

     }

    //添加线下充值
    public function AddChongzhi()
    {
      /*  $out_trade_no = request()->post("out_trade_no", '');
        $path = request()->post("path", '');
        $transaction_id = request()->post("transaction_id", '');
        $num = request()->post("num", '');
        if (empty($out_trade_no)) {
            $this->error('参数错误！');
        }
        if (empty($path)) {
            $this->error('参数错误！');
        }
        if (empty($transaction_id)) {
            $this->error('参数错误！');
        }*/

        $validate = new Validate([
            'num'=>'require|number',
            'out_trade_no'=> 'require',
            'transaction_id'=>'number',
            'path'=>'require'
        ]);
        $validate->message([
            'num.require' => '积分数量未填写!',
            'num.number' => '金额必须是数字',
            'transaction_id.number' => '汇款单号必须是数字!',
            'path.require' => '账号未填写!',

        ]);
        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        $xianxia=    new XianxiaModel();
        $order_model=new NsOrderModel();
        $orderinfo=$order_model->getInfo(['out_trade_no'=>$data['out_trade_no']],"order_id");
        Db::startTrans();
        try {
            $date=[
                'out_trade_no'=>$data['out_trade_no'],
                'path'=>'/'.$data['path'],
                'transaction_id'=>$data['transaction_id'],
                'create_time'=>time(),
                'order_id'=>$orderinfo['order_id'],
                'uid'=>$this->uid,
                'type'=>2,
                'status'=>0,
                'num'=>$data['num'],

            ];
            $res= $xianxia->save($date);
            //修改订单状态
            $order=  new NsOrderModel();
            $order_info=   $order->save(['order_status'=>6],['out_trade_no'=>$data['out_trade_no']]);

            Db::commit();
            runhook("Notify", "rechargeSuccessBusiness", [
                "shop_id" => 0,
                "out_trade_no" => $data['out_trade_no'],
                "uid" => $this->uid
            ]); // 用户现金充值成功商家提醒
            $this->success('提交成功，请等待审核');


        } catch (\Exception $e) {
            Db::rollback();
            $this->error('提交错误，请稍后再试', $e->getMessage());
            //  return $e->getMessage();
        }

    }



    /**
     *个人中心二次转售商品列表
     */
    public function userResellerOrderList(){
        $config=new Config();
        $instance_id=$this->instance_id;
        $key="TO_ORDER_ISOPEN";
        $res=$config->getConfig($instance_id, $key);
        if($res['value']=='0'){
            $this->error("敬请期待");
        }else{
            $status = request()->post('tostatus', 'all');
            $page_index = request()->post("page", 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $condition['buyer_id'] = $this->uid;
            if (! empty($this->shop_id)) {
                $condition['shop_id'] = $this->shop_id;
            }
            $condition['order_status'] = 4;
            if ($status != 'all') {
                switch ($status ){
                    case 0:
                        $condition['tostatus']=0;
                        break;
                    case 1:
                        $condition['tostatus']=1;
                        break;
                    case 2:
                        $condition['tostatus']=2;
                        break;
                    case 3:
                        $condition['tostatus']=3;
                        break;
                }
            }
            // 还要考虑状态逻辑
            $order = new OrderService();
            $order_list = $order->getToOrderList($page_index, $page_size, $condition, 'create_time desc');
            if($order_list[data]){
                $this->success('获取列表成功',$order_list);
            }else{
                $this->error("未获取到转售数据");
            }
        }
    }


    /**
     * 获取要转售的商品详情(用于设置出售价钱和数量)
     */
    public function getOrderGoods(){
        $orderId=request()->post('order_id',0);

        if($orderId==0){
            $this->error("获取订单失败");
        }
        $orderGoods=new NsOrderGoodsModel();
        $ordergoods=$orderGoods->getInfo(['order_id'=>$orderId],'*');
        //获取转售表中是否有转售商品的id
        $toorderModel=new NsToorderModel();
        $toorderId=$toorderModel->getInfo(['order_id'=>$orderId,'seller_id'=>$this->uid,'tostatus'=>0]);

        if($toorderId){
           $ordergoods['price']=$toorderId['price'];
        }

        $orderModel=new NsOrderModel();
        $order=$orderModel->getInfo(['order_id'=>$orderId],'*');

        //根据商品订单id查询订单完成时间
        $config=new Config();
        $instance_id=$this->instance_id;
        $key="OPENSELLERTIME";
        $res=$config->getConfig($instance_id, $key);
        $totime=$order['finish_time'] + ($res['value']*24*60*60) ;

//        //允许转售的时间
        if($totime<time()){
            //出售商品的商品详情
            $goods = new GoodsService();
            $goods_detail= $goods->getBasisGoodsDetail($ordergoods['goods_id']);
            //拼接图片全路径
            $goods_detail['picture_detail']['pic_cover_mid']=\think\Request::instance()->domain().'/'.$goods_detail['picture_detail']['pic_cover_mid'];
            $goods_detail['picture_detail']['pic_cover']=\think\Request::instance()->domain().'/'.$goods_detail['picture_detail']['pic_cover'];
            $goods_detail['picture_detail']['pic_cover_big']=\think\Request::instance()->domain().'/'. $goods_detail['picture_detail']['pic_cover_big'];
            $goods_detail['picture_detail']['pic_cover_small']=\think\Request::instance()->domain().'/'.$goods_detail['picture_detail']['pic_cover_small'];
            $goods_detail['picture_detail']['pic_cover_micro']=\think\Request::instance()->domain().'/'.$goods_detail['picture_detail']['pic_cover_micro'];

            $data['goods_detail']=$goods_detail ;
            $data['ordergoods']=$ordergoods;
            $data['order']=$order;
            if($data){
                $this->success("获取要出售商品详情成功",$data);
            }else{
                $this->success("未获取到列表数据");
            }
        }else{
            $this->error("未到可转售时间");
        }



    }


    /**
     * 获取转售商品的允许转售时间
     */
//    public function getYesToTime(){
//        $orderId=request()->post('order_id',0);
//        if($orderId==0){
//            $this->error("获取订单失败");
//        }
//        //根据商品订单id查询订单完成时间
//        $orderGoods=new NsOrderModel();
//        $finish_time=$orderGoods->getInfo(['order_id'=>$orderId],'finish_time');
//        //根据商品订单id查询订单完成时间
//        $config=new Config();
//        $instance_id=$this->instance_id;
//        $key="OPENSELLERTIME";
//        $res=$config->getConfig($instance_id, $key);
//        $totime=$finish_time['finish_time'] + ($res['value']*24*60*60) ;
//        //允许转售的时间
//        if($totime<time()){
//            $this->success("可以转售");
//        }else{
//            $this->error("未到转售时间");
//        }
//    }


    /**
     * 设置商品转售价格和数量
     */
    public function postToMoney(){
        $order_id=request()->post('order_id',0);
        $toPrice=request()->post('price',0);
        $memo=request()->post('memo','');
        $order=new OrderService();
        //获取转售商品的转售价格区间
        $toSetprice=$order->getToPrice($order_id);

        $toMinPrice=$toSetprice['tominprice'];
        $toMaxPrice=$toSetprice['tomaxprice'];
        $Price=$toSetprice['price'];

        if($toMinPrice > $toPrice || $toMaxPrice < $toPrice){
            $this->error("设置转售价超过允许的间隔区间");
        }

        if ($order_id == 0 || $toPrice == 0 ) {
            $this->error("没有获取到信息");
        }

        //设置转售价格时将转售信息存入
        $toorder=new NsToorderModel();
        $goods_money=$toorder->getInfo(['order_id'=>$order_id]);
        if(!$goods_money){
          $todata['price']=$Price;
        }

        $todata['order_id']=$order_id;
        $todata['seller_id'] = $this->uid;
//        $todata['tocreate_time']=$toSetprice['create_time'];
        $todata['tocreate_time']=time();
        $todata['memo'] =$memo;
        $todata['tostatus'] =1;
        $where['order_id']=$order_id;
        $where['seller_id']=$this->uid;
        $where['tostatus']=0;

        $todata['toprice']=$toPrice;


        $upMoney=$toorder->where($where)->select();
        if($upMoney){
            $tores=$toorder->save($todata,$where);
        }else{
            $tores=$toorder->save($todata);
        }
        //设置转售后修改订单表的转售状态
        $orderModel=new NsOrderModel();
        $data['tostatus']=1;
        $cond['order_id']=$order_id;
        $res=$orderModel->save($data,$cond);
        if($tores && $res){
            $this->success("转售设置成功");
        }else{
            $this->error("转售设置失败");
        }
    }

    /**
     * 转卖商品需要的详细信息(用于填写买受人的信息)
     */
    public function togetgoodsinfo(){
        $order_id = request()->post('order_id', 0);

        if ($order_id  == 0) {
            $this->error("没有获取到商品信息");
        }

        //判断商品是否允许转售是否通过
        $orderGoods=new NsOrderModel();
        $orderTosoldout=$orderGoods->getInfo(['order_id'=>$order_id],'tosoldout');
        if($orderTosoldout['tosoldout']==1){
            $this->error("转售未通过");
        }

        $order_goods=new NsOrderGoodsModel();
        $goods_info  = $order_goods->getInfo(['order_id'=>$order_id],'goods_id');

        //取出转售商品的价格等详情
        $toorderModel=new NsToorderModel();
        $data['sellDetail']=$toorderModel->getInfo(['order_id'=>$order_id,'tostatus'=>1],'*');

        //出售商品的商品详情
        $goods = new GoodsService();
        $goods_detail = $goods->getBasisGoodsDetail($goods_info['goods_id']);

        //拼接图片全路径
        $goods_detail['picture_detail']['pic_cover_mid']=\think\Request::instance()->domain().'/'.$goods_detail['picture_detail']['pic_cover_mid'];
        $goods_detail['picture_detail']['pic_cover']=\think\Request::instance()->domain().'/'.$goods_detail['picture_detail']['pic_cover'];
        $goods_detail['picture_detail']['pic_cover_big']=\think\Request::instance()->domain().'/'. $goods_detail['picture_detail']['pic_cover_big'];
        $goods_detail['picture_detail']['pic_cover_small']=\think\Request::instance()->domain().'/'.$goods_detail['picture_detail']['pic_cover_small'];
        $goods_detail['picture_detail']['pic_cover_micro']=\think\Request::instance()->domain().'/'.$goods_detail['picture_detail']['pic_cover_micro'];

        $data['goods_detail']=$goods_detail ;
        $this->success('获取成功',$data);
    }



    //提交转售商品买受人的信息,
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
        $touser_info=$user->getInfo(['user_tel'=>$mobile],"*");

        if($touser_info['real_name']!=$real_name){
            $this->error("姓名验证不正确，请确认后再提交");
        }
        //获取自己的信息,不允许转卖给自己
        $seller=$user->getInfo(['uid'=>$this->uid],'user_tel');
        if($seller['user_tel']==$mobile){
            $this->error("不能转卖给自己");
        }

        //转手成功后将转售信息保存至商品转售表
        $todata['touid'] = $touser_info['uid'];
        $todata['tosell_time']=time();
        $todata['tocreate_time']=time();
        $todata['tostatus']=2;
        $condition['order_id']=$order_id;
        $condition['seller_id']=$this->uid;
        $toorder=new NsToorderModel();
        $tores=$toorder->save($todata,$condition);
        //查询转售价钱
        $price=$toorder->where($condition)->field('toprice')->find();

        //转售成功后将买者的商品信息再次设置到转售列表用于再次转售
        $data['order_id']=$order_id;
        $data['tocreate_time']=time();
        $data['price']=$price['toprice'];
        $data['seller_id']=$touser_info['uid'];

        if($tores){
            $to=new NsToorderModel();
            $res=$to->save($data);
        }
        if($tores){
            $this->success('转售成功!');
        }else{
            $this->error("转售失败！");
        }
    }


    /**
     * 用户取消转售中商品
     */
    public function getCancel()
    {
        $sellerId=request()->post('seller_id',$this->uid);
        $order_id=request()->post('order_id');
        //查询取消转售中商品的转售价格详情

        $where['seller_id']=$sellerId;
        $where['order_id']=$order_id;
        $where['tostatus']=1;
        $data['tostatus']=0;
        $data['memo']="";
        $toorderModel=new NsToorderModel();
        $toGoods=$toorderModel->getInfo($where,'price');
        $data['price']=$toGoods['price'];
        $res=$toorderModel->save($data,$where);
        if($res){
            $this->success("取消转售成功");
        }else{
            $this->error("取消转售失败");
        }
    }


    /**
     * 交易关闭
     */
    public function orderClose()
    {
        $order_service = new OrderService();
        $order_id = request()->post('order_id', '');
        $res = $order_service->orderClose($order_id);
       if($res){

           $this->success('关闭成功！');
       }else{
           $this->error('关闭失败！');

       }
    }

    /**
     * 商品评价提交
     * @Date 2018-10-25
     * @author kongbo
     */
    public function addGoodsEvaluate()
    {
        $uid=$this->getUserId();
        $order = new OrderService();
        $order_id = request()->post('order_id/d', '0');
        $orderinfo=$order->getOrderInfo($order_id,$uid);
        if(!$orderinfo){
            $this->error('没有这个订单');
        }

        $goodsEvaluateArray = request()->post('goodsEvaluate/a', '');
        $memberService = new MemberService();
        $dataArr = array();
        foreach ($goodsEvaluateArray as $key => $goodsEvaluate) {
            $orderGoods = $order->getOrderGoodsInfo($goodsEvaluate['order_goods_id']);
            $data = array(
                'order_id' => $order_id,
                'order_no' => $orderinfo['order_no'],
                'order_goods_id' => intval($goodsEvaluate['order_goods_id']),

                'goods_id' => $orderGoods['goods_id'],
                'goods_name' => $orderGoods['goods_name'],
                'goods_price' => $orderGoods['goods_money'],
                'goods_image' => $orderGoods['goods_picture'],
                'shop_id' => $orderGoods['shop_id'],
                'shop_name' => "默认",
                'content' => $goodsEvaluate['content'],
                'addtime' => time(),
                'image' => $goodsEvaluate['imgs'],

                // 'explain_first' => $goodsEvaluate->explain_first,
                'member_name' => $memberService->getMemberDetail()['member_name'],
                'explain_type' => $goodsEvaluate['explain_type'],
                'uid' => $this->uid,
                'is_anonymous' => $goodsEvaluate['is_anonymous'],
                'scores' => intval($goodsEvaluate['scores'])
            );
            $dataArr[] = $data;
        }
        $result = $order->addGoodsEvaluate($dataArr, $order_id);
        if ($result) {
            $Config = new Config();
            $integralConfig = $Config->getIntegralConfig($this->instance_id);
            //读取店铺配置项，是否开启评价赠送优惠券
            if ($integralConfig['comment_coupon'] == 1) {
                $rewardRule = new PromoteRewardRule();
                $res = $rewardRule->getRewardRuleDetail($this->instance_id);
                if ($res['comment_coupon'] != 0) {
                    $member = new Member();
                    $retval = $member->memberGetCoupon($this->uid, $res['comment_coupon'], 2);
                }
            }
            $this->success('评论成功');
        }else{
            $this->error('评论失败');
        }

    }
    public function addGoodsEvaluate1()
    {
        $order = new OrderService();
        $order_id = request()->post('order_id', '');
        $order_no = request()->post('order_no', '');
        $order_id = intval($order_id);
        $order_no = intval($order_no);
        $goodsEvaluateArray = request()->post('goodsEvaluate/a', '');
        $memberService = new MemberService();
        $dataArr = array();
            $orderGoods = $order->getOrderGoodsInfo($goodsEvaluateArray['order_goods_id']);
            var_dump($orderGoods);die;
            $data = array(
                'order_id' => $order_id,
                'order_no' => $order_no,
                'order_goods_id' => intval($goodsEvaluateArray['order_goods_id']),
                'goods_id' => $orderGoods['goods_id'],
                'goods_name' => $orderGoods['goods_name'],
                'goods_price' => $orderGoods['goods_money'],
                'goods_image' => $orderGoods['goods_picture'],
                'shop_id' => $orderGoods['shop_id'],
                'shop_name' => "默认",
                'content' => $goodsEvaluateArray['content'],
                'addtime' => time(),
                'image' => $goodsEvaluateArray['imgs'],
                // 'explain_first' => $goodsEvaluate->explain_first,
                'member_name' => $memberService->getMemberDetail()['member_name'],
                'explain_type' => $goodsEvaluateArray['explain_type'],
                'uid' => $this->uid,
                'is_anonymous' => $goodsEvaluateArray['is_anonymous'],
                'scores' => intval($goodsEvaluateArray['scores'])
            );
            $dataArr[] = $data;
        $result = $order->addGoodsEvaluate($dataArr, $order_id);
        if ($result) {
            $Config = new Config();
            $integralConfig = $Config->getIntegralConfig($this->instance_id);
            //读取店铺配置项，是否开启评价赠送优惠券
            if ($integralConfig['comment_coupon'] == 1) {
                $rewardRule = new PromoteRewardRule();
                $res = $rewardRule->getRewardRuleDetail($this->instance_id);
                if ($res['comment_coupon'] != 0) {
                    $member = new Member();
                    $retval = $member->memberGetCoupon($this->uid, $res['comment_coupon'], 2);
                }
            }
            $this->success('评论成功');
        }else{
            $this->error('评论失败');
        }

    }

    /**
     * 商品-追加评价提交数据
     * @Date 2018-10-25
     * @author kongbo
     */
    public function addGoodsEvaluateAgain()
    {
        $order = new OrderService();
        $order_id = request()->post('order_id/d', '0');
        $uid=$this->getUserId();
        $orderinfo=$order->getOrderInfo($order_id,$uid);
        if(!$orderinfo){
            $this->error('没有这个订单');
        }
        $goodsEvaluateArray = request()->post('goodsEvaluate/a', '');
        $flag=true;
        foreach ($goodsEvaluateArray as $v) {
            $res = $order->addGoodsEvaluateAgain($v['content'], $v['imgs'], $v['order_goods_id']);
            if(!$res){
                $flag=false;
                break;
            }
        }
        if ($flag) {
            $data = array(
                'is_evaluate' => 2
            );
            $result = $order->modifyOrderInfo($data, $order_id );
            $this->success('追评成功');
        }else{
            $this->error('追评失败');
        }

    }


}