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
 * @since      Prank 0.10
 * @version    Prank 0.10
 */

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