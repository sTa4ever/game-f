<?php

/*******************************************************
 * core_Util_Secuity.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class core_Util_Secuity
{
    /**
     * 解密数据
     * 
     * return array
     */
    public static function decrypt($data) 
    {
		if (isset($_SERVER['HTTP_PC_ENC_SUPPORT']) && $_SERVER['HTTP_PC_ENC_SUPPORT'] == 2) {
			$key = pc_aes_key(array(1, 2, 5, 6, 10, 11, 12, 13));
			$input = $data;
			$data2 = base64_decode($input);
			$decrypted = pcdecrypt_cbc($data2, $key);
			$data = $decrypted;
		}
        return $data;
    }

    /**
     * 加密数据
     * 
     * @param array $data
     * @return array
     */
    public static function encrypt($data) 
    {
        if(isset($_SERVER['HTTP_PC_ENC_SUPPORT']) && $_SERVER['HTTP_PC_ENC_SUPPORT'] > 0){
            header("pc-response-enc: 1");

            $key = pc_aes_key(array(3,4,5,6,7,8,9,11));
            $encrypted_data = pcencrypt_cbc($data, $key, md5($data, true));
            $data = base64_encode($encrypted_data);
        }
        return $data;
    }
    
    /**
	 * Encode an integer and a string into an encrypt string
	 * 
	 * @param int $id
	 * @param string $str
	 * @return string
	 */
	public static function encodIdBit($id, $str = '')
	{
		$strChars = '0123456789abcdef';
		$arrValue = self::reinterpret_cast($id);
		
		$strCode = $strChars[$arrValue[0] >> 4] . $strChars[$arrValue[0] & 15];
		$strCode .= $strChars[$arrValue[1] >> 4] . $strChars[$arrValue[1] & 15];
		
		$intLen = strlen($str);
		for ($i = 0; $i < $intLen; ++$i) {
			$intValue = ord($str[$i]);
			$strCode .= $strChars[($intValue >> 4)] . $strChars[($intValue & 15)];
		}
		
		$strCode .= $strChars[$arrValue[2] >> 4] . $strChars[$arrValue[2] & 15];
		$strCode .= $strChars[$arrValue[3] >> 4] . $strChars[$arrValue[3] & 15];
		
		return $strCode;
	}
	
	/**
	 * Decode a encrypt string into an integer or an array
	 * 
	 * @param string $strCode	encrypt string
	 * @param bool $isNeed whether to retrive the string
	 * @return int|array|false
	 */
	public static function decodeidBit($strCode, $isNeed = false)
	{
		$intLen = strlen($strCode);
		if ($intLen < 10) {
			return false;
		}

		$id = hexdec($strCode[$intLen - 2] . $strCode[$intLen - 1]);
		$id = ($id << 8) + hexdec($strCode[$intLen - 4] . $strCode[$intLen - 3]);
		$id = ($id << 8) + hexdec($strCode[2] . $strCode[3]);
		$id = ($id << 8) + hexdec($strCode[0] . $strCode[1]);
		
		if ($isNeed) {
			$intLast = $intLen - 4;
			$str = '';
			for ($i = 4; $i < $intLast; $i += 2) {
				$str .= chr(hexdec($strCode[$i] . $strCode[$i + 1]));
			}
			if (strlen($str) > 32 || !preg_match('/^[^<>"\'\/]+$/', $str)) {
				return false;
			}
			return array('uid'  =>  $id,
                         'uname'=>  $str,
						);
		} else {
			return $id;
		}
	}
	
	/**
	 * Encode uid to protect it from the third party
	 *
	 * @param int $id
	 * @return  int
	**/
	public static function api_encode_id($id)
	{
		$sid = ($id & 0x0000ff00)<< 16;
		$sid += (($id & 0xff000000)>> 8)& 0x00ff0000;
		$sid += ($id & 0x000000ff)<< 8;
		$sid += ($id & 0x00ff0000)>> 16;
		$sid ^= 282335;	//该值定了就不能再改了，否则就出问题了
		return $sid;
	}

	/**
	 * Decode uid from sid
	 *
	 * @param int $sid
	 * @return int
	**/
	public static function api_decode_id($sid)
	{
		if (!is_int($sid)&& !is_numeric($sid))
		{
			return false;
		}

		$sid ^= 282335;	//该值定了就不能再改了，否则就出问题了
		$id = ($sid & 0x00ff0000)<< 8;
		$id += ($sid & 0x000000ff)<< 16;
		$id += (($sid & 0xff000000)>> 16)& 0x0000ff00;
		$id += ($sid & 0x0000ff00)>> 8;
		return $id;
	}
	
	/**
	 * Convert an integer into an array of bytes
	 * 
	 * @param int $id
	 * @return array
	 */
	private static function reinterpret_cast($id)
	{
		$arrValue = array();
		$id = intval($id);
		$arrValue[] = $id & 0x000000ff;
		$arrValue[] = ($id & 0x0000ff00) >> 8;
		$arrValue[] = ($id & 0x00ff0000) >> 16;
		$arrValue[] = ($id >> 24) & 0x000000ff;
		
		return $arrValue;
	}
	
	/**
	 * device id对称加密算法
	 *
	 * @param string $id 设备唯一标识码
	 * @param string $secret    密钥
	 * @return string
	 */
   public function encodeIdSign($id, $secret)
	{
		$md5_v = md5($secret);
		//Open the cipher
		$td = mcrypt_module_open('rijndael-128', '', 'cbc', '');
		//Create key and IV
		$key = substr($md5_v, 0, 16);
		$iv = strrev(substr($md5_v, 0, 16));
		//Intialize encryption，不满16字符后面补\0
		mcrypt_generic_init($td, $key, $iv);
		//Encrypt data
		$id = mcrypt_generic($td, trim($id));
		//Terminate encryption handler and close module
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return base64_encode($id);
	}
	
	/**
	 * device id解密算法
	 *
	 * @param string  $id 设备唯一标识码
	 * @param string  $secret    密钥
	 * @return string
	 */
	public function decodeIdSign($id, $secret)
	{
		$id = base64_decode($id);
		if (empty($id)) {
		    return false;
		}
		$md5_v = md5($secret);
		//Open the cipher
		$td = mcrypt_module_open('rijndael-128', '', 'cbc', '');
		//Create key and IV，不满16字符后面补\0
		$key = substr($md5_v, 0, 16);
		$iv = strrev(substr($md5_v, 0, 16));
		//Intialize encryption
		mcrypt_generic_init($td, $key, $iv);
		//Encrypt data
		$id = mdecrypt_generic($td, $id);
		//Terminate encryption handler and close module
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return trim($id);
	}
}
