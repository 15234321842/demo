<?php
	namespace KMC\Http;
	use \KMC\Core\Log;
	class Request{
		public $get = null;
		public $post = null;
		public $cookie = null;
		public $file = null;
        public $debug = 0;
        public static $instance = null;

		public function __construct(){
            if(self::$instance != null){
                return self::$instance;
            }

			$this->get = $this->_dealXSS($_GET);
			$this->post = $this->_dealXSS($_POST);
			$this->cookie = $this->_dealXSS($_COOKIE);
			$this->debug = isset($this->get['debug'])?$this->get['debug']:0;
            $this->file = $_FILES;
            
            self::$instance = $this;
			return $this;
		}


		private function _dealXSS($data){
			get_magic_quotes_gpc() && $data = stripslashes_deep($data);
			return $this->check_gpc($data);
		}

		/**
		 * GPC参数过滤
		 * @param array|string $value The array or string to be striped.
		 * @return array|string Stripped array (or string in the callback).
		 */
		function check_gpc($value=array()) {
			if(is_array($value)){
				foreach ($value as $key=>$data) {
					//对get、post的key值做限制，只允许数字、字母、及部分符号_-[]#@~
					if(!preg_match('/^[a-zA-z0-9_\-#!@~\[\]]+$/i',$key)){
						unset($value[$key]);
						Log::write('wrong_parameter:gpc key=>'.htmlspecialchars(strip_tags($key)),'ERR');
					}

					//如果key值为app\mod\act,对value也做如上限制
					if( ($key=='app' || $key=='mod' || $key=='act') && !empty($data) ){
						if(!preg_match('/^[a-zA-z0-9_]+$/i',$data)){
							unset($value[$key]);
							Log::write('wrong_parameter:gpc key=>'.htmlspecialchars(strip_tags($key)),'ERR');
						}
					}else{
						if(!preg_match('/^[a-zA-z0-9_\-#!@~\[\]]+$/i',$key)){
							unset($value[$key]);
							Log::write('wrong_parameter:gpc key=>'.htmlspecialchars(strip_tags($key)),'ERR');
						}
					}
				}
			}
			return $value;
		}

	}
?>