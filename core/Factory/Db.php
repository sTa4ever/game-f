<?php

/*******************************************************
 * core_Factory_Db.php
 * 
 * @package game-f
 * @date    2014-10-27
 * @version v1.0.0
 *******************************************************/

class core_Factory_Db extends core_Factory_Abstract
{
    // 进程中缓存的mongo
    private static $m_mongos  = array (); 
    // 进程缓存的所有db, 以schema 中定义的cstr+db
    private static $m_dbs     = array ();
    // 进程缓存的所有collection，dbkey+coll+sec
    private static $m_colls   = array ();
    
    /**
     * 获取mongo
     * 
	 * @param string sec        分区号
     * @param string dbkey      dbkey
     * @param string collection collection
     * @return db
	 */
	public static function getMongo($params) 
    {
        if ( !isset($params['dbkey']) ){
            throw new core_Exception_LogicAlertException("invalid getmongo param" , 
                core_Config_ErrLogicCode::ERR_INVALID_PARAM);
        }

        $config = self::_getConfig($params['sec'], 'mongo', $params['dbkey']);
        if (is_array($config)){
            if (!isset($params['collection'])) {
                if(!isset($config['collection'])) {
                    throw new core_Exception_LogicAlertException("invalid config {$params['dbkey']} , need collection" , core_ErrorCode::invalid_param);
                }
                $params['collection'] = $config['collection'];
            }
            $collection = str_replace("{sec}",$params['sec'], $params['collection']);
            return self::getMongoCollInst($config['cstr'], $config['db'], $collection);
        }else{
            throw new core_Exception_LogicAlertException("mongo config {$params['sec']} {$params['dbkey']} not found", core_ErrorCode::config_not_found);
        }
	}

    /**
     * 获取mongo实例
     * 
     * @param string $cstr
     * @param string $db
     * @return mongod
     */
    public static function getMongoDbInst($cstr, $db)
    {
        $key = "{$cstr}:{$db}";
        if ( !isset(self::$m_dbs[$key])){
            $mongo = self::getMongoInst($cstr);
            self::$m_dbs[$key] = $mongo->$db;
        }
        return self::$m_dbs[$key];
    }

    /**
     * 获取带有collection的mongod
     * 
     * @param string $cstr
     * @param string $db
     * @param string $coll
     * @return mongo
     */
    public static function getMongoCollInst($cstr, $db, $coll)
    {
        $key = "{$cstr}:{$db}:{$coll}";
        if ( !isset(self::$m_colls[$key]) ){
            $mongodb = self::getMongoDbInst($cstr, $db);
            self::$m_colls[$key] = $mongodb->selectCollection($coll);
        }
        return self::$m_colls[$key];
    }
}
