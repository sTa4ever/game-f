<?php

/*******************************************************
 * index.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

require __DIR__ . '/common/enc_ini.php';

$mod = empty($_REQUEST['mod']) ? 'api' : $_REQUEST['mod'];
App::getInstance()->run($mod);