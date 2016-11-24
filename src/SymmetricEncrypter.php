<?php
namespace Reactor\SymmetricEncrypter;

class SymmetricEncrypter {

    protected $report = '';

    public function encrypt($data, $secret) {
        $this->resetReport();

        $packed = $this->packData($data);

        $encrypted = $this->encryptStr($packed, $secret);

        $signature = $this->getSignature($encrypted, $secret);

        return $encrypted . '.' . $signature;
    }

    public function decrypt($message, $secret) {
        $this->resetReport();
        $message_data = explode('.', $message);
        if (count($message_data) != 3) {
            $this->addReport("Failed to decrypt {$message}");
            $this->addReport("Message stucrure (expected sss.sss.sss) is broken");
            return false;
        }

        $encrypted = $message_data[0].'.'.$message_data[1];
        $signature_test = $message_data[2];

        $signature = $this->getSignature($encrypted, $secret);
        if ($signature_test !== $signature) {
            $this->addReport("Failed to decrypt {$message}");
            $this->addReport("Message signature is invalid, Got {$signature} expected {$signature_test}");
            return false;
        }

        $packed = $this->decryptStr($encrypted, $secret);
        if ($packed === false) {
            $this->addReport("Failed to decrypt {$message}");
            $this->addReport("Key or encrypted message stucture (expected ss.ss) is wrong");
            return false;
        }
        return $this->unpackData($packed);
    }

    protected function resetReport() {
        $this->report = '';
    }

    protected function addReport($str) {
        $this->report .= $str.'; ';
    }

    public function getReport() {
        return rtrim($this->report);
    }

    protected function packData($data) {
        return gzcompress(json_encode($data));
    }

    protected function unpackData($packed) {
        return json_decode(gzuncompress($packed), true);
    }

    protected function getSignature($string, $salt) {
        return md5($string.$salt);
    }

    protected function encryptStr($string, $secret) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        $encrypted_str = mcrypt_encrypt(MCRYPT_BLOWFISH, $secret, $string, MCRYPT_MODE_CBC, $iv);
        return base64_encode($encrypted_str) . '.' . base64_encode($iv);
    }

    protected function decryptStr($encrypted, $secret) {
        $encrypted = explode('.', $encrypted);
        if (count($encrypted) != 2) {
            return false;
        }
        $encrypted_str = base64_decode($encrypted[0]);
        $iv = base64_decode($encrypted[1]);

        return mcrypt_decrypt(MCRYPT_BLOWFISH, $secret, $encrypted_str, MCRYPT_MODE_CBC, $iv);
    }

}

