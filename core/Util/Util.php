<?php

/*******************************************************
 * core_Util_Util.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class core_Util_Util
{
    /**
	 * check if the first arg starts with the second arg
	 *
	 * @param string $str		the string to search in
	 * @param string $needle	the string to be searched
	 * @return bool	true or false
	**/
	public static function starts_with($str, $needle)
	{
		$pos = stripos($str, $needle);
		return $pos === 0;
	}

	/**
	 * check if the first arg ends with the second arg
	 *
	 * @param string $str		the string to search in
	 * @param string $needle	the string to be searched
	 * @return bool	true or false
	**/
	public static function ends_with($str, $needle)
	{
		$pos = stripos($str, $needle);
		if( $pos === false ) {
			return false;
		}
		return ($pos + strlen($needle) == strlen($str));
	}
}
