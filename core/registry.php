<?php
/**
 * Registry
 *
 * @filesource
 * @copyright  Copyright (c) 2008-2014, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk/ Prank's project page
 * @link       http://github.com/brego/prank/ Prank's Git repository
 * @package    Prank
 * @subpackage Core
 * @since      Prank 0.10
 * @version    Prank 0.75
 */

/**
 * Object registry singleton
 *
 * @package    Prank
 * @subpackage Core
 */
class Registry {
	private static $instance = null;
	private        $items    = array();

/**
 * Returns an instance of the registry
 *
 * @return Registry
 */
	public static function instance() {
		if (self::$instance === null) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	private function __construct() {}
	
	private function __clone() {}

/**
 * Property overload
 * 
 * Returns a property if it is set, else throws an Exception.
 *
 * @param  string $property 
 * @return mixed
 */
	public function __get($property) {
		if (isset($this->items[$property])) {
			return $this->items[$property];
		} else {
			throw new Exception('Property '.$property.' does not exist.');
		}
	}

/**
 * Property overload
 *
 * @param  string $property 
 * @param  mixed  $value 
 * @return void
 */
	public function __set($property, $value) {
		$this->items[$property] = $value;
	}

/**
 * For testing if an overloaded property is set
 *
 * @param  string  $property 
 * @return boolean
 */
	public function __isset($property) {
		return isset($this->items[$property]);
	}
}

?>