<?php
    namespace think\core;
    
    abstract class Widget
    {

        protected $attr = array ();
        protected $cacheChecked = false;
        protected $mid;
        protected $user;
        protected $request;

        /**
         * 架构函数 取得模板对
         * @access public
         */
        public function __construct( $request ) {
            //控制器初始化
            if(method_exists($this,'_initialize'))
                $this->_initialize();
            $this->request = $request;
            $before = C('BEFORE_WIDGET');
            foreach($before as $cls){
                $_class = new $cls;
                $res = $_class->beforeWidget($this->request);
                if( $res['status'] == 0){
                    redirect($res['jumpUrl']);return ;
                }else if( isset($res['__set']) ){
                    foreach($res['__set'] as $k=>$v){
                        $this->$k = $v;
                    }
                }
            }
        }


        abstract public function index($data);

        /**
         * 魔术方法 有不存在的操作的时候
         * @access public
         * @param string $method 方法名
         * @param array $parms
         * @return mix
         */
        public function __call($method,$parms) {
            if( 0 === strcasecmp($method,ACTION_NAME)) {
                // 如果定义了_empty操作 则调用
                if(method_exists($this,'_empty')) {
                    $this->_empty($method,$parms);
                }else {
                    // 检查是否存在默认模版 如果有直接输出模版
                    $this->display();
                }
            }else{
                Log::record(__CLASS__.':'.$method.' is not exist','WIDGET_ERR');
            }
        }


        /**
         +----------------------------------------------------------
         * 渲染模板输出 供render方法内部调用
         +----------------------------------------------------------
         * @access public
         +----------------------------------------------------------
         * @param string $templateFile  模板文件
         * @param mixed $var  模板变量
         * @param string $charset  模板编码
         +----------------------------------------------------------
         * @return string
         +----------------------------------------------------------
         */
        protected function renderFile($templateFile = '', $var = '', $charset = 'utf-8', $dir=__DIR__) {
            if (! file_exists ( $templateFile )) {
                Log::record('Widget 模板不存在：'.$templateFile,'WIDGET_ERR');
            }

            $content = fetch($templateFile,$var,$charset);

            return $content;
        }

        /**
         * 专题的页面渲染
         * 如果选择了自定义模板，则使用自定义模板
         * @param string $templateFile
         * @param string $var
         * @param string $charset
         * @return void
         */
        protected function renderColumnFile($templateFile = '', $var = '', $charset = 'utf-8') {
            $var['qm'] = $GLOBALS['qm'];
    
            if($var['tpl_data']['type']=='custom'){
                $file = APP_RUN_PATH.'/'.$var['page_id'].'-'.$var['hash'].'.php';
                if(!file_exists($file) || filemtime($file)<$var['tpl_data']['tpl_mtime'] ){
                    if(!is_dir(APP_RUN_PATH)){
                        mkdir(APP_RUN_PATH);
                    }
                    file_put_contents($file, trim($var['tpl_data']['content']));
                }
                $templateFile = $file;
            }
    
            if (! file_exists( $templateFile )) {
                Log::record('Widget 模板不存在：'.$templateFile,'WIDGET_ERR');
            }
    
            $content = fetch($templateFile,$var,$charset);
            return $content;
        }
    }
?>