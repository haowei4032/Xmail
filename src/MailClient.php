<?php

namespace EastWood;

/**
 * Mail - A simple email tool library
 *
 * @package EastWood
 * @license MIT
 * @author  EastWood (haowei) <boss@haowei.me>
 *
 */

class MailClient
{
    /**
     * Mail version
     * @const string
     */
    const VERSION = '2.0.0';

    /**
     * SMTP protocol newline
     */
    const CRLF = "\r\n";

    /**
     * SMTP server handler
     * @var null
     */
    private $socket = null;

    /**
     * SMTP server scheme
     * @var string
     */
    private $scheme = 'tcp';

    /**
     * SMTP server host
     * @var string
     */
    private $host = 'localhost';

    /**
     * SMTP server port
     * @var int
     */
    private $port = 25;

    /**
     * email body character
     * @var string
     */
    private $charset = 'UTF-8';

    /**
     * emial body separator
     * @var string
     */
    private $separator = '';

    /**
     * SMTP activated status
     * @var bool
     */
    private $activated = false;

    /**
     * SMTP raw array
     * @var array
     */
    private $raw = [];

    /**
     * debug trace
     * @var array
     */
    private $debugTrace = [];

    /**
     * email reply address
     * @var null
     */
    private $reply = null;

    /**
     * email sender address
     * @var null
     */
    private $from = null;

    /**
     * SMTP recipients list
     * @var array
     */
    private $rcpt = [];

    /**
     * email to list
     * @var array
     */
    private $to = [];

    /**
     * email cc list
     * @var array
     */
    private $cc = [];

    /**
     * email bcc list
     * @var array
     */
    private $bcc = [];

    /**
     * email attachment list
     * @var array
     */
    private $attachment = [];

    /**
     * email subject
     * @var null
     */
    private $subject = null;

    /**
     * email body
     * @var null
     */
    private $body = null;

    /**
     * MailClient constructor.
     */
    public function __construct()
    {
        if (func_num_args())
            call_user_func_array([$this, 'connectServer'], func_get_args());
    }

    /**
     * Connect SMTP server
     *
     * @param string $dsn
     * @param string $user
     * @param string $password
     * @param int $timeout
     * @return MailClient
     */
    public function connectServer($dsn, $user, $password, $timeout)
    {
        $parse = parse_url($dsn);
        $this->scheme = empty($parse['scheme']) ?: $parse['scheme'];
        $this->host = empty($parse['host']) ?: $parse['host'];
        $this->port = empty($parse['port']) ? ($this->scheme != 'ssl' ?: 465) : $parse['port'];
        $this->socket = @fsockopen($this->scheme . '://' . $this->host, $this->port, $errno, $errstr, $timeout);
        if ($this->socket) {
            $pass = $this->readLine(220) &&
                $this->writeLine('HELO ' . $this->host, 250);
            $this->activated = $pass ?: false;
            $this->authentication($user, $password);
        }
        return $this;
    }

    /**
     * Set email character
     *
     * @param string $charset
     * @return MailClient
     */
    public function charset($charset)
    {
        $this->charset = strtoupper($charset);
        return $this;
    }

