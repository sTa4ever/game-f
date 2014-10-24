<?php

/*******************************************************
 * core_Redis_Set.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class core_Redis_Set extends core_Redis_Object #implements IteratorAggregate
{
    /**
     * 构造函数
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
	 * 向集合中添加元素
     * 
	 * @param string $element
     * @return bool
	 */
	public function add($element) 
    {
		return $this->_redis->sAdd($this->_name, $element);
	}
	
	/**
	 * 从集合中删除元素
     * 
	 * @param string $element
     * @return bool
	 */
	public function remove($element) 
    {
		return $this->_redis->sRemove($this->_name, $element);
	}
	
	/**
	 * 检测集合中是否包含元素
     * 
	 * @param string $element
     * @param bool
	 */
	public function isMember($element) 
    {
		return $this->_redis->sIsMember($this->_name, $element);
	}
	
	/**
	 * 清空集合
     * 
     * @return bool
	 */
	public function clear() 
    {
		return $this->_redis->delete($this->_name);
	}
	
	/**
	 * 获得集合元素个数 
     * 
     * @return bool
	 */
	public function size() 
    {
		return $this->_redis->sSize($this->_name);
	}
	
	/**
	 * 从集合中随机弹出一个元素
     * 
     * @return mix
	 */
	public function randPop() 
    {
		return $this->_redis->sPop($this->_name);
	}
	
	/**
	 * 从集合中随机获得一个元素
     * 
     * @return mix
	 */
	public function randGet() 
    {
		return $this->_redis->sRandMember($this->_name);
	}
	
    /**
     * 将元素从一个结合移动到另外一个集合
     * 
     * @param string $element
     * @param core_Redis_Set $dst_set
     * @return bool
     */
	public function move($element, core_Redis_Set $dst_set) 
    {
		return $this->_redis->sMove($this->_name, $dst_set->getName(), $element);
	}

    /**
     * 获取集合中所有元素
     * 
     * @return mix
     */
    public function members() 
    {
        return $this->_redis->sMembers($this->_name);
    }
	
	/*public function getIterator() {
		$all = $this->_redis->sMembers($this->_name);
		return new ArrayIterator($all);
	}*/
}
