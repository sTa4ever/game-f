<?php

/*******************************************************
 * core_Request_Http.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class core_Request_Http extends core_Request
{
    private $_post = array();
    private $_get  = array();

    public function collectData() {
        foreach($_REQUEST as $k => $v) {
            $this->_data[$k] = $v;
        }
        foreach($_POST as $k => $v) {
            $this->_post[$k] = $v;
        }
        foreach($_GET as $k => $v) {
            $this->_get[$k] = $v;
        }
    }

    /**
     * 获取POST信息
     * 
     * @return array
     */
    public function getPost() 
    {
        return $this->_post;
    }

    /**
     * 获取get信息
     * 
     * @return array
     */
    public function getGet() 
    {
        return $this->_get;
    }
}
