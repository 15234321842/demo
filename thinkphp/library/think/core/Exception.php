<?php
namespace KMC\Core;

use Exception;
/**
 * 异常基类，此类异常需要捕获并处理
 */
class QMException extends Exception
{
    public function __construct($message, $code=0){
        parent::__construct($message, $code);
    }	
}