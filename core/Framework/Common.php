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
            core_Config_ErrLogicCode::ERR_PARAM_NOT_EXISTS);
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

// 将定义的数组转成Mongo的字段查询
function array2path($root, $arr)
{
    $ret = array();
    if (is_array($arr)) {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $ret2 = call_user_func(__FUNCTION__, $root.$k.'.', $v );
                $ret = array_merge($ret2, $ret);
            } else {
                $ret[] = $root . $v . '.';
            }
        }
    }else{
        $ret[] = $root.$arr.'.';
    }
    return $ret;
}

// 将mongo的字段转化为数组
function path2array($path)
{
    if (is_string($path)) {
        $path = explode('.',$path);
    }
    $arr = null;
    foreach (array_reverse($path) as $k) {
        if (is_null($arr)) {
            $arr = array($k);
        } else {
            $arr = array($k => $arr);
        }
    }
    return $arr;
}

// 将mongo的字段转化为数组，并且带值
function path2arraywithval($path, $val)
{
    if (is_string($path)){ 
        $path = explode('.',$path);
    }
    $arr = null;
    foreach (array_reverse($path) as $k) {
        if (is_null($arr)) {
            $arr = array($k => $val);
        } else {
            $arr = array($k => $arr);
        }
    }
    return $arr;
}

function arrayvalbypath(array $data, $path)
{
    if (is_string($path)) {
        $path = explode('.',$path);
    }
    $d = $data;
    foreach ($path as $k) {
        if (is_null($d)) {
            return null;
        }
        $d = $d[$k];
    }
    return $d;
}

function setarrayvalbypath(array &$data, $path, $val)
{
    if (is_string($path)) {
        $path = explode('.',$path);
    }
    $d = &$data;
    foreach ($path as $k) {
        if ($d == null) {
            $d = array();
        }
        if ($i == $cnt - 1) {
            break;
        }
        $d = &$d[$k];
    }
    $d = $val;
}

function val_array2path($data, &$ret, $prekey = null) 
{
    if (!is_array($data)) {
        $ret[$prekey] = $data;
        return;
    }
    if ($prekey) {
        $prekey = $prekey . '.';
    }
    foreach($data as $k=>$v) {
        $key = $prekey . $k;
        if (!is_array($v)) {
            $ret[$key] = $v;
        }else {
            val_array2path($v, $ret, $key);
        }
    }
}

function &valbypath(&$data, $path)
{
    if(is_string($path)){
        $path = explode('.',$path);
    }
    $cnt = count($path);
    $d = &$data;
    for( $i=0; $i<$cnt; $i++ ) {
        if ($d == null) {
            $d = array();
        }
        if ($i == $cnt - 1) {
            break;
        }
        $d = &$d[$path[$i]];
    }
    return $d[$path[$i]];
}

function val_path2array($data, &$ret) 
{
    foreach($data as $path=>$val) {
        $d = &valbypath($ret, $path);
        $d = $val;
    }
}

// 判断是否是带键值的数组
function is_assoc($var) {
    return is_array($var) && array_diff_key($var,array_keys(array_keys($var)));
}