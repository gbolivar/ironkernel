<?php
class EncryptData
{
    private static $Key = "hack100ve";

    public static function encrypt ($input) {
        $output = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(EncryptData::$Key), $input, MCRYPT_MODE_CBC, md5(md5(EncryptData::$Key))));
        return $output;
    }

    public static function decrypt ($input) {
        $output = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(EncryptData::$Key), base64_decode($input), MCRYPT_MODE_CBC, md5(md5(EncryptData::$Key))), "\0");
        return $output;
    }
}