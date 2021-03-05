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
	private function generateSign($system_params = [], $body = [], $query = [])
	{
	    $request = array_merge($system_params, $body, $query);
	    ksort($request);
	    $str_join = '';
	    foreach( $request as $key => $value) {
	        $str_join .= $key.$value;
	    }
	    $toSign = $this->secretKey . $str_join . json_encode($body);
	    $sign = hash("sha256", $toSign);
	    return $sign;
	}
}