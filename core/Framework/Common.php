<?php

/*******************************************************
 * Common.php
 *  定义一些全局方法
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

// 定义fastcgi_finish_request函数
if(!function_exists('fastcgi_finish_request')){
    // 提前将请求结果返回
    function fastcgi_finish_request(){
        flush();
    }   
}

/**
 * 返回参数中的指定key的值
 * 
 * @param array  param 
 * @param string key 
 * @param bool   require 是否必须
 * @param mix    def 默认值
 * @return mix
 */
function getParam($param, $key, $require = true, $def = null) 
{
	if (isset($param[$key])) {
		return $param[$key];
	} elseif ($require) {
		throw new core_Exception_LogicAlertException("require param: no [$key] param", 
            core_Config_ErrLogicCode::PARAM_NOT_EXISTS);
	}
	return $def;
}

/**
 * 返回代码行的位置
 * 
 * @return string
 */
function whereCalled($level = 1) 
{
	$trace = debug_backtrace();
	$file = $trace[$level]['file'];
	$line = $trace[$level]['line'];
    if (isset($trace[$level]['object'])){
        $object = $trace[$level]['object'];
        if (is_object($object)) {
            $object = get_class($object);
        }
    }else{
        $object = "";
    }
	return "$file ($line) $object";
}

/**
 * 打印变量param
 */
function dump($param) 
{
	$loc = whereCalled();
	echo "\n$loc\n";
}