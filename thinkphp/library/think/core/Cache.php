<?php
    namespace think\action;

    class Cache
    {//类定义开始


        /**
         +----------------------------------------------------------
         * 操作句柄
         +----------------------------------------------------------
         * @var string
         * @access protected
         +----------------------------------------------------------
         */
        public $handler = null ;


        /**
         +----------------------------------------------------------
         * 缓存连接参数
         +----------------------------------------------------------
         * @var integer
         * @access protected
         +----------------------------------------------------------
         */
        protected $options = array();

        /**
         +----------------------------------------------------------
         * 缓存类型
         +----------------------------------------------------------
         * @var integer
         * @access protected
         +----------------------------------------------------------
         */
        protected    $type       ;

        /**
         +----------------------------------------------------------
         * 缓存过期时间
         +----------------------------------------------------------
         * @var integer
         * @access protected
         +----------------------------------------------------------
         */
        protected $expire;
        //换成读取日志
        public static $log = array();
        
        public $debug = true;

        private static $instance = null;
        private static $cacheLock = 5;
        private static $cacheCheckTime = 10;
        public  static $cacheLockHash = array();    //锁的HASH阵列

        //单例模式（为了避免对内存造成浪费，直到需要实例化该类的时候才将其实例化）
        public static function getInstance(){
            if( self::$instance == null){
                self::$instance = new self();
                self::$instance->connect();
                //调用空间KMC\Core\App中的debug记录错误信息
                self::$instance->debug = \KMC\Core\App::getInstance()->debug;
            }
            return self::$instance;
        }

        //缓存实例化
        public function connect()
        {
            $type = ucwords(strtolower(trim(C('DATA_CACHE_TYPE'))));
            $className = "\KMC\Cache\Cache{$type}";
            $this->handler= new $className();
            return $this;
        }
        
        public function select($db){
			if( method_exists($this->handler, 'select')){
				return $this->handler->select($db);
			}
		}

		public function getHandler(){
			return $this->handler;
		}
        /**
         * 设置缓存
         * 
         * @param unknown_type $key
         * @param unknown_type $value
         * @param int $expire 过期时间
         * @return boolean
         */
        public function set($key,$value,$expire = 1800){
            //接管过期时间设置 -1 表示永远不过期
            //静态缓存
            static_cache($key,$value);
            $val = array('CacheData'=>$value,'CacheMtime'=>time(),'CacheExpire'=>is_null($expire)?'-1':$expire);    
            $key = C('DATA_CACHE_PREFIX').$key;
            $this->N('cache_write',1,$key);
            return $this->handler->set($key,$val,$expire);
        }

        public function clear(){
            return $this->handler->clear();
        }

        //redis设置缓存
        public function hSet($key,$field,$value){
            $key = C('DATA_CACHE_PREFIX').$key;
            $value = json_encode($value);
            return $this->handler->hSet($key,$field,$value);
        }

        //redis获取缓存
        public function hGet($key,$field){
            $key = C('DATA_CACHE_PREFIX').$key;
            $data = $this->handler->hGet($key,$field);
            return $data ? (array)json_decode($data,true) : false;
        }
        
        /**
         * mutex get 设置 ,支持mutex模式
         * 
         * $mutex 使用注意
         * 1.set的时候设置有效时间
         * 2.get返回false的时候需要主动创建缓存
         * 
         * @param unknown_type $key
         * @param unknown_type $mutex 如果未取到数据,是否会主动创建缓存
         */
        public function get($_key,$mutex=false){
            $key  = C('DATA_CACHE_PREFIX').$_key;
            
            $data = $this->handler->get($key);
            $this->N('cache_read',1,$key);
            //未设置过缓存 
            if(!$data){ return false; }
            //不需要mutex模式 
            if(!$mutex){ 
                if($data['CacheExpire']<0 || ($data['CacheMtime'] + $data['CacheExpire'] > time())){
                    return $this->returnData($data['CacheData'],$key);
                }else{
                    //过期了 清理原始缓存
                    $this->rm($_key);   
                    return false;
                }
            }
            
            //需要mutex模式
            if( ($data['CacheMtime'] + $data['CacheExpire']) <= time()){
                //正常情况 --有过期时间设置的mutex模式  用的比较多
                if($data['CacheExpire'] > 0){   
                    $data['CacheMtime'] = time();
                    $this->handler->set($key,$data);
                    return false;   
                }else{              
                    //异常情况  -- 没有设置有效期的时候,永久有效的时候
                    if(!$data['CacheData']){
                        $this->rm($_key);
                        return false;
                    }
                    return $this->returnData($data['CacheData'],$key);
                }
            }else{
                return $this->returnData($data['CacheData'],$key);
            }
        }
        
        /**
         * 删除缓存
         * 
         * @param string key 换成key值
         * @return boolean 
         */
        public function rm($_key){
            $key  = C('DATA_CACHE_PREFIX').$_key;
            static_cache($key,false);
            return $this->handler->rm($key);
        }

        /**
         * 根据某个前缀 批量获取多个缓存
         * 
         * @param unknown_type $prefix
         * @param unknown_type $key
         */
        public function getList($prefix , $key ){
            if($this->type == 'MEMCACHE'){  //memcache 有批量获取缓存的接口
                $prefix = C('DATA_CACHE_PREFIX').$prefix;
                $_data = $this->handler->getMulti( $prefix , $key );
                foreach($_data as $k=>$d){
                    $data[$k] = $this->returnData($d['CacheData'],$key);
                }
            }else{
                foreach($key as $k){
                    $_k = $prefix.$k;
                    $data[$k] = $this->get($_k);
                }

            }
            return $data;
        }
        

        
        /**
         * 主动锁(时间锁)模式(根据设定的锁定时间进行更新) **不要滥用**
         * @param unknown_type $key 缓存键
         * @param unknown_type $data 缓存数据
         * @param unknown_type $ttl 锁有效时间
         * @param unknown_type $lockListKey 锁所在的阵列,建议每个app/或者每一种类型 使用自己的阵列标志
         * 注:使用阵列的原因,可以手动清除缓存
         */
        public function setTimeData($key,$data,$ttl=60,$lockListKey='TimeKey'){
            
            if(!isset(self::$cacheLockHash[$lockListKey]) || empty(self::$cacheLockHash[$lockListKey])){
                self::$cacheLockHash[$lockListKey] = $this->get($lockListKey); 
            }
            self::$cacheLockHash[$lockListKey][$key] = array('setTime'=>time(),'lifeTime'=>$ttl);
            $this->handler->set($lockListKey,self::$cacheLockHash[$lockListKey]);   
            return $this->handler->set( $key , $data );
            
        }
        /**
         * 获取锁的数据
         * @param  [type] $key         [description]
         * @param  string $lockListKey [description]
         * @return [type]              [description]
         */
        public function getTimeData($key,$lockListKey='TimeKey'){
            if(!isset(self::$cacheLockHash[$lockListKey]) || empty(self::$cacheLockHash[$lockListKey])){
                self::$cacheLockHash[$lockListKey] = $this->handler->get($lockListKey); 
            }
            
            if(!self::$cacheLockHash[$lockListKey][$key]){  //还没有设置过此锁
                return false;
            }
            if(( self::$cacheLockHash[$lockListKey][$key]['setTime'] + self::$cacheLockHash[$lockListKey][$key]['lifeTime'] ) <= time()){
                //过期了,重新设置setTime,并返回false
                self::$cacheLockHash[$lockListKey][$key]['setTime'] = time();
                $this->handler->set($lockListKey,self::$cacheLockHash[$lockListKey]);
                return false;   //返回false 让程序去主动更新缓存
            }
            
            return $this->handler->get( $key );
        
        }
        /**
         * 主动删除时间锁 (设置过期)
         * @param  string $key         [description]
         * @param  string $lockListKey [description]
         * @return [type]              [description]
         */
        public function deleteTimeData($key='',$lockListKey='TimeKey'){
            if(!isset(self::$cacheLockHash[$lockListKey]) || empty(self::$cacheLockHash[$lockListKey])){
                self::$cacheLockHash[$lockListKey] = $this->get($lockListKey); 
            }
            if(empty($key)){
                foreach(self::$cacheLockHash[$lockListKey] as $k=>$v){
                    self::$cacheLockHash[$lockListKey][$k]['setTime'] = time()- self::$cacheLockHash[$lockListKey][$k]['lifeTime'];                                     
                }
            }else{
                if(!self::$cacheLockHash[$lockListKey][$key]){  //还没有设置过此锁
                    return false;
                }
                self::$cacheLockHash[$lockListKey][$key]['setTime'] = time()- self::$cacheLockHash[$lockListKey][$key]['lifeTime'];
            }
            return $this->handler->set($lockListKey,self::$cacheLockHash[$lockListKey]);            
            
        }
        
        /**
         * <被动锁模式,需要程序主动去锁定数据>
         * @param unknown_type $key 缓存的key值
         * @param unknown_type $lock 锁定or解锁
         * @param unknown_type $lockListKey 所在的lockKey列表key值  默认为LockKey 可以按应用自行划分
         */
        public function lockData($key='',$lock=1,$lockListKey='LockKey'){
            if(!isset(self::$cacheLockHash[$lockListKey]) || empty(self::$cacheLockHash[$lockListKey])){
                self::$cacheLockHash[$lockListKey] = $this->get($lockListKey); 
            }
            if(empty($key)){//批量操作
                foreach(self::$cacheLockHash[$lockListKey] as $k=>$v){
                    self::$cacheLockHash[$lockListKey][$k] = $lock;
                }
            }else{
                self::$cacheLockHash[$lockListKey][$key] = $lock;   
            }
            return $this->set($lockListKey,self::$cacheLockHash[$lockListKey]);

        }
        
        /**
         * 获取被动锁锁定的数据
         * @param unknown_type $key
         * @param unknown_type $lockListKey
         */
        public function getLockData($key,$lockListKey='LockKey'){
            
            if(!isset(self::$cacheLockHash[$lockListKey]) || empty(self::$cacheLockHash[$lockListKey])){
                self::$cacheLockHash[$lockListKey] = $this->get($lockListKey);  //只有锁定过才有效
            }
            if( self::$cacheLockHash[$lockListKey][$key] == 1 ){ //如果锁定 则解锁
                $this->lockData($key,0,$lockListKey);
                return false;
            }
            if( !isset(self::$cacheLockHash[$lockListKey][$key]) ){
                $this->lockData($key,0,$lockListKey);
            }
            return  $this->get($key);
        } 
        /**
         * 批量获取被动锁数据 目前只支持Memcache
         * @param unknown_type $prefix
         * @param unknown_type $key
         * @return unknown
         */
        public function  getLockCacheList($prefix , $key , $lockListKey='LockKey'){
            if(!isset(self::$cacheLockHash[$lockListKey]) || empty(self::$cacheLockHash[$lockListKey])){
                self::$cacheLockHash[$lockListKey] = $this->get($lockListKey);
            }
            $data = $this->getList( $prefix , $key );
            foreach($data as $k=>$v){
                $trueKey = $prefix.$k;
                if( self::$cacheLockHash[$lockListKey][$trueKey] == 1 ){ //如果锁定 则解锁
                    $this->lockData($trueKey,0,$lockListKey);
                    $data[$k] = false;
                }
            }
            return $data;
        }   

        /**
         * 返回数据 
         * 
         */
        private function returnData($data,$key){
            //TODO 可以在此对空值进行处理判断
            return $data;
        }

        public function N($type,$nums=1,$name){
            $f = $type == 'cache_read' ? 'Q' : 'W';
            $this->$f(1,$name);
        }

        // 读取缓存次数
        public function Q($times='',$name) {
            
            static $_times = 0;
            
            if($this->debug){
                self::$log['Q'] = $_times +1;
                self::$log['Qkey'][] = $name;
            }
            
            if(empty($times))
                return $_times;
            else
                $_times++;
        }

        // 写入缓存次数
        public  function W($times='',$name) {
            static $_times = 0;
            if($this->debug){
                self::$log['W'] = $_times +1;
                self::$log['Wkey'][] = $name;
            }
            if(empty($times))
                return $_times;
            else
                $_times++;
        }
    }