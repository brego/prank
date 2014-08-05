<?php
/**
 * Css helper functions
 *
 * @filesource
 * @copyright  Copyright (c) 2008-2014, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk/ Prank's project page
 * @link       http://github.com/brego/prank/ Prank's Git repository
 * @package    Prank
 * @subpackage Helpers
 * @since      Prank 0.10
 * @version    Prank 0.75
 */

function css() {
	clearstatcache();
	$args  = func_get_args();
	$files = [];

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

?>