<?php

/*******************************************************
 * core_Util_Sign.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class core_Util_Sign
{
    // md5计算时的盐
    const KEY_MD5 = 'ABCEDFA';   
    
    /**
	 * 生成签名
     * 
     * @param string $method 方法
     * @param array  $data   数据
	 **/
	public static function genSign($method, $data, $secret = self::KEY_MD5, $namespace = 'sign')
	{
		$str = $method;
		ksort($data);
		foreach ($data as $k => $v) {
			if ($k != $namespace) {
				$str .= "$k=$v";
			}
		}
		$str .= $secret;
		return md5($str);
	}
    
    /**
     * 检查签名
     *
     * @param str $method
     * @param arr $data 数据
     * @param str $salt 密钥
     * @param str $sig_key data中的签名key
     * @return bool
     */
    public static function checkSign($method, array $data, $salt = self::KEY_MD5, $sig_key = 'sign') 
    {
        return $data[$sig_key] == self::genSign($method, $data, $salt, $sig_key);
    }
}

