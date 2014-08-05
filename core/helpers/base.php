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

function partial($name, $params = []) {
	$registry   = Registry::instance();
	$controller = $registry->current_controller;
	extract($params);
	extract($controller->view_variables);
	require file_path($registry->config->views, $controller->get_controller(), '_'.$name.'.php');
}


/**
 * Returns the filename with a relative path to the root of the page
 *
 * url([controller => controller_name]) // Assumes action = index
 * url([controller => controller_name, action => action_name])
 * url(action => action_name]) // Assumes current controller
 * url([controller => controller_name, action => action_name, param, param2])
 *
 * url('/controller/action')
 * url('action') // Assumes current controller, no '/' at first character
 *
 * @param  mixed $path A file or action/controller/param or an array
 * @return string Relative path to the file
 * @todo   This is far from being rock-solid... Could use some rethinking or
 *         refactoring...
 * @todo   Improve documentation
 **/
function url($path) {
	$controller = Registry::instance()->current_controller;
	
	if (is_array($path)) {
		if (isset($path['controller']) && isset($path['action']) === false) {
			$path['action'] = 'index';
		} elseif (isset($path['action']) && isset($path['controller']) === false) {
			$path['controller'] = $controller->get_controller();
		} elseif (isset($path['controller']) === false && isset($path['action']) === false) {
			$path['action']     = $controller->action;
			$path['controller'] = $controller->get_controller();
		}

		$result = [$path['controller'], $path['action']];
		unset($path['controller'], $path['action']);

		if (isset($path['id']) === true) {
			$result[] = $path['id'];
			unset($path['id']);
		}

		if (count($path) > 0) {
			$result = array_merge($result, $path);
		}

		$path = implode('/', $result);

	} else {
		if ($path[0] !== '/') {
			$path = $controller->get_controller().'/'.$path;
		} else {
			$path = substr($path, 1);
		}
	}
	
	if (isset($_SERVER['REQUEST_URI'])) {
		$request = explode('/', $_SERVER['REQUEST_URI']);
		$request = array_cleanup($request);	

		$url = [];
		if (isset($_GET['url'])) {
			$url = explode('/', $_GET['url']);
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