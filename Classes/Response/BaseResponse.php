<?php

namespace Classes\Response;

use Classes\ParametersSet;

/**
 * Class BaseResponse
 *
 * Response handling class
 *
 * @package Classes\Response
 */
class BaseResponse
{
    /**
     *  Some basic set of response codes
     */
    const HTTP_OK = 200;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_METHOD_NOT_ALLOWED = 405;

    /**
     * Response headers collection
     * @var ParametersSet
     */
    public $headers;

    /**
     * Response body, can be modified for nested classes
     * @var string
     */
    protected $content;

    /**
     * Just http protocol version for appropriate header line
     * @var string
     */
    protected $version;

    /**
     * Response code (e.g. 200, 404 etc.)
     * @var integer
     */
    protected $statusCode;

    /**
     * Response code description
     * @var string
     */
    protected $statusText;


    /**
     * BaseResponse constructor. Creates base header lines, status, content if set
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->headers = new ParametersSet($headers);
        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setProtocolVersion('1.0');
        if (!$this->headers->has('Date')) {
            $this->setDate(\DateTime::createFromFormat('U', time(), new \DateTimeZone('UTC')));
        }
    }

    /**
     * Status code setter
     *
     * @param integer $status
     * @return $this
     */
    public function setStatusCode($status)
    {
        $this->statusCode = (string)$status;
        return $this;
    }

    /**
     * Response body setter
     *
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = (string)$content;
        return $this;
    }

    /**
     * includes current date into header line
     *
     * @param \DateTime $date
     * @return $this
     */
    public function setDate(\DateTime $date)
    {
        $date->setTimezone(new \DateTimeZone('UTC'));
        $this->headers->set('Date', $date->format('D, d M Y H:i:s') . ' GMT');

        return $this;
    }

    /**
     * Response body getter
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Protocol version setter
     *
     * @param string $version
     * @return $this
     */
    public function setProtocolVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Protocol version getter
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }

    /**
     * Sends full request with headers and body to the client and makes exit
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();

        die;
    }

    /**
     * Sends headers part of the request
     *
     * @return $this
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            return $this;
        }

        foreach ($this->headers->all() as $name => $value) {
            header($name . ': ' . $value, false);
        }

        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);

        return $this;
    }

    /**
     * Sends body part of the request
     * @return $this
     */
    public function sendContent()
    {
        echo $this->content;
        return $this;
    }
}
