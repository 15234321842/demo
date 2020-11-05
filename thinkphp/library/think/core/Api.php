<?php
    namespace think\action;

/**
     +----------------------------------------------------------
 *   Api基类服务
     +----------------------------------------------------------
 * 
 *  基本功能、步骤：
 * 
 *  1.用户认证:   通过账号密码登录获取token，token有一定有效期  
 *               
 *  2.访问普通的api: 不论是get还是post方式都需要在header头中带上token
 *
 *  3.错误数据返回格式:
 *     {
 *       "status":0,  //状态码，1：成功；0：失败
 *       "error":"获取失败"  //错误信息
 *	     "error_code":"101" //错误码
 *     }      
 *     
 *  4.成功时候:直接返回接口返回的数据
 *    {
 *        "status":1,  //状态码，1：成功；0：失败
 *        "msg":"获取成功"  //成功提示信息
 *        "data":{     }   //返回数据，成功返回的数据都放在这里
 *     }
 */
abstract class Api
{

    protected $request;
    public $format = 'json';   //api返回数据格式，默认为json,可以返回json或者 test [调试] 

    public $error_msg  = '未知错误';         //错误信息
    public $error_code = '10000';           //错误代码

    protected   $mid = 0; //当前登录

    /**
     * 构造函数 取得模板对
     * @access public
     */
    public function __construct($request)
    {
        $this->request = $request;
        //控制器初始化
        $this->init();
        if (method_exists($this, '_initialize'))
            $this->_initialize();
    }

    public function init()
    {
        //API 
        $before = C('BEFORE_API');
        if ($before) {
            foreach ($before as $cls) {
                $_class = new $cls;
                $res = $_class->beforeAction($this->request);
                if ($res['status'] == 0) {
                    $this->error();
                } else if (isset($res['__set'])) {
                    foreach ($res['__set'] as $k => $v) {
                        $this->$k = $v;
                    }
                }
            }
        }
        $noNeedLogin = C('DEFAULT_NO_LOGIN');
        $mod = APP_NAME . '/' . MODULE_NAME;
        $uri = APP_NAME . '/' . MODULE_NAME . '/' . ACTION_NAME;
        if (in_array($mod, $noNeedLogin)  || in_array($uri, $noNeedLogin) || in_array(APP_NAME, $noNeedLogin)) { } else {
            $this->authorize();
        }

        $this->format = $this->request->get['format'] ? $this->request->get['format'] : 'json';
    }

    //身份认证处理
    public function authorize()
    {
        //通过token验证用户
        $uid = model('Passport')->checkUser($_SERVER['HTTP_TOKEN']);
        if (empty($uid)) {
            $this->error('登录状态错误', 100);
        } else {
            $this->mid = $uid;
        }
    }

    //失败返回
    final public function error($error_msg = '', $error_code = '', $echo = true)
    {
        $return = array();
        $return['status'] = 0;
        $return['error']   = empty($error_msg) ? $this->error_msg : $error_msg;
        $return['error_code'] = empty($error_code) ? $this->error_code : $error_code;
        return $this->output($return, $echo);
    }

    /**
     * 操作成功
     * @param  [type]  $data [description]
     * @param  boolean $echo [description]
     * @return [type]        [description]
     */
    final public function success($data = "", $msg = 'success', $echo = true)
    {
        //成功状态信息
        $return = array('status' => 1, 'msg' => $msg, 'data' => $data);

        return $this->output($return, $echo);
    }


    /**
     * 输出
     */
    private function output($return, $echo = true)
    {

        if ($this->format == 'json') {
            header('content-type:application/json;charset=utf8');
            $return = json_encode($return);
        } else if ($this->format == 'test') {
            $return = '<pre>' . var_export($return, true) . '</pre>';
        }
        if ($echo) {
            echo $return;
            exit();
        } else {
            return $return;
        }
    }
}
