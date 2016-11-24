<?php

include "vendor/autoload.php";

$sp = new \Reactor\SymmetricEncrypter\SymmetricEncrypter();

$data = array(
1,2,3,
"data",
"key1" => "value1",
"key2" => 2,
"key3" => "value1",
"key4" => 2,
);

$key = 'odfyfgyh';

echo "Excrypted string\n";
print_r($c = $sp->encrypt($data, $key));

echo "\nExpected success\n";
print_r($test = $sp->decrypt($c, $key));
assert(json_encode($test) ===  json_encode($data));
print_r($sp->getReport()."\n");

echo "\nExpected fail\n";
print_r($test = $sp->decrypt($c, 'wrongkey'));
assert($test ===  false);
print_r($sp->getReport()."\n");

echo "\nExpected fail\n";
print_r($sp->decrypt($c.'modified', $key));
assert($test ===  false);
print_r($sp->getReport()."\n");

echo "\nExpected fail\n";
print_r($sp->decrypt('totalyscruedmessage', $key));
assert($test ===  false);
print_r($sp->getReport()."\n");


