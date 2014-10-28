<?php

/*******************************************************
 * core_Request_Cli.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class core_Request_Cli extends core_Framework_Request 
{
    public function collectData() 
    {
        global $argv, $argc;
        for($i = 1;$i < $argc; $i++){
            foreach(explode(',',$argv[$i]) as $val){
                list($k, $v) = explode('=', $val);
                $this->_data[$k] = $v;
            }
        }
    }
}
