<?php
/**
 * Created by PhpStorm.
 * User: zhumeng
 * Date: 2018/3/13
 * Time: 14:04
 */
namespace app\appapi\controller;

use data\model\NsPlatformAdvPositionModel;
use data\service\Goods;
use data\service\GoodsCategory;
use data\service\Member;
use think\Request;
use data\service\Platform;
use data\service\Config;
use data\service\Address;
use data\service\WebSite;
use data\service\Order as OrderService;
use data\service\Goods as GoodsService;
use data\model\NsOrderModel;
use data\model\NsToorderModel;
use data\model\NsOrderGoodsModel;
use data\model\UserModel;
use think\Db;
use data\model\NsSpecialModel;
use data\model\NsPositionModel;
use data\model\NsPositionContentModel;
use data\service\Promotion as PromotionService;
use data\service\Config as WebConfig;

Class Index extends BaseController{

    protected $uid;
    protected $instance_id=0;


    public function test(){

    }

    /*
   * 获取公告列表,信息
   * */
    public function getNotice(){
        $id=request()->param('id',0);
        $plat=new Platform();
        if($id){
            $info=$plat->getNoticeDetail($id);
            $domain = \think\Request::instance()->domain();
            if($info){
                $info['notice_content']=htmlspecialchars_decode($info['notice_content']);
                $info['notice_content']=str_replace('/upload/ueditor',$domain.'/upload/ueditor',$info['notice_content']);
            }

            $this->success('获取公告信息完成',$info);
        }else{
            $list= $plat->getNoticeList(1,10,[],'sort desc','id,notice_title,create_time');
            $this->success('获取列表完成',$list);
        }

    }

    /*
     * 获取热词
     * */
    public function getKeyword(){
        $type=request()->param('type');
        $config=new Config();
        switch ($type){
            case 1 :
                $data=$config->getHotsearchConfig(0);
                break;
            case 2:
                $data=$config->getDefaultSearchConfig(0);
                break;
            default :
               $this->error('错误参数');
                break;
        }
        $this->success('获取完成',$data);
    }

    /**
     * 获取省列表
     */
    public function getProvince()
    {
        $address = new Address();
        $province_list = $address->getProvinceList();
        $this->success('获取完成',$province_list);
    }

    /*
     * 获取城市列表
     * */
    public function getCity()
    {
        $address = new Address();
        $province_id = request()->post('province_id', 0);
        $city_list = $address->getCityList($province_id);
        $this->success('获取完成',$city_list);
    }

    /**
     * 获取区域地址
     */
    public function getDistrict()
    {
        $address = new Address();
        $city_id = request()->post('city_id', 0);
        $district_list = $address->getDistrictList($city_id);
        $this->success('获取完成',$district_list);
    }

    /**
     * 获取首页数据
     */
    public function goodsData()
    {
        $list=array();
        $special= new NsSpecialModel();
        $positon =new NsPositionModel();
        $content = new NsPositionContentModel();
        $list['title']=$special->where(['is_show'=>1])->select();
        $id=request()->param('id',$list['title'][0]['id']);
        $vid=$positon->where(['tid'=>$id])->column('id','type');
         foreach ($vid as $k =>$v){
             $data=$content->where(['vid'=>$v]) ->select();
             foreach ($data as $a=>$b) {
                 if($b['type']=='discount'){
                    $discount = new PromotionService();
                    $detail = $discount->getPromotionDiscountDetail($b['data_id']);
                    $data[$a]['detail']=$detail;
                 }
             }
            $list[$k]=$data;//$content->where(['vid'=>$v]) ->select();
        }
        $list['id']=$id;
        $this->success('获取完成',$list);
    }
    public function indexData1()
    {
        $page=$this->request->post('page',1);
        if($page>0){
            $goods=new GoodsService();
            $list=$goods->getGoodsViewList($page,10,'','ng.sales desc');
            $this->success('获取完成',$list);
        }else{
            $lunbo = new Platform();
            $domain_name = \think\Request::instance()->domain();
            $slide = $lunbo -> getPlatformAdvPositionDetail(6667);
            foreach($slide['adv_list'] as $k => $v){
                $slide['adv_list'][$k]['adv_image'] = $domain_name.'/'.$v['adv_image'];
            }
            $slide['default_content'] = $domain_name.'/'.$slide['default_content'];
            $data['slide'] = $slide;
            $goods=new GoodsService();
            $data['shoplist']=$goods->getGoodsList(1,10,'','ng.sales desc');
            $plat=new Platform();
            $data['plat']= $plat->getNoticeList(1,10,[],'sort desc','id,notice_title,create_time');
            $this->success('获取完成',$data);
        }
    }
    public function indexData()
    {
        $platform = new Platform();
        $good_category = new GoodsCategory();
        $goods = new Goods();
        $config = new Config();
        $member = new Member();
        $this->web_site = new WebSite();
        $shop_id = $this->instance_id;

        // 首页轮播图
        $slide = $platform->getPlatformAdvPositionDetail(6667);
        $domain_name = \think\Request::instance()->domain();
        foreach($slide['adv_list'] as $k => $v){
            $slide['adv_list'][$k]['adv_image'] = $domain_name.'/'.$v['adv_image'];
        }
        $slide['default_content'] = $domain_name.'/'.$slide['default_content'];
        $data['slide'] = $slide;

        // 首页楼层版块
        $block_list = $good_category->getGoodsCategoryBlockQuery($shop_id, 4);

        foreach ($block_list as $k=>$v){
           if($v['category_id']){
               foreach ($v['goods_list'] as $vv){
                   $vv['pic_cover_small']=$domain_name.'/'.$vv['pic_cover_small'];
               }
           }
        }

        $data['block_list']=$block_list;
        $plat=new Platform();
        $data['plat']= $plat->getNoticeList(1,10,[],'sort desc','id,notice_title,create_time');
        $this->success('获取完成',$data);

    }

//首页列表-----张俊杰
    public function index()
    {
        $platform = new Platform();
        $good_category = new GoodsCategory();
        $goods = new Goods();
        $config = new Config();
        $member = new Member();
        $this->web_site = new WebSite();
        $shop_id = $this->instance_id;

        // 首页轮播图
        $where = array('check' => 0,'type'=>2,'is_use'=>1,'ap_keyword'=>'banner');
        $banner = $platform->getPlatformAdvPositionDetail2($where);

        $domain_name = \think\Request::instance()->domain();
        foreach($banner['adv_list'] as $k => $v){
            $banner['adv_list'][$k]['adv_image'] = $domain_name.'/'.$v['adv_image'];
        }
        $banner['default_content'] = $domain_name.'/'.$banner['default_content'];
        $data['banner'] = $banner;

        // 首页楼层版块
        $block_list = $good_category->getGoodsCategoryBlockQuery($shop_id, 4);
        foreach ($block_list as $k=>$v){
            if($v['category_id']){
                foreach ($v['goods_list'] as $vv){
                    $vv['pic_cover_small']=$domain_name.'/'.$vv['pic_cover_small'];
                }
            }
        }

        $data['block_list']=$block_list;
        $plat=new Platform();
        $data['plat']= $plat->getNoticeList(1,10,[],'sort desc','id,notice_title,create_time');

        $this->success('获取完成',$data);

    }


    /**
     * 获取指定轮播图列表
     */
    public function slideList(){
        $lunbo_id=$this->request->post('lunbo_id',6667);
        $lunbo = new Platform();
        $domain_name = \think\Request::instance()->domain();
        $slide = $lunbo -> getPlatformAdvPositionDetail($lunbo_id);
        foreach($slide['adv_list'] as $k => $v){
            $slide['adv_list'][$k]['adv_image'] = $domain_name.'/'.$v['adv_image'];
        }
        return $slide;
    }

    public function orderFinishMessges(){
        $condition = [];
        $condition['shipping_status'] = 0; // 0 待发货
        $condition['pay_status'] = 2; // 2 已支付
        $condition['order_status'] = array(
            'neq',
            4
        ); // 4 已完成
        $condition['order_status'] = array(
            'neq',
            5
        ); // 5 关闭订单
        $order_service = new OrderService();
        $list = $order_service->getOrderList(1,10,$condition,'create_time desc');
        $res = [];
        foreach( $list['data'] as $k => $v ){
            $is_Mobile = preg_match('/(1[358]{1}[0-9])[0-9]{4}([0-9]{4})/i',$v['user_name']); //手机
            if($is_Mobile == 1){
                $str = substr_replace($v['user_name'],'****',3,4);
            }else{
                $str = $v['user_name'];
            }
            $res[] = date('H:i:s',$v['pay_time']).' '.$str .' 购买：'. $v['order_item_list'][0]['goods_name'].'!';
        }
        $this->success('获取完成',$res);
    }


    /**
     * 个人中心二次转售商品列表
     */
    public function ResellerOrderList(){

        $config=new Config();
        $instance_id=$this->instance_id;
        $key="TO_ORDER_ISOPEN";
        $res=$config->getConfig($instance_id, $key);
        if($res['value']=='0'){
            $this->error("敬请期待");
        }
        $status = request()->post('tostatus', 'all');
        $page_index = request()->post("page", 1);
        $page_size = request()->post('page_size', PAGESIZE);

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
            $this->success("未获取到数据");
        }
    }

    /**
     * 展示转卖商品需要的详细信息
     */
    public function getgoodsinfo(){
        $order_id = request()->post('order_id', 0);
//        $goods_id = request()->post('goods_id', 0);

        if ($order_id  == 0) {
            $this->error("没有获取到商品信息");
        }
      $order_goods=new NsOrderGoodsModel();
      $goods_info  = $order_goods->getInfo(['order_id'=>$order_id],'goods_id');
        //获取出卖人的联系方式
        $oderModel=new NsToOrderModel();
        $seller=$oderModel->getInfo(['order_id'=>$order_id,'tostatus'=>1],'seller_id');
        $userModel=new UserModel();
        $data['sellerDetail']=$userModel->getInfo(['uid'=>$seller['seller_id']],'nick_name,user_name,real_name,user_tel');

        //取出转售商品的价格等详情
        $toorderModel=new NsToorderModel();
        $data['sellDetail']=$toorderModel->getInfo(['order_id'=>$order_id,'tostatus'=>1],'*');
        //出售商品的商品详情
        $goods = new GoodsService();
        $goods_detail = $goods->getBasisGoodsDetail($goods_info['goods_id']);
//        $goods_detail= $goods->getBasisGoodsDetail($ordergoods['goods_id']);
        //拼接图片全路径
        $goods_detail['picture_detail']['pic_cover_mid']=\think\Request::instance()->domain().'/'.$goods_detail['picture_detail']['pic_cover_mid'];
        $goods_detail['picture_detail']['pic_cover']=\think\Request::instance()->domain().'/'.$goods_detail['picture_detail']['pic_cover'];
        $goods_detail['picture_detail']['pic_cover_big']=\think\Request::instance()->domain().'/'. $goods_detail['picture_detail']['pic_cover_big'];
        $goods_detail['picture_detail']['pic_cover_small']=\think\Request::instance()->domain().'/'.$goods_detail['picture_detail']['pic_cover_small'];
        $goods_detail['picture_detail']['pic_cover_micro']=\think\Request::instance()->domain().'/'.$goods_detail['picture_detail']['pic_cover_micro'];

        $data['goods_detail']=$goods_detail ;
        $this->success('获取成功',$data);
    }

    public function getWebConfig(){
        $website = new WebSite();
        $list = $website->getWebSiteInfo();
        if($list){
            $this->success('获取成功',[
                'app_version'=>$list['app_version'],
                'path_one'=>$list['path_one'],
                'path_two'=>$list['path_two'],
            ]);
        }else{
            $this->error('获取失败');
        }
    }
	
	  public function getAppConfig(){
        $path=\think\Request::instance()->domain().'/';
        $WebConfig = new WebConfig();
        $param=[];
        //注册登陆相关参数
        $register_and_visit = $WebConfig->getRegisterAndVisit($this->instance_id);
        $param['reg']=json_decode($register_and_visit['value']);

        // 获取默认图
        $result = $WebConfig->getDefaultImages($this->instance_id);
        $param['img']=$result['value'];
        foreach ($param['img'] as $k=>$v) {
            $param['img'][$k]=$path.$v;
        }

        //第三方登陆
        $qq_config = $WebConfig->getQQConfig($this->instance_id);
        $wchat_config = $WebConfig->getWchatConfig($this->instance_id);
        $param['party']=['qq'=>$qq_config['is_use'],'wx'=>$wchat_config['is_use']];

        $this->success('获取完成',$param);
    }

}