<?php

/*******************************************************
 * core_Redis_HashMap.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class core_Redis_HashMap extends core_Redis_Object #implements IteratorAggregate
{
    /**
     * 构造方法
     * 
     * @param model $redis
     * @param string $name
     * @return void
     */
    public function __construct($redis, $name) 
    {
        parent::__construct($redis, $name);
    }
    
    /**
	 * 根据Key获得value
     * 
	 * @param string $key
     * @return mix
	 */
	public function get($key) 
    {
		return $this->_redis->hGet($this->_name, $key);
	}
	
	/**
	 * 向哈希表中添加k/v
     * 
	 * @param string $key
	 * @param string $value
     * @return bool
	 */
	public function set($key, $value) 
    {
		return $this->_redis->hSet($this->_name, $key, $value);
	}
	
	/**
	 * 移除key
     * 
	 * @param string $key
     * @returm bool
	 */
	public function remove($key) 
    {
		return $this->_redis->hDel($this->_name, $key);
	}
	
	/**
	 * 检测哈希表中是否包含key
     * 
	 * @param string $key
     * @return bool
	 */
	public function containsKey($key) 
    {
		return $this->_redis->hExists($this->_name, $key);
	}
	
	/**
	 * 返回哈希表的key集合
     * 
     * @return mix
	 */
	public function keySet() 
    {
		return $this->_redis->hKeys($this->_name);
	}
	
	/**
	 * 返回哈希表的value集合
     * 
     * @return mix 
	 */
	public function values() 
    {
		return $this->_redis->hVals($this->_name);
	}
	
	/**
	 * 清空哈希表
     * 
     * @return bool
	 */
	public function clear() 
    {
		return $this->_redis->delete($this->_name);
	}
	
	/**
	 * 获得哈希表的长度
     * 
     * @return int
	 */
	public function size() 
    {
		return $this->_redis->hLen($this->_name);
	}
	
	/**
	 * 向哈希表中添加多个k/v
     * 
	 * @param array $array
     * @return bool
	 */
	public function mutiPut($array) 
    {
		return $this->_redis->hMset($this->_name, $array);
	}

    /**
     * 从哈希表中获取指定field的数据
     * 
     * @param array $array 无索引值的纯数组
     * @return mix
     */
    public function mutiGet($array) 
    {
        if (!is_array($array)) {
            return false;
        }
        if (array_diff_key($array, array_keys(array_keys($array)))) {
            // 带索引值的也需要剔除
            return false;
        }
        return $this->_redis->hMget($this->_name, $array);
    }

    /**
     * 获取哈希表中所有数据
     * 
     * @return mix
     */
    public function allGet() 
    {
        return $this->_redis->hGetAll($this->_name);
    }
	
	/**
	 * 使哈希表中的指定key的value自增inc值
     * 
	 * @param string $key
	 * @param int $inc
	 * @throws Exception
	 */
	public function incrBy($key, $inc) {
		if(is_float($inc)) {
			return $this->_redis->hIncrByFloat($this->_name, $key, floatval($inc));
		} else if(is_int($inc)) {
			return $this->_redis->hIncrBy($this->_name, $key, intval($inc));
		} else {
			throw new core_Exception_LogicAlertException("Illegal inc value: $inc");
		}
	}
	
	/*public function getIterator() {
		$all = $this->_redis->hGetAll($this->_name);
		return new ArrayIterator($all);
	}*/

}
