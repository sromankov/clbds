<?php

namespace Classes\View\Compiler;

/**
 * Class DwooCompiler
 *
 * Using Dwoo as a templates compiler
 *
 * @package Classes\View\Compiler
 */
class DwooCompiler implements CompilerInterface
{
    /**
     * Rendered instance
     * @var \Dwoo\Core
     */
    private $templateRenderer;
    /**
     * The place where templates are placed
     * @var string
     */
    private $templateDir;

    /**
     * DwooCompiler constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->templateRenderer = new \Dwoo\Core();
        $this->templateDir = $path;

    }

    /**
     * Returns rendered content
     *
     * @param $template
     * @param array $data
     * @return string|void
     */
    public function get($template, $data = [])
    {
        $path = $this->templateDir . $template;
        return $this->templateRenderer->get($path, $data);
    }
}
