<?php
/**
 * Collection - used to collect items
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

/**
* Collection - used to collect items
* 
* Collection makes it easy to enclose multiple objects in one.
* Dev note: this could also be based on ArrayObject.
*
* @package    Prank
* @subpackage Core
 */
class Collection implements Iterator, Countable {
	protected $items     = array();
/**
 * Name of single item in the colection (used in each)
 *
 * @var string
 */
	protected $item_name = 'item';
	private   $key       = 0;
	private   $size      = 0;
	private   $loader    = null;

/**
 * Constructor
 *
 * Takes a variable number of parameters, which will be added to the initial
 * collection with Collection::add().
 * 
 * @return void
 */
	public function __construct() {
		$items = func_get_args();
		if (count($items) > 0) {
			if (is_array($items[0]) !== false && count($items) === 1) {
				foreach ($items[0] as $item) {
					$this->add($item);
				}
			} elseif (count($items) === 1) {
				$this->add($items[0]);
			} else {
				foreach ($items as $item) {
					$this->add($item);
				}
			}	
		}
	}

/**
 * Registers a lazyload function
 * 
 * Function is expected to be a callable (lambda), accepting one parameter,
 * which will be used to pass reference to the collection. Loader function will
 * run only once, and will be erased afterwards.
 *
 * @param  callable $callable 
 * @return void
 */
	public function register_loader($callable) {
		if (is_callable($callable)) {
			$this->loader = $callable;
		} else {
			throw new Exception('Non-callable function passed as a loader: '.$callable);
		}
	}

/**
 * Runs the lazyload function
 *
 * @return void
 */
	protected function load() {
		if ($this->loader !== null) {
			$loader = $this->loader;
			$loader($this);
			$this->loader = null;
		}
	}

/**
 * Sets the name of a single item, used in each
 *
 * @param  string $name 
 * @return mixed
 */
	public function item_name($name=null) {
		if ($name === null) {
			return $this->item_name;
		} else {
			$this->item_name = $name;
		}
	}

/**
 * Magic internal iterator
 *
 * This method iterates through the Collection, executing the lambda function
 * at each iteration.
 * Be advised that each calls on a collection result in the lazy-load being run
 * (if applicable).
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
		
		// find the parameters given in lambda
		foreach ($reflection->getParameters() as $param) {
			$parameter_names[] = $param->getName();
		}
		
		// load eventual lazyload
		$this->load();
		
		// loop through the collection
		foreach ($this as $item) {
			$parameters = array();
			
			if (count($parameter_names)>1) {
				foreach ($parameter_names as $key => $value) {
					if ($value == $this->item_name) {
						// parameter refers to current object
						$parameters[$key] = $item;
					} elseif(isset($item->$value)) {
						// parameter refers to a property
						$parameters[$key] = $item->$value;
					} else {
						// we default to null
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

/**
 * Implements the Countable interface
 * 
 * Be advised that count calls on a collection result in the lazy-load being
 * run (if applicable).
 * 
 * @return integer Size of the collection
 */	
	public function count() {
		$this->load();
		return $this->size;
	}

/**
 * Part of the Iterator, returns current item
 *
 * Be advised that current calls on a collection result in the lazy-load being
 * run (if applicable).
 * 
 * @return mixed Current item from the collection
 */
	public function current() {
		$this->load();
		return $this->items[$this->key];
	}

/**
 * Part of the Iterator, moves the set one step forward
 *
 * @return void
 */
	public function next() {
		$this->load();
		$this->key++;
	}

/**
 * Part of the Iterator, moves the set to the beginning
 *
 * @return void
 */
	public function rewind() {
		$this->load();
		$this->key = 0;
	}

/**
 * Part of the Iterator, checks if there are any more elements after the
 * current one
 *
 * @return boolean
 */
	public function valid() {
		$this->load();
		if ($this->key >= $this->size) {
			return false;
		} else {
			return true;
		}
	}

/**
 * This reverses the internal array
 *
 * Array gets reversed, and internal pointer gets rewinded.
 *
 * @return void
 */
	public function reverse() {
		$this->load();
		$this->items = array_reverse($this->items);
		$this->rewind();
	}

/**
 * Part of the Iterator, returns the current position in the set
 *
 * @return integer
 */
	public function key() {
		$this->load();
		return $this->key;
	}

/**
 * Adds new item to the collection
 *
 * Adds new item to the collection and ++'s the size of it.
 *
 * @param  mixed $item
 * @return void
 */
	public function add($item) {
		$this->items[] = $item;
		$this->size++;
	}

/**
 * Clears the collection
 *
 * @return void
 */
	public function clear() {
		$this->items = array();
		$this->key   = 0;
		$this->size  = 0;
	}
}

?>