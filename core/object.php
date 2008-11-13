<?php
/**
 * Object base class
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

namespace Prank\Core;

/**
 * Object base class
 *
 * Used for extending the basic functionality of objects. Currently providing
 * following functionality:
 *
 * - Adding methods to classes through Object::extend()
 * - Adding methods to objects through $object->extend()
 * - Testing if a class responds to a method through Object::responds()
 * - Testing if an object responds to a method through $object->responds()
 * 
 * If overriding Object's __call() or __callStatic(), remember to call on
 * parent::__call() and parent::__callStatic() - or else the functionality
 * won't work.
 *
 * @package    Prank
 * @subpackage Core
 */
class Object {
	protected static $extended_class_methods  = array();
	protected        $extended_object_methods = array();

/**
 * Provides object-specific capabilities
 * 
 * Remember to return the return of this function. If $method is not
 * registered, throws a new Exception.
 *
 * Registers following public methods:
 * 
 *  - Registers 'responds'. Checks if the class responds publicly to a method,
 *    takes into account also the dynamic extended methods. Object::responds()
 *    expects one parameter, and it should be a string.
 * 
 *  - Registers 'extend', which adds a method to an object. Object::extend()
 *    expects two parameters - a string which will identify the method, and
 *    a callable lambda function. On call, the lambda functions first parameter
 *    will be replaced with $this.
 * 
 * @param  string $method 
 * @param  string $arguments 
 * @return mixed
 */	
	public function __call($method, $arguments) {
		if ($method == 'responds') {
			if (isset($this->extended_object_methods[$arguments[0]]) === true) {
				return true;
			} else {
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
			}
		} elseif ($method == 'extend') {
			if (is_callable($arguments[1])) {
				$this->extended_object_methods[$arguments[0]] = $arguments[1];
				return true;
			} else {
				return false;
			}
		} elseif (isset($this->extended_object_methods[$method]) === true) {
			array_unshift($arguments, $this);
			return call_user_func_array($this->extended_object_methods[$method], $arguments);
		} else {
			throw new Exception('Unknown method '.$method.' called.');
		}
	}

/**
 * Provides class-specific capabilities
 * 
 * Remember to return the return of this function. If $method is not
 * registered, throws a new Exception.
 * 
 * Registers following public methods:
 * 
 *  - Registers 'responds'. Checks if the class responds publicly to a method,
 *    takes into account also the dynamic extended methods. Object::responds()
 *    expects one parameter, and it should be a string.
 * 
 *  - Registers 'extend', which adds a method to an object. Object::extend()
 *    expects two parameters - a string which will identify the method, and
 *    a callable lambda function. On call, the lambda functions first parameter
 *    will be replaced with the name of the current class.
 *
 * @param  string $method 
 * @param  string $arguments 
 * @return mixed
 */	
	public static function __callStatic($method, $arguments) {
		if ($method == 'responds') {
			if (isset(self::$extended_class_methods[$arguments[0]]) === true) {
				return true;
			} else {
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
			}
		} elseif ($method == 'extend') {
			if (is_callable($arguments[1])) {
				self::$extended_class_methods[$arguments[0]] = $arguments[1];
				return true;
			} else {
				return false;
			}
		} elseif (isset(self::$extended_class_methods[$method]) === true) {
			array_unshift($arguments, get_called_class());
			return call_user_func_array(self::$extended_class_methods[$method], $arguments);
		} else {
			throw new Exception('Unknown method '.$method.' called.');
		}
	}

}
