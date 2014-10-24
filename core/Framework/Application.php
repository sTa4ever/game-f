<?php

/*******************************************************
 * core_Framework_Application.php
 * 
 * @package game-f
 * @date    
 * @version v1.0.0
 *******************************************************/

abstract class core_Framework_Application
{
    /**
     * 请求参数
     *
     * @var array 
     */
    protected $_request;
    
    /**
     * 返回数据
     *
     * @var array 
     */
    protected $_response; 
    
    /**
     * 请求时间戳
     * 
     * @var int 
     */
    private $_requestTime;
    
    /**
     * 单例
     *  
     * @var core_Framework_Application 
     */
    protected static $_instance = null;
    
    /**
     * 请求处理完毕时会执行的回调
     * 
     * @var array 
     */
    protected $_shutdownCallbacks = array();
    
    /**
     * 是否打开timer
     * 
     * @var bool
     */
    protected $_timerEnable = false;

    /**
     * 构造函数
     * 
     * @return void
     */
    protected function __construct() 
    {
        $this->_requestTime = time();
    }
    
    /**
     * 获取单例
     * 
     * @return core_Framework_Application
     */
    public static function getInstance()
    {
        if (self::$_instance === NULL) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }
    
    /**
     * 设置输入请求对象
     * 
     * @return void
     */
    abstract public function setRequest();

    /**
     * 输出
     * 
     * @return mix
     */
    abstract function doResponse($ret);
    
    /**
     * 执行
     * 
     * @return mix 
     */
    public function run()
    {
        $this->_beforeAppRun(); // 开始执行之前的准备
        try {
            $this->setRequest();
            $this->startSession();
            $method = getParam($this->request, 'm');
            $ret    = $this->handleOne($method,$this->request['p']);
        }catch (core_Exception_LogicAlertException $ex){
            // 自定义的异常处理
            // 框架内部异常，如果非测试环境，需要进行warning
            $ret = array(
                's'   => "s_ex".$ex->getCode(),
                'msg' => $ex->getMessage(),
            );        
        }catch (exception $ex){
            $ret = array(
                's'  =>'s_error',
                'msg'=>$ex->getMessage(),
            );
        }

        $this->doResponse($ret);

        // 返回直接结束，其他再做处理
        fastcgi_finish_request();
        $this->_afterAppRun($ret); // 应用执行结束
	}
    
    /**
     * 开启session
     * 
     * @return void
     */
    abstract public function startSession();
    
    /**
     * 处理单个请求
     * 
     * @param m Class.method
     * @param param 请求参数内容
     */
    public function handleOne($m, $param)
    {
		$tmp = explode('.', $m);
		$class  = 'controller_' .$tmp[0];
		$method = $tmp[1];
        if (!class_exists($class)){
            throw new core_Exception_LogicAlertException("class $class not exists",
                core_Config_ErrLogicCode::CLASS_NOT_EXISTS);
        }
		$inst = new $class($param);
		$inst->run($method);
        return $inst->response;
    }

    /**
     * 注册结束后的函数回调
     * 在fastcgi_finish_request之后执行，不影响主逻辑
     */
	function registShutdownCallback($name, $callback, $obj=null) 
    {
        if (!isset($this->shutdownCallbacks[$name])) {
            $this->shutdownCallbacks[$name] = ($obj === null)  
                    ? $callback : array($obj, $callback);
        }
	}
    
    /**
     * 应用执行前预处理
     * 
     * @return mix
     */
    protected function _beforeAppRun() 
    {
        if ($this->_timerEnable) {
            core_Timer::start();
        }
    }

    /**
     * 执行完应用逻辑
     * 
     * @param array $ret
     * @return void
     */
    protected function _afterAppRun($ret) 
    {
        if (count($this->shutdownCallbacks) > 0) {
            foreach($this->shutdownCallbacks as $callback) {
                call_user_func($callback);
            }
        }
        if ($this->timer_enable) {
            core_Timer::end();
        }
    }
}
