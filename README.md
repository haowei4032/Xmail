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
    $mail->connectServer($host, $port, $scheme, $timeout)
        ->auth('boss@haowei.me', 'xxxxxx')
        ->addTo('xxx@xxx.com')
        ->addTo('xxx1@xxx.com')
        ->addCc('xxx2@xxx.com')
        ->subject('这是写标题')
        ->body('这里可以写<div style="color: red; font-weight: bold;">HTML</div>')
        ->send();
} catch (\Exception $e) {
    var_dump($e->getMessage());
}
```
