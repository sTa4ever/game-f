<?php

/*******************************************************
 * core_Redis_ZSet.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class core_Redis_ZSet extends core_Redis_Object #implements IteratorAggregate
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
	 * 添加元素及对应的分值
     * 
	 * @param string $value
	 * @param string|int $score
	 * @return boolean
	 */
	public function add($value, $score) 
    {
		return $this->_redis->zAdd($this->_name, $score, $value) === 1;
	}
	
	/**
	 * 获得指定rank的元素
     * 
	 * @param int  $rank
     * @param bool $score_desc 是否降序。 默认false，升序
     * @param bool $withscores 返回值中是否包含score。默认false，不带score
	 * @return array
	 */
	public function get($rank, $score_desc = false, $withscores = false) 
    {
		return $this->getByRankRange($rank, $rank, $score_desc, $withscores);
	}
	
	/**
	 * 移除某个元素
     * 
	 * @param string $value
	 * @return bool
	 */
	public function remove($value) 
    {
		return $this->_redis->zDelete($this->_name, $value) === 1;
	}
	
	/**
	 * 移除有序集合中rank值介于start和stop之间的所有元素。
	 * start和end均是从0开始的，并且两者均可以是负值。
     * 当索引值为负值时，表明偏移值从有序集合中score值最高的元素开始。
	 * 例如：-1表示具有最高score的元素，而-2表示具有次高score的元素，以此类推。
     * 
	 * @param int $rank_start
	 * @param int $rank_end
	 * @param bool $withscores 返回值中是否包含score, 默认false
	 * @return array 删除的元素列表
	 */
	public function removeByRankRange($rank_start, $rank_end, $withscores = false) 
    {
		if($withscores) {
			return $this->_redis->zRemRangeByRank($this->_name, $rank_start, 
                    $rank_end, array('withscores' => true));
		}
		return $this->_redis->zRemRangeByRank($this->_name, $rank_start, $rank_end);
	}
	
	/**
	 * 移除有序集合中scroe位于min和max（包含端点）之间的所有元素
     * 
	 * @param int $score_start
	 * @param int $score_end
	 * @param bool $withscores 返回值中是否包含score, 默认false
	 * @return 删除的元素列表
	 */
	public function removeByScoreRange($score_start, $score_end, $withscores = false) 
    {
		if($withscores) {
			return $this->_redis->zRemRangeByScore($this->_name, $score_start, 
                    $score_end, array('withscores' => true));
		}
		return $this->_redis->zRemRangeByScore($this->_name, $score_start, $score_end);
	}
	
	/**
	 * 获得某个元素的score
     * 
	 * @param string $value
     * @return mix
	 */
	public function getScore($value) 
    {
		return $this->_redis->zScore($this->_name, $value);
	}
	
	/**
	 * 获得某个元素的排名
     * 
	 * @param string $value
	 * @param bool   $score_desc 是否降序,默认false
	 * @return int 排名rank值
	 */
	public function getRank($value, $score_desc = false) 
    {
		// 降序
		if($score_desc) {
			return $this->_redis->zRevRank($this->_name, $value);
		} else {
			return $this->_redis->zRank($this->_name, $value);
		}
	}
	
	/**
	 * 是否包含元素
     * 
	 * @param string $value
	 * @return bool
	 */
	public function contains($value) {
		return $this->getScore($value) != null;
	}
	
	/**
	 * 获得指定分值范围（包含两头）的元素个数
     * 
	 * @param int $score_start 开始分数
	 * @param int $score_end   结束分数
     * @return int
	 */
	public function count($score_start, $score_end) 
    {
		return $this->_redis->zCount($this->_name, $score_start, $score_end);
	}
	
	/**
	 * 根据排名范围获得元素列表
     * 
	 * @param int  $rank_start 排名起始
	 * @param int  $rank_end   排名终止
	 * @param bool $score_desc 是否降序。 默认false，升序
	 * @param bool $withscores 返回值中是否包含score。默认false，不带score
     * @return array
	 */
	public function getByRankRange($rank_start, $rank_end, $score_desc = false, $withscores = false) {
		if($score_desc) {
			return $this->_redis->zRevRange($this->_name, $rank_start, $rank_end, $withscores);
		} else {
			return $this->_redis->zRange($this->_name, $rank_start, $rank_end, $withscores);
		}
	}
	
	/**
	 * 根据分值范围获得元素列表
     * 
	 * @param int  $score_start 分值起始
	 * @param int  $score_end   分值终止
	 * @param bool $withscores  返回值中是否包含score, 默认false
	 * @param int  $offset      skip数
	 * @param int  $count       返回值列表长度
     * @return array
	 */
	public function getByScoreRange($score_start, $score_end, 
            $withscores = false, $offset = null, $count = null)
    {
		$with_option = false;
		$option = array();
		if($withscores) {
			$option['withscores'] = true;
			$with_option = true;
		}
		if($offset && $count) {
			$option['limit'] = array($offset, $count);
			$with_option = true;
		}
		if($with_option) {
			return $this->_redis->zRangeByScore($this->_name, $score_start, $score_end, $option);
		} else {
			return $this->_redis->zRangeByScore($this->_name, $score_start, $score_end);
		}
	}
	
	/**
	 * 为某个元素增加分值(原子),如果指定的元素不存在，那么将会添加该元素，并且其score的初始值为inc值
     * 
	 * @param string $value
	 * @param int|float $inc
     * @return mix
	 */
	public function incScore($value, $inc) 
    {
		return $this->_redis->zIncrBy($this->_name, $inc, $value);
	}
	
	/**
	 * 清空集合
     * 
     * @return mix
	 */
	public function clear() 
    {
		return $this->_redis->delete($this->_name);
	}
	
	/**
	 * 获得集合元素个数
     * 
     * @return int
	 */
	public function size() 
    {
		return $this->_redis->zSize($this->_name);
	}
	
	/*public function getIterator() {
		$all = $this->_redis->sMembers($this->_name);
		return new ArrayIterator($all);
	}*/
}