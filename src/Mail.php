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
		$this->writeLine('HELO ' . $this->host);
		var_dump( $this->readLine() );
		var_dump( $this->readLine() );
	}

	public function authorized($user, $password)
	{
		$this->writeLine('AUTH LOGIN');
		$this->readLine();

		$this->writeLine(base64_encode($user));
		$this->readLine();

		$this->writeLine(base64_encode($password));
		$this->readLine();
	}

	private function readLine()
	{
		$text = trim(fgets($this->socket));
		$this->debugTrace[] = $text;
		return $text;
	}

	private function writeLine($text)
	{
		$this->raw[] = $text;
		return fputs($this->socket, $text . PHP_EOL);
	}
}

