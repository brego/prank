<?php
/**
 * Base class for sets of Models.
 *
 * Iterator. Used as a base class for collections of Models. Creates methods 
 * for working with sets.
 *
 * PHP version 5.3.
 *
 * @filesource
 * @copyright  Copyright (c) 2008, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk Prank's project page
 * @package    Prank
 * @subpackage Core.Model
 * @since      Prank 0.10
 * @version    Prank 0.10
 */

namespace Prank::Model;

class Set extends ::Collection {
	// private $key    = 0;
	// private $size   = 0;
	// private $models = array();
	// private $hollow = true;
	// 
	// public function __construct() {
	// }
	// 
	// /**
	//  * Set/get the hollowness of the set
	//  *
	//  * If $set is set, then set the hollowness to the given value. Else return the
	//  * current value.
	//  *
	//  * @param  boolean $set Hollowness to be set
	//  * @return boolean The hollowness of the set
	//  */
	// public function hollow($set = null) {
	// 	if (is_bool($set)) {
	// 		$this->hollow = $set;
	// 	}
	// 	return $this->hollow;
	// }
	// 
	// public function each($lambda_function) {
	// 	foreach($this->models as $model){
	// 		call_user_func_array($lambda_function, array($model));
	// 	}
	// }
	// 
	// /**
	//  * Part of the Iterator interface, returns the current object from the
	//  * container
	//  *
	//  * @return Model
	//  */
	// public function current() {
	// 	return $this->models[$this->key];
	// }
	// 
	// /**
	//  * Part of the Iterator interface, moves the set one step forward
	//  *
	//  * @return void
	//  */
	// public function next() {
	// 	$this->key++;
	// }
	// 
	// /**
	//  * Part of the Iterator interface, moves the set to the beginning
	//  *
	//  * @return void
	//  */
	// public function rewind() {
	// 	$this->key = 0;
	// }
	// 
	// /**
	//  * Part of the Iterator interface, checks if there are any more elements after
	//  * the current one
	//  *
	//  * @return boolean
	//  */
	// public function valid() {
	// 	if ($this->key >= $this->size) {
	// 		return false;
	// 	} else {
	// 		return true;
	// 	}
	// }
	// 
	// /**
	//  * This reverses the internall array
	//  *
	//  * Array gets reversed, and internall pointer gets rewind'ed.
	//  *
	//  * @return void
	//  */
	// public function reverse() {
	// 	$this->models = array_reverse($this->models);
	// 	$this->rewind();
	// }
	// 
	// /**
	//  * Part of the Iterator interface, returns the current position in the set
	//  *
	//  * @return integer
	//  */
	// public function key() {
	// 	return $this->key;
	// }
	// 
	// /**
	//  * Adds new Model to the container
	//  *
	//  * Adds new Model to the container and ++'s the size of the container. Use of
	//  * this method sets hollowness to false.
	//  *
	//  * @param  Model $model
	//  * @return void
	//  */
	// public function add($model) {
	// 	$this->models[] = $model;
	// 	$this->size++;
	// 	$this->hollow(false);
	// }
	// 
	// public function clear() {
	// 	$this->models = array();
	// 	$this->rewind();
	// 	$this->hollow(true);
	// 	$this->size = 0;
	// }
}

?>
