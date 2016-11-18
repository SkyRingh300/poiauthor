<?php
namespace InnStudio\PoiAuthor\Apps\CryptoJsAes;

/**
* Helper library for CryptoJS AES encryption/decryption
* Allow you to use AES encryption on client side and server side vice versa
*
* @author BrainFooLong (bfldev.com)
* @link https://github.com/brainfoolong/cryptojs-aes-php
*/
class CryptoJsAes
{
    /**
    * Decrypt data from a CryptoJS json encoding string
    *
    * @param mixed $passphrase
    * @param mixed $jsonString
    * @return mixed
    */
    public static function decrypt($passphrase, $jsonString){
        $jsondata = json_decode($jsonString, true);

        if (! isset($jsondata['s']) || ! isset($jsondata['iv']) || ! isset($jsondata['ct'])) {
            return false;
        }

        try {
            $salt = hex2bin($jsondata['s']);
            $iv  = hex2bin($jsondata['iv']);
        } catch (Exception $e) { 
            return null; 
        }

        $ct = base64_decode($jsondata['ct']);
        $concatedPassphrase = $passphrase . $salt;
        $md5 = [];
        $md5[0] = md5($concatedPassphrase, true);
        $result = $md5[0];
        
        for ($i = 1; $i < 3; $i++) {
            $md5[$i] = md5($md5[$i - 1] . $concatedPassphrase, true);
            $result .= $md5[$i];
        }
        
        $key = substr($result, 0, 32);
        $data = openssl_decrypt($ct, 'aes-256-cbc', $key, true, $iv);
        
        return json_decode($data, true);
    }

    /**
    * Encrypt value to a cryptojs compatiable json encoding string
    *
    * @param mixed $passphrase
    * @param mixed $value
    * @return string
    */
    public static function encrypt($passphrase, $value)
    {
        $salt = openssl_random_pseudo_bytes(8);
        $salted = '';
        $dx = '';
        
        while (strlen($salted) < 48) {
            $dx = md5($dx.$passphrase . $salt, true);
            $salted .= $dx;
        }

        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32, 16);
        $encryptedData = openssl_encrypt(json_encode($value), 'aes-256-cbc', $key, true, $iv);
        $data = ['ct' => base64_encode($encryptedData), 'iv' => bin2hex($iv), 's' => bin2hex($salt)];

        return json_encode($data);
    }
}