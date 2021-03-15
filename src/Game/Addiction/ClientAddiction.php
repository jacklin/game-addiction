<?php

namespace Game;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
/**
 * 
 */
class ClientAddiction 
{
	public $cipher = 'aes-128-gcm'; //密码学方式。openssl_get_cipher_methods() 可获取有效密码方式列表。 
	public $iv = ""; //非 NULL 的初始化向量
	public $appId; //由系统发放 账号内查看
	public $headers;
	public $uri = "https://api.wlc.nppa.gov.cn" ;
	public $timeout = 30; //请示超时
	public $allowRedirects = false; //是否支持302|301跳转
	public $headers = []; // 默认http头部信息
	private $httpclient; //http请求客户端
	private $response;//http响应数据
	/**
	 * 构造方法
	 * BaZhang Platform
	 * @Author   Jacklin@shouyiren.net
	 * @DateTime 2021-03-05T18:07:22+0800
	 * @param    string                   $appId     由系统发放
	 * @param    string                   $bizId     游戏备案识别码一致
	 * @param    string                   $key       数据加密码
	 * @param    string                   $secretKey [description]
	 */
	public function __construct(string $appId, string $bizId, string $secretKey)
    {
        $this->appId = $appId;
        $this->secretKey = $secretKey;
        
        $this->setHeaders($appId, $bizId);
        $this->aesbase64 = new AesBase64($secretKey, $this->cipher, $this->iv);
        $this->sign = new Sign($secretKey);
    }
    /**
     * common request headers
     *
     * @param string $appId
     * @param string $bizId
     */
    private function setHeaders(string $appId, string $bizId)
    {
        $this->headers = array_merge([
            'appId' => $appId,
            'bizId' => $bizId,
            'timestamps' => (int)(microtime(true)*1000)
        ]);
    }
    /**
     * 创建请求客户端
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-05T16:19:59+0800
     * @return   [type]                   [description]
     */
    private function createClient(){
    	$config = [
    		'base_uri'        =>  $this->uri,
    		'timeout'         => $this->timeout,
    		'allow_redirects' => $this->allowRedirects,
    	];
    	return new Client($config);
    }
    /**
     * 获取http请求客户端
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-05T16:42:51+0800
     * @return   [type]                   [description]
     */
    public function getClient(){
    	if (self::$httpclient instanceof Client) {
    		return self::$httpclient;
    	}else{
    		self::$client = $this->createClient();
    		return self::$client;
    	}
    }
    /**
     * 实名认证接口
     *
     * @param string $ai     本次实名认证行为在游戏内部对应的唯一
							标识，该标识将作为实名认证结果查询的
							唯一依据
							备注：不同企业的游戏内部成员标识有不
							同的字段长度，对于超过 32 位的建议使用
							哈希算法压缩，不足 32 位的建议按企业自
							定规则补齐
     * @param string $name 游戏用户姓名（实名信息）
     * @param string $idNum 游戏用户身份证号码（实名信息）
     * @param string $apiPath  接口的PATH
     * @return string
     * @throws AESException
     * @throws GuzzleException
     */
    public function check(string $ai, string $name, string $idNum, string $apiPath = '', $header=['Content-Type' => 'application/json; charset=utf-8'])
    {
        $apiPath = $apiPath ?: '/idcard/authentication/check';
        $query = $this->doQuery($apiPath);
        $body = $this->doFormat(['ai'=>$ai, 'name'=>$name, 'idNum'=>$idNum]);
        $sign = $this->doSign($body,$query);
    	$header = array_merge(['sign' => $sign],$header);
        return $this->doRequest('POST', $apiPath, $header, $body);
    }
    /**
     * 实名认证结果查询
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-05T18:30:38+0800
     * @param string $ai     本次实名认证行为在游戏内部对应的唯一
							标识，该标识将作为实名认证结果查询的
							唯一依据
							备注：不同企业的游戏内部成员标识有不
							同的字段长度，对于超过 32 位的建议使用
							哈希算法压缩，不足 32 位的建议按企业自
							定规则补齐
	 * @param string $apiPath  接口的PATH
     * @param    array                    $header [description]
     * @return   [type]                           [description]
     */
    public function query(string $ai, string $apiPath = '', $header=['Content-Type' => 'application/json; charset=utf-8'])
    {
	    $apiPath = $apiPath ?: '/idcard/authentication/query';
	    $query = $this->doQuery($apiPath);
	    $body = $this->doFormat(['ai'=>$ai]);
	    $sign = $this->doSign($body,$query);
		$header = array_merge(['sign' => $sign],$header);
	    return $this->doRequest('GET', $apiPath, $header, $body);
    }
    /**
     *  游戏用户行为数据上报
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-05T18:35:08+0800
     * @param    [type]                   $data    数据
     * @param    string                   $apiPath 接口的PATH
     * @param    array                    $header  [description]
     * @return   [type]                            [description]
     */
    public function report($data, string $apiPath = '', $header=['Content-Type' => 'application/json; charset=utf-8'])
    {
    	$apiPath = $apiPath ?: '/behavior/collection/loginout';
    	$collections = [];
    	foreach($data as $key => $value) {
    	    $tmp = [];
    	    $tmp['no'] = $key+1;
    	    $tmp['si'] = $d['si'] ?? md5($d['pi']);
    	    $tmp['bt'] = $d['bt'];
    	    $tmp['ot'] = $d['ot'] ?? time();
    	    $tmp['ct'] = $d['ct'];
    	    $tmp['di'] = $d['di'] ?? "";
    	    $tmp['pi'] = $d['pi'] ?? "";
    	    $collections['collections'][] = $tmp;
    	}
	    $query = $this->doQuery($apiPath);
	    $body = $this->doFormat($collections);
	    $sign = $this->doSign($body,$query);
		$header = array_merge(['sign' => $sign],$header);
    	return $this->doRequest('POST', $apiPath, $header, $body);
    }
    /**
     * 
     * 实名认证测试接口
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-05T18:40:46+0800
     * @param    string                   $apiPath  [description]
     * @param    string                   $ai       [description]
     * @param    string                   $name     [description]
     * @param    string                   $idNum    [description]
     * @param    string                   $testCode 测试码
     * @return   [type]                             [description]
     */
    public function testCheck(string $ai, string $name, string $idNum, string $testCode, string $apiPath = '')
    {
    	$apiPath = $apiPath ?: '/test/authentication/check';
        $apiPath = $apiPath. "/" .$testCode;
        return $this->check($ai, $name, $idNum, $apiPath);
    }
    /**
     * 实名认证测试查询接口
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-10T17:54:52+0800
     * @param    string                   $ai       [description]
     * @param    string                   $testCode [description]
     * @return   [type]                             [description]
     */
    public function testQuery(string $ai, string $testCode)
	{
		$apiPath = $apiPath ?: '/test/authentication/query';
		$apiPath = $apiPath. "/" .$testCode;
		return $this->query($ai, $apiPath);
	}
	/**
	 * 用户行为数据上报测试接口
	 * BaZhang Platform
	 * @Author   Jacklin@shouyiren.net
	 * @DateTime 2021-03-10T17:58:21+0800
	 * @param    array                    $data     [description]
	 * @param    string                   $testCode [description]
	 * @return   [type]                             [description]
	 */
	public function testReport(array $data, string $testCode)
	{
		$apiPath = $apiPath ?: '/test/collection/loginout';
		$apiPath = $apiPath. "/" .$testCode;
	    return $this->report($data, $uri);
	}
    /**
     * 获取url链接参数
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-05T18:04:32+0800
     * @param    [type]                   $apiPath [description]
     * @return   [type]                            [description]
     */
    private function doQuery($apiPath)
    {
    	$parse_api_path = explode('?', $apiPath);
    	$query = [];
        if ($parse_api_path && isset($parse_api_path[1])) {
        	parse_str($parse_api_path[1],$query);
        	return $query;
        }else{
        	return [];
        }
    }
    /**
     * 拼接请求体
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-05T18:05:03+0800
     * @param    [type]                   $body [description]
     * @return   [type]                         [description]
     */
    private function doFormat($body)
    {
    	$body = json_encode($body, JSON_UNESCAPED_UNICODE);
    	$body = '{"data":"' . $this->aesbase64->encbase64($body) . '"}';
    	return $body;
    }
    /**
     * 数据签字
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-05T18:05:37+0800
     * @param    [type]                   $body  [description]
     * @param    [type]                   $query [description]
     * @return   [type]                          [description]
     */
    private function doSign($body,$query)
    {
    	return $this->sign->generateSign($this->headers,json_decode($body,true),$query);
    }
    /**
     * 返回http响应数据
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-10T18:02:58+0800
     * @return   [type]                   [description]
     */
    private function getRespone(){
    	return $this->response;
    }
    /**
     * 请求数据
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-05T18:11:29+0800
     * @param    string                   $method 请求方法GET|POST|PUT|HEAD
     * @param    string                   $uri    请求统一资源标识符
     * @param    array                    $header 请求头
     * @param    array                    $body   请求体
     * @return   [type]                           返回响应内容
     */
    public function doRequest(string $method, string $uri, array $header = [], array $body = [])
    {
    	$options = [
       		'body' => $body,
       		'header' => array_merge($this->headers, $header)
    	];
        $this->response = $this->httpclient->request($method, $uri, $options);
        return $this->getRespone();
    }
}