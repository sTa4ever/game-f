<?php

/*******************************************************
 * core_Factory_Abstract.php
 * 
 * @package game-f
 * @date    2014-10-27
 * @version v1.0.0
 *******************************************************/

abstract class core_Factory_Abstract
{
    // 处理过的数据库的配置, 分区+dbkey
    protected static $m_config  = array ();
    
    /**
     * 获取数据库配置
     * 
     * @params array params
     *              sec     必须，分区号
     *              type    必须，数据库类型，redis or mongo
     *              dbkey   必须
     */
    protected static function _getConfig($sec, $type, $dbkey) 
    {
        #return core_DI::get('dbconf')->get($type, $dbkey, $sec);
        if ( self::$m_config == null) {
            self::_initConfig();
        }
        if ($sec == null){
            $sec = "_default";
        }
        return self::$m_config[$sec][$type][$dbkey];
    }

    /**
     * 初始化配置
     * 
     * @return void
     */
    protected static function _initConfig()
    {
        // read from file to init self::m_config
        $file_path = APP_ROOT . '/dev/config.php';
        $config = include $file_path;
        self::$m_config  = array();

        $secdbs = $config['secs'];
        unset($config['secs']);
        self::$m_config['_default'] = $config;
        foreach ($secdbs as $sec=>$conf) {
            $sec_config = &self::$m_config[$sec];
            $sec_config = array();
            foreach ($config as $type=>$default_config){ 
                $sec_config[$type] = $default_config;
                if (isset($conf['internal'][$type])) {
                    array_merge($sec_config[$type], $conf['internal'][$type]);
                }
            }
        }
    }
    
    /**
     * 通过dbkey获取数据库连接对象
     *
     * @params array param 连接参数
     *          cstr
     *          db
     *          option
     */
    public static function getMongoInst($cstr)
    {
        if ( !isset(self::$m_mongos[$cstr]) ){
            self::$m_mongos[$cstr] = new Mongo($cstr);;
        }
        return self::$m_mongos[$cstr];
    }
}