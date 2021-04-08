<?php

namespace Game\Addiction;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use http\Env\Request;

/**
 * 
 */
class ClientAddiction 
{
    public $cipher = 'aes-128-gcm'; //密码学方式。openssl_get_cipher_methods() 可获取有效密码方式列表。 
    public $iv = ""; //非 NULL 的初始化向量
    private $ivlen = 0; //iv 长度
    public $appId; //由系统发放 账号内查看
    public $bizId; //由系统发放 账号内查看
    public $uri = "https://api.wlc.nppa.gov.cn" ;
    public $timeout = 30; //请示超时
    public $debug = false; //是否Debug 
    public $sections = array(); // 记录执行步骤
    public $allowRedirects = false; //是否支持302|301跳转
    public $headers = array(); // 默认http头部信息
    private static $httpclient; //http请求客户端
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
        $this->bizId = $bizId;
        $this->secretKey = $secretKey;
        
        $this->aesbase64 = new AesBase64($secretKey, $this->cipher, $this->iv,$this->ivlen);
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
        $this->sections[] = 'setHeaders';
        $this->headers = array_merge(array(
            'appId' => $appId,
            'bizId' => $bizId,
            'timestamps' => (int)(microtime(true)*1000)
        ));
        $this->debug(json_encode($this->headers));
    }
    public function setDebug($isDebug)
    {
        $this->debug = $isDebug;
        return $this;
    }
    public function debug($message)
    {
        $prefix = date('c') . ': ';

        if ($this->sections) {
            $prefix .= '[' . end($this->sections) . '] ';
        }
        if ($this->debug) {
            fwrite(STDERR, $prefix . $message . "\n");
        }
    }
    /**
     * 创建请求客户端
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-18T10:04:56+0800
     * @param    [type]                   $config [description]
     * @return   [type]                           [description]
     */
    private function createClient($config){
        $config = $config ? $config : array(
            'base_uri'        =>  $this->uri,
            'timeout'         => $this->timeout,
            'allow_redirects' => $this->allowRedirects,
        );
        $client =  new \Guzzle\Http\Client($config['base_uri']);
        return $client;
    }
    /**
     * 获取http请求客户端
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-18T10:04:16+0800
     * @return   [type]                           [description]
     */
    public function getClient(){
        $config  =  array(
            'base_uri'        =>  $this->uri,
            'timeout'         => $this->timeout,
            'allow_redirects' => $this->allowRedirects,
        );
        $url_arr = parse_url($this->uri);
        $key = $url_arr['host'];
        if (isset(self::$httpclient[$key]) && self::$httpclient[$key] instanceof Client) {
            return self::$httpclient[$key];
        }else{
            self::$httpclient[$key] = $this->createClient($config);
            return self::$httpclient[$key];
        }
    }
    public function setBaseUri($baseUri){
        $this->uri = $baseUri;
        return $this;
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
    public function check(string $ai, string $name, string $idNum, string $apiPath = null, $header=array('Content-Type' => 'application/json; charset=utf-8'))
    {
        $apiPath = $apiPath ?: '/idcard/authentication/check';
        $query = $this->doQuery($apiPath);
        $body = $this->doFormat(array('ai'=>$ai, 'name'=>$name, 'idNum'=>$idNum));
        $sign = $this->doSign($body,$query);
        $header = array_merge(array('sign' => $sign),$header);
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
    public function query(string $ai, string $apiPath = null, $header=array('Content-Type' => 'application/json; charset=utf-8'))
    {
        $query = array('ai'=>$ai);
        $apiPath = $apiPath ?: '/idcard/authentication/query' . "?" . http_build_query($query);
        $query = $this->doQuery($apiPath);
        $body = $this->doFormat($query);
        $sign = $this->doSign($body,$query);
        $header = array_merge(array('sign' => $sign),$header);
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
    public function report($data, string $apiPath = null, $header=array('Content-Type' => 'application/json; charset=utf-8'))
    {
        $apiPath = $apiPath ?: '/behavior/collection/loginout';
        $collections = array();
        foreach($data as $key => $value) {
            $tmp = array();
            $tmp['no'] = $key+1;
            $tmp['si'] = isset($value['si']) ? $value['si'] : md5(uniqid(mt_rand(0,1000)));
            $tmp['bt'] = $value['bt'];
            $tmp['ot'] = isset($value['ot']) ? $value['ot'] : time();
            $tmp['ct'] = $value['ct'];
            $tmp['di'] = isset($value['di']) ? $value['di'] : "";
            $tmp['pi'] = isset($value['pi']) ? $value['pi'] : "";
            $collections['collections'][] = $tmp;
        }
        $query = $this->doQuery($apiPath);
        $body = $this->doFormat($collections);
        $sign = $this->doSign($body,$query);
        $header = array_merge(array('sign' => $sign),$header);
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
    public function testCheck(string $ai, string $name, string $idNum, string $testCode, string $apiPath = null)
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
        $apiPath = '/test/authentication/query';
        $query = array('ai' => $ai);
        $apiPath = $apiPath. "/" .$testCode . "?" . http_build_query($query) ;
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
        $apiPath = '/test/collection/loginout';
        $apiPath = $apiPath . "/" . $testCode;
        return $this->report($data, $apiPath);
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
        $this->setHeaders($this->appId, $this->bizId);
        $parse_api_path = explode('?', $apiPath);
        $query = array();
        if ($parse_api_path && isset($parse_api_path[1])) {
            parse_str($parse_api_path[1],$query);
            return $query;
        }else{
            return array();
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
        $this->sections[] = 'doFormat_body_before';
        if(version_compare(PHP_VERSION,'5.4.0','<')){
            $str = json_encode($body);
            $str = preg_replace_callback("#\\\u([0-9a-f]{4})#i",function($matchs){
                return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
            },$str);
            $body = $str;
        }else{
            $body = json_encode($body, JSON_UNESCAPED_UNICODE);
        }
        $this->debug($body);
        $body = '{"data":"' . $this->aesbase64->encbase64($body) . '"}';
        $this->sections[] = 'doFormat_body_after';
        $this->debug($body);
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
    private function doSign($body,$query = array())
    {
        $this->sections[] = 'doSign';
        $sign = $this->sign->generateSign($this->headers,$body,$query);
        $this->debug($sign);
        return $sign;
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
     * @param    string                    $body   请求体
     * @return   [type]                           返回响应内容
     */
    public function doRequest(string $method, string $uri, array $header = array(), string $body = null)
    {
        $options = array(
            'body' => $body,
            'headers' => array_merge($this->headers, $header)
        );
        $this->sections[] = 'doRequest';
        $this->debug(json_encode($options));
        $request = $this->getClient()->$method($uri, $options['headers'], $options['body'], $options);
        $this->response = $request->send();
        $this->response->getBody();
        $this->response->getHeader('Content-Length');
        $this->sections[] = 'doRequest_response';
        $this->debug($this->response->json());
        return $this->response->json();
    }
    public function __destruct()
    {
        if ($this->sections) {
           unset($this->sections);
        }
    }
}
