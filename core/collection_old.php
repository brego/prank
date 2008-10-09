<?php
/**
 * Collection - used to collect items
 * 
 * Collection makes it easy to enclose multiple 
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

class Collection extends ArrayObject { // implements Iterator, Countable {
	// private   $key       = 0;
	// private   $size      = 0;
	// private   $items     = array();
	protected $item_name = 'item';

/**
 * Constructor
 *
 * @param  array $items Items to initially create the collection
 * @return void
 */
	// public function __construct($items = null) {
	// 	if (is_array($items)) {
	// 		parent::__construct($items);
	// 	}
	// }

/**
 * Magic internal iterator
 *
 * This method iterates through the Collection, executing the lambda function
 * at each iteration.
 * 
 * Lambda can have a varied number of arguments, which names have significance
 * to what will be passed to them:
 *  - If there's more than one parameter:
 *     - If current object has a property with a name corresponding to the
 *       parameter, it will be passed to this parameter.
 *     - If parameter's name equals Collection::$item_name then each will pass
 *       the current object to it.
 *     - If the none of the above conditions are met, each will pass null to
 *       the parameter.
 *  - If there's only one parameter, and current object has a property with
 *    name corresponding to the name of the parameter, each will pass this
 *    property to the lambda.
 *  - If there's only one parameter, but it's not corresponding to any property
 *    of the current object, the object itself will be passed.
 * All the correspondence checks between parameter names and object properties
 * are done using isset - so if your object uses overloaded parameters, using
 * __set and __get, remember to define __isset to make the magic work.
 * 
 * The return value of each depends on the return value of lambda - if it has
 * none, null will be returned. If, on the other hand, it has any return value,
 * each will return an array of return values.
 * 
 * Note that magic comes at a cost - each uses Reflection to find lambda's
 * parameter names. So if speed is an issue, and you need to loop through a
 * large Collection, consider using foreach instead.
 * 
 * @param  callable $lambda Lambda function (no callbacks!)
 * @return mixed Array of results or null
 */
	public function each($lambda) {
		$reflection      = new ReflectionMethod($lambda);
		$parameter_names = array();
		$return          = array();
		
		foreach ($reflection->getParameters() as $param) {
			$parameter_names[] = $param->getName();
		}
		
		foreach($this->items as $item){
			$parameters = array();
			if (count($parameter_names)>1) {
				foreach ($parameter_names as $key => $value) {
					if ($value == $this->item_name) {
						$parameters[$key] = $item;
					} elseif(isset($key->$value)) {
						$parameters[$key] = $item->$value;
					} else {
						$parameters[$key] = null;
					}
				}
			} elseif (isset($item->$parameter_names[0])) {
				$parameters[] = $item->$parameter_names[0];
			} else {
				$parameters[] = $item;
			}

			$lambda_return = call_user_func_array($lambda, $parameters);
			if (empty($lambda_return) === false) {
				$return[] = $lambda_return;
			}
		}
		return (count($return)==0?null:$return);
	}
	
	// public function count() {
	// 	return $this->size;
	// }

	/**
	 * Part of the Iterator, returns current item.
	 *
	 * @return mixed Current item from the collection
	 */
	// public function current() {
	// 	return $this->items[$this->key];
	// }

	/**
	 * Part of the Iterator, moves the set one step forward
	 *
	 * @return void
	 */
	// public function next() {
	// 		$this->key++;
	// 	}

	/**
	 * Part of the Iterator, moves the set to the beginning
	 *
	 * @return void
	 */
	// public function rewind() {
	// 	$this->key = 0;
	// }

	/**
	 * Part of the Iterator, checks if there are any more elements after the
	 * current one
	 *
	 * @return boolean
	 */
	// public function valid() {
	// 	if ($this->key >= $this->size) {
	// 		return false;
	// 	} else {
	// 		return true;
	// 	}
	// }

	/**
	 * This reverses the internal array
	 *
	 * Array gets reversed, and internal pointer gets rewind'ed.
	 *
	 * @return void
	 */
	// public function reverse() {
	// 	$this->items = array_reverse($this->items);
	// 	$this->rewind();
	// }

	/**
	 * Part of the Iterator, returns the current position in the set
	 *
	 * @return integer
	 */
	// public function key() {
	// 	return $this->key;
	// }

	/**
	 * Adds new item to the collection
	 *
	 * Adds new item to the collection and ++'s the size of it.
	 *
	 * @param  mixed $item
	 * @return void
	 */
	public function add($item) {
		// $this->items[] = $item;
		// $this->size++;
		$this->append($item);
	}

	// public function clear() {
	// 	$this->items = array();
	// 	$this->rewind();
	// 	$this->size = 0;
	// }
}


?>