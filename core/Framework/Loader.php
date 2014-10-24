<?php

/*******************************************************
 * loader.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

/**
 * loader规则
 * 
 * 根据路径查找目录model_User_Info类，放在model/User/Info.php中
 */
class Loader
{
    /**
     * 单例
     * 
     * @var Loader 
     */
    private static $_instance = null;
    
    /**
     * 是否开启使用的标示
     * 
     * @var bool
     */
    private $_registed;
    
    /**
     * 搜索路径
     * 
     * @var array 
     */
    private $_searchPaths = array();
    
    /**
     * 构造方法
     * 
     * @return void
     */
    private function __construct() 
    {
        $this->_registed = false;
        $this->addSearchPath(APP_ROOT);
    }
    
    /**
     * 获取单例
     * 
     * @return Loader
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }
    
    /**
     * 添加查找目录
     * 当发现没有后缀的未定义类的时候，在这些目录下寻找
     * @param dir 目录
     * @param desc 备注，
     */
    public function addSearchPath($dir, $desc = null)
    {
        if (null === $desc){
            $this->_searchPaths[] = $dir;
        }else{
            $this->_searchPaths[$desc] = $dir;
        }
    }
    
    /**
     * 查找文件
     * @param class 类名
     * @return 文件名
     */
    public function loadClass($class)
    {
        // 第三方类库的自动加载
        foreach ($this->_searchPaths as $dir){
            // model_User_Info => model/User/Info.php
            $file = $dir . DIRECTORY_SEPARATOR 
                    . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
            if (is_file($file)) {
                include $file;
                return true;
            }
        }
    }
    
    /**
     * Registers this instance as an autoloader.
     *
     * @param Boolean $prepend Whether to prepend the autoloader or not
     */
    public function register($prepend = false){
		if( false == $this->registered){
			$this->registered = true;
            spl_autoload_register(array($this, 'loadClass'), true, $prepend);
		}
    }
}