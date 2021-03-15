<?php

namespace Game;

/**
 * 
 */
class AesBase64
{
    private $key; //key
    private $cipherMethod; //密码学方式。openssl_get_cipher_methods() 可获取有效密码方式列表。 
    private $iv; //非 NULL 的初始化向量
    private $error; //错误信息
    public function __construct($key, $cipherMethod, $iv){
        $this->key = $key;
        $this->cipherMethod = $cipherMethod;
        $this->iv = $iv
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
            $ciphertext = openssl_encrypt($plaintext, $this->cipherMethod, $this->key, $options=0, $this->iv);
            return $ciphertext;
        }else{
           $this->error = $this->cipherMethod . "does not exist openssl_get_cipher_methods() of return values"
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
            $plaintext = openssl_decrypt($ciphertext, $this->cipherMethod, $this->key, $options=0, $this->iv);
            return $plaintext;
        }else{
           $this->error = $this->cipherMethod . "does not exist openssl_get_cipher_methods() of return values"
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
        return $this->descrypt(base64_encode($data));
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