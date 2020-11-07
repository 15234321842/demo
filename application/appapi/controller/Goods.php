<?php
/**
 * Created by 比谷网络.
 * User: niuchao
 * Date: 2018-03-09
 * Time: 14:43
 */
namespace app\appapi\controller;

use data\service\Config as WebConfig;
use data\service\Goods as GoodsService;
use data\service\GoodsBrand as GoodsBrand;
use data\service\GoodsCategory;
use data\service\GoodsGroup;
use data\service\Member;
use data\service\Order as OrderService;
use data\service\Platform;
use data\service\promotion\GoodsExpress;
use data\service\Address;
use data\service\WebSite;
use data\service\Promotion;
use data\service\promotion\PromoteRewardRule;
use think\Db;
use data\model\NsCouponTypeModel;
use data\service\Member as MemberService;

class Goods extends BaseController
{
    public function goodsDetail(){
        $goods_id = request()->post('id', 0);

        if ($goods_id == 0) {
            $this->error("没有获取到商品信息");
        }

        $goods = new GoodsService();
        $config_service = new WebConfig();
        $url= \think\Request::instance()->domain();
        $goods_detail = $goods->getBasisGoodsDetail($goods_id);
        foreach ($goods_detail['sku_picture_list'] as $k=>$v){
            $goods_detail['sku_list'][$k]['pic_cover_small']=$url.'/'.$v['sku_picture_query'][0]['pic_cover_small'];
            $goods_detail['sku_list'][$k]['pic_cover_big']=$url.'/'.$v['sku_picture_query'][0]['pic_cover_big'];
            $goods_detail['sku_list'][$k]['pic_cover_mid']=$url.'/'.$v['sku_picture_query'][0]['pic_cover_mid'];
            $goods_detail['sku_list'][$k]['pic_cover']=$url.'/'.$v['sku_picture_query'][0]['pic_cover'];
        }
        $goods_detail['picture_detail']['pic_cover_small']=$url.'/'.$goods_detail['picture_detail']['pic_cover_small'];
        $goods_detail['picture_detail']['pic_cover_big']=$url.'/'.$goods_detail['picture_detail']['pic_cover_big'];
        $goods_detail['picture_detail']['pic_cover_mid']=$url.'/'.$goods_detail['picture_detail']['pic_cover_mid'];
        $goods_detail['picture_detail']['pic_cover']=$url.'/'.$goods_detail['picture_detail']['pic_cover'];
        $goods_detail['description'] = str_replace('src="/upload/','src="'.$url.'/upload/',$goods_detail['description']);


        if (empty($goods_detail)) {
            $this->error("没有获取到商品信息");
        }
        $config = new WebConfig();
        $res = $config->getIsOpenVirtualGoodsConfig($this->instance_id);
        if ($res == 0 && $goods_detail['goods_type'] == 0) {
            $this->error("未开启虚拟商品功能");
        }
        //商品点击量
        $goods -> updateGoodsClicks($goods_id);

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
        $goods_detail['goods_attribute_list'] = $goods_attribute_list_new;

        // 获取商品的优惠劵
        $goods_coupon_list = $goods->getGoodsCoupon($goods_id, $this->userId);
//        $this->assign("goods_coupon_list", $goods_coupon_list);
        $goods_detail['goods_coupon_list'] = $goods_coupon_list;

        // 组合商品
        $promotion = new Promotion();
        $comboPackageGoodsArray = $promotion->getComboPackageGoodsArray($goods_id);
//        $this->assign("comboPackageGoodsArray", $comboPackageGoodsArray[0]);
        $goods_detail['comboPackageGoodsArray'] = $comboPackageGoodsArray[0];

        // 商品阶梯优惠
        $goodsLadderPreferentialList = $goods->getGoodsLadderPreferential([
            "goods_id" => $goods_id
        ], "quantity desc", "quantity,price");
//        $this->assign("goodsLadderPreferentialList", array_reverse($goodsLadderPreferentialList));
        $goods_detail['goodsLadderPreferentialList'] = array_reverse($goodsLadderPreferentialList);
        // 添加足迹
        if ($this->userId > 0) {
            $goods->addGoodsBrowse($goods_id, $this->userId);
        }

        //商品标签
        $goods_group = new GoodsGroup();
        $goods_group_list = $goods_group -> getGoodsGroupList(1,0, [
            "group_id" => array("in", $goods_detail["group_id_array"])
        ], "", "group_name");
//        $this->assign("goods_group_list", $goods_group_list["data"]);
        $goods_detail['goods_group_list'] = $goods_group_list["data"];
        //商家服务展示
        $existingMerchant = $config_service -> getExistingMerchantService(0);
        $goods_detail['existingMerchant'] = $existingMerchant;

        $this->success('获取成功！',$goods_detail);
    }

