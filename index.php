<?php

ini_set('display_errors', 'On');
ini_set('html_errors', 0);
error_reporting(-1);

require_once __DIR__ . '/vendor/autoload.php';
// include_once __DIR__ . '/cors.php';

use app\lib\Response;
use app\lib\Router;

define('MainDirectory', str_replace('\\', '/', __DIR__) . '');
define('EnvDirectory', MainDirectory . '/environments');
define('AppDirectory', MainDirectory . '/app');
define('AppNamespace', 'app\\');



try {

	$dotenv = \Dotenv\Dotenv::createImmutable(EnvDirectory);
	$dotenv->load();

	$response = new Response('json');
	$router = new Router();

	$routes = require('routes.php');
	$router->add($routes);


	if ($router->isFound()) {
		$router->executeHandler($router->getRequestHandler(), $router->getParams());
	} else {
		$response->setHtmlCode(404)->send('Page not found');
	}
} catch (Exception $e) {

	$response
		->setHtmlCode($e->getCode())
		->send(array(
			'status' => false,
			'message' => $e->getMessage(),
			'result' => null
		));
}
