<?php

namespace Hsoft;

class Mail
{

    const VERSION = '1.0.0';

    private $raw = [];
    private $debugTrace = [];
    private $socket;
    private $timeout = 5;
    private $scheme = 'tcp';

    private $separator;
    private $charset = 'UTF-8';

    private $host;
    private $port;

    private $from;
    private $to = [];
    private $cc = [];
    private $bcc = [];
    private $subject = '';
    private $body = '';


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

        $this->socket = fsockopen($this->scheme . '://' . $this->host, $this->port, $errno, $errstr, $this->timeout);
        $this->readLine(220);
        $this->writeLine('HELO ' . $this->host, 250);
        return $this;
    }

    public function authentication($user, $password)
    {
        $this->separator = '----=_Part_' . md5($user . time()) . uniqid();
        $this->setFrom($user);
        $this->writeLine('AUTH LOGIN', 334);
        $this->writeLine(base64_encode($this->from), 334);
        $this->writeLine(base64_encode($password), 235);
        return $this;
    }

    public function auth()
    {
        return call_user_func_array([$this, 'authentication'], func_get_args());
    }

    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    public function addTo($to)
    {
        $this->to[] = $to;
        return $this;
    }

    public function addCc($cc)
    {
        $this->cc[] = $cc;
        return $this;
    }

    public function addBcc($bcc)
    {
        $this->bcc[] = $bcc;
        return $this;
    }

    public function addAttachment($name, $body)
    {
        return $this;
    }

    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function body($body)
    {
        $this->body = $body;
        return $this;
    }

    public function send()
    {
        $to = implode(',', array_map(function ($value) {
            return '<' . $value . '>';
        }, $this->to));

        $cc = implode(',', array_map(function ($value) {
            return '<' . $value . '>';
        }, $this->cc));

        $bcc = implode(',', array_map(function ($value) {
            return '<' . $value . '>';
        }, $this->bcc));

        $this->writeLine('MAIL FROM: <' . $this->from . '>', 250);
        $this->writeLine('RCPT TO: ' . $to, 250);
        $this->writeLine('DATA', 354);
        $this->writeLine('From: <' . $this->from . '>');
        $this->writeLine('To: ' . $to);
        if ($cc) $this->writeLine('Cc: ' . $cc);
        if ($bcc) $this->writeLine('Bcc: ' . $bcc);
        $this->writeLine('Date: ' . gmdate(DATE_RFC1123 ));
        $this->writeLine('Subject: =?' . $this->charset . '?B?' . base64_encode($this->subject) . '?=');
        $this->writeLine('Content-Type: multipart/mixed;');
        $this->writeLine("\t" . 'boundary="' . $this->separator . '"');
        $this->writeLine('MIME-Version: 1.0');
        $this->writeLine('');
        $this->writeLine('--' . $this->separator);
        $this->writeLine('Content-Type: text/html;charset=' . $this->charset);
        $this->writeLine('Content-Transfer-Encoding: base64');
        $this->writeLine('');
        $this->writeLine(base64_encode($this->body));
        $this->writeLine('--' . $this->separator);
        $this->writeLine(PHP_EOL . '.', 250);
        $this->writeLine('QUIT', 221);
        echo implode(PHP_EOL, $this->raw);
        return true;
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

