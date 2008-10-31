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
		
		if (class_exists($class_name) === false) {
			$class_name = Inflector::underscore($class_name);
			$class_name = str_replace('_', c()->ds, $class_name);
			$class_name = str_replace('prank'.c()->ds, '', $class_name);
		
			if(is_file(c()->core.$class_name.'.php')) {
				require c()->core.$class_name.'.php';
			}
		
			if(is_file(c()->models.$class_name.'.model.php')) {
				require c()->models.$class_name.'.model.php';
			}
		}
	}
}

/**
 * Shortcut for echo
 *
 * @return void
 * @param  string $string String to be echoed to the screen.
 **/
function _($string) {
	echo $string;
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
		echo "<pre>\n";
		var_dump($arg);
		echo "</pre>\n";
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
 * If called without arguments, returns the configuration object. If called
 * with a parameter, it is asumed to be name of a configuration property (will
 * be fetched with Config::get()).
 * 
 * @param  string $name
 * @return mixed
 */
function c($name = false) {
	if ($name !== false) {
		return Config::get($name);
	} else {
		$config = Config::instance();
		return $config;
	}
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
	if (count($array) == 1 && is_array($array)) {
		$array = $array[0];
	}
	sort($array);
	return $array;
}

/**
 * Recursively delete a directory
 * 
 * Removes specified directory/files, recursively.
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
 * File format functions
 *****************************************************************************/

/**
 * Returns the $variable converted into JSON format (using built-in
 * json_encode()).
 *
 * @param  mixed  $variable 
 * @return string
 */
function to_json($variable) {
	return json_encode($variable);
}

/**
 * Convenience method for writing $variable in JSON format to $file
 *
 * @param  mixed  $variable 
 * @param  string $file 
 * @return mixed  Number of bytes written/false
 */
function to_json_file($variable, $file) {
	return file_put_contents($file, to_json($variable));
}

/**
 * Returns $json parsed into a php object/array
 * 
 * If $array is true, will return an associative array of given data. If it's
 * false, will return an object. Conversion is done using built-in
 * json_decode().
 *
 * @param  string  $json
 * @param  boolean $array
 * @return mixed
 */
function from_json($json, $array = true) {
	return json_decode($json, $array);
}

/**
 * Convenience method for reading JSON from $file
 *
 * @param  string  $file 
 * @param  boolean $array 
 * @return mixed
 */
function from_json_file($file, $array = true) {
	return from_json(file_get_contents($file), $array);
}

/**
 * Returns YAML-formatted $variable
 *
 * If the Syck extension is avilable, it will be used. Else, the Spyc class is
 * expected to be found in core/lib/spyc/spyc.php.
 * 
 * @link   http://pecl.php.net/package/syck Pecl Syck extension
 * @link   http://spyc.sourceforge.net/ A simple php yaml class
 * @param  mixed  $variable 
 * @return string
 */
function to_yaml($variable) {
	if (function_exists('syck_dump')) {
		return syck_dump($variable);
	} else {
		if (class_exists('Spyc') === false) {
			require c()->lib.'spyc'.c()->ds.'spyc.php';
		}
		return Spyc::YAMLDump($variable);
	}
}

/**
 * Convenience method for writing $variable in YAML format into $file
 *
 * @param  string $variable 
 * @param  string $file 
 * @return mixed  Number of bytes written/false
 */
function to_yaml_file($variable, $file) {
	return file_put_contents($file, to_yaml($variable));
}

/**
 * Returns given $yaml converted into php datatypes
 * 
 * If the Syck extension is avilable, it will be used. Else, the Spyc class is
 * expected to be found in core/lib/spyc/spyc.php.
 * 
 * @link   http://pecl.php.net/package/syck Pecl Syck extension
 * @link   http://spyc.sourceforge.net/ A simple php yaml class
 * @param  string $yaml 
 * @return mixed
 */
function from_yaml($yaml) {
	if (function_exists('syck_load')) {
		return syck_load($yaml);
	} else {
		if (class_exists('Spyc') === false) {
			require c()->lib.'spyc'.c()->ds.'spyc.php';
		}
		return Spyc::YAMLLoad($yaml);
	}
}

/**
 * Convenience method for reading YAML from $file
 *
 * @param  string $file 
 * @return mixed
 */
function from_yaml_file($file) {
	return from_yaml(file_get_contents($file));
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
	return method_exists(Inflector::to_controller($controller), $action);
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
	echo $output;
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
	echo $output;
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
