<?php

namespace KumbiaPHP\Kernel;

use KumbiaPHP\Kernel\Parameters;

/**
 * Description of Response
 *
 * @author manuel
 */
class Response
{

    /**
     *
     * @var Parameters 
     */
    public $headers;

    /**
     *
     * @var type 
     */
    protected $content;

    /**
     *
     * @var type 
     */
    protected $statusCode;

    /**
     *
     * @var type 
     */
    protected $charset;
    public static $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );

    public function __construct($content = NULL, $statusCode = 200, array $headers = array())
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->statusText = isset(self::$statusTexts[$statusCode]) ? self::$statusTexts[$statusCode] : NULL;
        $this->headers = new Parameters($headers);
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusText()
    {
        return $this->statusText;
    }

    public function setStatusText($statusText)
    {
        $this->statusText = $statusText;
    }

    public function getCharset()
    {
        return $this->charset;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();
    }

    protected function sendHeaders()
    {
        if (headers_sent()) {
            return;
        }

        if (!$this->headers->has('Content-Type')) {
            $charset = $this->getCharset() ? : 'UTF-8';
            $this->headers->set('Content-Type', "text/html; charset=$charset");
        }

        //mandamos el status
        header(sprintf('HTTP/1.1 %s %s', $this->statusCode, $this->statusText));

        foreach ($this->headers->all() as $index => $value) {
            header("{$index}: {$value}", false);
        }
    }

    protected function sendContent()
    {
        echo $this->content;
        while (ob_get_level()){
            ob_end_flush();//vamos limpiando y mostrando todos los niveles de buffer creados.
        }
    }

}