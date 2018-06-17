# EastWood/Mail

composer require eastwood/mail

```php
<?php
// Import EastWood\Mail classes into the global namespace
// These must be at the top of your script, not inside a function
use EastWood\Mail;

//Load Composer's autoloader
require 'vendor/autoload.php';

try {
    $mail = new Mail([
        'host' => 'smtp.exmail.qq.com',
        'port' => 465,
        'scheme' => 'ssl',
        'charset' => 'utf-8'
    ]);
    
    /**
     * In the constructor, you can pass in the following parameters
     * 
     * string $host [optional]
     * int $port [optional]
     * string $scheme [optional]
     * int $timeout [optional]
     */
    
    $stream = fopen('file:////tmp/example.zip', 'rb');
    $mail->connectServer($host = 'localhost', $port = 25, $scheme = 'tcp', $timeout = 5)
        ->auth('boss@haowei.me', 'password')
        ->addTo('xxx1@xxx.com')
        ->addTo('xxx2@xxx.com')
        ->addCc('xxx3@xxx.com')
        ->addAttachment('file:////tmp/1.txt') // support protocol ( file:/// , http:// )
        ->addAttachment('123.zip', $stream) // large file, please use file stream
        ->subject('这是写标题')
        ->body('这里可以写<div style="color: red; font-weight: bold;">HTML</div>')
        ->send();
    if (is_resource($stream)) fclose($stream);
} catch (\Exception $e) {
    var_dump($e->getMessage());
}
```
