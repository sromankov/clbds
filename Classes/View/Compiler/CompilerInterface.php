<?php

namespace Classes\View\Compiler;

/**
 * Interface CompilerInterface
 *
 * May be others will be in use, Dwoo is just an example
 *
 * @package Classes\View\Compiler
 */
interface CompilerInterface
{
    public function get($template, $data = []);
}