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
     * 解密通过rawdata传过来的是数据
     */
    public static function decrypt() 
    {
		$rawp = file_get_contents('php://input');

		if (isset($_SERVER['HTTP_PC_ENC_SUPPORT']) && $_SERVER['HTTP_PC_ENC_SUPPORT'] == 2) {
			$key = pc_aes_key(array(1, 2, 5, 6, 10, 11, 12, 13));
			$input = $rawp;
			$data2 = base64_decode($input);
			$decrypted = pcdecrypt_cbc($data2, $key);
			$rawp = $decrypted;
		}
        return $rawp;
    }

    /**
     * 如果客户端支持加密，则在返回值的时候进行加密
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
}
