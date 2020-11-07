<?php
/**
 * Order.php
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

use data\model\NsOrderModel;
use data\model\NsToorderModel;
use data\model\XianxiaModel;
use data\model\UserModel;
use data\model\NsOrderGoodsModel;
use data\service\Address;
use data\service\Address as AddressService;
use data\service\Config;
use data\service\Express as ExpressService;
use data\service\Order\OrderGoods;
use data\service\Order\OrderStatus;
use data\service\Order as OrderService;
use data\service\Pay\AliPay;
use data\service\Pay\WeiXinPay;
use data\service\Goods as GoodsService;

/**
 * 订单控制器
 *
 * @author Administrator
 *        
 */
class Toorder extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 订单列表
     */
    public function orderList()
    {
        if (request()->isAjax()) {
            $page_index = request()->post('page_index', 1);
            $page_size = request()->post('page_size', PAGESIZE);
            $start_date = request()->post('start_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('start_date'));
            $end_date = request()->post('end_date') == "" ? 0 : getTimeTurnTimeStamp(request()->post('end_date'));
            $condition['is_deleted'] = 0; // 未删除订单
            $tostatus = request()->post('tostatus'); // 未删除订单
            $order_no = request()->post('order_no'); // 未删除订单
            if ($start_date != 0 && $end_date != 0) {
                $condition["create_time"] = [
                    [
                        ">",
                        $start_date
                    ],
                    [
                        "<",
                        $end_date
                    ]
                ];
            } elseif ($start_date != 0 && $end_date == 0) {
                $condition["create_time"] = [
                    [
                        ">",
                        $start_date
                    ]
                ];
            } elseif ($start_date == 0 && $end_date != 0) {
                $condition["create_time"] = [
                    [
                        "<",
                        $end_date
                    ]
                ];
            }
            if (! empty($this->shop_id)) {
                $condition['shop_id'] = $this->shop_id;
            }
            if (! empty($order_no)) {
                $condition['order_no'] = $order_no;
            }
            $condition['order_status'] = 4;
            if($tostatus != ''){
                switch ($tostatus){
                    case 0:
                        $condition['tostatus']=0;
                        break;
                    case 1:
                        $condition['tostatus']=1;
                        break;
                    case 2:
                        $condition['tostatus']=2;
                        break;
                }
            }else{
                $condition['tostatus']=3;
            }
            $order_service = new OrderService();
            $list = $order_service->getToOrderList($page_index, $page_size, $condition, 'create_time desc');
            return $list;
        } else {
            $status = request()->get('status', '');
            $this->assign("status", $status);
            $all_status = OrderStatus::getToorderCommonStatus();

            $child_menu_list = array();
            $child_menu_list[] = array(
                'url' => "toorder/orderList",
                'menu_name' => '全部',
                "active" => $status == '' ? 3 : 0
            );
            foreach ($all_status as $k => $v) {
                $child_menu_list[] = array(
                    'url' => "toorder/orderlist?status=" . $v['status_id'],
                    'menu_name' => $v['status_name'],
                    "active" => $status == $v['status_id']
                );
            }

            $this->assign('child_menu_list', $child_menu_list);
            return view($this->style . "Toorder/orderList");
        }
    }

    /**
     * 转售商品上架
     */
    public function ModifyGoodsOnline()
    {
        $condition = request()->post('order_id', '');
        $goods_detail = new OrderService();
        $result = $goods_detail->ModifyGoodsOnline($condition);
        return AjaxReturn($result);
    }

    /**
     * 转售商品下架
     */
    public function ModifyGoodsOffline()
    {
        $condition = request()->post('order_id', '');
        $goods_detail = new OrderService();
        $result = $goods_detail->ModifyGoodsOffline($condition);
        return AjaxReturn($result);
    }

//    /**
//     * 更改商品最小转售价格
//     */
//    public function updateOrderMinPriceAjax()
//    {
//        if (request()->isAjax()) {
//            $order_id = request()->post("order_id", "");
//            $tominprice = request()->post("tominprice", "");
//            $order = new OrderService();
//            $res = $order->updateOrderMinPrice($order_id, $tominprice);
//            return AjaxReturn($res);
//        }
//    }
//    /**
//     * 更改商品最大转售价格
//     */
//    public function updateOrderMaxPriceAjax()
//    {
//        if (request()->isAjax()) {
//            $order_id = request()->post("order_id", "");
//            $tomaxprice = request()->post("tomaxprice", "");
//            $order = new OrderService();
//            $res = $order->updateOrderMaxPrice($order_id,$tomaxprice);
//            return AjaxReturn($res);
//        }
//    }

}