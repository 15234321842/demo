<?php
	namespace think\action;
	abstract class Action
	{

	    //声明私有变量$name，只能在该类中使用，在该类的实例、子类中、子类的实例中都不能调用私有类型的属性和方法
	    private		$name =  '';
	    //声明受保护数组$tVar,$trace
	    protected   $tVar =  array();
	    protected   $trace = array();
	    protected   $appCssList = [];
	    protected   $appJsList = [];
	    //声明受保护变量$templateFile
	    protected   $templateFile = '';
	    protected 	$request;
	    protected 	$mid=0;
	    protected	$user=[];

	    /**
	     * 构造函数 取得模板对
	     * @access public
	     */
	    public function __construct($request) {
	    	$this->request = $request;
			//控制器初始化
	        $this->init();
	        if(method_exists($this,'_initialize'))
	            $this->_initialize();
	        
	    }

	    
	    public function init(){
	    	$before = C('BEFORE_ACTION');
	    	foreach($before as $cls){
	    		$_class = new $cls;
	    		$res = $_class->beforeAction($this->request);
	    		if( $res['status'] == 0){
	    			redirect($res['jumpUrl']);exit();
	    		}else if( isset($res['__set']) ){
	    			foreach($res['__set'] as $k=>$v){
	    				$this->$k = $v;
	    			}
	    		}
	    	}
	    }


	    public function goBack(){
	    	$href = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER'] : U('core/Index/index');
	    	redirect($href);exit();
	    }
	    /**
	     * 模板Title，keyword,用于在浏览器中动态显示页面标题
	     * @access public
	     * @param mixed $input 要
	     * @return
	     */
	    public function setTitle($title = '') {
	        $this->assign('_title',$title);
	    }

	    /**
	     * 模板变量赋
	     * @access protected
	     * @param mixed $name 要显示的模板变量
	     * @param mixed $value 变量的
	     * @return voi
	     */
	    public function assign($name,$value='') {
	        if(is_array($name)) {//$name是数组，$tVar与$name合并
	            $this->tVar   =  array_merge($this->tVar,$name);
	        }elseif(is_object($name)){//$name是对象，遍历对象，组合成数组
	            foreach($name as $key =>$val)
	                $this->tVar[$key] = $val;
	        }else {
	            $this->tVar[$name] = $value;//只赋值一个值$value
	        }
	    }

	    /**
	     * 取得模板显示变量的值(用来获取私有成员$tVar的值)
	     * @access protected
	     * @param string $name 模板显示变量
	     * @return mixed
	     */
	    protected function get($name) {
	        if(isset($this->tVar[$name]))
	            return $this->tVar[$name];
	        else
	            return false;
	    }
	    /**
	     * 模板显示
	     * 调用内置的模板引擎显示方法
	     * @access protected
	     * @param string $templateFile 指定要调用的模板文件
	     * 默认为空 由系统自动定位模板文件
	     * @param string $charset 输出编码
	     * @param string $contentType 输出类
	     * @return voi
	     */
	    protected function display($templateFile='',$charset='utf-8',$contentType='text/html') {
			// $site = model('Xdata')->get('admin:site');
	        // $this->assign('site',$site);
	        // $this->assign('user_info',$this->user);
			// $this->assign('mid',$this->mid);
	        echo $this->fetch($templateFile,$charset,$contentType,true);
	    }

	    /**
	     *  获取输出页面内容
	     * 调用内置的模板引擎fetch方法
	     * @access protected
	     * @param string $templateFile 指定要调用的模板文件
	     * 默认为空 由系统自动定位模板文件
	     * @param string $charset 输出编码
	     * @param string $contentType 输出类
	     * @return strin
	     */
	    protected function fetch($templateFile='',$charset='utf-8',$contentType='text/html',$display=false) {
	       	
	       	if(!in_array(APP_NAME.DS.APP_NAME.'.css',$this->appCssList) && file_exists(PUBLIC_PATH.DS.APP_NAME.DS.APP_NAME.'.css')){
	            $this->appCssList[] = APP_NAME.'/'.APP_NAME.'.css';
	        }
	        if(!in_array(APP_NAME.DS.APP_NAME.'.js',$this->appJsList) && file_exists(PUBLIC_PATH.DS.APP_NAME.DS.APP_NAME.'.js')){
	            $this->appJsList[] = APP_NAME.'/'.APP_NAME.'.js';
	        }
	    	$this->tVar['appCssList'] = $this->appCssList;
	    	$this->tVar['appJsList']  = $this->appJsList;
	    	$this->tVar['request'] = $this->request;

	        return fetch($templateFile,$this->tVar,$charset,$contentType,$display);
	    }

	    /**
	     * 操作错误跳转的快捷方法
	     * @access protected
	     * @param string $message 错误信息
	     * @param Boolean $ajax 是否为Ajax方
	     * @return voi
	     */
	    protected function error($message,$ajax=false) {
	        $this->_dispatch_jump($message,0,$ajax);
	    }
	    //wap端错误信息显示
	    protected function wapError($info){
	        if(!$this->get('waitSecond'))  $this->assign('waitSecond',"3");
	        $this->assign('error_info', $info);
	        $this->display(THEME_PATH.DS.'wap_errorPage.html');
	        exit;
	    }  
	    //404页面
	    protected function page404($message){
	       $this->assign('message',$message);
	       $this->display(THEME_PATH.DS.'page404.html');
	    }
	    /**
	     * 操作成功跳转的快捷方法
	     * @access protected
	     * @param string $message 提示信息
	     * @param Boolean $ajax 是否为Ajax方
	     * @return voi
	     */
	    protected function success($message='', $ajax=false) {
	        $this->_dispatch_jump($message,1,$ajax);
	    }

	    /**
	     * Ajax方式返回数据到客户端
	     * @access protected
	     * @param mixed $data 要返回的数据
	     * @param String $info 提示信息
	     * @param boolean $status 返回状态
	     * @param String $status ajax返回类型 JSON XML
	     * @return void
	     */
	    protected function ajaxReturn($data,$info='',$status=1,$type='JSON') {
	        // 保证AJAX返回后也能保存日志
	        if(C('LOG_RECORD')) Log::save();
	        $result  =  array();
	        $result['status']  =  $status;
	        $result['info'] =  $info;
	        $result['data'] = $data;
	        if(strtoupper($type)=='JSON') {
	            // 返回JSON数据格式到客户端 包含状态信息
	            header("Content-Type:text/html; charset=utf-8");
	            exit(json_encode($result));
	        }elseif(strtoupper($type)=='XML'){
	            // 返回xml格式数据
	            header("Content-Type:text/xml; charset=utf-8");
	            exit(xml_encode($result));
	        }elseif(strtoupper($type)=='EVAL'){
	            // 返回可执行的js脚本
	            header("Content-Type:text/html; charset=utf-8");
	            exit($data);
	        }else{
	            // TODO 增加其它格式
	        }
	    }

	    /**
	     * Action跳转(URL重定向） 支持指定模块和延时跳转
	     * @access protected
	     * @param string $url 跳转的URL表达式
	     * @param array $params 其它URL参数
	     * @param integer $delay 延时跳转的时间 单位为秒
	     * @param string $msg 跳转提示信息
	     * @return void
	     */
	    protected function redirect($url,$params=array(),$delay=0,$msg='') {
	        //保存日志
	        if(C('LOG_RECORD')) Log::save();
	        $url    =   U($url,$params);
	        redirect($url,$delay,$msg);
	    }

	    /**
	     * 默认跳转操作 支持错误导向和正确跳转
	     * 调用模板显示 默认为public目录下面的success页面
	     * 提示页面为可配置 支持模板标签
	     * @param string $message 提示信息
	     * @param Boolean $status 状态
	     * @param Boolean $ajax 是否为Ajax方式
	     * @access private
	     * @return void
	     */
	    private function _dispatch_jump($message,$status=1,$ajax=false) {
	        // 判断是否为AJAX返回
	        if($ajax || $this->isAjax()) $this->ajaxReturn('',$message,$status);
	        // 提示标题
	        $this->assign('msgTitle',$status? L('success') : L('fail'));
	        //如果设置了关闭窗口，则提示完毕后自动关闭窗口
	        if($this->get('closeWin'))    $this->assign('jumpUrl','javascript:window.close();');
	        $this->assign('status',$status);   // 状态
	        empty($message) && ($message = $status==1?L('success tips'):L('fail tips'));
	        $this->assign('message',$message);// 提示信息
	        
	        if($status) { //发送成功信息
	            // 成功操作后默认停留1秒
	            if(!$this->get('waitSecond'))    $this->assign('waitSecond',"2");
	            // 默认操作成功自动返回操作前页面
	            if(!$this->get('jumpUrl')) $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
	           
	            $this->display(THEME_PATH.DS.'success.html');

	        }else{
	            //发生错误时候默认停留3秒
	            if(!$this->get('waitSecond'))    $this->assign('waitSecond',"5");
	            // 默认发生错误的话自动返回上页
	            if(!$this->get('jumpUrl')) $this->assign('jumpUrl', $_SERVER["HTTP_REFERER"]);

	            $this->display(THEME_PATH.DS.'error.html');
	            
	        }
	        if(C('LOG_RECORD')) Log::save();
	        // 中止执行  避免出错后继续执行
	        exit ;
	    }

	    /**
	     * 是否AJAX请求
	     * @access protected
	     * @return bool
	     */
	    protected function isAjax() {
	        return is_ajax();
	    }

	     /**
	     * 魔术方法：注册模版变量(在直接设置私有属性值的时候，自动调用了这个__set()方法为私有属性$tVar赋值 )
	     * @access protected
	     * @param string $name 模版变量
	     * @param mix $value 变量值
	     * @return mixed
	     */
	    public function __set($name,$value) {
	        $this->assign($name,$value);
	    }

	     /**
	     * 魔术方法 调用不存在的方法的时候
	     * @access public
	     * @param string $method 方法名
	     * @param array $parms
	     * @return mix
	     */
	    public function __call($method,$parms) {
	    	//strcasecmp比较两个字符串，不区分大小
	        if( 0 === strcasecmp($method,ACTION_NAME)) {
	            // 如果定义了_empty操作 则调用
	            if(method_exists($this,'_empty')) {
	                $this->_empty($method,$parms);
	            }else {
	                // 检查是否存在默认模版 如果有直接输出模版
	                $this->display();
	            }
	        }else{
	        	//如果没有定义$method方法则记录日志
	        	Log::record(__CLASS__.':'.$method.' is not exist','ERR');
	        }
	    }
	   
	}
?>