<?php
    namespace think\action;
class App
{
	//单例
	private static $instance = null;

	//存放一些可以被应用调用的属性，如config,basepath,debug等
	private $attributes = array('config' => [], 'basepath' => '', 'debug' => false);

	//不可以被动态修改的属性
	private static $unchangeAttr = array(
		'basepath',
	);
	//单例模式
	public static function getInstance($basepath = null)
	{
		if (self::$instance == null) {
			self::$instance = new self();
			self::$instance->init($basepath);
		}
		return self::$instance;
	}

	//初始化操作
	private function init($basepath)
	{
		//加载config
		$this->attributes['basepath'] = $basepath;
		//设置常量
		$this->_setDefine();

		//处理HTTP请求，目前包括get，post(调用命名空间KMC\Http\Request中的all)
		$this->request = (new \KMC\Http\Request);
		$this->debug = $this->request->debug;
		debug_start('App');

		//设置其他默认配置
		$this->_setDefault();
	}

	//配置文件定义（定义了一堆常量）
	private function _setDefine()
	{

		define('SITE_PATH', $this->attributes['basepath']);
		define('SITE_DOMAIN', strip_tags($_SERVER['HTTP_HOST']));
		//检测PHP的运行环境
		define('IS_CGI', substr(PHP_SAPI, 0, 3) == 'cgi' ? 1 : 0);
		//检测当前操作系统
		define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
		if (!defined('_PHP_FILE_')) {
			if (IS_CGI) {
				// CGI/FASTCGI模式下
				$_temp  = explode('.php', $_SERVER["PHP_SELF"]);
				define('_PHP_FILE_',  rtrim(str_replace($_SERVER["HTTP_HOST"], '', $_temp[0] . '.php'), '/'));
			} else {
				define('_PHP_FILE_', rtrim($_SERVER["SCRIPT_NAME"], '/'));
			}
		}
		if (!defined('__ROOT__')) {
			// 网站URL根目录
			$_root = dirname(_PHP_FILE_);
			define('__ROOT__', (($_root == '/' || $_root == '\\') ? '' : rtrim($_root, '/')));
		}
		define('DS', DIRECTORY_SEPARATOR);

		define('CONF_PATH',	SITE_PATH . DS . 'config');
		define('DATA_PATH',	SITE_PATH . DS . 'data');

		//日志记录
		define('LOG_PATH',	DATA_PATH . DS . 'logs');


		define('LANG_PATH', CONF_PATH . DS . 'lang');
		define('PUBLIC_PATH',	SITE_PATH . DS . 'public');
		define('CORE_RUN_PATH',	DATA_PATH . DS . '_runtime');
		define('THEME_PATH', SITE_PATH . DS . 'views');
	}


	//时区、异常、缓存定义
	private function _setDefault()
	{
		set_error_handler(array('\KMC\Core\KException', 'appError'));
		set_exception_handler(array('\KMC\Core\KException', 'appException'));
		register_shutdown_function(array('\KMC\Core\KException', 'shutDown'));
		// 读取站点配置. 时区、语言包、应用列表、插件列表等等   

		$conv = include(CONF_PATH . DS . 'conv.inc.php');
		$config = include(CONF_PATH . DS . 'config.inc.php');
		$this->config = array_merge($conv, $config);

		date_default_timezone_set($this->config['DEFAULT_TIMEZONE']);
		header("Content-Type:text/html;charset=" . $this->config['HEADER_CHARSET']);

		//配置APP相关的常量
		define('APP_NAME', isset($this->request->get['app']) ? $this->request->get['app'] : $this->config['DEFAULT_APP']);
		$mod = isset($this->request->get['mod']) ? $this->request->get['mod'] : $this->config['DEFAULT_MODULE'];
		define('IS_WIDGET', strpos($mod, 'Widget'));
		define('IS_API', strpos($mod, 'Api'));
		define('MODULE_NAME', str_replace(array('Widget', 'Api'), '', $mod));
		define('ACTION_NAME', isset($this->request->get['act']) ? $this->request->get['act'] : $this->config['DEFAULT_ACTION']);
		define('APP_PATH', SITE_PATH . DS . 'apps' . DS . APP_NAME);
		define('APP_RUN_PATH', CORE_RUN_PATH . DS . '' . APP_NAME);
		define('APP_TPL_PATH', THEME_PATH . DS . APP_NAME);

		//网站地址支持配置
		define('SITE_URL', !empty($this->config['SITE_URL']) ? $this->config['SITE_URL'] : $_SERVER['REQUEST_SCHEME'] . '://' . SITE_DOMAIN . __ROOT__);
		!defined('STATIC_URL') && define('STATIC_URL',	SITE_URL);
		!defined('THEME_PUBLIC_URL') && define('THEME_PUBLIC_URL',	STATIC_URL);
		define('PUBLIC_URL',	STATIC_URL);

		//上传地址支持配置
		define('UPLOAD_PATH', !empty($this->config['UPLOAD_PATH']) ? $this->config['UPLOAD_PATH'] : DATA_PATH . DS . 'upload');
		define('UPLOAD_URL',  !empty($this->config['UPLOAD_URL']) ? $this->config['UPLOAD_URL'] : SITE_URL . '/upload');

		//加载模块下的方法
		file_exists(APP_PATH . DS . 'Common' . DS . 'common.php') && include_once(APP_PATH . DS . 'Common' . DS . 'common.php');

		if (file_exists(APP_PATH . DS . 'Conf' . DS . 'config.php')) {
			$config = include(APP_PATH . DS . 'Conf' . DS . 'config.php');
			$this->config = array_merge($this->config, $config);
		}
	}


