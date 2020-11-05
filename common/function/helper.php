<?php
/**
 * 公共函数库
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-09-27
 * Time: 17:46:11
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */

/**
 * 系统加密方法
 * @param string $string 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 单位 秒
 * @return string
 * @author ls-huang
 */
function think_encrypt($string, $key = '', $expiry = 0){
    $ckey_length = 4;
    $key = md5($key != '' ? $key : config('crypt_key'));
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? substr(md5(microtime()), -$ckey_length) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string =  sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    return $keyc.str_replace(array('+','/','='),array('-','_',''),base64_encode($result));
}

/**
 * 系统解密方法
 * @param  string $string 要解密的字符串
 * @param  string $key  加密密钥
 * @return string
 * @author ls-huang
 */
function think_decrypt($string,$key = ''){
    $ckey_length = 4;
    $key = md5($key != '' ? $key : config('crypt_key'));
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));

    $string   = str_replace(array('-','_'),array('+','/'),$string);
    $mod4   = strlen($string) % 4;
    if ($mod4) {
        $string .= substr('====', $mod4);
    }

    $keyc = $ckey_length ? substr($string, 0, $ckey_length) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = base64_decode(substr($string, $ckey_length));
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
        return substr($result, 26);
    } else {
        return '';
    }
}

/**
 * 判断字符串是否为空
 * @param $string   需要判断的字符串
 * @return bool
 * @author ls-huang
 */
function string_empty($string){
    $empty = false;
    if(is_null($string) || strlen(trim($string)) == 0){
        $empty = true;
    }
    return $empty;
}

/**
 * 生成传统的查询参数url
 * @param $action
 * @param $params
 * @return string
 */
function letu_url($action,$params){
    $url = url($action);
    if(is_array($params)){
        $i = 0;
        $query = '';
        foreach($params as $k=>$v){
            if($i > 0){
                $query .= '&'.$k.'='.$v;
            }else{
                $query = $k.'='.$v;
            }
            $i++;
        }
        $url .= '?'.$query;
    }elseif(!string_empty($params)){
        $url .= '?'.$params;
    }
    return $url;
}

/**
 * 过虑掉数组空元素
 * @param $arr
 * @return array
 */
function letu_array_filter_empty($arr){
    return array_filter($arr,function($v){
        if(is_null($v) || strlen(trim($v)) == 0){
            return false;
        }else{
            return true;
        }
    });
}

/**
 * 随机产生字符串
 * @param int $len
 * @param string $format
 * @return string
 */
function rand_string($len=6,$format='ALL') {
    switch($format) {
        case 'ALL':
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@';
            break;
        case 'CHAR':
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-@';
            break;
        case 'NUMBER':
            $chars = '0123456789';
            break;
        default :
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@';
            break;
    }
    mt_srand((double)microtime()*1000000*getmypid());
    $password='';
    while(strlen($password) < $len){
        $password .= substr($chars,(mt_rand()%strlen($chars)),1);
    }
    return $password;
}
