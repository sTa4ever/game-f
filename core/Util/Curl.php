<?php

/*******************************************************
 * core_Util_Curl.php
 * 
 * @package game-f
 * @date    2014-10-28
 * @version v1.0.0
 *******************************************************/

class core_Util_Curl
{
    /**
     * Get user agent.
     * 
     * @return string
     */
    private static function _getUserAgent()
    {
        return sprintf('PHP%s Server (curl)', phpversion());
    }
    
	/**
	 * Do a http request by curl
	 *
	 * @param string $url 			the target uri of the http request
	 * @param array  $param     	query string for GET request or post string for POST request
	 * @param string $http_method	http request mothod, use 'POST' or 'GET'
	 * @param int $connect_timeout	timeout for connect
	 * @param int $read_timeout		timeout for reading http response
	 * @return string	http response body
	**/
	public static function curlHttpRequest($url, $param, $http_method = 'GET', 
	                            $connect_timeout = 1000, $read_timeout = 1000)
	{
	    //Create a curl instance for a http post request
		$ch = self::_createSingleMethodCurlResource($url, $param, $http_method,
		                                           $connect_timeout, $read_timeout);
		$result = curl_exec($ch);
		if ($result === false) {
		    #$curl_errno = curl_errno($ch);
		    #$curl_error = curl_error($ch);
		}
		
        $curl_info = curl_getinfo($ch);
        if ($curl_info['http_code'] != 200) {
            $result = false;
        }
		curl_close($ch);

		return $result;
	}

	/**
	 * Create a curl instance for a http post request
	 *
	 * @param string $url	    the request url
	 * @param array  $params	post/get string of the http request
	 * @param int    $connect_timeout	timeout of the connection to the server
	 * @param int    $read_timeout   	timeout of read time out
	 * @return resource  a curl instance handler
	**/
	private static function _createSingleMethodCurlResource($url, Array $params, $http_method,
														   $connect_timeout, $read_timeout)
	{
		$timeout     = $connect_timeout + $read_timeout;
        $http_method = strtoupper($http_method);
		$user_agent  = self::_getUserAgent();

		$ch = curl_init();
		$curl_opts = array( 
            CURLOPT_USERAGENT      => $user_agent,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => false,
        );
		if (defined('CURLOPT_CONNECTTIMEOUT_MS')) {
			$curl_opts[CURLOPT_CONNECTTIMEOUT_MS] = $connect_timeout;
			$curl_opts[CURLOPT_TIMEOUT_MS]        = $timeout;
		} else {
			$curl_opts[CURLOPT_CONNECTTIMEOUT] = ceil($connect_timeout / 1000);
			$curl_opts[CURLOPT_TIMEOUT]        = ceil($timeout / 1000);
		}
		if ($http_method == 'POST') {
			$curl_opts[CURLOPT_URL]        = $url;
			$curl_opts[CURLOPT_POSTFIELDS] = http_build_query($params, '', '&');
		} else {
			$delimiter = strpos($url, '?') === false ? '?' : '&';
			$curl_opts[CURLOPT_URL]  = $url . $delimiter . http_build_query($params, '', '&');
			$curl_opts[CURLOPT_POST] = false;
		}

		curl_setopt_array($ch, $curl_opts);
		return $ch;
	}
}
