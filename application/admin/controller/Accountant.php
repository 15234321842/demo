<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/5
 * Time: 14:15
 */

namespace app\admin\controller;

//财务统计
use data\model\NfxUserAccountModel;
use data\model\NfxUserCommissionWithdrawModel;
use data\model\NsMemberAccountModel;
use data\model\NsMemberAccountRecordsModel;
use data\model\NsMemberBalanceWithdrawModel;
use data\model\NsOrderModel;
use data\model\NsOrderPaymentModel;
use data\model\NsOrderServicesModel;
use data\model\XianxiaModel;

class Accountant extends BaseController
{

//虚拟积分统计
    public function index()
    {

        if (request()->isAjax()) {
            $start_date = request()->post('start_date') == "" ? 0 : request()->post('start_date');
            $end_date = request()->post('end_date') == "" ? 0 : request()->post('end_date');

            if($start_date != 0 && $end_date != 0){
                $where["create_time"] = [
                    [
                        ">",
                        getTimeTurnTimeStamp($start_date)
                    ],
                    [
                        "<",
                        getTimeTurnTimeStamp($end_date)
                    ]
                ];
            }
            if($start_date != 0 && $end_date != 0){
                $where1["ask_for_date"] = [
                    [
                        ">",
                        getTimeTurnTimeStamp($start_date)
                    ],
                    [
                        "<",
                        getTimeTurnTimeStamp($end_date)
                    ]
                ];
            }
            $order_model = new NsOrderModel();
            $condition['pay_status'] = 2;
          $condition['or.order_status'] = ['egt', 1];
          $condition['refund_status'] = 0;
            $data['Shangping']      = $order_model->alias('or')->join('ns_order_goods gd','or.order_id=gd.order_id','left')->where($condition)->where($where)->sum('order_money');//商品订单金额统计
            $orderservice_model = new NsOrderServicesModel();
            $condition1['or.order_type'] = 2;
            $data['ZhuanyeFuwudingdan']  = $orderservice_model->alias('or')->join('ns_order_services_goods gd','or.order_id=gd.order_id','left')->where($condition)->where($where)->where($condition1)->sum('order_money');//专业服务订单统计
            $condition2['or.order_type'] = 1;
            $data['Jisidingdan']      = $orderservice_model->alias('or')->join('ns_order_services_goods gd','or.order_id=gd.order_id','left')->where($condition)->where($where)->where($condition2)->sum('order_money');//祭祀订单统计
            $condition3['status'] = 1;

            $UserCommissionWithdraw = new NfxUserCommissionWithdrawModel();

            $data['Jifentixian']        = $UserCommissionWithdraw->where($condition3)->where($where1)->sum('actual_cash');//收益积分提现
            $data['Jifengeshui']      = $UserCommissionWithdraw->where($condition3)->where($where1)->sum('tax');//收益积分提现个税
            $data['Jifenfree']     = $UserCommissionWithdraw->where($condition3)->where($where1)->sum('poundage');//收益积分手续费

            //代理充值积分
            $NsMemberAccountModel = new NsMemberAccountRecordsModel();
            $condition4['from_type'] = 30;
            $condition4['account_type'] = 1;
            $data['Dailicongzhi'] = $NsMemberAccountModel->where($condition4)->where($where)->sum('number');
            //转账，提现的时候给公司发票抵个税，平台调整积分并转现金到用户银行卡
            $condition5['from_type'] = 10;
            $Zhuanzhang = $NsMemberAccountModel->where($condition5)->where($where)->sum('number');
            $data['Zhuanzhang']= -$Zhuanzhang;
            //积分转给财务
            $condition6['from_type'] = 40;
            $condition6['account_type'] = 1;
            $data['Caiwu']=  $NsMemberAccountModel->where($condition6)->where($where)->sum('number');

            //线下充值现金
            $condition7['from_type'] = 45;
            $condition7['account_type'] = 2;
            $Xianxia=  $NsMemberAccountModel->where($condition7)->where($where)->sum('number');
            $data['Xianxia'] =-$Xianxia;

            //用户使用积分购买的订单
            $MemberAccount=new NsMemberAccountRecordsModel();
            $condition12['from_type']=['in','1,2'];
            $condition12['account_type']=1;
            $orderjifen=$MemberAccount->where($where)->where($condition12)->sum('number');
            $data['orderjifen'] =-$orderjifen;

            //用户使用现金余额购买

            $condition13['from_type']=['in','1,2'];
            $condition13['account_type']=2;
            $orderxianjin=$MemberAccount->where($where)->where($condition13)->sum('number');
            $data['orderxianjin'] =-$orderxianjin;

            //在线支付
            $orderpayment=new NsOrderPaymentModel();
            $condition8['pay_type']=['in','1,2'];
           $condition8['pay_status']=1;
            $Xianshang=$orderpayment->where($condition8)->where($where)->sum('pay_money');
            $data['Xianshang']= -$Xianshang;
            $data['Xianshangfree']= $Xianshang;

            //银行卡订单支付
            $Xianxia_model=new XianxiaModel();
            $condition10['type']=1;
            $condition10['status']=1;
            $Orderbank=$Xianxia_model->where($condition10)->where($where)->sum('num');
            $data['Orderbank']=$Orderbank;
            $data['Orderbankfree']=-$Orderbank;
            //现金提现
            $memberbalance=new NsMemberBalanceWithdrawModel();
            $condition9['status']=1;
            $data['Xianjintixian']=$memberbalance->where($condition9)->where($where1)->sum('actual_cash');

            //现金提现手续费
            $condition9['status']=1;
            $data['Xianjinfree']=$memberbalance->where($condition9)->where($where1)->sum('poundage');

            //订单返佣金
            $UserAccount_model=new NfxUserAccountModel();
            $UserAccount=$UserAccount_model->where($where)->sum('commission');
            $data['UserAccount']=-$UserAccount;
            $Tongji=($data['Dailicongzhi']+$Xianxia+$UserAccount+ $Orderbank+$Xianshang)-($Xianshang+(-$orderjifen)+(-$orderxianjin)+$data['Caiwu']+ $data['Jifentixian']  +$data['Jifengeshui']+ $data['Jifenfree']+$data['Xianjintixian']+ $data['Xianjinfree']+$Orderbank);
            $data['Tongji']=-$Tongji;
            return $data;

        }else{
            return view($this->style . 'Accountant/index');
        }
    }




}