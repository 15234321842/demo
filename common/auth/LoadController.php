<?php
/**
* controller 基类
*/

namespace common\auth;

use common\model\Result;
use HttpResponseException;
use think\Controller;
use think\facade\Request;
use think\facade\Response;
use think\facade\Session;
use think\facade\Url;

class LoadController extends Controller
{
    public $my_uid = 0;  //当前登录者id
    public $my_departid = 0; //当前登录者所在部门
    public $my_info = []; //当前登录者信息
    public $store_id = 0; //当前店铺ID
    public $this_uid = 0;
    public $this_user = [];

    protected $notNeedLogin = array();
    protected $needPermission = array();

    public function __construct(){
        parent::__construct();
        //网站配置
        $this->initSite();
        $this->initUser();

        //控制器初始化
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
    }

    /**
     * 站点信息初始化
     * @access private
     * @return void
     */
    private function initSite()
    {

        //载入站点配置全局变量

        // $this->site = model('Xdata')->get('admin_Config:site');

        // $this->site['logo'] = getSiteLogo();

        // $GLOBALS['qm']['site'] = $this->site;
        // //客服
        // $customer_service = model('User')->getDetailByIds($this->site['customer_service']);
        // $this->assign('site', $this->site);
        // $this->assign('customer_service', $customer_service);

        return;
    }

    /**
     * 用户信息初始化
     * @access private
     * @return void
     */
    private function initUser()
    {

        $this->my_info = Session::get('userInfo');
        if($this->my_info){
            $this->my_uid = $this->my_info['user_id'];
            $this->my_departid = isset($this->my_info['depart_id']) ? intval($this->my_info['depart_id']) : 0;
        }
        $this->checkUserLogin();

        //当前访问对象的uid
        $this->this_uid = isset($_REQUEST['uid']) && !empty($_REQUEST['uid']) ? intval($_REQUEST['uid']) : $this->my_uid;
        $this->this_user = !empty($this->uid) ? model('User')->getUserInfo($this->this_uid) : array();

        $this->store_id = isset($_REQUEST['store_id']) && !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;

        //检测用户是否需要登录
        $this->checkUserLogin();
        $this->assign('my_uid',$this->my_uid); //登录者
        $this->assign('my_departid',$this->my_departid); //登录者所属部门
        $this->assign('my_info',$this->my_info); //登录者信息
        $this->assign('store_id',$this->store_id); //访问店铺ID
        $this->assign('this_uid',$this->this_uid); //访问用户id
        $this->assign('this_user',$this->this_user); //访问用户信息
    }


    /**
     * 检测用户是否需要登录
     */
    private function checkUserLogin(){
        $doLogin = true;

        if(in_array($this->request->action(),$this->notNeedLogin)) {
            $doLogin = false;
        }
        if($doLogin  && !($this->my_uid > 0)){

            if($this->request->isAjax()){
                $result = new Result();
                $result->success = false;
                $result->msg = '请先登录，再继续操作！';
                $result->needLogin = true;

                $response = Response::create($result,'json');
                $response->send();
                exit;
            }else{
                $this->redirect('/houtai/login/index');
            }
        }
    }


    /**
     * 判断用户权限
     */
    public function checkPermission(){
        if(in_array($this->request->action(),$this->needPermission)) {
            if(!($this->user_type > 0)){
                $result = new Result();
                $result->success = false;
                $result->msg = '没有权限操作！';
                $result->needPermission = true;

                $this->letuResponse($result);
            }
        }
    }


    public function letuResponse(Result $result){
        if($this->request->isAjax()){
            $response = Response::create($result,'json');
            $response->send();
            exit;
        }else{
            $this->error($result->msg);
        }
    }

    public function getUserId(){
        return $this->my_uid;
    }


    public function getUserInfo(){
        return $this->my_info;
    }

    public function setUserId($user_id){
        $this->my_uid = $user_id;
    }
}