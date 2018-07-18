<?php

namespace Controllers;

/**
 * Class IndexController
 *
 * Just Frontend Controller
 *
 * @package Controllers
 */
class IndexController
{
    public function index()
    {
        return view('list.tmpl.php', []);
    }

}