<?php
/**
 * Javascript helper functions
 *
 * @filesource
 * @copyright  Copyright (c) 2008, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk Prank's project page
 * @package    Prank
 * @subpackage Helpers
 * @since      Prank 0.20
 * @version    Prank 0.25
 */

/**
 * Return HTML code for linking JS files, and compress them
 * 
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
	clearstatcache();
	$files  = func_get_args();
	$output = '';

	foreach ($files as $file) {
		$output .= javascript_link($file);
	}
	
	$output .= javascript_link('behavior.php');

	return $output;
}

/**
 * Print alias for javascript method
 *
 * This is an alias for the javascript method - with the subtle change of
 * outputting the link, instead of returning it.
 *
 * @return void
 */
function _javascript() {
	$files  = func_get_args();
	$output = call_user_func_array('javascript', $files);
	echo $output;
}

/**
 * Returns a html link to a javascript file
 *
 * @param  string $file 
 * @return string
 */
function javascript_link($file) {
	if (substr($file, -3, 3) !== '.js' && substr($file, -4, 4) !== '.php') {
		$file = $file.'.js';
	}
	$file = '<script src="'.url('js/'.$file).'" type="text/javascript" charset="utf-8"></script>'."\n";
	return $file;
}

/**
 * Print alias for javascript_link method
 *
 * @param  string $file 
 * @return string
 */
function _javascript_link($file) {
	echo javascript_link($file);
}

function add_javascript_behavior($behavior) {
	if (isset($_SESSION) === false) {
		session_start();
	}
	if (isset($_SESSION['prank']) === false) {
		$_SESSION['prank'] = array();
	}
	if (isset($_SESSION['prank']['javascript']) === false) {
		$_SESSION['javascript'] = array();
	}
	if (isset($_SESSION['prank']['javascript']['behaviors']) === false) {
		$_SESSION['javascript'] = array();
	}
	
	$_SESSION['prank']['javascript']['behaviors'][] = $behavior;
}

?>