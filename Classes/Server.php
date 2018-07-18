<?php

namespace Classes;

/**
 * Class Server
 *
 * Just a tool to arrange server environment
 *
 * @package Classes
 */
class Server extends ParametersSet
{
    /**
     * Returns http headers
     *
     * @return array|false
     */
    public function getHeaders()
    {
        return getallheaders();
    }
}