<?php
/**
 * Booting the framework.
 *
 * Routing and setup for controllers.
 *
 * 
 * PHP version 5.3.
 *
 * @filesource
 * @copyright  Copyright (c) 2008, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk Prank's project page
 * @package    Prank
 * @subpackage Core
 * @since      Prank 0.10
 * @version    Prank 0.10
 */

require_once CORE.'inflector.php';

$path       = array();
$url        = isset($_GET['url']) ? $_GET['url'] : null;
$controller = null;
$action     = null;
$params     = array();


// Parsing the URL
if ($url != null) {
	$url  = explode('/', $url);
	$path = array_cleanup($url);
	
// URL parsed, saved to $path
// Setting & loading the $controller
	if (is_controller($path[0])) {
		$controller = $path[0];
	} elseif (is_controller('default')) {
		$controller = 'default';
	} else {
		$controller = '404';
	}
	load_controller($controller);
	
// Setting the $action
	if ($controller == $path[0] && count($path) > 1 && is_action_of($path[1], $controller)) {
		$action = $path[1];
	} elseif (is_action_of($path[0], $controller)) {
		$action = $path[0];
	} elseif (is_action_of('index', $controller)) {
		$action = 'index';
	}

// Setting the $params
	if ($controller == $path[0] && count($path) > 1 && $action == $path[1]) {
		unset($path[0], $path[1]);
		$params = $path;
	} elseif ($action == $path[0]) {
		unset($path[0]);
		$params = $path;
	} else {
		$params = $path;
	}
} else {
	$controller = 'default';
	$action     = 'index';
	load_controller($controller);
}

// Envoirment is set up by this point. Time to do some magic.

function partial($name) {
	require_once VIEWS.$GLOBALS['controller'].DS.'_'.$name.'.php';
}

try {
	$controller_name   = to_controller($controller);
	$controller_object = new $controller_name;

	$controller_object->set_action($action);
	$controller_object->set_view($action);
	$controller_object->set_params($params);
	$controller_object->set_shortname($controller);

	$controller_object->run();
} catch (Exception $e) {
	print $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine().'.';
}
?>