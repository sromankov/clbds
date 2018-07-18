<?php

namespace Classes;

/**
 * Parameters collection (linear array of properties
 * and related method (getters, setters, etc.)
 * 
 * Class ParametersSet
 * @package Classes
 */
class ParametersSet
{
    /**
     * Any set of information
     * @var array
     */
    protected $parameters;

    /**
     * ParametersSet constructor.
     * 
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    /**
     * Returns all parameters
     * 
     * @return array
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * Getter
     *
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function get($name, $default = null, $ignoreCase = false)
    {

        $value = (isset($this->parameters[$name]))? $this->parameters[$name] : null;

        if (is_null($value) && $ignoreCase) {
            $lowerCased = array_change_key_case($this->parameters, CASE_LOWER);
            $value = (isset($lowerCased[strtolower( $name )]))? $lowerCased[strtolower( $name )] : null;
        }

        return !is_null($value)? $value : $default;
    }

    /**
     * Setter
     * 
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Checks property exists
     * 
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return (isset($this->parameters[$name]));
    }

    /**
     * Magic method to access parameters like $entity->name
     *
     * @param string $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->parameters)) {
            return $this->parameters[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property : ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);

        return null;
    }

    /**
     * Magic method to check parameter exists ($entity->name)
     *
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->parameters[$name]);
    }
}