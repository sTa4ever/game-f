<?php

/********************************************************
 * core_Cache_Apc.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 ********************************************************/

class core_Cache_Apc
{
    
    /**
     * APC默认缓存时间
     */
    const APC_CACHE_TIME_DEFAULT = 3600;
    
    /**
     * 获取存储在apc中的key
     * 
     * @param string  $key 原始key
     * @param bool    $isVersionSpecial 是否各版本不同
     * @return string key
     */
    private static function _getCacheKey($key, $isVersionSpecial)
    {
        return $isVersionSpecial ? 'test' . $key : $key;
    }

    /**
	 * Look for a value in the cache. If it exists, return the data
	 * if not, return false
	 *
	 * @param string $key              原始key
     * @param bool   $isVersionSpecial 是否版本相关
	 * @return 	mixed  value that is stored/FALSE on failure
	 */
	public static function get($key, $isVersionSpecial = false)
    {
		return apc_fetch(self::_getCacheKey($key, $isVersionSpecial));
	}

	// ------------------------------------------------------------------------

	/**
	 * Cache Save
	 *
	 * @param string $key   Unique Key
	 * @param mixed  $data  value to store
     * @param bool   $isVersionSpecial 是否版本相关
	 * @param int	 $ttl   Length of time (in seconds) to cache the data
	 * @return 	boolean		true on success/false on failure
	 */
	public static function set($key, $data, $isVersionSpecial = false, 
            $ttl = self::APC_CACHE_TIME_DEFAULT)
    {
		return apc_store(self::_getCacheKey($key, $isVersionSpecial), $data, $ttl);
	}

	// ------------------------------------------------------------------------

	/**
	 * Delete from Cache
	 *
	 * @param string $key 
     * @param bool   $isVersionSpecial 是否版本相关
	 * @return boolean true on success/false on failure
	 */
	public static function delete($key, $isVersionSpecial = false)
    {
		return apc_delete(self::_getCacheKey($key, $isVersionSpecial));
	}
    
	/**
	 * is_supported()
	 *
	 * Check to see if APC is available on this system, return false if it isn't.
	 */
	public static function is_supported()
    {
		if (!extension_loaded('apc') || !function_exists('apc_store')) {
			error_log('The APC PHP extension must be loaded to use APC Cache.');
			return false;
		}
		return true;
	}
}
