# symmetric-encrypter

* Encripts and decripts php arrays or strings.
* Safe to use encripted string in URL.
* Secured with hash from changes.

Basicaly it's a wrapper on openssl_encrypt.
Default algorythm is `AES-256-CBC`

Tested 
php 5.5 Ubuntu 14.04.4 LTS
php 7.3 Ubuntu 16.04.6 LTS

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

if ($recieved_data === false) {
    echo "Message was modified or password is incorrect";
} else {
    print_r($recieved_data);
}

```


###Testing
```bash

git clone git@github.com:webreactor/symmetric-encrypter.git
cd symmetric-encrypter
composer install
php test.php

```