    /**
     * 返回商品数量和当前商品的限购
     *
     * @param unknown $goods_id
     */
    public function getCartInfo()
    {
        $goods_id = request()->get('goods_id', 0);
        if(empty($goods_id)){
            $this->error("购物车中商品数量获取失败");
        }
        $goods = new GoodsService();
        $cartlist = $goods->getCart($this->userId);
        $num = 0;
        foreach ($cartlist as $v) {
            if ($v["goods_id"] == $goods_id) {
                $num = $v["num"];
            }
        }

        $data['carcount'] =  count($cartlist); // 购物车商品数量
        $data['num'] =  $num;// 购物车已购买商品数量
        $this->success('购物车中商品数量获取成功',$data);
    }
    
    /*
     * 返回商品分类列表信息
     *
     */
     public function goodsCategoryList(){
        $goods_category = new GoodsCategory();
        $one_list = $goods_category->getCategoryTreeUseInAdmin();
        if($one_list){
             $this->success("查询商品分类成功", $one_list);
         }else{
             $this->error("查询商品分类失败");
         }
     }
     
     /*
     * 返回分类商品下列表信息
     *@param top_name post
     */
     public function goodsCategoryNextListByid(){



         $categoryName=request()->post('top_name','热门');
         $categoryId=request()->post('category_id','');
         $pageSize=$this->request->post('pageSize',6);
         $page=$this->request->post('page',1);
         //根据请求的分类名称查找分类id
//         $goods_category = new GoodsCategory();
//         $condition['category_name']=['like','%'.$categoryName.'%'];
//         $categoryList=$goods_category->getGoodsCategoryList('','',$condition);
//         unset($condition['category_name']);
//         foreach($categoryList[data] as $k=>$v){
//             if (!empty($v)) {
//                 $categoryId[]=$v[category_id];
//             }
//         }



         if($categoryId==0){
             $this->error("该分类下的商品列表获取失败");
         }
         if(empty($categoryId) && $categoryId == null){
             $this->error('该分类下的商品列表获取失败');
         }
         $goods=new GoodsService();
//         $condition['ng.category_id']=['in',$categoryId];
         $condition['ng.category_id']=$categoryId;
         $goodsList = $goods->getcategoryGoodsListByid($page,$pageSize,$condition);
         if($goodsList){
             if($categoryName=='特价'){
                 $lunbo_id=6669;
                 $lunbo = new Platform();
                 $domain_name = \think\Request::instance()->domain();
                 $slide = $lunbo -> getPlatformAdvPositionDetail($lunbo_id);
                 foreach($slide['adv_list'] as $k => $v){
                     $slide['adv_list'][$k]['adv_image'] = $domain_name.'/'.$v['adv_image'];
                 }
                 $param['slide']=$slide;
                 $param['list']=$goodsList;
                 $this->success("该分类下的商品列表获取成功", $param);
             }else{
                 $this->success("该分类下的商品列表获取成功", $goodsList);
             }

         }else{
             $this->error('该分类下的商品列表获取失败');
         }
     }

