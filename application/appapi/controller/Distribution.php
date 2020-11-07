<?php
/**
 * Created by PhpStorm.
 * User: Dy
 * Date: 2018/3/14
 * Time: 17:14
 */

namespace app\appapi\controller;
use data\model\UserModel as UserModel;
use data\service\NfxPartner;
use data\service\NfxPromoter;
use data\service\NfxRegionAgent;
use data\service\NfxShopConfig;
use data\service\NfxUser as NfxUserService;
use data\service\Member as MemberService;
use data\service\Platform;
use data\service\Shop as ShopService;
use data\service\Address;
use think;
use data\service\Config;


class Distribution  extends BaseController
{
    protected $uid;
    protected $shop_id;
    public function __construct()
    {
        parent::__construct();

    $this->uid=$this->getUserId();
        $this->shop_id=0;
    }


    /**
     * 推广员申请
     * 任鹏强
     */
    public function applyPromoter()
    {

            $promoter = new NfxPromoter();
            $uid = $this->uid;
            $shop_id = isset($_POST['shop_id']) ? $_POST['shop_id'] : 0;
            $promoter_shop_name = isset($_POST['promoter_shop_name']) ? $_POST['promoter_shop_name'] : '';
            $retval = $promoter->promoterApplay($uid, $shop_id, $promoter_shop_name);
            if($retval){
                $this->success('申请成功！');

            }else{
                $this->error('申请失败！');
            }




    }
    //申请推广员需要的信息
    public function applyPromoterinfo(){

        $reapply = isset($_GET['reapply']) ? $_GET['reapply'] : '0';
        // 推广员信息表
        $nfx_promoter = new NfxPromoter();
        $promoter_info = $nfx_promoter->getUserPromoter($this->uid, $this->shop_id);

        // 获取店铺推广员等级
        $shop_id = $this->shop_id;
        $promoter_level = $nfx_promoter->getPromoterLevelAll($shop_id);
        if (empty($promoter_level)) {
            $this->error("当前店铺未设置推广员");
        }

        // 获取用户在本店的消费
        $shop = new ShopService();
        $uid = $this->uid;
        $user_consume = $shop->getShopUserConsume($shop_id, $uid);
        $data=[
            'reapply'=>$reapply,
            'user_consume'=>$user_consume,
            'promoter_level'=>$promoter_level,
            'promoter_info'=>$promoter_info
        ];
     $this->success('申请推广员需要的信息',['data'=>$data]);
    }



}