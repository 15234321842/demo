<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/3
 * Time: 18:09
 */

namespace app\appapi\controller;


use data\model\NsOrderGoodsModel;
use data\model\NsOrderModel;
use data\model\NsToorderModel;

class Credential extends  BaseController
{
    protected $uid;
    protected $instance_id;
    public function __construct()
    {
        parent::__construct();
       $this->uid=$this->getUserId();
        $this->instance_id=0;
    }


    public function index(){
        $order_id = request()->post('order_id','');
        if(empty($order_id)){
            $this->error('参数错误！');
            return;
        }

        //确认是否转售了
        $ordertolist=  New NsToorderModel();
        $ordertolist_info= $ordertolist->getInfo(['order_id'=>$order_id,'seller_id'=>$this->uid],"tostatus");
        if($ordertolist_info['tostatus']==2){
            $this->error('商品已经转售！');
            return;
        }
        //获取订单信息
        $order_g=new NsOrderGoodsModel();
        $order=new NsOrderModel();
        $orderinfo=$order->getInfo(['order_id'=>$order_id],"*");
        $ordergoods_info=$order_g->getInfo(['order_id'=>$order_id],"*");
        $order_no=$orderinfo['order_no'];
        $goods_name=$ordergoods_info['goods_name'];
        $sku_name=$ordergoods_info['sku_name'];
        $time1=$orderinfo['create_time'];
        $time=date("Y-m-d H:i:s",$time1) ;
        $upload_path = "upload/credential"; // 后台推广二维码模版
        if (! file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        $thumb = $upload_path . '/' . 'credential_' . $this->uid . '_' . $order_id . '.png';
     if (file_exists($thumb)) {
         $this->success('证书',['data'=>'http://'.$_SERVER['HTTP_HOST'].'/'.$thumb]);
        }
        // 背景图片
        $dst = "public/static/images/qrcode_bg/credential.png";
        // 生成画布
        list ($max_width, $max_height) = getimagesize($dst);
        $dests = imagecreatetruecolor($max_width, $max_height);
        $dst_im = getImgCreateFrom($dst);
        imagecopy($dests, $dst_im, 0, 0, 0, 0, $max_width, $max_height);
        imagedestroy($dst_im);
        $rgb = hColor2RGB("#333333");
        $rgb1 = hColor2RGB("#ee4511");
        $bg = imagecolorallocate($dests, $rgb['r'], $rgb['g'], $rgb['b']);
        $bg1 = imagecolorallocate($dests, $rgb1['r'], $rgb1['g'], $rgb1['b']);
        $name_top_size = "135px" * 2 + "23";
        @imagefttext($dests, 22, 0, "145px" * 2, 230, $bg1, "public/static/font/Microsoft.ttf", $order_no );
        @imagefttext($dests, 16, 0, "113px" * 2, $name_top_size, $bg, "public/static/font/Microsoft.ttf", $goods_name );
        @imagefttext($dests,16, 0, "113px" * 2, $name_top_size + 40, $bg, "public/static/font/Microsoft.ttf",$sku_name );
        @imagefttext($dests,16, 0, "113px" * 2, $name_top_size + 160, $bg, "public/static/font/Microsoft.ttf", $time );
        imagejpeg($dests,$thumb);
       $res= $order->save(['credential'=>$thumb],['order_id'=>$order_id]);
       if($res){
           $this->success('证书',['data'=>'http://'.$_SERVER['HTTP_HOST'].'/'.$thumb]);
       }else{
           $this->error('生成证书错误，请稍后再试！');
       }
    }
}