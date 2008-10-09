<?php
/**
 * Config
 *
 * PHP version 5.3.
 *
 * @filesource
 * @copyright  Copyright (c) 2008, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk Prank's project page
 * @link       http://cakephp.org CakePHP's project page
 * @package    Prank
 * @subpackage Core
 * @since      Prank 0.10
 * @version    Prank 0.10
 */

class Config {
	private static $instance = null;
	private static $config   = array();
	
	private function __construct() {}
	
	private function __clone() {}
	
	public static function instance() {
		if (self::$instance === null) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	public static function set() {
		$args = func_get_args();
		if (is_array($args[0]) && count($args) == 1) {
			self::$config = array_merge(self::$config, $args[0]);
		} else {
			self::$config[$args[0]] = $args[1];
		}
	}
	
	public function __set($name, $value) {
		self::$config[$name] = $value;
	}
	
	public static function get($name) {
		return self::$config[$name];
	}
	
	public function __get($name) {
		return self::$config[$name];
	}
}