    /**
     * Set email sender address
     *
     * @param string $from
     * @return MailClient
     */
    public function from($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Add email recipient address
     *
     * @param string $to
     * @return MailClient
     */
    public function to($to)
    {
        $this->to[] = $to;
        return $this;
    }

    /**
     * Add email cc address
     *
     * @param string $cc
     * @return MailClient
     */
    public function cc($cc)
    {
        $this->cc[] = $cc;
        return $this;
    }

    /**
     * Add email bcc address
     *
     * @param string $bcc
     * @return MailClient
     */
    public function bcc($bcc)
    {
        $this->bcc[] = $bcc;
        return $this;
    }

    /**
     * Set reply email address
     *
     * @param string $reply
     * @return MailClient
     */
    public function reply($reply)
    {
        $this->reply = $reply;
        return $this;
    }

    /**
     * Add email attachment
     *
     * @param string $path
     * @param resource|null $stream [optional]
     * @return MailClient
     */
    public function attachment($path, $stream = null)
    {
        if (is_resource($stream)) {
            $binary = '';
            fseek($stream, SEEK_SET);
            while (!feof($stream)) $binary .= fgets($stream);
            fclose($stream);
            $this->attachment[] = [
                'name' => $path,
                'body' => base64_encode($binary)
            ];
        } else {
            if (is_file($path)) {
                $this->attachment[] = [
                    'name' => basename($path),
                    'body' => base64_encode(file_get_contents($path))
                ];
            }
        }

        return $this;
    }

    /**
     * Set email subject
     *
     * @param string $subject
     * @return MailClient
     */
    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set email body
     *
     * @param string $body
     * @return MailClient
     */
    public function body($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Sending email
     *
     * @return bool
     */
    public function send()
    {
        if (!$this->activated) return $this->activated;
        $this->rcpt = array_merge($this->to, $this->cc, $this->bcc);
        $pass = $this->writeLine('MAIL FROM: <' . $this->from . '>', 250);
        if (!$pass) return false;
        foreach ($this->rcpt as $rcpt) {
            $this->writeLine('RCPT TO: <' . $rcpt . '>', 250);
            if (!$pass) return false;
        }

        $pass = $this->writeLine('DATA', 354);
        if (!$pass) return false;
        $this->writeLine('From: ' . $this->from);
        $this->writeLine('To: ' . implode(',', $this->to));
        if ($this->reply) $this->writeLine('Reply-To: ' . $this->reply);
        if ($this->cc) $this->writeLine('Cc: ' . implode(',', $this->cc));
        if ($this->bcc) $this->writeLine('Bcc: ' . implode(',', $this->bcc));
        $this->writeLine('Date: ' . date('r'));
        $this->writeLine('X-Mailer: ' . __CLASS__ . ' v' . self::VERSION);
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
        if ($this->attachment) {
            foreach ($this->attachment as $part) {
                $this->writeLine('Content-Type: application/octet-stream; name="' . $part['name'] . '"');
                $this->writeLine('Content-Transfer-Encoding: base64');
                $this->writeLine('Content-Disposition: attachment; filename="' . $part['name'] . '"');
                $this->writeLine('');
                $this->writeLine($part['body']);
                $this->writeLine('--' . $this->separator);
            }
        }
        $this->writeLine('');
        $pass = $this->writeLine('.', 250) &&
            $this->writeLine('QUIT', 221);
        return $pass;

    }

    /**
     * Get service is activated
     *
     * @return bool
     */
    public function isActivated()
    {
        return $this->activated;
    }

    /**
     * Get expected code status
     *
     * @param string $code
     * @return bool
     */
    private function readLine($code)
    {
        $line = trim(fgets($this->socket));
        $this->debugTrace[] = $line;
        return substr($line, 0, 3) == $code;

    }

    /**
     * Write binary
     *
     * @param string $text
     * @param int $code
     * @return bool|int
     */
    private function writeLine($text, $code = 0)
    {
        $this->raw[] = $text;
        $length = fputs($this->socket, $text . self::CRLF);
        if ($code > 0) return $this->readLine($code);
        return $length;
    }

    /**
     * Identity authentication
     *
     * @param string $user
     * @param string $password
     * @return void
     */
    private function authentication($user, $password)
    {
        if (!$this->activated) return;
        $this->from = $user;
        $this->separator = '----=_Part_' . md5($user . time()) . uniqid();
        $pass = $this->writeLine('AUTH LOGIN', 334) &&
            $this->writeLine(base64_encode($user), 334) &&
            $this->writeLine(base64_encode($password), 235);
        $this->activated = $pass ?: false;
    }

    /**
     * Get debug array
     *
     * @return array
     */
    public function getDebugTrace()
    {
        return array_merge(['raw' => $this->raw, 'debugTrace' => $this->debugTrace]);
    }

    /**
     * MailClient destructor.
     */
    public function __destruct()
    {
        if ($this->socket) fclose($this->socket);
    }
}