<?php

/********************************************************
 * enc_ini.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 * ******************************************************/

define('APP_DIR_ROOT', __DIR__ .'/../');
define('APP_CORE_ROOT', APP_DIR_ROOT . 'core/');

// 自动加载函数注册
require APP_CORE_ROOT . 'framework/loader.php';
Loader::getInstance()->register();

// 预定义的部分
require APP_CORE_ROOT . 'framework/Common.php';
