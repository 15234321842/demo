<?php
    namespace think\action;
    class Log {

        // 日志级别 从上到下，由低到高
        const EMERG   = 'EMERG';  // 严重错误: 导致系统崩溃无法使用
        const ALERT    = 'ALERT';  // 警戒性错误: 必须被立即修改的错误
        const CRIT      = 'CRIT';  // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
        const ERR       = 'ERR';  // 一般错误: 一般性错误
        const WARN    = 'WARN';  // 警告性错误: 需要发出警告的错误
        const NOTICE  = 'NOTIC';  // 通知: 程序可以运行但是还不够完美的错误
        const INFO     = 'INFO';  // 信息: 程序输出信息
        const DEBUG   = 'DEBUG';  // 调试: 调试信息
        const SQL       = 'SQL';  // SQL：SQL语句 注意只在调试模式开启时有效

        // 日志记录方式
        const SYSTEM = 0;
        const MAIL      = 1;
        const TCP       = 2;
        const FILE       = 3;

        // 日志信息
        static $log =   array();
        static $_debug = array();

        // 日期格式
        static $format =  '[ c ]';

        /**
         +----------------------------------------------------------
         * 记录日志 并且会过滤未经设置的级别
         +----------------------------------------------------------
         * @static
         * @access public
         +----------------------------------------------------------
         * @param string $message 日志信息
         * @param string $level  日志级别
         * @param boolean $record  是否强制记录
         +----------------------------------------------------------
         * @return void
         +----------------------------------------------------------
         */
        static function record($message,$level=self::ERR,$record=false) {
            
            $now = date(self::$format);
            self::$log[$level][] =   "{$now} ".$_SERVER['REQUEST_URI']." \n  {$level}: {$message}\r\n";
            
            if(C('DEV_MOD') && $level =='ERR'){
                self::printDebug();
                self::save();
                exit();
            }
        }

        static function debug($label,$type,$value){
            self::$_debug[$label][$type] = $value;
        }
        static function printDebug(){
            debug_end('App');
            echo '<div style="color:#fff;background-color:#666;margin-left:310px;padding:10px"><pre> <font color="orange">KMC DEBUG:</font><br/>';

            foreach(self::$_debug as $label => $item){
                
                echo '<hr/> <font color="orange">'.$label.' msg:</font><br/>';
                echo 'Memories:<br/>',number_format(($item['mem_end'] - $item['mem_start']) /1024),'k<br/>';
                echo 'Time:<br/>',($item['times_end'] - $item['times_start']),' s<br/>';
            }
            echo '<br/> <font color="orange">Log:</font>：';
            print_r(Log::$log);
            echo '<br/> <font color="orange">Cache:</font>：';
            print_r(Cache::$log);
            echo '<br/> <font color="orange">File includes:</font>：';
            $files = get_included_files();
            dump($files);
            echo '</div>';
        }

        /**
         +----------------------------------------------------------
         * 日志保存
         +----------------------------------------------------------
         * @static
         * @access public
         +----------------------------------------------------------
         * @param integer $type 日志记录方式
         * @param string $destination  写入目标
         * @param string $extra 额外参数
         +----------------------------------------------------------
         * @return void
         +----------------------------------------------------------
         */
        static function save($type=self::FILE) {
            if( empty(self::$log)){
                return ;
            }
            $level_record = C('LOG_LEVEL');
            foreach(self::$log as $level => $log){
            	$destination = LOG_PATH.DS.$level.DS.date('y_m_d').".log";
            	!is_dir(LOG_PATH.DS.$level) && mkdir(LOG_PATH.DS.$level,0775,true);
            	if(is_file($destination) && floor(C('LOG_FILE_SIZE')) <= filesize($destination) ){
            		rename($destination,dirname($destination).DS.time().'-'.basename($destination));
            	}
                if($level == 'ERR' || in_array($level,$level_record) || empty($level_record)){
                    error_log(implode("",$log), $type,$destination );    
                }
                if(strpos($level, 'RR') != false ){
                    self::recentLog(implode("",$log),false);
                }
            	// 保存后清空日志缓存
            	self::$log[$level] = array();
            }
        }

        /**
         +----------------------------------------------------------
         * 日志直接写入
         +----------------------------------------------------------
         * @static
         * @access public
         +----------------------------------------------------------
         * @param string $message 日志信息
         * @param string $level  日志级别
         * @param integer $type 日志记录方式
         * @param string $destination  写入目标
         * @param string $extra 额外参数
         +----------------------------------------------------------
         * @return void
         +----------------------------------------------------------
         */
        static function write($message,$level=self::ERR,$type=self::FILE,$destination='',$extra='') {
            $level_record = C('LOG_LEVEL');
            if($level != 'ERR' && (!empty($level_record) && !in_array($level,$level_record))){
                return;
            }

            $now = date(self::$format);
            if(empty($destination)){
                $destination = LOG_PATH.DS.$level.DS.date('y_m_d').".log";
                !is_dir(LOG_PATH.DS.$level) && mkdir(LOG_PATH.DS.$level,0775,true);
            }
            if(self::FILE == $type) { // 文件方式记录日志
                //检测日志文件大小，超过配置大小则备份日志文件重新生成
                if(is_file($destination) && floor(C('LOG_FILE_SIZE')) <= filesize($destination) )
                      rename($destination,dirname($destination).DS.time().'-'.basename($destination));
            }

            error_log("{$now} ".$_SERVER['REQUEST_URI']." | {$level}: {$message}\r\n", $type,$destination,$extra );
            if(strpos($level, 'RR') != false ){
                self::recentLog("{$now} ".$_SERVER['REQUEST_URI']." | {$level}: {$message}\r\n");
            }
            if(C('DEV_MOD') && $level =='ERR'){
                self::printDebug();exit();
           }
        }

        static function recentLog($msg,$flush=true){
            if($flush){
                file_put_contents(LOG_PATH.DS.'recent.log', $msg);
            }else{
                error_log($msg, 3,LOG_PATH.DS.'recent.log','' );
            }
        }
    }
?>