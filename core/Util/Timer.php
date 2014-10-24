<?php

/*******************************************************
 * core_Util_Timer.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class core_Util_Timer
{
    /**
     * 时间
     *
     * @var int 
     */
    private static $_t;
    
    /**
     * 内存
     * 
     * @var int 
     */
    static $_m;

    /**
     * 计时器开始
     * 
     * @return void
     */
    public static function start() 
    {
        self::$_t = microtime(true);
        self::$_m = memory_get_usage();
    }

    /**
     * 计时器结束,返回处理时间（ms）及内存
     * 
     * @return array
     */
    public static function end() 
    {
        $t_cost = ceil(1000000 * (microtime(true) - self::$_t));
        $m_cost = memory_get_usage() - self::$_m;
        
        return array(
            't' => $t_cost,
            'm' => $m_cost,
        );
    }
    
    /**
     * 获取请求开始时间
     */
    public static function getStartTime()
    {
        return self::$_t;
    }
}
