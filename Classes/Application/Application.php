<?php

namespace Classes\Application;

use Classes\Request\Request;
use Classes\Routing\Router;

/**
 * Class Application
 *
 * Just an application core
 *
 * @package Classes\Application
 */
class Application
{
    protected $serviceProviders = [];
    protected $router;
    protected $request;

    public function __construct()
    {
        $this->router = new Router($this);
    }

    public function handle(Request $request)
    {
        $response =  $this->router->dispatch($request);
    }
}