    public function goodsCategoryNextList(){



        $categoryName=request()->post('top_name','热门');
        $pageSize=$this->request->post('pageSize',6);
        $page=$this->request->post('page',1);
        //根据请求的分类名称查找分类id
        $goods_category = new GoodsCategory();
        $condition['category_name']=['like','%'.$categoryName.'%'];
        $categoryList=$goods_category->getGoodsCategoryList('','',$condition);
        unset($condition['category_name']);
        foreach($categoryList[data] as $k=>$v){
            if (!empty($v)) {
                $categoryId[]=$v[category_id];
            }
        }



        if($categoryId==0){
            $this->error("该分类下的商品列表获取失败");
        }
        if(empty($categoryId) && $categoryId == null){
            $this->error('该分类下的商品列表获取失败');
        }
        $goods=new GoodsService();
        $condition['ng.category_id']=['in',$categoryId];
        $goodsList = $goods->getcategoryGoodsList($page,$pageSize,$condition);
        if($goodsList){
            if($categoryName=='特价'){
                $lunbo_id=6669;
                $lunbo = new Platform();
                $domain_name = \think\Request::instance()->domain();
                $slide = $lunbo -> getPlatformAdvPositionDetail($lunbo_id);
                foreach($slide['adv_list'] as $k => $v){
                    $slide['adv_list'][$k]['adv_image'] = $domain_name.'/'.$v['adv_image'];
                }
                $param['slide']=$slide;
                $param['list']=$goodsList;
                $this->success("该分类下的商品列表获取成功", $param);
            }else{
                $this->success("该分类下的商品列表获取成功", $goodsList);
            }

        }else{
            $this->error('该分类下的商品列表获取失败');
        }
    }

