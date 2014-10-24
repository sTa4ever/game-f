<?php

/*******************************************************
 * App.php
 * 
 * @package game-f
 * @date    2014-10-24
 * @version v1.0.0
 *******************************************************/

class App extends core_Framework_Application
{
    /**
     * 不需要进行校验的方法
     * 
     * @var array
     */
    private $_skipCheckMethod = array();
    
    /**
     * 不需要进行校验的controller
     * 
     * @var array
     */
	private $_skipCheckController = array();

    /**
     * 运行模式
     * 
     * @var string
     */
    private $_runMode = 'api';
    
    /**
     * 构造方法
     * 
     * @return void
     */
    public function __construct() 
    {
        parent::__construct();
    }
    
    /**
     * 开始执行
     * 
     * @param string $mod 模式
     * @return array
     */
    public function run($mod)
    {
        $this->_runMode = empty($mod) ? 'api' : $mod;
        $this->_registerSkipCheck();
        
        parent::run();
    }
    
    /**
     * 忽略验证的一些接口
     * 
     * @return void
     */
    private function _registerSkipCheck()
    {
        // 下面为示例
        // 一些跳过验证的方法
        $this->_skipCheckMethod['User.get'] = 1;
        
        if ($this->_runMode == 'gm') {
            $this->_skipCheckController['GM_User'] = 1;
        }
    }
    
    /**
     * set request
     * 
     * @return void
     */
    public function setRequest() 
    {
        if ($this->run_mode == 'api') {
            // api为游戏接口请求，从加密rawdata中获取数据
            $this->request = new core_Request_Json(util_Security::decryptRawData());
        } elseif($this->run_mode == 'cli') {
            $this->request = new core_Request_Cli();
        } else {
            $this->request = new core_Request_Http();
        }
        $this->checkBaseParam();
    }
    
    /**
     * 处理输出内容
     * 
     * @return void
     */
    public function doResponse($ret) 
    {
        if (is_array($ret)){
            // 正常的返回值
            $ret['_t'] = time();
            $response = json_encode($ret);
        }else{
            // 输出页面
            $response = $ret;
        }
        if ($this->run_mode == 'api') {
            echo core_Util_Secuity::encrypt(core_Util_Gzip::compress($response));
        } else {
            echo $response;
        }
    }
    
    /**
     * 开启session
     * 此处session用来记录登录信息等
     * 
     * @return void
     */
    public function startSession() 
    {
    }
    
    /**
     * 检查参数校验
     *
     * @return arr|false false表示通过检查，否则出错信息保存在数组中返回
     */
	public function checkSig()
    {
		$method = $this->request['m'];
        if($this->skip_check_method[$method]) {
            return false;
        }

        $method_info =explode('.', $method);
        $controller = $method_info[0];
        if($this->skip_check_controller[$controller]) {
            return false;
        }

        $param = $this->request['p'];
		if(!$param['sign']){
		    return array(
                's'   => core_Config_ErrLogicCode::ERR_SIGN_INVALID, 
                'msg' =>'no signature',
            );
        }

		if(!core_Util_Sign::checkSign($method, $param)){
			return array(
                's'   => core_Config_ErrLogicCode::ERR_SIGN_INVALID,
                'msg' => 'invalid sign['.$param['sign'].']');
		}
        return false;
	}
}
