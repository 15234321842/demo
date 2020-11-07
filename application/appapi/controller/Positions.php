<?php

/* 
 * Created by 比谷网络.
 * User: niuchao
 * Date: 2018-03-09
 * Time: 9:43
 * 福位的类
 */
namespace app\appapi\controller;

use data\service\Positions as GoodsService;
use data\model\NsPositionsModel as NsPositionsModel;
use data\service\PositionsCategory as GoodsCategory;
use think\Db;
use think\Validate;
use think\Request;


class Positions extends BaseController{
    /**
    * 获取福位列表信息
    */
    public function positionsCategoryList(){
        $goods_category = new GoodsCategory();
        $one_list = $goods_category->getCategoryTreeUseInAdmin();
        foreach ($one_list as $k => $v) {
            $one_list[$k][url]=\think\Request::instance()->domain().'/';
        }
        if($one_list){
            $this->success('获取福位列表成功', $one_list);
        }else{
            $this->error("获取福位列表失败");
        }
    }

    /*
    * 获取某个福位分类下的福位信息
    *@param int category_id 福位分类id
    */
    public function positionsCategoryNextList(){
        $categoryId= request()->post('category_id','0');
        if(empty($categoryId) && $categoryId==null){
            $this->error('获取该分类下的福位分类信息失败');
        }
        $goodservice = new GoodsService();
        $condition["ng.category_id"] = $categoryId;
        $oneGoodsCategory = $goodservice->getBackStageGoodsList('','',$condition);
        foreach($oneGoodsCategory[data] as $k=>$v){
          $goods_detail[$v[goods_id]] = $goodservice->getBasisGoodsDetail($v[goods_id]);
        }
        if($goods_detail){
            $this->success("获取该分类福位下的福位列表成功", $goods_detail);
        }else{
            $this->error("获取该分类下的福位信息失败");
        }
    }
}