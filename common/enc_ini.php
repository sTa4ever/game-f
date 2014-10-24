<?php

/********************************************************
 * enc_ini.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 * ******************************************************/

define('ROOT', __DIR__ .'/../');
define('CORE_ROOT', ROOT . 'core/');
define('APP_ROOT', ROOT . 'app/');

// 自动加载函数注册
require CORE_ROOT . 'framework/loader.php';
Loader::getInstance()->register();

// 预定义的部分
require CORE_ROOT . 'framework/Common.php';

// 加载应用
require APP_ROOT . 'App.php';
