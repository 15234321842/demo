<?php
    namespace think\action;
	
	class KException 
	{
		/**
		 * app异常处理
	     * @access public
	     * @return void
	     */
	    static public function appException($e) {
			Log::write($e->__toString(),Log::DEBUG);
			//开发状态 或者 是该处理的异常
			if( $e instanceof \KMC\core\Exception || C('DEV_MOD')){

				throw $e;
			}
			
	        return ;
	        //TODO 待优化
	    }

	    /**
	     * 自定义错误处理
	     * @access public
	     * @param int $errno 错误类型
	     * @param string $errstr 错误信息
	     * @param string $errfile 错误文件
	     * @param int $errline 错误行数
	     * @return void
	     */
	    static public function appError($errno, $errstr, $errfile, $errline)
	    {
			
	        switch ($errno) {
	            case E_DEPRECATED:break;
	            case E_RECOVERABLE_ERROR:
	            case E_ERROR:
	            case E_USER_ERROR:
	            case E_CORE_ERROR:
	            case E_PARSE:
	            case E_COMPILE_ERROR:
	                $errorStr = "[$errno] $errstr ".basename($errfile)." on line ".$errline;
	                if(C('LOG_RECORD')) Log::write($errorStr,Log::ERR);
	                break;
	            case E_USER_NOTICE:
	            	if(C('DEV_MOD')) Log::write($errorStr,Log::NOTICE);
	            	break;
	            case E_STRICT:
	            case E_USER_WARNING:
	            default:
	                $errorStr = "[$errno] $errstr ".basename($errfile)." on line ".$errline;
	                if(C('LOG_RECORD'))  Log::write($errorStr,Log::NOTICE);
	            break;
	      }
	    }

	    static public function shutDown(){
			
	    	//关闭session
	    	session_write_close();
	    	//返回最后发生的错误
			$_error = error_get_last();
	        //E_ERROR 致命的运行时错误（它会阻止脚本的执行）
	        //E_PARSE 从语法中解析错误
	        //E_CORE_ERROR 类似E_ERROR，但不包括PHP核心造成的错误
	        //E_COMPILE_ERROR 致命的编译时错误
	        //E_USER_ERROR 用户导致的错误消息
	        //E_RECOVERABLE_ERROR 大多数的致命错误
	        //E_ALL 所有的错误、警告和注意
	        if( $_error && in_array($_error['type'],array(E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR,E_RECOVERABLE_ERROR,E_ALL)) ){
	            $errorStr = "[{$_error['type']}] {$_error['message']} {$_error['file']} line: {$_error['line']}";
	            Log::write($errorStr,'shutDown');
	        }

	    }

	}
?>