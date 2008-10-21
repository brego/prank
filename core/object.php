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
 * The $method's first param will be replaced with $this or name of the called 
 * class (get_called_class()) if called in a static context.
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
 * Registers extension functions (mixins)
 * 
 * Remember to return the return of this function. If $method is not
 * registered, throws a new Exception.
 *
 * Also registers 'responds' method (checks if the class responds publicly to
 * a method).
 * 
 * @param  string $method 
 * @param  string $arguments 
 * @return mixed
 */	
	public function __call($method, $arguments) {
		if ($method == 'responds') {
			
			$class = new ReflectionClass(get_class($this));
			try {
				$method = $class->getMethod($arguments[0]);
				if ($method->isPublic() === false || $method->isAbstract() === true) {
				    return false;
				}
			} catch (ReflectionException $e) {
				return false;
			}
			return true;
			
		} elseif (isset(self::$methods[$method])) {
			array_unshift($arguments, $this);
			return call_user_func_array(self::$methods[$method], $arguments);
		} else {
			throw new Exception('Unknown method '.$method.' called.');
		}
	}

/**
 * Registers static extension functions (mixins)
 * 
 * Remember to return the return of this function. If $method is not
 * registered, throws a new Exception.
 * 
 * Also registers 'responds' method (checks if the object responds publicly to
 * a method).
 *
 * @param  string $method 
 * @param  string $arguments 
 * @return mixed
 */	
	public static function __callStatic($method, $arguments) {
		if ($method == 'responds') {
			
			$class = new ReflectionClass(get_called_class());
			try {
				$method = $class->getMethod($arguments[0]);
				if ($method->isPublic() === false || $method->isAbstract() === true) {
				    return false;
				}
			} catch (ReflectionException $e) {
				return false;
			}
			return true;
			
		} elseif (isset(self::$methods[$method])) {
			array_unshift($arguments, get_called_class());
			return call_user_func_array(self::$methods[$method], $arguments);
		} else {
			throw new Exception('Unknown method '.$method.' called.');
		}
	}

}
