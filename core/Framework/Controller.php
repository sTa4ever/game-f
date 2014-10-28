<?php

/*******************************************************
 * core_Framework_Controller.php
 * 
 * @package game-f
 * @date    2014-10-28
 * @version v1.0.0
 *******************************************************/

class core_Framework_Controller
{
    /**
     * 返回数据
     *
     * @var array 
     */
    public  $response;
    
    /**
     * 请求数据
     * 
     * @var array
     */
    public  $request;

    /**
     * 构造函数
     * 
     * @param array $param
     * @return void
     */
	public function __construct($param)
    {
		$this->request = $param;
	}
	
    /**
     * 执行
     * 
     * @param string $method 请求方法
     * @return void
     */
	public function run($method)
    {
        try {
            $this->$method();
        } catch (core_Exception_NotOkException $ex) {
            // 正常的游戏逻辑非OK
            $this->response = array(
                's'   => $ex->getCode(),
                'msg' => $ex->getMessage(),
                'e'   => $ex->getErrInfo(),
            );
        }
        $this->checkResponse();
	}

    /**
     * 错误返回值的组装
     * 
     * @params int    $code 错误代号
     * @params string $msg  错误信息
     * @return void
     */
    public function errResponse($code, $msg, $err = array()) 
    {
        $this->response['s']   = $code;
        $this->response['msg'] = $msg;
        $this->response['e']   = $err;
    }

    /**
     * 返回值检查、处理
     * 
     * @return void
     */
    public function checkResponse() 
    {
    }
}
