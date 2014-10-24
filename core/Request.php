<?php

/*******************************************************
 * core_Request.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class core_Request implements ArrayAccess
{
    // 数据
    protected $_data = array();

    // 采集数据
    public function collectData() {
    }

    public function __construct() {
        $this->collectData();
    }

    // 重置请求数据
    public function setData($data) {
        $this->_data = $data;
    }

    public function getData() {
        return $this->_data;
    }

    public function __get($k) {
        return $this->_data[$k];
    }

    public function __set($k, $v) {
        return $this->_data[$k] = $v;
    }

    public function __isset($k) {
        return isset($this->_data[$k]);
    }

    public function __unset($k) {
        unset($this->_data[$k]);
    }

    public function offsetSet($k,$v){
        return $this->_data[$k] = $v;
    }

    public function offsetGet($k){
        return $this->_data[$k];
    }

    public function offsetExists($k){
        return isset($this->_data[$k]);
    }

    public function offsetUnset($k){
        unset($this->_data[$k]);
    }
}