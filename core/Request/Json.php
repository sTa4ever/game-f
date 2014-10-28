<?php

/********************************************************
 * core_Request_Json.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 * ******************************************************/

class core_Request_Json extends core_Framework_Request 
{

    private $json_str = null;

    public function __construct($json) 
    {
        $this->json_str = $json;
        parent::__construct();
    }

    public function collectData() 
    {
        $this->_data = json_decode($this->json_str, JSON_FORCE_OBJECT);
    }
}

