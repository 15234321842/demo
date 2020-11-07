<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/22
 * Time: 11:44
 */

namespace app\appapi\controller;
use data\service\Member as Member;
use data\service\Goods;

class Cart extends BaseController
{
    public $uid;
    public function __construct()
    {
        parent::__construct();
        $this->uid=$this->getUserId();
        $this->shop_id=0;
        $this->instance_id=0;


    }

    /**
     * 添加购物车
     */
    public function addCart()
    {
        $cart_detail=$this->request->param('cart_detail/a');
        if ( empty($cart_detail)) {
            $this->error('错误参数');
        }
        $cart_tag = request()->post('cart_tag', '');
        $uid = $this->uid;
//        return $uid;
        $shop_id = $cart_detail["shop_id"];
//        $shop_id = 0;
        $shop_name = $cart_detail["shop_name"];
//        $shop_name = "分销商城";
        $goods_id = $cart_detail['trueId'];
//        $goods_id = 48;
        $goods_name = $cart_detail['goods_name'];
//        $goods_name = "\n\t\t\t\t\tm8";
        $num = $cart_detail['count'];
//        $num = 1;
        $sku_id = $cart_detail['select_skuid'];
//        $sku_id = 106;
        $sku_name = $cart_detail['select_skuName'];
//        $sku_name = '';
        $price = $cart_detail['price'];
//        $price = 2000;
        $cost_price = $cart_detail['cost_price'];
        $picture = $cart_detail['picture'];
//        $picture = 169;
        $member=new Member();

        if(empty($sku_id)){
            $this->error('参数错误！');

        }
        if(empty($num)){
            $this->error('参数错误！');

        }
        if(empty($price)){
            $this->error('参数错误！');

        }
        if (! empty($this->uid)) {
            $goods = new Goods();
            $retval = $goods->addCart($uid, $shop_id, $shop_name, $goods_id, $goods_name, $sku_id, $sku_name, $price, $num, $picture, 0);
        } else {
           $this->error('请先登录！');
        }

        $this->success('添加购物车成功',$retval) ;
    }

    /**
     * 购物车修改数量
     */
    public function cartAdjustNum()
    {
          $cart_id = request()->post('cartid', '');
           $num = request()->post('num', '');
           if($num<=0){
               $num = 1;
           }
            $goods = new Goods();
            if(empty($cart_id)){
                $this->error('参数出错！');
            }
            if(empty($num)){
                $this->error('参数出错！');
            }
            $retval = $goods->cartAdjustNum($cart_id, $num,$this->uid);
          if($retval){
              $this->success('修改成功！',$retval);
          }else{
              $this->error('修改出错，请稍后再试！');
          }
    }

    /**
     * 购物车项目删除
     */
    public function cartDelete()
    {

            $cart_id_array = request()->post('cart_id', '');
            $goods = new Goods();
            $retval = $goods->cartDelete($cart_id_array);
        if($retval){
            $this->success('删除成功！',$retval);
        }else{
            $this->error('删除出错，请稍后再试！');
        }

    }


    /**
     * 购物车页面
     */
    public function cart()
    {
        $goods = new Goods();
        $cartlist = $goods->getCart($this->uid, 0);

        // 店铺，店铺中的商品 多店铺
//        $list = Array();
//        for ($i = 0; $i < count($cartlist); $i ++) {
//            $list[$cartlist[$i]["shop_id"] . ',' . $cartlist[$i]["shop_name"]][] = $cartlist[$i];
//        }
//
//
//         $data['list']=$list;
//         $data['countlist']=count($cartlist);
      $this->success('购物车信息！',$cartlist);
    }




}