<?php
/**
 * JSON helper file
 *
 * @filesource
 * @copyright  Copyright (c) 2008-2014, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk/ Prank's project page
 * @package    Prank
 * @subpackage Helpers
 * @since      Prank 0.25
 * @version    Prank 0.25
 */

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

?>