    /**
     * 商品评价列表
     * @date 2018-10-25
     * @author kongbo
     */
    public function goodsEvaluateList(){
        $comments_type = request()->post('comments_type', '');
        $order = new OrderService();
        $condition['goods_id'] = request()->post('goods_id', '');
        $page=$this->request->param('page/d',1);
        $size=$this->request->param('size/d',10);
        switch ($comments_type) {
            case 1:
                $condition['explain_type'] = 1;
                break;
            case 2:
                $condition['explain_type'] = 2;
                break;
            case 3:
                $condition['explain_type'] = 3;
                break;
            case 4:
                $condition['image|again_image'] = array(
                    'NEQ',
                    ''
                );
                break;
        }
        $condition['is_show'] = 1;
        $goodsEvaluationList = $order->getOrderEvaluateDataList($page,$size , $condition, 'addtime desc');
        // 查询评价用户的头像
        $memberService = new Member();
        foreach ($goodsEvaluationList['data'] as $v) {
            $v["user_img"] = $memberService->getMemberImage($v["uid"]);
        }
        $this->success('获取商品评论成功！',$goodsEvaluationList);
    }
    /**
     * 商品列表
     */
    public function goodsList()
    {
        // 查询购物车中商品的数量
        $uid = $this->uid;
        $goods = new GoodsService();
        $goods_category_service = new GoodsCategory();
        $cartlist = $goods->getCart($uid);

        $result = [];
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

        $goods_list = $this->getGoodsListByConditions($category_id, $brand_id, $min_price, $max_price, $page, PAGESIZE, $orderby, $attr_array, $spec_array);
        $result['good_list'] = $goods_list;

        // 筛选条件
        if ($category_id != "") {

            // 获取商品分类下的品牌列表、价格区间
            $category_brands = null;
            $category_price_grades = [];

            // 查询品牌列表，用于筛选
            $category_brands = $goods_category_service->getGoodsCategoryBrands($category_id);

            // 查询价格区间，用于筛选
            $category_price_grades = $goods_category_service->getGoodsCategoryPriceGrades($category_id);

            foreach ($category_price_grades as $k => $v) {
                $category_price_grades[$k]['price_str'] = $v[0] . '-' . $v[1];
            }
            $category_count = 0; // 默认没有数据
            if ($category_brands != "") {
                $category_count = 1; // 有数据
            }
            $goods_category_info = $goods_category_service->getGoodsCategoryDetail($category_id);

            $attr_id = $goods_category_info["attr_id"];
            // 查询商品分类下的属性和规格集合
            $goods_attribute = $goods->getAttributeInfo([
                "attr_id" => $attr_id
            ]);
            $attribute_detail = $goods->getAttributeServiceDetail($attr_id, [
                'is_search' => 1
            ]);
            $attribute_list = array();
            if (! empty($attribute_detail['value_list']['data'])) {
                $attribute_list = $attribute_detail['value_list']['data'];
                foreach ($attribute_list as $k => $v) {
                    $value_items = explode(",", $v['value']);
                    $new_value_items = array();
                    foreach ($value_items as $ka => $va) {
                        $new_value_items[$ka]['value'] = $va;
                        $new_value_items[$ka]['value_str'] = $attribute_list[$k]['attr_value_name'] . ',' . $va . ',' . $attribute_list[$k]['attr_value_id'];
                    }
                    $attribute_list[$k]['value'] = trim($v["value"]);
                    $attribute_list[$k]['value_items'] = $new_value_items;
                }
            }
            $attr_list = $attribute_list;
            // 查询本商品类型下的关联规格
            $goods_spec_array = array();
            if ($goods_attribute["spec_id_array"] != "") {
                $goods_spec_array = $goods->getGoodsSpecQuery([
                    "spec_id" => [
                        "in",
                        $goods_attribute["spec_id_array"]
                    ]
                ]);
                foreach ($goods_spec_array as $k => $v) {
                    foreach ($v["values"] as $z => $c) {
                        $c["value_str"] = $c['spec_id'] . ':' . $c['spec_value_id'];
                    }
                }
                sort($goods_spec_array);
            }


        }
        $goodsCategoryList = $goods_category_service->getCategoryTreeUseInShopIndex();
        $result['goodsCategoryList'] = $goodsCategoryList;
        $result['attr_or_spec'] = $attr_list;
        $result['category_brands'] = $category_brands;
        $result['category_count'] = $category_count;
        $result['category_price_grades'] = $category_price_grades;
        $result['category_price_grades_count'] = count($category_price_grades);
        $result['goods_spec_array'] = $goods_spec_array;
        $result['title_before'] = $goods_category_info['category_name'];
        // 获取分类列表

        $result['uid'] = $uid;
        $result['carcount'] = count($cartlist);
//            print_r($goodsCategoryList);die;
        $this->success("该分类下的商品列表获取成功", $result);
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
    public function getGoodsListByConditions($category_id, $brand_id, $min_price, $max_price, $page, $page_size, $order, $attr_array, $spec_array)
    {
        $goods = new GoodsService();
        $condition = null;
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
     * 搜索商品显示
     */
    public function goodsSearchList()
    {

        $sear_name = request()->post('sear_name', '');
        $sear_type = request()->post('sear_type', '');
        $controlType = request()->get('controlType', ''); // 什么类型 1最新 2精品 3推荐
        $controlTypeName = request()->get('controlTypeName', ''); // 什么类型 1最新 2精品 3推荐
        $order = request()->post('obyzd', '');
        $sort = request()->post('st', 'desc');
        $page = request()->post("page", 1);
        $goods = new GoodsService();
        $condition['goods_name'] = [
            'like',
            '%' . $sear_name . '%'
        ];


        switch ($controlType) {
            case 1:
                $condition = [
                    'is_new' => 1
                ];
                break;
            case 2:
                $condition = [
                    'is_hot' => 1
                ];
                break;
            case 3:
                $condition = [
                    'is_recommend' => 1
                ];
                break;
            default:
                break;
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


        if (! empty($shop_id)) {
            $condition['ng.shop_id'] = $shop_id;
        }
        $condition['state'] = 1;
        $search_good_list = $goods->getGoodsListNew($page, PAGESIZE, $condition, $orderby);

        $result = [];
        $result['search_good_list'] = $search_good_list;

        if (! empty($sear_name)) {
            $search_title = $sear_name;
        } else {
            $search_title = $controlTypeName;
        }
        if (mb_strlen($sear_name) > 10) {
            $sear_name = mb_substr($sear_name, 0, 7, 'utf-8') . '...';
        }

//            $shop_id = $this->shop_id;
//            $result['shop_id'] = $shop_id;
        $result['controlType'] = $controlType;
//            $result['wherename'] = 'sear_name';
        $result['sear_name'] = $sear_name;
        $result['search_title'] = $search_title;

        $this->success("搜索商品列表成功", $result);
    }

    public function getCouponList(){
        $uid=$this->getUserId();
        $page=$this->request->param('page/d',1);
        $size=10;
        $list= Db('ns_coupon_type')
            ->alias('nct')
            ->field('nct.*')
            ->join('ns_coupon nc','nct.max_fetch=0 || (nc.coupon_type_id=nct.coupon_type_id and nc.uid='.$uid.')','left')
            ->group('nct.coupon_type_id')
            ->having('nct.max_fetch=0||nct.max_fetch>count(nc.coupon_id)')
            ->where(['nct.end_time'=>['>',time()]])
            ->page($page,$size)
            ->select();
        $this->success("获取优惠券列表", $list);
    }

    /**
     * 首页领用红包
     */
    public function getCoupon()
    {
        $uid=$this->getUserId();
        $coupon_type_id = request()->post('coupon_type_id', 0);
        $member = new MemberService();
        $ret = $member->memberGetCoupon($uid, $coupon_type_id, 2);
        $this->success('操作完成',$ret);
    }

    /**
     * 添加收藏
     */
    public function FavoritesGoodsorshop()
    {
            $fav_id = request()->post('fav_id', '');
            $fav_type = request()->post('fav_type', '');
            $log_msg = request()->post('log_msg', '');
            $member = new MemberService();
            $result = $member->addMemberFavouites($fav_id, $fav_type, $log_msg);
            $this->success('操作完成',$result);
    }

    /**
     * 取消收藏
     */
        public function cancelFavorites()
    {
            $fav_id = request()->post('fav_id', '');
            $fav_type = request()->post('fav_type', '');
            $member = new MemberService();
            $result = $member->deleteMemberFavorites($fav_id, $fav_type);
            $this->success('操作完成',$result);
    }
    /**
     * 我的收藏
     */
    public function myCollection()
    {
            $member = new MemberService();
            $page = request()->post('page', '1');
            $type = request()->post('type', 0);
            $condition = array(
                "nmf.fav_type" => 'goods',
                "nmf.uid" =>$this->getUserId()
            );
            if ($type == 1) { // 获取本周内收藏的商品
                $start_time = mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y"));
                $end_time = mktime(23, 59, 59, date("m"), date("d") - date("w") + 7, date("Y"));
                $condition["fav_time"] = array(
                    "between",
                    $start_time . "," . $end_time
                );
            } else
                if ($type == 2) { // 获取本月内收藏的商品
                    $start_time = mktime(0, 0, 0, date("m"), 1, date("Y"));
                    $end_time = mktime(23, 59, 59, date("m"), date("t"), date("Y"));
                    $condition["fav_time"] = array(
                        "between",
                        $start_time . "," . $end_time
                    );
                } else
                    if ($type == 3) { // 获取本年内收藏的商品
                        $start_time = strtotime(date("Y", time()) . "-1" . "-1");
                        $end_time = strtotime(date("Y", time()) . "-12" . "-31");
                        $condition["fav_time"] = array(
                            "between",
                            $start_time . "," . $end_time
                        );
                    }

            $goods_collection_list = $member->getMemberGoodsFavoritesList($page, PAGESIZE, $condition, "fav_time desc");
            foreach ($goods_collection_list['data'] as $k => $v) {
                $v['fav_time'] = date("Y-m-d H:i:s", $v['fav_time']);
            }
            $this->success('操作完成',$goods_collection_list);
    }

    /**
     * 我的足迹
     */
    public function newMyPath()
    {
            $good = new GoodsService();
            $data = request()->post();
            $condition = [];
            $condition["uid"] = $this->getUserId();
            if (! empty($data['category_id']))
                $condition['category_id'] = $data['category_id'];

            $order = 'create_time desc';
            $list = $good->getGoodsBrowseList($data['page_index'], $data['page_size'], $condition, $order, $field = "*");

            foreach ($list['data'] as $key => $val) {
                $month = ltrim(date('m', $val['create_time']), '0');
                $day = ltrim(date('d', $val['create_time']), '0');
                $val['month'] = $month;
                $val['day'] = $day;
            }
            $this->success('操作完成',$list);
            return $list;
    }

    /**
     * 删除我的足迹
     */
    public function delMyPath()
    {
        $type = request()->post('type');
        $value = request()->post('value');

        if ($type == 'browse_id')
            $condition['browse_id'] = $value;

        $good = new GoodsService();
        $res = $good->deleteGoodsBrowse($condition);
        $this->success('操作完成',$res);
    }

}