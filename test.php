<?php


require_once __DIR__ . '/vendor/autoload.php';

use Hsoft\Mail;



$mail = new Mail([
	'host' => 'smtp.exmail.qq.com',
	'port' => 465,
	'scheme' => 'ssl'
]);

$mail->connectServer();
$mail->authorized('boss@haowei.me', 'Blank2017');

exit;



$ms = round(microtime(true) * 1000);
$gmdate = gmdate(DATE_RFC822);

$host = 'smtp.exmail.qq.com';
$from = 'boss@haowei.me';
$password = 'Blank2017';

$to = '843390444@qq.com';
$subject = '测试发送邮件';
$body = 'asdfasdf';

$separator = '__=Part_'.md5(microtime()).'=__';

$fp = fsockopen('ssl://'. $host, '465', $errno, $errstr, $timeout = 5);
var_dump($fp);

fputs($fp, 'HELO '.$host . PHP_EOL);
var_dump(trim(fgets($fp)));
var_dump(trim(fgets($fp)));

fputs($fp, 'AUTH LOGIN' . PHP_EOL);
var_dump(trim(fgets($fp)));

fputs($fp, base64_encode($from) . PHP_EOL);
var_dump(trim(fgets($fp)));

fputs($fp, base64_encode($password) . PHP_EOL);
var_dump(trim(fgets($fp)));

fputs($fp, 'MAIL FROM:<'.$from.'>' . PHP_EOL);
var_dump(trim(fgets($fp)));

fputs($fp, 'RCPT TO:<'.$to.'>' . PHP_EOL);
var_dump(trim(fgets($fp)));

fputs($fp, 'DATA' . PHP_EOL);
var_dump(trim(fgets($fp)));

fputs($fp, 'From: <'.$from.'>' . PHP_EOL);
fputs($fp, 'To: <'.$to.'>' . PHP_EOL);
fputs($fp, 'Date: ' . $gmdate . PHP_EOL);
fputs($fp, 'MIME-Version: 1.0' . PHP_EOL);
fputs($fp, 'Content-Type: multipart/mixed;' . PHP_EOL);
fputs($fp, "\t".'boundary="'.$separator.'"' . PHP_EOL);
fputs($fp, 'Subject: =?UTF-8?B?'.base64_encode($subject).'?=' . PHP_EOL . PHP_EOL);
fputs($fp, '--'. $separator . PHP_EOL);
fputs($fp, 'Content-Type: text/html;charset=UTF-8' . PHP_EOL);
fputs($fp, 'Content-Transfer-Encoding: base64' . PHP_EOL . PHP_EOL);
fputs($fp, base64_encode($body) . PHP_EOL . PHP_EOL);
fputs($fp, '--'. $separator . PHP_EOL . PHP_EOL);
fputs($fp, '.' . PHP_EOL);
var_dump(fgets($fp));
fputs($fp, 'QUIT' . PHP_EOL);
var_dump(fgets($fp));
var_dump(round(microtime(true) * 1000) - $ms);