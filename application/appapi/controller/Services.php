<?php

/*
 * Created by 比谷网络.
 * User: KongBo
 * Date: 2018-03-19
 * Time: 17:19
 * 服务相关接口
 */
namespace app\appapi\controller;

use data\service\Positions;
use data\model\FwServicesDetailsModel;
use data\service\Services as ServiceGoods;
use think\Db;
use think\Validate;
use think\Request;
//use data\model\ServicesModel;
use data\service\WebSite;
use data\service\Config as WebConfig;
use data\service\Promotion;
use data\service\ServiceGoodsGroup as GoodsGroup;


class Services extends BaseController{
    public $web_site;
    public $instance_id;
    protected $uid;
    public function __construct()
    {
        parent::__construct();

        $this->shop_id=0;
        $this->instance_id=0;
//        $this->getUserId();
     /*   $web_config = new Config();
        $this->login_verify_code = $web_config->getLoginVerifyCodeConfig($this->instance_id);*/
    }
    /**
     * 新增预约
     */
    public function addVisit(){
        $userId = $this -> getUserId();
        $data = $this->request->param();
//        dump($data);
        if(empty($data['style'])){
            $this->error('缺少参数');
        }

        switch($data['style']){
            case 1:
                $validate = new Validate([
                    'name' => 'require|chs',
                    'mobile' => 'require|max:11',
                    'time' => 'require',
                    'content' => 'require',
                ]);
                $validate->message([
                    'name.require' => '请输入您的姓名!',
                    'name.chs' => '姓名必须为汉字!',
                    'mobile.require' => '请输入您的联系电话!',
                    'mobile.max' => '手机号码不能超过11位！',
                    'time.require' => '请输入您的来访时间!',
                    'content.require' => '请输入您的来访事项!',
                ]);
            break;
            case 2:
                $validate = new Validate([
                    'positions_no' => 'require',
                    'mobile' => 'require|max:11',
                    'time' => 'require',
                ]);
                $validate->message([
                    'positions_no.require' => '请输入您要祭祀的福位编号!',
                    'mobile.require' => '请输入您的联系电话!',
                    'mobile.max' => '手机号码不能超过11位！',
                    'time.require' => '请输入您的来园日期!',
                ]);
            break;
            case 3:
                $validate = new Validate([
                    'buyer_name' => 'require|chs',
                    'mobile' => 'require|max:11',
                    'time' => 'require',
                    'positions_no' => 'require',
                ]);
                $validate->message([
                    'buyer_name.require' => '请输入购买者姓名!',
                    'buyer_name.chs' => '购买者姓名必须为汉字!',
                    'mobile.require' => '请输入您的联系电话!',
                    'mobile.max' => '手机号码不能超过11位！',
                    'time.require' => '请输入计划登位日期!',
                    'positions_no.require' => '请输入您要登位的福位编号!',
                ]);
            break;
        }
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        if(!empty($data['mobile'])){
            if(!preg_match('/^0?(13[0-9]|15[012356789]|17[013678]|18[0-9]|14[57])[0-9]{8}$/',$data['mobile'])){
                $this->error('手机号码格式错误！');
            }
        }
        $data['uid'] = $userId;
        $data['create_time'] = time();
        $model = new FwServicesDetailsModel();
        $res = $model -> save($data);
        if($res){
            $this -> success('预约成功！');
        }else{
            $this -> error("预约失败！");
        }
    }

    /**
     * 预约列表
     */
    public function visitList(){
        //$style = $this -> request->get('style');
        $userId = $this->getUserId();
        $style = $this -> request->post('style');
        if(empty($style)){
            $this->error('参数错误');
        }
        $where['style'] = $style;
        $where['uid'] = $userId;
        $model = new FwServicesDetailsModel();
        $field = '';
        switch ($style){
            case 1:
                $field = 'id,uid,name,mobile,time,content,';
            break;
            case 2:
                $field = 'id,uid,peoples,mobile,time,positions_no,remark,';
            break;
            case 3:
                $field = 'id,uid,buyer_name,time,mobile,username,birthday,dieday,positions_no,';
            break;
        }
        $field = $field."create_time,status,style,error_content";
        $list = $model->getQuery($where,$field,'create_time desc');
        if($list){
            $this -> success('获取数据成功',$list);
        }else{
            $this -> error("暂无数据");
        }
    }

