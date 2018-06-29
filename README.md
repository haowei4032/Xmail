# EastWood/Mail

Mail Version 1.0.0 is deprecated, please use MailClient 2.0.0 instead

[![Latest Stable Version](https://poser.pugx.org/eastwood/mail/v/stable)](https://packagist.org/packages/eastwood/mail)
[![Total Downloads](https://poser.pugx.org/eastwood/mail/downloads)](https://packagist.org/packages/eastwood/mail)
![php>=5.4](https://img.shields.io/badge/php-%3E%3D5.4-orange.svg?maxAge=2592000)
[![License](https://poser.pugx.org/eastwood/mail/license)](https://packagist.org/packages/eastwood/mail)



Installation
------------
- The minimum PHP 5.4 version required
- It works best with PHP 7

```
composer require eastwood/mail
```



Example
------------

```php
<?php

//Load Composer's autoloader
require 'vendor/autoload.php';

use EastWood\MailClient;

// case 1
$dsn = 'smtp.qq.com'; 

// case 2
$dsn = 'tcp://smtp.qq.com:25';

// case 3
$dsn = 'ssl://smtp.qq.com';

// case 4
$dsn = 'ssl://smtp.qq.com:465';

$mail = new MailClient($dsn = 'ssl://smtp.qq.com', $user = '123456@qq.com', $password = '843390444', $timeout = 5);
$mail->to('654321@qq.com'); 
$mail->cc('test1@qq.com');
$mail->bcc('test2@qq.com');
$mail->reply('root@localhost');
$mail->attachment('file:////tmp/1.txt');
$mail->attachment('test.tar.gz', fopen('file:////tmp/test.tar.gz', 'rb'));
$mail->subject('test');
$mail->body('this is test <b>HTML</b>');
$mail->send(); // return bool

var_dump($mail->getDebugTrace());


```