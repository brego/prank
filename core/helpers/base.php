<?php
/**
 * Base helper, accessible in controllers, views and models
 *
 * @filesource
 * @copyright  Copyright (c) 2008, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk Prank's project page
 * @package    Prank
 * @subpackage Helpers
 * @since      Prank 0.25
 * @version    Prank 0.25
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
 * @param  string $file_or_action A file or action/controller/param
 * @todo   This is far from being rock-solid... Could use some rethinking or
 *         refactoring...
 **/
function url($file_or_action) {
	if (isset($_GET['url'])) {
		$request = split('/', $_SERVER['REQUEST_URI']);
		$request = array_cleanup($request);	
	
		$url = split('/', $_GET['url']);
		$url = array_cleanup($url);
	
		$result = array_diff($request, $url);
		
		return '/'.implode('/', $result).'/'.$file_or_action;
	} else {
		return $file_or_action;
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
	// header("Location: http://".$_SERVER['HTTP_HOST']
	// 	."/".dirname($_SERVER['PHP_SELF'])
	// 	."/".$address);
	header('Location: '.$address);
	exit();
}

?>