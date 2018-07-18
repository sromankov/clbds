<?php

namespace Classes\Routing;

use Classes\Application\Application;
use Classes\Response\BaseResponse;
use Classes\Response\Response;
use Classes\Collection\RoutesCollection;
use Classes\Request\Request;

/**
 * Class Router
 *
 * Base Router implementation
 * handles requests, controllers methods execution
 *
 * @package Classes\Routing
 */
class Router
{
    /**
     * @var RoutesCollection
     */
    protected $routes;

    /**
     * @var Application
     */
    private $app;

    /**
     * Router constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->app = $application;
        /**
         * @var $routes RoutesCollection
         */
        $this->routes = require_once __DIR__.'/../../Controllers/routes.php';
    }

     /**
     * Matches route for the request and executes linked action method
     * Response is being performed as well
     *
     * @param Request $request
     */
    public function dispatch(Request $request)
    {
        $controller = null;
        $action = null;
        $matchedRoute = $this->routes->findMatch($request);

        if ($matchedRoute) {

            $CA = $matchedRoute->getAction();
            $attributes = $this->arrangeArguments(
                $CA['controller'],
                $CA['action'],
                array_merge($matchedRoute->getParameters($request->url()), ['request' => $request])
            );

            $controller = new $CA['controller'];
            $action = $CA['action'];
            $response = call_user_func_array(array($controller,$action), $attributes);

            if (!($response instanceof BaseResponse)) {

                if (is_string($response)) {
                    $response = new Response('404. Not found', 404);

                } else {
                    $response = new Response('406. Not Acceptable', 406);
                }
            }

        } elseif ($request->isJson()) {

            $response = json(['errors' => 'Not found'])->setStatusCode(404);
        } else {
            $response = new Response('404. Not found', 404);
        }

        $response->send();
    }


    /**
     * Build parameters set for passing into actions
     * Request usage is supported (kind of Laravel style)
     *
     * @param $controllerClass
     * @param $actionName
     * @param $arguments
     * @return array
     * @throws \Exception
     */
    private function arrangeArguments($controllerClass, $actionName, $arguments)
    {
        $reflectedClass = new \ReflectionObject(new $controllerClass());

        if ($reflectedClass->hasMethod($actionName)) {

            $reflectedParameters = $reflectedClass->getMethod($actionName)->getParameters();

            return array_map(function (\ReflectionParameter $param) use ($arguments) {
                if (isset($arguments[$param->getName()])) {
                    return $arguments[$param->getName()];
                }
                if ($param->isOptional()) {
                    return $param->getDefaultValue();
                }
                return null;
            },
                $reflectedParameters
            );

        }

        throw new \Exception('Router configuration mismatch');
    }
}
