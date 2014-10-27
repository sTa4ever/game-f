<?php

/*******************************************************
 * core_Util_DI.php
 * 
 * @package game-f
 * @date    2014-10-27
 * @version v1.0.0
 * Desc: 
 *      全局的对象注册管理
 *      Dependency Injection
 *******************************************************/

class core_Util_DI
{
    protected static $_callbacks = array(); // 回调
	protected static $_vars      = array();

	/**
     * 设置注入对象
     * @param str $name
     * @param mix $value
     * @param mix $callable 闭包或者对象
	 *                  使用时初始化
	 *                  保证初始化带代码只执行一次
	 * @return 
	 */
    public static function set($name, $value, $callable) 
    {
        if (! $callable instanceof Closure) {
            self::$_vars[$name] = $value;
            return;
        }
        self::$_callbacks[$name] =  function () use ($name, $callable) {
            static $object;
            if (is_null($object)) {
				$object = $callable();
			}
			return $object;
		};
	}

    /**
     * 获取依赖注入的对象
     * 
     * @rerurn Closure
     */
    public static function get($name) 
    {
        if (isset(self::$_vars[$name])) {
            return self::$_vars[$name];
        }

		if (isset(self::$_callbacks[$name])) {
			$temp = self::$_callbacks[$name];
			return  self::$_vars[$name] = $temp instanceof Closure ? $temp() : $temp;
		}

        throw new core_Exception_LogicAlertException("invalid param di.$name not set", 
            core_Config_ErrLogicCode::ERR_INVALID_PARAM);
	}
}
