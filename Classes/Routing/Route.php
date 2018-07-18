<?php

namespace Classes\Routing;

/**
 * Class Route
 *
 * Single route class
 * POST, GET methods, regexp support for route declaration
 *
 * @package Classes\Routing
 */
class Route
{
    /**
     * Url mask for matching, supports regexp constructions
     * @var string
     */
    protected $url;

    /**
     * Allowed http methods list
     * @var array
     */
    protected $methods;

    /**
     * Linked action (constructor-action pair)
     * @var array
     */
    protected $action;

    /**
     * Route constructor.
     *
     * @param $methods
     * @param $url
     * @param $action
     */
    public function __construct($methods, $url, $action)
    {
        $this->url = $url;
        $this->methods = (array) $methods;
        $this->action = $this->parseAction($action);
    }

    /**
     * Parses action line (eg. "\Controller\With\NameSpace@actionInController") to get Constructor-action pair
     *
     * @param $actionLine
     * @return array
     * @throws \Exception
     */
    protected function parseAction($actionLine)
    {
        if (is_string($actionLine) && preg_match('/@/', $actionLine)) {

            list($controllerClass, $actionName) = preg_split('/@/', $actionLine);

            if (class_exists($controllerClass)) {

                $reflectedClass = new \ReflectionObject(new $controllerClass());

                if ($reflectedClass->hasMethod($actionName)) {
                    return ['controller' => $controllerClass, 'action' => $actionName ];
                }
            }
        }

        throw new \Exception('Invalid route resource');
    }

    /**
     * Returns whether http method is allowed for this route
     *
     * @param $method
     * @return bool
     */
    public function methodIsAllowed($method)
    {
        return in_array($method, $this->methods);
    }

    /**
     * Action getter
     *
     * @return array
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Builds parameters set within URI
     *
     * @param $url
     * @return mixed
     */
    public function getParameters($url)
    {
        $regEx = $this->convertToRegex($this->url);
        preg_match($regEx, $url==''?'/': $url, $params);

        return $params;
    }

    /**
     * Returns whether requested URI matches this route
     *
     * @param $url
     * @return bool
     */
    public function match($url)
    {
        $regEx = $this->convertToRegex($this->url);
        $match = preg_match($regEx, $url==''?'/': $url, $params);

        return $match === 1;
    }

    /**
     * Returns controller-action pair
     *
     * @param $requestLine
     * @return array
     */
    public function getData($requestLine)
    {
        return  $this->action;
    }

    /**
     * Converts normal route URI description (eg. "/api/get/{id}") to regex expressions
     *
     * @param $route
     * @return string
     */
    private function convertToRegex($route) {
        return '@^' . preg_replace_callback("@{([^}]+)}@", function ($match) {
                return $this->regexParameter($match[1]);
            }, $route) . '$@';
    }

    /**
     * Returns Regex naming for parameters parts (eg. {id})
     *
     * @param $name
     * @return string
     */
    private function regexParameter($name) {
        if ($name[strlen($name) - 1] == '?') {
            $name = substr($name, 0, strlen($name) - 1);
            $end = '?';
        } else {
            $end = '';
        }
        $pattern = isset($this->parameters[$name]) ? $this->parameters[$name] : "[^/]+";
        return '(?<' . $name . '>' . $pattern . ')' . $end;
    }

    /**
     * Static method for GET route creation
     *
     * @param $url
     * @param $resource
     * @return static
     */
    public static function get($url, $resource)
    {
        return new static (['GET'], $url, $resource);
    }

    /**
     * Static method for POST route creation
     *
     * @param $url
     * @param $resource
     * @return static
     */
    public static function post($url, $resource)
    {
        return new static (['POST'], $url, $resource);
    }
}
