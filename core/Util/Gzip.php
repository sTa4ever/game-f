<?php

/*******************************************************
 * core_Util_Gzip.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class core_Util_Gzip
{
    /**
     * 压缩数据
     * 
     * @param array $data
     * @return array
     */
    public static function compress($data) 
    {
        if(isset($_SERVER['HTTP_PC_ZIP_SUPPORT']) && $_SERVER['HTTP_PC_ZIP_SUPPORT'] == 1 ){
            header("pc-response-length: ".strlen($data));
            $data = gzcompress($data);
        }
        return $data;
    }
}
