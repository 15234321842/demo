<?php
/**
 * Pay.php
 * 天贵
 * @author : shuangxi
 * @date : 2017.10.18
 */
namespace app\appapi\controller;
use data\extend\QRcode;
use data\service\BaseService;
use data\service\Config;
use data\service\Member as MemberService;
use data\service\Pay\WeiXinPay;
use data\service\UnifyPay;
use data\service\WebSite;
use think\Controller;
use think\Log;
use data\service\ServicesOrder as OrderService;
use data\model\NsOrderPaymentModel;
use data\model\NsOrderServicesModel as NsOrderModel;
use data\model\UserModel;
use think\response\Redirect;
use think\exception\HttpResponseException;



/**
 * 服务订单支付控制器
 *
 * @author Administrator
 *
 */
class Servicespay extends BaseController
{


    public $style;
    public $shop_config;

    public function __construct()
    {
      parent::__construct();
        $this->web_site = new WebSite();
        $web_info = $this->web_site->getWebSiteInfo();
        $shopname = $web_info['title'];
        $title = $web_info['title'];

        $dateinfo["web_info"] = $web_info;
        $dateinfo["shopname"] = $shopname;
        $dateinfo["title"] = $title;

        $this->style = 'wap/default';

        $dateinfo["style"] = $this->style;
        $Config = new Config();
        $seoConfig = $Config->getSeoConfig(0);

        // 购物设置
        $this->shop_config = $Config->getShopConfig(0);
        $dateinfo["shop_config"] =$this->shop_config;
        $dateinfo["seoconfig"] = $seoConfig;

//        if ($web_info['wap_status'] == 2) {
//            webClose($web_info['close_reason']);
//        }
        // 获取会员昵称
        $member = new MemberService();
        $member_info = $member->getMemberDetail();
        $dateinfo["member_info"] = $member_info;


        return ['dateinfo'=>$dateinfo,'status'=>1,'message'=>'Pay自动加载信息'];
    }

    /*
     * 订单后期支付相关信息,待付款订单
     */
    public function orderPay()
    {
        $order_id = request()->post('order_id','');
        if(empty($order_id) || $order_id ==0){
            return [
                'status'=>-1,
                'msg'=>"参数错误！"
            ];
        }

        $order_service = new OrderService();
        // 更新支付流水号
        $new_out_trade_no = $order_service->getOrderNewOutTradeNo($order_id);
        if (empty($new_out_trade_no)) {
            return ['status'=>1,'error'=>'没有获取到支付信息'];
        }

        $pay = new UnifyPay();
        $pay_config = $pay->getPayConfig();

        $payinfo["pay_config"] = $pay_config;

        $pay_value = $pay->getPayInfo($new_out_trade_no);

        if (empty($pay_value)) {

            return ['status'=>-1,'msg'=>'订单主体信息已发生变动'];
        }

        if ($pay_value['pay_status'] == 1) {
            // 订单已经支付
            return ['status'=>-1,'msg'=>'订单已经支付或者订单价格为0.00，无需再次支付!'];
        }

        //shuangxi 修改 普通订单与指价订单 关闭时间不同
        $order=new NsOrderModel();
        $condition['out_trade_no']=$new_out_trade_no;
        $order_info=$order->getInfo($condition, $field = 'finger_id');
        $zero1 = time(); // 当前时间 ,注意H 是24小时 h是12小时
        $zero2 = $pay_value['create_time'];


        if (empty($order_info['finger_id']) && $zero1 > ($zero2 + ($this->shop_config['order_buy_close_time'] * 60))) {

            return ['status'=>-1,'msg'=>'订单已经支付或者订单价格为0.00，无需再次支付!'];

        }elseif(!empty($order_info['finger_id']) && $zero1 >($zero2 + ($this->shop_config['day']*24*60*60))){

            return ['status'=>-1,'msg'=>'订单已关闭'];

        } else {

            return ['pay_value'=>$pay_value,'pay_config'=>$payinfo];
        }

    }

