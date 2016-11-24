# symmetric-encrypter

* Encripts and decripts php arrays or strings.
* Safe to use encripted string in URL.
* Secured with hash from changes.

###Instalation

In composer.json:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/webreactor/symmetric-encrypter.git"
        }
    ],
    "require": {
        "reactor/symmetric-encrypter": "dev-master"
    }
}
```

###Usage


```php
<?php

include "vendor/autoload.php";

$encrypter = new \Reactor\SymmetricEncrypter\SymmetricEncrypter();

$data = array("some data1", "some data2");
$key = 'some key';
$encrypted_string = $encrypter->encrypt($data, $key);
echo "Encrypted string: $encrypted_string\n";
$recieved_data = $encrypter->decrypt($encrypted_string, $key);
print_r($recieved_data);
```


###Handle fails

`$encrypter->decrypt($encrypted_string, $key);` will return false if key is wrong or message is corrupted.

Use `$encrypter->getReport()` to get debug information string. Do not expose it to user.

Example of getReport output:

```
Failed to decrypt fMSI1CH0JKsYDPKmoLfwSL0FsSyfxDCr17WcnYkcCZhgsmZQ5qLdOfML+J0yHrYSLYD0az5jzpvzcoGZRRS+Aw==.EdaZRhxWhTU=.5a3043902d5f05c1f397d5e54d7e51b7; Message signature is invalid, Got 9ba799cb3de73a8289492d26f21189ed expected 5a3043902d5f05c1f397d5e54d7e51b7;

Failed to decrypt fMSI1CH0JKsYDPKmoLfwSL0FsSyfxDCr17WcnYkcCZhgsmZQ5qLdOfML+J0yHrYSLYD0az5jzpvzcoGZRRS+Aw==.EdaZRhxWhTU=.5a3043902d5f05c1f397d5e54d7e51b7modified; Message signature is invalid, Got 5a3043902d5f05c1f397d5e54d7e51b7 expected 5a3043902d5f05c1f397d5e54d7e51b7modified;

Failed to decrypt totalyscruedmessage; Message stucrure (expected sss.sss.sss) is broken;
```
