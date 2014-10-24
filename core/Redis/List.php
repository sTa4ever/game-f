<?php

/*******************************************************
 * core_Reids_List.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class core_Redis_List extends core_Redis_Object #implements IteratorAggregate
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
     * 获取指定位置的值
     * 
     * @param int $index
     * @return mix
     */
	public function get($index) 
    {
		return $this->_redis->lGet($this->_name, $index);
	}
	
    /**
     * 向列表中添加一个元素
     * 
     * @param mix $element
     * @return bool
     */
	public function add($element) 
    {
		return $this->rpush($element);
	}
	
    /**
     * 在列表头加入一个元素
     * 
     * @param mix $element
     * @return bool
     */
	public function lpush($element) 
    {
		return $this->_redis->lPush($this->_name, $element);
	}
	
    /**
     * 在列表尾加入一个元素
     * 
     * @param mix $element
     * @return bool
     */
	public function rpush($element) 
    {
		return $this->_redis->rPush($this->_name, $element);
	}
	
    /**
     * 在列表头弹出一个元素
     * 
     * @return mix
     */
	public function lpop() 
    {
		return $this->_redis->lPop($this->_name);
	}
	
    /**
     * 在列表尾弹出一个元素
     * 
     * @return mix
     */
	public function rpop() 
    {
		return $this->_redis->rPop($this->_name);
	}
	
    /**
     * 修剪列表，保留列表中一段数据
     * 
     * @param int $start 起始位置
     * @param int $stop  结束位置
     * @return mix
     */
	public function trim($start, $stop) 
    {
		return $this->_redis->lTrim($this->_name, $start, $stop);
	}
	
    /**
     * 清空列表
     * 
     * @return bool
     */
	public function clear() 
    {
		return $this->_redis->delete($this->_name);
	}
	
    /**
     * 返回列表的大小
     * 
     * @return int
     */
	public function size() 
    {
		return $this->_redis->lSize($this->_name);
	}

    /**
     * 移除列表的元素
     * 
     * @param mix $element
     * @param int $num     移除个数，默认全部
     * @return bool
     */
    public function remove($element, $num = 0)
    {
        return $this->_redis->lRem($this->_name, $element, $num);// all the matching elements are removed
    }

    /**
     * 获取列表中置顶区间的元素
     * 
     * @param int $start 起始位置
     * @param int $stop  终止位置
     * @return mix
     */
    public function range($start, $stop) 
    {
        return $this->_redis->lRange($this->_name, $start, $stop);
    }
	
	/*public function getIterator() {
		$all = $this->_redis->lRange($this->_name, 0, -1);
		return new ArrayIterator($all);
	}*/
}
