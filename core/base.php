<?php
/**
 * Basic Prank functions.
 *
 * Shorthands and utilities for easying both internal workings of the framework
 * and the userland code.
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

/******************************************************************************
 * Basic helper functions.
 *****************************************************************************/

/**
 * Autoloader function.
 *
 * Saves us from keeping track of the require's on the framework level. ATM no
 * autoloading for userland classes.
 *
 * @return void
 * @param  string $class_name Name of the class to be loaded.
 **/
function __autoload($class_name) {
	if (substr($class_name, -10, 10) !== 'Controller') {
		
		$class_name = Inflector::underscore($class_name);
		$class_name = str_replace('_', c('DS'), $class_name);
		$class_name = str_replace('prank'.c('DS'), '', $class_name);
		
		if(is_file(c('CORE').$class_name.'.php')) {
			require_once c('CORE').$class_name.'.php';
		}
		
		if(is_file(c('MODELS').$class_name.'.model.php')) {
			require_once c('MODELS').$class_name.'.model.php';
		}
	}
}

/**
 * Shortcut for print.
 *
 * @return void
 * @param  string $string String to be printed to the screen.
 **/
function _($string) {
	print $string;
}

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
		print '<pre>';
		var_dump($arg);
		print '</pre>';
	}
}

/**
 * Deletes empty elements from an array.
 *
 * @return array
 * @param  array $array Array to be cleaned
 **/
function array_cleanup($array) {
	$clean_array = array();
	foreach ($array as $element) {
		if (!empty($element)) {
			$clean_array[] = $element;
		}
	}
	return $clean_array;
}

/**
 * Interface for Config class
 *
 * Acts as a combined interface to Config::get and Config::set methods.
 * If given two arguments, the first one is treated as a lable for an option,
 * and the second one as value of that option.
 * If given an array, treats the array as an array of option=>value pairs to be
 * added to the configuration.
 * If given one argument, returns value of a corresponding key in the Config
 * class.
 * 
 * @return mixed
 */
function c() {
	$args = func_get_args();
	if (is_array($args[0]) && count($args) == 1) {
		Config::set($args[0]);
	} elseif (count($args) == 2) {
		Config::set($args[0], $args[1]);
	} elseif (is_string($args[0])) {
		return Config::get($args[0]);
	}
}

/**
 * Returns an array composed of given arguments.
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
 * to an array.
 *
 * @return array
 */
function s() {
	$array = func_get_args();
	if (count($array) == 1 && is_array($array)) {
		$array = $array[0];
	}
	sort($array);
	return $array;
}

/**
 * Recursively delete a directory
 * 
 * Removes specified directory/files.
 *
 * @param  string  $target Directory or file to be removed.
 * @return boolean Result of the removal.
 */
function rm($target) {
	if (is_file($target)) {
		if (is_writable($target)) {
			if (unlink($target)) {
				return true;
			}
		}
		return false;
	}
	if (is_dir($target)) {
		if (is_writable($target)) {
			foreach(new DirectoryIterator($target) as $object) {
				if ($object->isDot()) {
					unset($object);
					continue;
				}
				if ($object->isFile()) {
					rm($object->getPathName());
				} elseif ($object->isDir()) {
					rm($object->getRealPath());
				}
				unset($object);
			}
			if (rmdir($target)) {
				return true;
			}
		}
		return false;
	}
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


/**
 * Transforms a string into a valid controller class name.
 *
 * down()'s given string, capitalizes the first letter, and adds 'Controller'
 * at the end of the string.
 *
 * @return string Name to be transformed.
 * @param  string $string Valid class shortname of a controller.
 **/
function to_controller($name)
{
	return Inflector::controlize($name);
}


/******************************************************************************
 * Basic controller handling functions.
 *****************************************************************************/

/**
 * Checks if controller file exists.
 *
 * Checks if the $name exists in the CONTROLLERS directory.
 *
 * @return boolean
 * @param  string  $name Shortname of the controller
 **/
function is_controller($name) {
	if (file_exists(c('CONTROLLERS').down($name).'.controller.php')) {
		return true;
	} else {
		return false;
	}
}

/**
 * Loads the controller file.
 *
 * @return boolean
 * @param  string  $name Shortname of the controller.
 **/
function load_controller($name) {
	if (is_controller($name)) {
		require_once c('CONTROLLERS').down($name).'.controller.php';
		return true;
	} else {
		return false;
	}
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

/******************************************************************************
 * View helper functions
 *****************************************************************************/


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
		$request = explode('/', $_SERVER['REQUEST_URI']);
		$request = array_cleanup($request);	
	
		$url = explode('/', $_GET['url']);
		$url = array_cleanup($url);
	
		$result = array_diff($request, $url);
		
		return '/'.implode('/', $result).'/'.$file_or_action;
	} else {
		return $file_or_action;
	}
}

