<?php

/*******************************************************
 * core_Factory_Redis.php
 * 
 * @package game-f
 * @date    2014-10-27
 * @version v1.0.0
 *******************************************************/

class core_Factory_Redis extends core_Factory_Abstract
{
    // redis实例
    private static $_redises = array();
    
    /**
     * 获取redis实例
     * 
     * @param array $params
     * @return Redis
     * @throws core_Exception_LogicAlertException
     */
    public static function getRedis($params)
    {
        if (empty($params['dbkey'])) {
            throw new core_Exception_LogicAlertException("invalid getredis param" , 
                core_Config_ErrLogicCode::ERR_INVALID_PARAM);
        }

        $config = self::_getConfig($params['sec'], 'redis', $params['dbkey']);
        if (is_array($config)) {
            $_redis_key = $config['host'] . $config['port'];
            if (isset(self::$_redises[$_redis_key])) {
                return self::$_redises[$_redis_key];
            }
            $redis = new Redis();
            $redis->pconnect($config['host'], $config['port']);
            if (isset($config['serializer']) && $config['serializer'] == 'none') {
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
            } else {
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
            }
            self::$_redises[$_redis_key] = $redis;
            return $redis;
        }else{
            throw new core_Exception_LogicAlertException(
                    "redis config {$params['sec']} {$params['dbkey']} not found", 
                    core_Config_ErrLogicCode::ERR_INVALID_PARAM);
        }
    }
}
