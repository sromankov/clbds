<?php

namespace Classes\Request;

use Classes\Server;
use Classes\ParametersSet;

/**
 * Class Request
 *
 * The simplest class working with HTTP requests
 * (methods, headers, query etc.)
 *
 * @package Classes\Request
 */
class Request
{
    /**
     * The collection of the _POST attributes
     * @var ParametersSet
     */
    public $request;

    /**
     * The collection of the _GET attributes
     * @var ParametersSet
     */
    public $query;

    /**
     * The collection of the _SERVER attributes
     * @var Server
     */
    public $server;

    /**
     * The collection of the request headers attributes
     * @var ParametersSet
     */
    public $headers;

    /**
     * Normalized request parameters collection with getters/setters etc.
     * @var ParametersSet
     */
    public $attributes;

    /**
     * The content of the request body
     * @var String
     */
    protected $content;

    /**
     * Limited set of the content-types possible properties
     * @var array
     */
    protected static $formats;

    /**
     * Http method of the request instance
     * @var
     */
    protected $method;

    public $url;

    /**
     * Creates a new instance of the Request
     * based on _GET, _POST, _SERVER  environment variables
     *
     * @param array $query
     * @param array $request
     * @param array $server
     */
    public function __construct(array $query = array(), array $request = array(), array $server = array())
    {
        $this->server     = new Server($server);
        $this->request    = new ParametersSet($request);
        $this->query      = new ParametersSet($query);
        $this->headers    = new ParametersSet($this->server->getHeaders());
        $this->attributes = new ParametersSet($this->getAttributesSet());
    }

    /**
     * Returns normalised properties for request (from GET and POST)
     * Parses request body to extract parametrs from the plain JSON
     *
     * @return array|mixed
     */
    protected function getAttributesSet()
    {
        $attributes = [];
        $queryAttributes = $this->query->all();
        $requestAttributes = $this->query->all();

        if (('' !== $content = $this->getContent()) && $this->isJson())
        {
            $requestAttributes = json_decode($content, true);
        }

        if (sizeof($queryAttributes)) {
            $attributes = $queryAttributes;
        }

        if (sizeof($requestAttributes)) {
            $attributes = $requestAttributes;
        }

        return $attributes;
    }

    protected static function initializeFormats()
    {
        static::$formats = array(
            'json' => array('application/json', 'application/x-json'),
            'form' => array('application/x-www-form-urlencoded'),
        );
    }

    /**
     * Returns unified string lexeme for request mime type line
     * e.g. application/json => json
     *
     * @param string $mimeType
     * @return int|string
     */
    public function getFormat($mimeType)
    {
        $canonicalMimeType = null;
        if (false !== $pos = strpos($mimeType, ';')) {
            $canonicalMimeType = substr($mimeType, 0, $pos);
        }

        if (null === static::$formats) {
            static::initializeFormats();
        }

        foreach (static::$formats as $format => $mimeTypes) {
            if (in_array($mimeType, (array) $mimeTypes)) {
                return $format;
            }
            if (null !== $canonicalMimeType && in_array($canonicalMimeType, (array) $mimeTypes)) {
                return $format;
            }
        }
    }

    /**
     * Check if request mime type belongs to the JSON group
     *
     * @return bool
     */
    public function isJson()
    {
        return in_array($this->getContentType(), ['json']);
    }

    /**
     * Extracts mime type string from request headers
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->getFormat($this->headers->get('Content-type', null, true));
    }

    /**
     * Extracts plain body from the request
     *
     * @return bool|string
     */
    public function getContent()
    {
        $this->content = file_get_contents('php://input');
        return $this->content;
    }

    /**
     * Returns http method for the request
     *
     * @return string
     */
    public function getMethod()
    {
        return strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
    }

    /**
     * Getter for request attributes
     *
     * @param $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if ($this !== $result = $this->query->get($key, $this)) {
            return $result;
        }

        if ($this !== $result = $this->request->get($key, $this)) {
            return $result;
        }

        return $default;
    }

    /**
     * Returns URI for the curent environment
     *
     * @return string
     */
    public function url()
    {
        return rtrim(preg_replace('/\?.*/', '', $this->server->get('REQUEST_URI')), '/');
    }
}
