<?php

/*******************************************************
 * core_Redis_Factory.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class core_Redis_Factory 
{
    // redis
    private $_redis;
    
    /**
     * 构造函数
     * 
     * @param model $redis
     * @return void
     */
    public function __construct($redis) 
    {
        $this->_redis = $redis;
    }
    
    /**
     * 魔术方法
     * 
     * @param string $method
     * @param mix $args
     * @return mix
     * @throws core_Exception_LogicAlertException
     */
    public function __call($method, $args) 
    {
        if (method_exists($this->_redis, $method)){
            return call_user_func_array(array($this->_redis, $method), $args);
        }
		throw new core_Exception_LogicAlertException("Nout found method $method in " 
            . get_class($this), core_Config_ErrLogicCode::ERR_METHOD_NOT_EXISTS);
	}
	
	/**
	 * 获得一个List(具有队列特性)
     * 
	 * @param string $name 全局唯一的名称
	 * @return core_Redis_List
	 */
	public function getList($name) 
    {
		return new core_Redis_List($this->_redis, $name);
	}
	
	/**
	 * 获得一个哈希表
     * 
	 * @param string $name 全局唯一的名称
	 * @return core_Redis_HashMap
	 */
	public function getHashMap($name) 
    {
		return new core_Redis_HashMap($this->_redis, $name);
	}
	
	/** 
	 * 获得一个集合
     * 
	 * @param string $name 全局唯一的名称
	 * @return core_Redis_Set
	 */
	public function getSet($name) 
    {
		return new core_Redis_Set($this->_redis, $name);
	}
	
	/**
	 * 获得一个有序集合
	 * @param string $name 全局唯一的名称
	 * @return core_Redis_ZSet
	 */
	public function getZSet($name) 
    {
		return new core_Redis_ZSet($this->_redis, $name);
	}
}