    /**
     * 充值
     */
    public function createRechargeOrder(){
        $userModel=new UserModel();
        $condition['uid']=$this->uid;
        $user_info=$userModel->getInfo($condition,'*');
        if($user_info['is_worker']==1){
            return ['data'=> 0 ,'status'=>-1,'message'=>' 员工不能充值！'];
        }

        $recharge_money = request()->post('recharge_money', '');
        if (empty($recharge_money)) {
            return ['status'=>-1,'message'=>'充值金额不能为空'];
        }
        $pay = new UnifyPay();
        $out_trade_no = $pay->createOutTradeNo();//交易号
        $member = new MemberService();
        $retval = $member->createMemberRecharge($recharge_money, $this->member_info['uid'], $out_trade_no);
        if($retval==false){
            return ['status'=>-1,'message'=>'创建交易号失败'];
        }
        $pay_config = $pay->getPayConfig();

        $payinfo["pay_config"] = $pay_config;

        $pay_value = $pay->getPayInfo($out_trade_no);

        if (empty($pay_value)) {

            return ['status'=>-1,'msg'=>'订单主体信息已发生变动'];
        }

        if ($pay_value['pay_status'] == 1) {
            // 订单已经支付
            return ['status'=>-1,'msg'=>'订单已经支付或者订单价格为0.00，无需再次支付!'];
        }


        $zero1 = time(); // 当前时间 ,注意H 是24小时 h是12小时
        $zero2 = $pay_value['create_time'];

        if($zero1 >($zero2 + ($this->shop_config['day']*24*60*60))){

            return ['status'=>-1,'msg'=>'订单已关闭'];

        } else {

            return ['pay_value'=>$pay_value,'pay_config'=>$payinfo];
        }
    }

    /**
     * 获取支付相关信息
     */
    public function getPayValue()
    {
   $out_trade_no = request()->post('out_trade_no', '');

        if (empty($out_trade_no)) {
            $this->error("没有获取到支付信息");
        }
       $deviceType= $this->deviceType ;//设备类型
        $pay = new UnifyPay();
        $pay_config = $pay->getPayConfig();

        if($deviceType=='wxapp'){

           unset($pay_config['ali_pay_config']) ;
        }

        $data['pay_config']=$pay_config;
        $data['deviceType']=$deviceType;
        $pay_value = $pay->getPayInfo($out_trade_no);

        if (empty($pay_value)) {
            $this->error("订单主体信息已发生变动!", __URL(__URL__ . "/member/index"));
        }

        if ($pay_value['pay_status'] == 1) {
            // 订单已经支付

            $this->error("订单已经支付或者订单价格为0.00，无需再次支付!");
        }
        if ($pay_value['type'] == 1) {
            // 订单
            $order_status = $this->getOrderStatusByOutTradeNo($out_trade_no);
            // 订单关闭状态下是不能继续支付的
            if ($order_status == 5) {
                $this->error("订单已关闭");
            }
        }

        $zero1 = time(); // 当前时间 ,注意H 是24小时 h是12小时
        $zero2 = $pay_value['create_time'];
        if ($zero1 > ($zero2 + ($this->shop_config['order_buy_close_time'] * 60))) {
            $this->error("订单已关闭");
        } else {
           $data['pay_value']=$pay_value;
            $this->success('获取支付相关信息',$data);
        }
    }
    /**
     * 获取订单支付剩余时间
     * 创建时间：2017年9月14日 16:36:06 王永杰
     */
    public function getPayTimeRemaining()
    {
        $out_trade_no = request()->post('out_trade_no', '');
        if (empty($out_trade_no)) {
            return "00:00:00";
        }

        $pay = new UnifyPay();
        $pay_value = $pay->getPayInfo($out_trade_no);
        if (! empty($pay_value)) {

            $startdate = date("Y-m-d H:i:s");
            $enddate = date("Y-m-d H:i:s", $pay_value['create_time'] + ($this->shop_config['order_buy_close_time'] * 60));

            // $date = floor((strtotime($enddate) - strtotime($startdate)) / 86400);
            $hour = floor((strtotime($enddate) - strtotime($startdate)) % 86400 / 3600);
            $minute = floor((strtotime($enddate) - strtotime($startdate)) % 86400 / 60);
            $second = floor((strtotime($enddate) - strtotime($startdate)) % 86400 % 60);
            if ($hour < 10) {
                $hour .= "0";
            }
            if ($minute < 10) {
                $minute .= "0";
            }
            if ($second < 10) {
                $second .= "0";
            }
            return $hour . ":" . $minute . ":" . $second;
        } else {
            return "00:00:00";
        }
    }

