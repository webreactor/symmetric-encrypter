<?php

namespace Reactor\SymmetricEncrypter;

class SymmetricEncrypter {

    public function __construct($static_iv = false, $cipher = 'AES-256-CBC') {
        $this->cipher = $cipher;
        $this->iv_length = \openssl_cipher_iv_length($cipher);
        $this->static_iv = $static_iv;
    }

    public function encrypt($data, $secret) {
        $encrypted = $this->encryptBin($data, $secret);
        return $this->bin2str($encrypted);
    }

    public function decrypt($message, $secret) {
        $encrypted = $this->str2bin($message);
        return $this->decryptBin($encrypted, $secret);
    }

    public function encryptBin($data, $secret) {
        $packed = $this->packData($data);
        $encrypted = $this->encryptStr($packed, $secret);
        return $this->signStr($encrypted, $secret);
    }

    public function decryptBin($message, $secret) {
        $encrypted = $this->checkSignedStr($message, $secret);

        if ($encrypted === false) {
            return false;
        }

        $packed = $this->decryptStr($encrypted, $secret);
        if ($packed === false) {
            return false;
        }
        return $this->unpackData($packed);
    }

    // ---------------------------------------------------------------------------------
    // Convert data structure to sting and back

    protected function packData($data) {
        return gzcompress(json_encode($data));
    }

    protected function unpackData($packed) {
        return json_decode(gzuncompress($packed), true);
    }

    // ---------------------------------------------------------------------------------
    // Message signature support

    protected function getSignature($string, $salt) {
        return md5($string . $salt);
    }

    protected function signStr($string, $salt) {
        return $this->getSignature($string, $salt) . $string;
    }

    protected function checkSignedStr($envelope, $salt) {
        // 32 - md5 size
        if (strlen($envelope) < 32) {
            return false;
        }
        $signature_test = substr($envelope, 0, 32);
        $message = substr($envelope, 32);

        $signature = $this->getSignature($message, $salt);
        if ($signature === $signature_test) {
            return $message;
        }
        return false;
    }

    // ---------------------------------------------------------------------------------
    // pass data over url support

    protected function bin2str($bin) {
        return strtr(base64_encode($bin), '+/=', '-._');
    }

    protected function str2bin($string) {
        return base64_decode(strtr($string, '-._', '+/='));
    }

    // ---------------------------------------------------------------------------------
    // Symmetric encrypting

    protected function encryptStr($string, $secret) {
        if ($this->static_iv !== false) {
            $iv = substr(str_repeat(md5($secret), ceil($this->iv_length/32)), 0, $this->iv_length);
        } else {
            $iv = openssl_random_pseudo_bytes($this->iv_length);
        }
        return $iv.\openssl_encrypt($string, $this->cipher, $secret, \OPENSSL_RAW_DATA, $iv);
    }

    protected function decryptStr($encrypted, $secret) {
        $iv = substr($encrypted, 0, $this->iv_length);
        $encrypted = substr($encrypted, $this->iv_length);
        return \openssl_decrypt($encrypted, $this->cipher, $secret, \OPENSSL_RAW_DATA, $iv);
    }

}