function css() {
	clearstatcache();
	$args  = func_get_args();
	$files = array();
	
	foreach ($args as $index => $filename) {
		if (substr($filename, -4, 4) != '.css') {
			$args[$index] = $filename.'.css';
		}
	}
	
	foreach ($args as $filename) {
		$files[] = $filename;
		$files[] = filemtime(WEBROOT.'css'.DS.$filename);
	}
	$hash = md5(implode($files));
	
	if(is_file(WEBROOT.'tmp/'.$hash.'.php')) {
		$link = $link = '<link rel="stylesheet" href="'.url('tmp/'.$hash.'.php').'" type="text/css" />'."\n";
	} else {
		$compressed = null;
		foreach ($args as $filename) {
			$file        = file_get_contents(WEBROOT.'css'.DS.$filename);
			$compressed .= compress_css($file);
		}
		$compressed_filename = 'tmp/'.md5(implode('', $files)).'.php';
		$output  = "<?php header('Content-Type: text/css'); ob_start('ob_gzhandler'); ?>\n";
		$output .= "/*\nThis file is a compressed version of this site's CSS code.\n";
		$output .= "For uncompressed version, refer to the following files:\n";
		$output .= implode("\n", $args)."\nIn the css/ directory of this site.";
		$output .= "\n*/\n";
		$output .= $compressed;
		$output .= "\n<?php ob_end_flush(); ?>";
		file_put_contents($compressed_filename, $output);
		$link = '<link rel="stylesheet" href="'.url($compressed_filename).'" type="text/css" />'."\n";
	}
	return $link;
}

function _css() {
	$args   = func_get_args();
	$output = call_user_func_array('css', $args);
	print $output;
}


function compress_css($script) {
	$script = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $script);
	$script = preg_replace('!//.*!', '', $script);
	$script = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '   ', '    '), '', $script);
	return $script;
}

/**
 * Return HTML code for linking JS files, and compress them.
 *
 * Accepts one or more string filenames for javascript files. Files are asumed
 * to reside in /webroot/js/. On first call, files will be catched in a
 * compressed version - multiple files will be parsed into a single file, in
 * order given. New version will be generated each time original files are
 * changed.
 * The final file is also gzipped for further speed (if server & client
 * supports that).
 *
 * @return string Link to the compressed JS file.
 */
function javascript() {
	/*
	add a comment to the compressed file, with location of the uncompessed ones
	allow for multiple files & compress them into a single file (in given order)
	use url()
	use catching & compressing
		use filemtime() and md5 on filename	
	*/
	clearstatcache();
	$args  = func_get_args();
	$files = array();
	
	foreach ($args as $index => $filename) {
		if (substr($filename, -3, 3) != '.js') {
			$args[$index] = $filename.'.js';
		}
	}
	
	foreach ($args as $filename) {
		$files[] = $filename;
		$files[] = filemtime(WEBROOT.'js'.DS.$filename);
	}
	$hash = md5(implode($files));
	
	if(is_file(WEBROOT.'tmp/'.$hash.'.php')) {
		$link = $link = '<script type="text/javascript" src="'.url('tmp/'.$hash.'.php').'"></script>'."\n";
	} else {
		$compressed = null;
		foreach ($args as $filename) {
			$file        = file_get_contents(WEBROOT.'js'.DS.$filename);
			$compressed .= compress_javascript($file);
		}
		$compressed_filename = 'tmp/'.md5(implode('', $files)).'.php';
		$output  = "<?php header('Content-Type: text/javascript'); ob_start('ob_gzhandler'); ?>\n";
		$output .= "/*\nThis file is a compressed version of this site's JavaScript code.\n";
		$output .= "For uncompressed version, refer to the following files:\n";
		$output .= implode("\n", $args)."\nIn the js/ directory of this site.";
		$output .= "\n*/\n";
		$output .= $compressed;
		$output .= "\n<?php ob_end_flush(); ?>";
		file_put_contents($compressed_filename, $output);
		$link = '<script type="text/javascript" src="'.url($compressed_filename).'"></script>'."\n";
	}
	return $link;
}

/**
 * Print alias for javascript method.
 *
 * This is an alias for the javascript method - with the subtle change of
 * printing the link, instead of returning it.
 *
 * @return void
 */
function _javascript() {
	$args   = func_get_args();
	$output = call_user_func_array('javascript', $args);
	print $output;
}

/**
 * This function compresses JS code.
 *
 * @param  string $script JS to be compressed.
 * @return string Compressed JS code.
 */
function compress_javascript($script) {
	$script = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $script);
	$script = preg_replace('!//.*!', '', $script);
	$script = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '   ', '    '), '', $script);
	return $script;
}

?>