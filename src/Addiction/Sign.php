<?php

namespace Game\Addiction;

/**
 * 
 */
class Sign 
{
	public $secretKey; // 访问密钥
	public function __construct($secretKey){
		$this->secretKey = $secretKey;
	}
	/**
	 * make the sign
	 *
	 * @param $body
	 * @param array $query
	 * @return string
	 */
	public function generateSign($system_params = array(), $body, $query = array())
	{
	    $request = array_merge($system_params, $query);
	    ksort($request);
	    $str_join = '';
	    foreach( $request as $key => $value) {
	        $str_join .= $key.$value;
	    }
	    $toSign = $this->secretKey . $str_join . $body;
	    $sign = hash("sha256", $toSign);
	    return $sign;
	}
}
