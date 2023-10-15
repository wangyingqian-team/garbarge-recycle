<?php

/**
 * DES 加密
 */
if (! function_exists('des_encrypt')) {
    function des_encrypt($data, $key, $iv){
        return base64_encode(openssl_encrypt($data, 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $iv));
    }
};

/**
 * DES 解密
 */
if (! function_exists('des_decrypt')) {
    function des_decrypt($data, $key, $iv){
        return openssl_decrypt(base64_decode($data), 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $iv);
    }
};

/**
 * AES 加密
 */
if (! function_exists('aes_encrypt')) {
    function aes_encrypt($data, $key, $iv){
        return base64_encode(openssl_encrypt($data, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv));
    }
};

/**
 * AES 解密
 */
if (! function_exists('aes_decrypt')) {
    function aes_decrypt($data, $key, $iv){
        return openssl_decrypt(base64_decode($data), 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv);
    }
};