    /**
     * 商品列表
     */
    public function goodsList()
    {
        // 查询购物车中商品的数量
        $goods = new ServiceGoods();
     /*   $goods_category_service = new GoodsCategory();
        $cartlist = $goods->getCart($uid);*/
        $goods_type = request()->post('goods_type','');


            $category_id = request()->post('category_id', ''); // 商品分类
            $brand_id = request()->post('brand_id', ''); // 品牌
            $order = request()->post('obyzd', ''); // 商品排序分类,order by ziduan
            $sort = request()->post('st', 'desc'); // 商品排序分类 sort
            $page = request()->post('page', 1);
            $min_price = request()->post('mipe', ''); // 价格区间,最小min_price
            $max_price = request()->post('mape', ''); // 最大 max_price
            $attr = request()->post('attr', ''); // 属性值
            $spec = request()->post('spec', ''); // 规格值

            // 将属性条件字符串转化为数组
            $attr_array = $this->stringChangeArray($attr);
            // 规格转化为数组
            if ($spec != "") {
                $spec_array = explode(";", $spec);
            } else {
                $spec_array = array();
            }

            //参数过滤


            // 如果排序方式不为空，则进行过滤
            if ($sort != "") {
                if ($sort != "desc" && $sort != "asc") {
                    // 非法参数进行过滤
                    $sort = "";
                }
            }
            $orderby = ""; // 排序方式
            if ($order != "") {
                if ($order != "ng.sales" && $order != "ng.is_new" && $order != "ng.promotion_price") {
                    // 非法参数进行过滤
                    $orderby = "ng.sort asc,ng.create_time desc";
                } else {
                    $orderby = $order . " " . $sort;
                }

            } else {
                $orderby = "ng.sort asc,ng.create_time desc";
            }

            $goods_list = $this->getGoodsListByConditions($goods_type,$category_id, $brand_id, $min_price, $max_price, $page, PAGESIZE, $orderby, $attr_array, $spec_array);
        foreach($goods_list as $k=> $v){

        }
        $this->success('获取完成',$goods_list);
    }

    /**
     * 将属性字符串转化为数组
     *
     * @param unknown $string
     * @return multitype:multitype: |multitype:
     */
    private function stringChangeArray($string)
    {
        if (trim($string) != "") {
            $temp_array = explode(";", $string);
            $attr_array = array();
            foreach ($temp_array as $k => $v) {
                $v_array = array();
                if (strpos($v, ",") === false) {
                    $attr_array = array();
                    break;
                } else {
                    $v_array = explode(",", $v);
                    if (count($v_array) != 3) {
                        $attr_array = array();
                        break;
                    } else {
                        $attr_array[] = $v_array;
                    }
                }
            }
            return $attr_array;
        } else {
            return array();
        }
    }

