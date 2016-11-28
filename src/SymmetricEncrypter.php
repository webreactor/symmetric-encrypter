<?php

namespace Reactor\SymmetricEncrypter;

class SymmetricEncrypter {

    protected $report = '';

    public function encrypt($data, $secret) {
        $this->resetReport();

        $packed = $this->packData($data);

        $encrypted = $this->encryptStr($packed, $secret);

        return $this->signStr($encrypted, $secret);
    }

    public function decrypt($message, $secret) {
        $this->resetReport();

        $encrypted = $this->checkSignedStr($message, $secret);

        if ($encrypted === false) {
            $this->addReport("Message signature is invalid");
            return false;
        }

        $packed = $this->decryptStr($encrypted, $secret);
        if ($packed === false) {
            $this->addReport("Failed: encrypted message is modified");
            return false;
        }
        return $this->unpackData($packed);
    }

    // ---------------------------------------------------------------------------------
    // Debuging, issue investigating

    protected function resetReport() {
        $this->report = "";
    }

    protected function addReport($message) {
        $this->report .= "{$message}; ";
    }

    public function getReport() {
        return rtrim($this->report);
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
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        $encrypted_str = mcrypt_encrypt(MCRYPT_BLOWFISH, $secret, $string, MCRYPT_MODE_CBC, $iv);
        return $this->bin2str($iv.$encrypted_str);
    }

    protected function decryptStr($encrypted, $secret) {
        $encrypted = $this->str2bin($encrypted);

        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
        if (strlen($encrypted) < $iv_size) {
            return false;
        }

        $iv = substr($encrypted, 0, $iv_size);
        $encrypted_str = substr($encrypted, $iv_size);
        return mcrypt_decrypt(MCRYPT_BLOWFISH, $secret, $encrypted_str, MCRYPT_MODE_CBC, $iv);
    }

}
