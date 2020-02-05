<?php
include "vendor/autoload.php";

ini_set('display_errors','1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

$tester = new Tester();
$sp = new \Reactor\SymmetricEncrypter\SymmetricEncrypter();

$key = 'secret';
$data = array(
1,2,3,
"data",
"key1" => "value1",
"key2" => 2,
"key3" => "value1",
"key4" => 2,
);


$encrypted_message = $sp->encrypt($data, $key);
$tester->assert("Encrypted string is url safe", $encrypted_message === urlencode($encrypted_message));


$decrypted = $sp->decrypt($encrypted_message, $key);
$tester->assert("Decoded message is correct for array", json_encode($decrypted) === json_encode($data));

$data = 'str';
$encrypted_message = $sp->encrypt($data, $key);
$decrypted = $sp->decrypt($encrypted_message, $key);
$tester->assert("Decoded message is correct for string", json_encode($decrypted) === json_encode($data));

$data = 123;
$encrypted_message = $sp->encrypt($data, $key);
$decrypted = $sp->decrypt($encrypted_message, $key);
$tester->assert("Decoded message is correct for number", json_encode($decrypted) === json_encode($data));

$decrypted = $sp->decrypt($encrypted_message.'modified', $key);
$tester->assert("Returns false if message is modified", $decrypted === false);

$decrypted = $sp->decrypt('totalyscruedmessage', $key);
$tester->assert("Returns false if message is made up", $decrypted === false);


$decrypted = $sp->decrypt($encrypted_message, 'wrongkey');
$tester->assert("Returns false if key is wrong", $decrypted === false);


$encrypted_message = $sp->encrypt($data, $key);
$encrypted_message2 = $sp->encrypt($data, $key);
$tester->assert("IV works", $encrypted_message != $encrypted_message2);

$sp2 = new \Reactor\SymmetricEncrypter\SymmetricEncrypter(true);
$encrypted_message = $sp2->encrypt($data, $key);
$encrypted_message2 = $sp2->encrypt($data, $key);
$tester->assert("Static IV works", $encrypted_message == $encrypted_message2);


echo "Total: {$tester->total}\n";
echo "Failed: {$tester->failed}\n";

if ($tester->failed > 0) {
    exit(1);
}

class Tester {

    public $total = 0;
    public $failed = 0;

    function assert($message, $test) {
        $this->total++;
        if ($test) {
            echo "Success - $message\n";
        } else {
            echo "Fail - $message\n";
            $this->failed++;
        }
        return $test === true;
    }

}