    /**
     * 根据条件查询商品列表：商品分类查询，关键词查询，价格区间查询，品牌查询
     * 创建人：王永杰
     * 创建时间：2017年2月24日 16:55:05
     */
    public function getGoodsListByConditions($goods_type,$category_id, $brand_id, $min_price, $max_price, $page, $page_size, $order, $attr_array, $spec_array)
    {
        $goods = new ServiceGoods();
        $condition = null;
        if($goods_type !== ""){
            $condition['ng.goods_type'] = $goods_type;
        }
        if ($category_id != "") {
            // 商品分类Id
            $condition["ng.category_id"] = $category_id;
        }
        // 品牌Id
        if ($brand_id != "") {
            $condition["ng.brand_id"] = array(
                "in",
                $brand_id
            );
        }

        // 价格区间
        if ($max_price != "") {
            $condition["ng.promotion_price"] = [
                [
                    ">=",
                    $min_price
                ],
                [
                    "<=",
                    $max_price
                ]
            ];
        }

        // 属性 (条件拼装)
        $array_count = count($attr_array);
        $goodsid_str = "";
        $attr_str_where = "";
        if (! empty($attr_array)) {
            // 循环拼装sql属性条件
            foreach ($attr_array as $k => $v) {
                if ($attr_str_where == "") {
                    $attr_str_where = "(attr_value_id = '$v[2]' and attr_value_name='$v[1]')";
                } else {
                    $attr_str_where = $attr_str_where . " or " . "(attr_value_id = '$v[2]' and attr_value_name='$v[1]')";
                }
            }
            if ($attr_str_where != "") {
                $attr_query = $goods->getGoodsAttributeQuery($attr_str_where);

                $attr_array = array();
                foreach ($attr_query as $t => $b) {
                    $attr_array[$b["goods_id"]][] = $b;
                }
                $goodsid_str = "0";
                foreach ($attr_array as $z => $x) {

                    if (count($x) == $array_count) {
                        if ($goodsid_str == "") {
                            $goodsid_str = $z;
                        } else {
                            $goodsid_str = $goodsid_str . "," . $z;
                        }
                    }
                }
            }
        }

        // 规格条件拼装
        $spec_count = count($spec_array);
        $spec_where = "";
        if ($spec_count > 0) {
            foreach ($spec_array as $k => $v) {
                if ($spec_where == "") {
                    $spec_where = " attr_value_items_format like '%{$v}%' ";
                } else {
                    $spec_where = $spec_where . " or " . " attr_value_items_format like '%{$v}%' ";
                }
            }

            if ($spec_where != "") {

                $goods_query = $goods->getGoodsSkuQuery($spec_where);
                $temp_array = array();
                foreach ($goods_query as $k => $v) {
                    $temp_array[] = $v["goods_id"];
                }
                $goods_query = array_unique($temp_array);
                if (! empty($goods_query)) {
                    if ($goodsid_str != "") {
                        $attr_con_array = explode(",", $goodsid_str);
                        $goods_query = array_intersect($attr_con_array, $goods_query);
                        $goods_query = array_unique($goods_query);
                        $goodsid_str = "0," . implode(",", $goods_query);
                    } else {
                        $goodsid_str = "0,";
                        $goodsid_str .= implode(",", $goods_query);
                    }
                } else {
                    $goodsid_str = "0";
                }
            }
        }
        if ($goodsid_str != "") {
            $condition["goods_id"] = [
                "in",
                $goodsid_str
            ];
        }

        $condition['ng.state'] = 1;

        $list = $goods->getGoodsListNew($page, $page_size, $condition, $order);

        return $list;
    }


