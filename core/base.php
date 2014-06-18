<?php
/**
 * Basic Prank functions.
 *
 * Shorthands and utilities for easying both internal workings of the framework
 * and the userland code.
 *
 * @filesource
 * @copyright  Copyright (c) 2008-2014, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk/ Prank's project page
 * @link       http://github.com/brego/prank/ Prank's Git repository
 * @package    Prank
 * @subpackage Core
 * @since      Prank 0.10
 * @version    Prank 0.75
 */

/**
 * Shortcut for dumping variables.
 *
 * Dumps variables on the screen, using var_dump, wrapped in <pre>. Accepts
 * variable number of arguments, and treats them separatly.
 *
 * @return void
 **/
function d() {
	$args = func_get_args();
	foreach ($args as $arg) {
		echo "<pre>\n";
		var_dump($arg);
		echo "</pre>\n";
	}
}

/**
 * Deletes empty elements from an array.
 *
 * Alias for array_filter.
 *
 * @return array
 * @param  array $array Array to be cleaned
 **/
function array_cleanup($array) {
	return array_filter($array);
}

/**
 * This function is deprecated.
 *
 * @param      string $name
 * @return     mixed
 * @deprecated
 */
function c($name = false) {
	throw new DeprecatedException('c');
}

/**
 * Returns an array composed of given arguments
 *
 * @return array
 */
function a() {
	$args = func_get_args();
	return $args;
}

/**
 * Sorts an array and returns the result
 *
 * Parameter can be either an array, or multiple parameers that'll be converted
 * to an array. Sorting will be performed using sort().
 *
 * @return array
 */
function s() {
	$array = func_get_args();
	if (count($array) == 1 && is_array($array[0])) {
		$array = $array[0];
	}
	sort($array);
	return $array;
}

/**
 * Returns a given arguments separated by current directory separator
 *
 * This method accepts multiple rguments, and returns them glued together by
 * current directory separator. If the first argument has the separator as it's
 * last character, it'll be stripped (meaning it's ok to use Prank's leading
 * '/' configuration variables, as long as they're used as the first argument).
 *
 * @return string
 */
function file_path() {
	$files = func_get_args();
	$ds    = DIRECTORY_SEPARATOR;
	if (substr($files[0], -1) === $ds) {
		$files[0] = substr($files[0], 0, -1);
	}
	return implode($ds, $files);
}

/******************************************************************************
 * YAML functions.
 *
 * Deprecated.
 *****************************************************************************/

/**
 * This function is deprecated.
 *
 * @param      mixed  $variable
 * @return     void
 * @deprecated
 */
function to_yaml($variable) {
	throw new DeprecatedException('to_yaml');
}

/**
 * This function is deprecated.
 *
 * @param      string $variable
 * @param      string $file
 * @return     void
 * @deprecated
 */
function to_yaml_file($variable, $file) {
	throw new DeprecatedException('to_yaml_file');
}

/**
 * This function is deprecated.
 *
 * @param      string $yaml
 * @return     void
 * @deprecated
 */
function from_yaml($yaml) {
	throw new DeprecatedException('from_yaml');
}

/**
 * This function is deprecated.
 *
 * @param      string $file
 * @return     void
 * @deprecated
 */
function from_yaml_file($file) {
	throw new DeprecatedException('from_yaml_file');
}

/******************************************************************************
 * Textual helper functions.
 *****************************************************************************/

/**
 * Uppercases the string.
 *
 * @return string Uppercased string.
 * @param  string $string String to be uppercased.
 **/
function up($string) {
	return strtoupper($string);
}

/**
 * Lowercases the string.
 *
 * @return string Lowercased string.
 * @param  string $string String to be lowercased.
 **/
function down($string) {
	return strtolower($string);
}

/******************************************************************************
 * Basic action handling functions.
 *****************************************************************************/

/**
 * Checks if method exists in the controller class.
 *
 * @return boolean
 * @param  string  $action     Shortname of the action.
 * @param  string  $controller Shortname of the controller.
 **/
function is_action_of($action, $controller) {
	return method_exists(to_controller($controller), $action);
}

/**
 * Use this to require helpers
 *
 * This method takes multiple arguments, and they don't have to contain .php
 * extension. A helper file can be included only once, so the method will
 * simply ignore a second call with the same helper name. Helpers are searched
 * for in app/helpers and core/helpers respectively.
 *
 * If the helper doesn't exist, will throw an Exception.
 *
 * @todo   require_once needs to be changed to require! damn test suite!
 * @return void
 */
function use_helper() {
	$files     = func_get_args();
	$registry  = Registry::instance();
	if (isset($registry->helpers_loaded) === false) {
		$registry->helpers_loaded = new stdClass;
	}
	foreach ($files as $file) {
		if (substr($file, -4, 4) !== '.php') {
			$file = $file.'.php';
		}
		if (isset($registry->helpers_loaded->$file) === false) {
			$core_path = file_path($registry->config->core, 'helpers', $file);
			$app_path  = file_path($registry->config->app, 'helpers', $file);
			if (is_file($app_path)) {
				$registry->helpers_loaded->$file = true;
				require_once $app_path;
			} elseif (is_file($core_path)) {
				$registry->helpers_loaded->$file = true;
				require_once $core_path;
			} else {
				break;
				throw new Exception('Helper '.$file.' not found in core or application helper directories.');
			}
		}
	}
}

/**
 * Deprecated Exception
 */
class DeprecatedException extends Exception {
	public function __construct($method, $code = 0) {
		parent::__construct("Method '$method' is deprecated.", $code);
	}
}

?>