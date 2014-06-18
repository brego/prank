<?php
/**
 * Base helper, accessible in controllers, views and models
 *
 * @filesource
 * @copyright  Copyright (c) 2008-2014, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk/ Prank's project page
 * @package    Prank
 * @subpackage Helpers
 * @since      Prank 0.25
 * @version    Prank 0.75
 */

function partial($name, $params = array()) {
	$registry   = Registry::instance();
	$controller = $registry->current_controller;
	extract($params);
	extract($controller->view_variables);
	require file_path($registry->config->views, $controller->controller, '_'.$name.'.php');
}


/**
 * Returns the filename with a relative path to the root of the page.
 *
 * @return string Relative path to the file
 * @param  string $path A file or action/controller/param
 * @todo   This is far from being rock-solid... Could use some rethinking or
 *         refactoring...
 **/
function url($path) {
	$controller = Registry::instance()->current_controller;
	
	if (is_array($path)) {
		if (isset($path['controller']) === false) {
			$path['controller'] = $controller->controller;
		}
		if (isset($path['action']) === false) {
			$path['action'] = $controller->action;
		}
		$result = array($path['controller'], $path['action']);
		if (isset($path['id']) === true) {
			$result[] = $path['id'];
		}
		$path = implode('/', $result);
	} else {
		if ($path[0] !== '/') {
			$path = $controller->controller.'/'.$path;
		} else {
			$path = substr($path, 1);
		}
	}
	
	if (isset($_SERVER['REQUEST_URI'])) {
		$request = split('/', $_SERVER['REQUEST_URI']);
		$request = array_cleanup($request);	
	
		$url = array();
		if (isset($_GET['url'])) {
			$url = split('/', $_GET['url']);
			$url = array_cleanup($url);
		}

		$result = array_diff($request, $url);
		
		return '/'.implode('/', $result).'/'.$path;
	} else {
		return '/'.$path;
	}
}

/**
 * Preforms a proper HTTP redirect to given $address (passed through url())
 *
 * @param  string $address
 * @return void
 */
function redirect($address) {
	$address = url($address);
	header('Location: '.$address);
	exit();
}

?>