    /**
     * 微信支付
     */
    public function companyPaymentpx(){
        $deviceType=$this->deviceType ;
        $out_trade_no= request()->get('out_trade_no', '');
        if(empty($out_trade_no)){
            $this->error('交易号不能为空！');
        }
        $order_payment=new NsOrderPaymentModel();
        $payment_info=$order_payment->getInfo(['out_trade_no'=>$out_trade_no],'pay_money');
        if($payment_info['pay_money']=='0'){

            $this->error('支付金额不能为0！');
        }
        $pay_money=$payment_info['pay_money'];

        if($deviceType=='mobile' || $deviceType==''){
            //h5微信支付
            $red_url = str_replace("/index.php", "", __URL__);
            $red_url = str_replace("index.php", "", $red_url);
            $red_url = $red_url . "/weixinpay.php";
            $pay=  new UnifyPay();
            $res = $pay->wchatPay($out_trade_no, 'JSAPI', $red_url);

            if (! empty($res["return_code"]) && $res["return_code"] == "FAIL" && $res["return_msg"] == "JSAPI支付必须传openid") {
                $this->error('JSAPI支付必须传openid！');
            } else {
                $retval = $pay->getWxJsApi($res);

                $str = <<<EOT
                <script type="text/javascript">
                //调用微信JS api 支付
                function jsApiCall(){
                   WeixinJSBridge.invoke('getBrandWCPayRequest', {$retval},
                      function(res){
                        alert(JSON.stringify(res));
                         WeixinJSBridge.log(res.err_msg);
                         if(res.err_msg == "get_brand_wcpay_request:ok" ) {
                            //window.location.href="{:__URL('APP_MAIN/pay/wchatpayresult?msg=1&out_trade_no='.$out_trade_no)}";
                         }else if(res.err_msg == "get_brand_wcpay_request:fail"){
                            alert(JSON.stringify(res));
                            //window.location.href="{:__URL('APP_MAIN/pay/wchatpayresult?msg=0&out_trade_no='.$out_trade_no)}";
                         }else{
                            //window.location.href="{:__URL('APP_MAIN/pay/wchatpayresult?msg=0&out_trade_no='.$out_trade_no)}";
                         }
                         // 使用以上方式判断前端返回,微信团队郑重提示：res.err_msg 将在用户支付成功后返回 ok，但并不保证它绝对可靠。
                      }
                   );
                }
                
                function WeixinPay(){
                   var retval = {$retval};
                   if(retval !=null && retval.return_code == "FAIL"){
                      alert(JSON.stringify(retval));
                   }else{
                      if(typeof WeixinJSBridge == "undefined"){
                         if(document.addEventListener ){
                            document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
                         }else if (document.attachEvent){
                            document.attachEvent('WeixinJSBridgeReady', jsApiCall);
                            document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
                         }
                      }else{
                         jsApiCall();
                      }
                   }
                }
                WeixinPay();
                </script>
EOT;

echo $str;
exit();
                //$this->success('微信h5支付',$retval);
            }

        }else{
            //微信app支付
            $res = file_get_contents("http://{$_SERVER['SERVER_NAME']}/data/extend/wxpaycom/comwxpay.php?out_trade_no=$out_trade_no&pay_money=$pay_money&deviceType=$deviceType");
            $this->success('微信app支付',$res);
        }



    }

