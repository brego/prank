<?php
/**
 * HTML helper
 *
 * @filesource
 * @copyright  Copyright (c) 2008, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk/ Prank's project page
 * @package    Prank
 * @subpackage Helpers
 * @since      Prank 0.25
 * @version    Prank 0.25
 */

function is_current_page($path) {
	$controller = Registry::instance()->current_controller;
	if (is_string($path)) {
		$result = array();
		if ($path[0] === '/') {
			$result['controller'] = true;
		}
		$path = split('/', $path);
		$path = array_cleanup($path);
		if (isset($result['controller']) && isset($path[0])) {
			$result['controller'] = $path[0];
			if (isset($path[1])) {
				$result['action'] = $path[1];
			}
		} elseif (isset($path[0])) {
			$result['action'] = $path[0];
		} else {
			return false;
		}
		$path = $result;
	}
	if (isset($path['controller']) === true && $path['controller'] === $controller->controller) {
		if (isset($path['action']) === true) {
			if ($path['action'] === $controller->action) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	} elseif (isset($path['action']) === true && $path['action'] === $controller->action) {
		return true;
	} else {
		return false;
	}
}

function link_to($path, $name = null, $options = null) {
	if (is_array($name) === true) {
		$options = $name;
		$name   = null;
	}
	if (is_array($path) === true || substr($path, 0, 7) !== 'http://') {
		$path = url($path);
	}
	if ($name === null) {
		$name = $path;
	}

	$options = html_parse_params($options);
	
	return '<a href="'.$path.'"'.$options.'>'.$name.'</a>';
}

function link_to_unless_current($path, $name = null, $options = null, $block = null) {
	if (is_current_page($path)) {
		if ($block === null && is_callable($options) === true) {
			$block = $options;
		}
		if ($block === null && is_callable($name) === true) {
			$block = $name;
		}
		if ($name === null || is_callable($name) === true) {
			$name = url($path);
		}
		if ($block !== null) {
			return $block($name);
		} else {
			return $name;
		}
	} else {
		return link_to($path, $name, $options);
	}
}

function image_path($file) {
	if (substr($file, 0, 7) !== 'http://') {
		if ($file[0] !== '/') {
			$file = '/images/'.$file;
		}
		$file = url($file);
	}
	return $file;
}

function image($file, $options = null) {
	$file   = image_path($file);
	$options = html_parse_params($options);
	return '<img src="'.$file.'"'.$options.' />';
}

function stylesheet_path($file) {
	if (substr($file, -4) !== '.css') {
		$file .= '.css';
	}
	if (substr($file, 0, 7) !== 'http://') {
		if ($file[0] !== '/') {
			$file = '/stylesheets/'.$file;
		}
		$file = url($file);
	}
	return $file;
}

function stylesheet($file, $options = null) {
	$file   = stylesheet_path($file);
	$options = html_parse_params($options);
	return '<link href="'.$file.'" rel="stylesheet" type="text/css"'.$options.' />';
}

function html_parse_params($options = null) {
	if ($options !== null) {
		$options = array_map(function($key, $value) {return $key.'="'.$value.'"';}, array_keys($options), $options);
		return ' '.implode(' ', $options);
	}
	return $options;
}

?>