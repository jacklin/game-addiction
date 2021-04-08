<?php

namespace Game\Addiction;

/**
 * 
 */
class AesBase64
{
    private $key; //key
    private $cipherMethod; //密码学方式。openssl_get_cipher_methods() 可获取有效密码方式列表。 
    private $iv; //非 NULL 的初始化向量。 
    private $ivlen; //iv 长度
    private $error; //错误信息
    public function __construct($key, $cipherMethod, $iv="",$ivlen=0){
        $this->key = pack('H*', $key);//hex2bin($key)
        $this->cipherMethod = $cipherMethod;
        $this->ivlen = $ivlen >= 12 ? $ivlen : openssl_cipher_iv_length($this->cipherMethod);
        $this->iv = $iv ? $iv : openssl_random_pseudo_bytes($this->ivlen);
    }
    /**
     * 加密方法
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-05T15:31:52+0800
     * @param    string                   $plaintext 加密字符串
     * @return   mixed                              错误返回false
     */
    private function encrypt(string $plaintext){
        if (in_array($this->cipherMethod, openssl_get_cipher_methods()))
        {
            $tag = null;
            $ciphertext =  $this->iv . openssl_encrypt($plaintext, $this->cipherMethod, $this->key, OPENSSL_RAW_DATA, $this->iv, $tag) . $tag;
            return $ciphertext;
        }else{
           $this->error = $this->cipherMethod . "does not exist openssl_get_cipher_methods() of return values";
            return false;
        }
    }
    /**
     * 解密方法
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-05T15:32:49+0800
     * @param    string                   $ciphertext 待加密字符串
     * @return   mixed                               错误返回false
     */
    private function descrypt(string $ciphertext){
        if (in_array($this->cipherMethod, openssl_get_cipher_methods()))
        {
            $iv   = substr($ciphertext, 0, $this->ivlen);
            $data = substr($ciphertext, $this->ivlen, strlen($ciphertext) - 16 - $this->ivlen);
            $tag  = substr($ciphertext, strlen($ciphertext) - 16);
            $plaintext = openssl_decrypt($data, $this->cipherMethod, $this->key, OPENSSL_RAW_DATA, $iv, $tag);
            return $plaintext;
        }else{
           $this->error = $this->cipherMethod . "does not exist openssl_get_cipher_methods() of return values";
            return false;
        }
    }
    /**
     * 加密并base64
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-05T15:33:50+0800
     * @param    string                   $data [description]
     * @return   [type]                         [description]
     */
    public function encbase64(string $data) {
        return base64_encode($this->encrypt($data));
    }
    /**
     * 解密
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-05T15:34:03+0800
     * @param    [type]                   $data base64加密字符串
     * @return   [type]                         [description]
     */
    public function descbase64($data){
        return $this->descrypt(base64_decode($data));
    }
    /**
     * 获取错误
     * BaZhang Platform
     * @Author   Jacklin@shouyiren.net
     * @DateTime 2021-03-05T15:35:20+0800
     * @return   [type]                   [description]
     */
    public function getError(){
        return $this->error;
    }
}