    /**
     * 支付宝支付
     */
    public function companyPaymenttail(){


        $out_trade_no= request()->post('out_trade_no', '');
        if(empty($out_trade_no)){
            $this->error('交易号不能为空！');
        }
        $order_payment=new NsOrderPaymentModel();
        $payment_info=$order_payment->getInfo(['out_trade_no'=>$out_trade_no],'pay_money');
        if($payment_info['pay_money']=='0'){
            $this->error('支付金额不能为0！');
        }

            $pay_money=$payment_info['pay_money'];
            /* $url="http://{$_SERVER['SERVER_NAME']}/data/extend/alipayrsa2/comalipay.php?out_trade_no=$out_trade_no&pay_money=$pay_money";
              $this->redirect($url);*/
            $res =   file_get_contents("http://{$_SERVER['SERVER_NAME']}/data/extend/alipayrsa2/comalipay.php?out_trade_no=$out_trade_no&pay_money=$pay_money");

            $this->success('支付宝app支付',$res);

    }


    /**
     * 微信支付回调
     */
    public function payCallpay(){
        $out_trade_no = request()->get('out_trade_no','');// 流水号
        if(empty($out_trade_no)){

            $this->error('交易号不能为空!');
        }
        $pay = new UnifyPay();
        $retval = $pay->onlinePay($out_trade_no, 1,'');
        if(!empty($retval)){

            $this->success('微信支付回调',['out_trade_no'=>$out_trade_no]);
        }
    }

    /**
     * 微信支付回调--充值
     */
    public function payCallpay2(){
        $out_trade_no = request()->get('out_trade_no','');// 流水号
        if(empty($out_trade_no)){
            return ['status'=>-1,'msg'=>'交易号不能为空!'];
        }
        $pay = new UnifyPay();
        $retval = $pay->onlinePay($out_trade_no, 4);
        if(!empty($retval)){
            return ['out_trade_no'=>$out_trade_no];
        }
    }

    /**
     *
     */
    public function aliUrlBack(){
        $out_trade_no = request()->get('out_trade_no','');// 流水号
        if(empty($out_trade_no)){
            $this->error('交易号不能为空!');
        }
        $pay = new UnifyPay();
        $retval = $pay->onlinePay($out_trade_no, 2);
        if(!empty($retval)){
            $this->success('ali支付回调',['out_trade_no'=>$out_trade_no]);
        }
    }

    public function getOrderStatusByOutTradeNo($out_trade_no)
    {
        $order = new OrderService();
        $order_status = $order->getOrderStatusByOutTradeNo($out_trade_no);
        if (! empty($order_status)) {
            return $order_status['order_status'];

        }
        return 0;
    }
    public function getOrderNoByOutTradeNo($out_trade_no)
    {
        $pay = new UnifyPay();
        $order = new OrderService();
        $pay_value = $pay->getPayInfo($out_trade_no);
        $order_no = "";
        if ($pay_value['type'] == 1) {
            // 订单
            $list = $order->getOrderNoByOutTradeNo($out_trade_no);
            if (! empty($list)) {
                foreach ($list as $v) {
                    $order_no .= $v['order_no'];
                }
            }
        } elseif ($pay_value['type'] == 4) {
            // 现金充值不进行处理
        }
        return $order_no;
    }
    /**
     * URL重定向
     * @access protected
     * @param string         $url 跳转的URL表达式
     * @param array|integer  $params 其它URL参数
     * @param integer        $code http code
     * @param array          $with 隐式传参
     * @return void
     */
    protected function redirect($url, $params = [], $code = 302, $with = [])
    {
        $response = new Redirect($url);
        if (is_integer($params)) {
            $code   = $params;
            $params = [];
        }
        $response->code($code)->params($params)->with($with);
        throw new HttpResponseException($response);
    }

}