	public function run()
	{

		// (new LicenseClient())->check();
		//API控制器
		if (IS_WIDGET !== false) {
			$this->execWidget();
		} elseif (IS_API !== false) {
			$this->execApi();
		} else {
			$this->execApp();
		}
		debug_end('App');
		//打印错误信息
		$this->printDebug();
		static_cache(null, null, true);
	}
	//widget访问
	public function execWidget()
	{
		//创建Widget控制器实例
		$className = "\APP\\" . APP_NAME . "\Widget\\" . MODULE_NAME . "\\" . MODULE_NAME;
		$act = ACTION_NAME;
		$class = new $className($this->request, true);
		echo $class->$act();
	}
	//app访问
	public function execApp()
	{
		//创建Action控制器实例
		$className = "\APP\\" . APP_NAME . "\Action\\" . MODULE_NAME;
		$act = ACTION_NAME;
		$class = new $className($this->request);
		$class->$act();
	}
	//api访问
	public function execApi()
	{
		//创建Api控制器实例
		$className = "\APP\\" . APP_NAME . "\Api\\" . MODULE_NAME;
		$act = ACTION_NAME;
		$class = new $className($this->request);
		$class->$act();
	}
	//打印错误信息
	public function printDebug()
	{
		if ($this->debug) {
			debug_end('App');
			!is_ajax() && Log::printDebug();
		}
	}

	public function setConfig($key, $value)
	{
		$this->attributes['config'][$key] = $value;
	}
	//获取私有属性值
	public function __get($key)
	{
		return $this->attributes[$key];
	}
	//设置私有属性值
	public function __set($key, $value)
	{
		if (!in_array($key, self::$unchangeAttr)) {
			$this->attributes[$key] = $value;
		}
	}
}


class LicenseClient{
	private $license = '';
	private $license_info = [];
	/**
	 * 获取本地License
	 */
	public function getLocalLicense(){
		$license = CONF_PATH.DS.C('LICENSE_FILE');

		if(!file_exists($license)){
			$this->reportError("没有找到授权文件，请确认.lic文件正确的放到了 config目录中，并在config.inc.php中定义了 LICENSE_FILE 配置。");
		}
		$this->license = file_get_contents($license);

		$this->parseLicense();

		return $this->license_info;
	}

	/**
	 * 解析License
	 */
	public function parseLicense(){
		$info = explode("\n", $this->license);


		foreach($info as $value){
			$temp = explode(':', $value);
			$this->license_info[$temp[0]] = $temp[1];
		}
		unset($info, $temp);
	}

	/**
	 * 验证License的有效性
	 *
	 * @return bool
	 */
	public function check(){
		$this->getLocalLicense();
		
		if( !checkSign($this->license_info, C('HOME_PUBLIC_KEY'))){
			$this->reportError( "验证Lic文件失败，请确认Lic文件内容 或者 是否正确的配置了 HOME_PUBLIC_KEY。");
			exit;
		}

		$this->checkDomain();
		$this->checkUserLimit();
		$this->checkTimeLimit();
		return true;
	}

	/**
	 * 验证用户数限定
	 *
	 * @return void
	 */
	public function checkDomain()
	{
		if($this->isLocalIP()) 
			return true;

		// $domain = $this->getDomain();
		// if($domain!=$this->license_info['domain']){
		// 	$this->reportError( "域名验证失败，只允许{$this->license_info['domain']}的域名访问");
		// 	exit;
		// }
	
		return true;
	}

	/**
	 * 验证用户数限定
	 *
	 * @return void
	 */
	public function checkUserLimit($uid = 0){
		if($this->license_info['users']=='nerver') return true;
		
		if($mid = session('mid')){
			$map['uid'] = ['lt', $mid];
			$map['is_del'] = 0;
			$user_order = model('User')->where($map)->count();

			if($user_order > $this->license_info['users']){
				$this->reportError("用户验证失败，当前只允许{$this->license_info['users']}个用户访问");
				exit;
			}
		}

		return true;
	}

	/**
	 * 管理员在登录时，对判断是否需要进行提醒
	 *
	 * @return void
	 */
	public function checkUserLimitAdmin(){
		if($this->license_info['users']=='nerver') return true;

		$map['is_del'] = 0;
		$all_count = model('User')->where($map)->count();

		if($all_count > $this->license_info['users']){
			$this->error = "当前用户已经超过了{$this->license_info['users']}的允许值，请联系管理员处理";
			return false;
		}

		return true;
	}

	/**
	 * 验证时间限定
	 *
	 * @return void
	 */
	public function checkTimeLimit(){
		if($this->license_info['expires']=='nerver') return true;

		if(strtotime($this->license_info['expires']) < time()){
			$this->reportError("授权已经超时，允许的时间为 {$this->license_info['expires']}内");
			exit;
		}

		return true;
	}

	
	/**
     * 判断当前是不是Window
     *
     * @return boolean
     */
    private function isWindows()
    {
        return (strtolower(substr(php_uname(), 0, 7)) === 'windows');
    }



	/**
     * 是否是本地IP
     *
     * @return bool
     */
    private function isLocalIP()
    {

        $local_ip = $this->getDomain();
		if ($local_ip === '127.0.0.1' 
			|| $local_ip === 'localhost'  
			||  strpos($local_ip, "192.")!==false 
		)
            return true;

        return false;
	}
	

	/**
     * 获取服务器的访问地址
     */
    private function getDomain()
    {
		\KMC\Core\Log::write('after create Sigin'. var_export($_SERVER, 1), 'SIGN');
        return $_SERVER['SERVER_NAME'];
	}
	

	public function reportError($error){
		header("Content-Type:text/html;charset=utf-8");
		echo $error;exit;
	}
}