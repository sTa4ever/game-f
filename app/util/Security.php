<?php

/*******************************************************
 * util_Security.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class util_Security
{
    /**
     * 解密通过rawdata传过来的是数据
     * 
     * @return array
     */
    public function decryptRawData()
    {
        $data = file_get_contents('php://input');
        return core_Util_Secuity::decrypt($data);
    }
}
