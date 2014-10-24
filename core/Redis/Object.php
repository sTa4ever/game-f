<?php

/*******************************************************
 * core_Redis_Object.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

abstract class core_Redis_Object
{
    protected $_redis;
	protected $_name;
	
    /**
     * 构造函数
     * 
     * @param model $redis
     * @param string $name
     * @throws Exception
     */
	public function __construct($redis, $name) {
		if($redis == null || $name == null) {
			throw new core_Exception_LogicAlertException("name and redis can not be null");
		}
		$this->_redis = $redis;
		$this->_name = $name;
	}
	
	/**
	 * 获取当前实例在redis中的key值
     * 
     * @return string
	 */
	public function getName() 
    {
		return $this->_name;
	}
	
	/**
	 * 查看当前redis实例的ttl时间(秒)
     * 
     * @return int
	 */
	public function ttl() 
    {
		return $this->_redis->ttl($this->_name);
	}
	
	/**
	 * 设置当前实例的过期时间
     * 
	 * @param int $time Unix timestamp in seconds
     * @return bool
	 */
	public function expireAt($time) {
		return $this->_redis->expireAt($this->_name, $time);
	}
	
	/**
	 * 设置当前实例的超时时间周期
     * 
	 * @param int $timeout_seconds
     * @return bool
	 */
	public function setTimeout($timeout_seconds) 
    {
		return $this->_redis->setTimeout($this->_name, $timeout_seconds);
	}
	
	/**
	 * 删除当前实例的生命周期设置,使其永久有效
     * 
     * @return bool
	 */
	public function persist() 
    {
		return $this->_redis->persist($this->_name);
	}
}
