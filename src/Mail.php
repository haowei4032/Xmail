<?php

namespace Hsoft;

class Mail
{

	private $raw = [];
	private $debugTrace = [];
	private $socket;
	private $timeout = 5;
	private $scheme = 'tcp';

	private $charset = 'UTF-8';

	private $host;
	private $port;

	private $from = 'boss@haowei.me';
	private $to = '843390444@qq.com';
	private $cc;
	private $bcc;
	private $subject;
	private $body;


	public function __construct(array $argv = null)
	{
		if ($argv) {
			foreach ($argv as $property => $value) {
				if (property_exists($this, $property)) {
					$this->$property = $value;
				}
			}
		}
	}

	public function connectServer($host = null, $port = null, $scheme = null, $timeout = null)
	{
		if ($host) $this->host = $host;
		if ($port) $this->port = $port;
		if ($scheme) $this->scheme = $scheme;
		if ($timeout) $this->timeout = $timeout;

		$this->socket = fsockopen($this->scheme .'://'. $this->host, $this->port, $errno, $errstr, $this->timeout);
		$this->readLine(220);
		$this->writeLine('HELO '. $this->host, 250);
		$this->writeLine('AUTH LOGIN', 334);
		$this->writeLine(base64_encode('boss@haowei.me'), 334);
		$this->writeLine(base64_encode('Blank2017'), 235);
		$this->writeLine('MAIL FROM: '.$this->from, 250);
		$this->writeLine('RCPT TO: '.$this->to, 250);
		$this->writeLine('DATA', 354);
		$this->writeLine('From: <'.$this->from.'>');
		$this->writeLine('To: <'.$this->to.'>');
		$this->writeLine('Date: '. gmdate(DATE_RFC1123));

		var_dump($this->debugTrace);
	}

	private function readLine($code)
	{
		$line = trim(fgets($this->socket));
		$this->debugTrace[] = $line;
		if (($resCode = substr($line, 0, 3)) != $code) {
			return $resCode;
		}
	}

	private function writeLine($text, $code = 0)
	{
		$this->raw[] = $text;
		$length = fputs($this->socket, $text . PHP_EOL);
		if ($code > 0) $this->readLine($code);
		return $length;
	}
}

