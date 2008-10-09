<?php
/**
 * Object base class
 *
 * Used for extending the basic functionality of objects. Currently providing
 * following functionality:
 *
 * - Adding methods to classes (through Object::extend)
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

class Object {
	private static $methods = array();

/**
 * Extend - add a method to a class
 *
 * The $method's first param will be replaced with $this.
 * 
 * @param  string $name   Name the method will be called in class.
 * @param  string $method A callable function/method.
 * @return void
 */	
	public static function extend($name, $method) {
		if (is_callable($method)) {
			self::$methods[$name] = $method;
		}
	}

/**
 * Prototype for __call
 * 
 * If extending __call locally, remember to use Object::register_extensions().
 *
 * @param  string $method 
 * @param  string $args 
 * @return mixed
 */	
	public function __call($method, $args) {
		return $this->register_extensions($method, $args);
	}

/**
 * Registers extension functions (mixins)
 * 
 * Useful when local __call needs to be defined. Remember to return the return
 * of this function. If $method is not registered, throws a new Exception.
 *
 * @param  string $method Method to be called (from self::$methods)
 * @param  array  $args Arguments for the method
 * @return mixed
 */	
	private function register_extensions($method, $args) {
		if (isset(self::$methods[$method])) {
			array_unshift($args, $this);
			return call_user_func_array(self::$methods[$method], $args);
		} else {
			throw new Exception('Unknown method');
		}
	}
}
