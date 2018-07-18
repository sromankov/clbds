<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

define('ROOT', __DIR__ . '/..' . DIRECTORY_SEPARATOR);
define('VENDOR', ROOT . 'vendor' . DIRECTORY_SEPARATOR);

/**
 * Just a helper to render templates
 *
 * @param $templateName
 * @param $data
 * @return \Classes\Response\Response
 */
function view($templateName, $data)
{
    $template = new \Classes\View\Compiler\DwooCompiler(__DIR__ . '/../resources/views/');
    $content =  $template->get( $templateName, $data);
    $response = new \Classes\Response\Response($content, 200);

    return $response;
}

/**
 * Also a helper to render json responses
 *
 * @param $data
 * @return \Classes\Response\JsonResponse
 */
function json($data)
{
    $response = \Classes\Response\Response::json($data);

    return $response;
}

/**
 * I use composer to generate autoloader files
 */
require VENDOR . 'autoload.php';

/**
 * Start the thing
 */
$app = new \Classes\Application\Application();

$response =  $app->handle(new \Classes\Request\Request($_GET, $_POST, $_SERVER));