    /**
     * 商品详情
     *
     * @return Ambigous <\think\response\View, \think\response\$this, \think\response\View>
     */
    public function goodsDetail()
    {
        $goods_id = request()->post('goods_id', 0);
        if ($goods_id == 0) {
            $this->error("没有获取到商品信息");
        }
        $this->web_site = new WebSite();
        $goods = new ServiceGoods();
        $url= \think\Request::instance()->domain();
        $goods_detail = $goods->getBasisGoodsDetail($goods_id);
        foreach ($goods_detail['sku_picture_list'] as $k=>$v){
            $goods_detail['sku_list'][$k]['pic_cover_small']=$url.'/'.$v['sku_picture_query'][0]['pic_cover_small'];
            $goods_detail['sku_list'][$k]['pic_cover_big']=$url.'/'.$v['sku_picture_query'][0]['pic_cover_big'];
            $goods_detail['sku_list'][$k]['pic_cover_mid']=$url.'/'.$v['sku_picture_query'][0]['pic_cover_mid'];
            $goods_detail['sku_list'][$k]['pic_cover']=$url.'/'.$v['sku_picture_query'][0]['pic_cover'];
        }
        if (empty($goods_detail)) {
            $this->error("没有获取到商品信息");
        }
        if ($this->getIsOpenVirtualGoodsConfig() == 0 && $goods_detail['goods_type'] == 0) {
            $this->error("未开启虚拟商品功能");
        }
        // 把属性值相同的合并
        $goods_attribute_list = $goods_detail['goods_attribute_list'];
        $goods_attribute_list_new = array();
        foreach ($goods_attribute_list as $item) {
            $attr_value_name = '';
            foreach ($goods_attribute_list as $key => $item_v) {
                if ($item_v['attr_value_id'] == $item['attr_value_id']) {
                    $attr_value_name .= $item_v['attr_value_name'] . ',';
                    unset($goods_attribute_list[$key]);
                }
            }
            if (! empty($attr_value_name)) {
                array_push($goods_attribute_list_new, array(
                    'attr_value_id' => $item['attr_value_id'],
                    'attr_value' => $item['attr_value'],
                    'attr_value_name' => rtrim($attr_value_name, ',')
                ));
            }
        }
        $domain_name = \think\Request::instance()->domain();
        foreach ($goods_detail['img_list'] as $k => $v){
            $goods_detail['img_list'][$k]['pic_cover'] = $domain_name.'/'.$v['pic_cover'];
            $goods_detail['img_list'][$k]['pic_cover_big'] = $domain_name.'/'.$v['pic_cover_big'];
            $goods_detail['img_list'][$k]['pic_cover_micro'] = $domain_name.'/'.$v['pic_cover_micro'];
            $goods_detail['img_list'][$k]['pic_cover_mid'] = $domain_name.'/'.$v['pic_cover_mid'];
            $goods_detail['img_list'][$k]['pic_cover_small'] = $domain_name.'/'.$v['pic_cover_small'];
        }

        $goods_detail['picture_detail']['pic_cover'] = $domain_name.'/'.$goods_detail['picture_detail']['pic_cover'];
        $goods_detail['picture_detail']['pic_cover_big'] = $domain_name.'/'.$goods_detail['picture_detail']['pic_cover_big'];
        $goods_detail['picture_detail']['pic_cover_micro'] = $domain_name.'/'.$goods_detail['picture_detail']['pic_cover_micro'];
        $goods_detail['picture_detail']['pic_cover_mid'] = $domain_name.'/'.$goods_detail['picture_detail']['pic_cover_mid'];
        $goods_detail['picture_detail']['pic_cover_small'] = $domain_name.'/'.$goods_detail['picture_detail']['pic_cover_small'];
        $goods_detail['description'] = str_replace('src="/upload/','src="'.$domain_name.'/upload/',$goods_detail['description']);
        $goods_detail['goods_attribute_list'] = $goods_attribute_list_new;
//        dump($goods_detail['info']);
        $product['info'] = "预约日期,姓　　名,电　　话,福　　位,备　　注,".$goods_detail['info'];
//        $product['info'] = serialize(explode(',',trim($product['info'],',')));
//        $goods_detail['info'] = unserialize(str_replace('&quot;','"',$goods_detail['info']));
        $goods_detail['info'] = explode(',',trim($product['info'],','));
//        dump($goods_detail['info']);
        // 获取当前时间
        $current_time = $this->getCurrentTime();


        $data['ms_time']=$current_time;
        $data['url']=\think\Request::instance()->domain().'/';
        $data['goods_detail']=$goods_detail;
        $data['price']=intval($goods_detail["promotion_price"]);
        $data['goods_id']=$goods_id;
        $data['title_before']=$goods_detail['goods_name'];
        // 返回商品数量和当前商品的限购
        $cart= $this->getCartInfo($goods_id);
        $data['cart']= $cart;


        // 组合商品
        $promotion = new Promotion();
        $comboPackageGoodsArray = $promotion->getComboPackageGoodsArray($goods_id);

        $data['comboPackageGoodsArray']=$comboPackageGoodsArray[0];

        //商品标签
        $goods_group = new GoodsGroup();
        $goods_group_list = $goods_group -> getGoodsGroupList(1,0, [
            "group_id" => array("in", $goods_detail["group_id_array"])
        ], "", "group_name");
        $data['goods_group_list']=$goods_group_list["data"];
        $this->success('商品详情',$data);

    }


    /**
     * 是否开启虚拟商品功能，0：禁用，1：开启
     */
    public function getIsOpenVirtualGoodsConfig()
    {
        $config = new WebConfig();
        $res = $config->getIsOpenVirtualGoodsConfig(0);
        return $res;
    }
    /**
     * 返回商品数量和当前商品的限购
     *
     * @param unknown $goods_id
     */
    public function getCartInfo($goods_id)
    {
        $this->uid=  $this->getUserId();
        $goods = new ServiceGoods();
        $cartlist = $goods->getCart($this->uid);
        $num = 0;
        foreach ($cartlist as $v) {
            if ($v["goods_id"] == $goods_id) {
                $num = $v["num"];
            }
        }
        /*   $this->assign("carcount", count($cartlist)); // 购物车商品数量
           $this->assign("num", $num); // 购物车已购买商品数量*/
        $data['carcount']=count($cartlist);// 购物车商品数量
        $data['num']=$num;// 购物车已购买商品数量
        return  $data;
    }
    /**
     * 得到当前时间戳的毫秒数
     *
     * @return number
     */
    public function getCurrentTime()
    {
        $time = time();
        $time = $time * 1000;
        return $time;
    }







}