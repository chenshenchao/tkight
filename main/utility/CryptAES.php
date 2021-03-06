<?php

namespace tkight\utility;

use tkight\exception\ParamException;

/**
 * AES 加密。
 * 
 */
class CryptAES
{
    private $key;
    private $method;
    private $ivlength;

    public function __construct($key, $method = 'aes-256-cbc')
    {
        $this->key = $key;
        $this->method = $method;
        $this->ivlength = openssl_cipher_iv_length($method);
    }

    /**
     * 加密。
     *
     * @param mixed $data
     * @return string
     */
    public function encrypt($data)
    {
        $iv = openssl_random_pseudo_bytes($this->ivlength);
        $text = openssl_encrypt(
            json_encode($data, JSON_UNESCAPED_UNICODE),
            $this->method,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );
        $hmac = hash_hmac('sha256', $text, $this->key, true);
        return base64_encode($iv . $hmac . $text);
    }

    /**
     * 解密。
     *
     * @param string $data
     * @return mixed
     */
    public function decrypt($data)
    {
        $raw = base64_decode($data);
        $iv = substr($raw, 0, $this->ivlength);
        $hmac = substr($raw, $this->ivlength, 32);
        $text = substr($raw, $this->ivlength + 32);
        $result = openssl_decrypt(
            $text,
            $this->method,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );
        $calcmac  = hash_hmac('sha256', $text, $this->key, true);
        if ($hmac != $calcmac) {
            throw new ParamException("哈希校验有误");
        }
        return json_decode($result, true);
    }
}
