<?php

namespace App;

use Exception;
use App\ResponseInterface;


class Main
{
	/**
	 * Konstruktor.
	 */
	function __construct()
	{
		\session_start();
		$controller = '\\App\\Controller\\' . $_GET['controller'];
		$action = $_GET['action'];

		if (false === class_exists($controller)) {
			throw new Exception('Wskazany kontroler nie został odnaleziony');
		}

		$controller = new $controller();

		if (false === method_exists($controller, $action)) {
			throw new Exception("Akcja $action nie została odnaleziona");
		}

		$data = $controller->$action();

		if (is_a($data, ResponseInterface::class)) {
			$data->response();
		}
	}
}
