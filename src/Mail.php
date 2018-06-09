<?php

namespace Hsoft;

class Mail
{
	private $insecure = false;
	private $scheme = 'ssl';
	private $host;
	private $port;
	private $timeout = 5;
	private $fp;

	private $from;
	private $to;
	private $cc;
	private $bcc;
	private $attachment;

	public function __construct()
	{
	}

	public function setFrom()
	{
	}

	public function addTo()
	{}

	public function addCc()
	{}

	public function addBcc()
	{}

	public function addBody()
	{}

	public function send()
	{}
}