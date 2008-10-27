<?php
/**
 * ModelCollection - used to collect Models
 * 
 * @filesource
 * @copyright  Copyright (c) 2008, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk Prank's project page
 * @package    Prank
 * @subpackage Model
 * @since      Prank 0.10
 * @version    Prank 0.10
 */

/**
 * ModelCollection - used to collect Models
 * 
 * ModelCollection makes it easy to enclose multiple Models in one, and adds
 * some model-related methods.
 *
 * @package    Prank
 * @subpackage Model
 */
class ModelCollection extends Collection {
	private $modified      = false;
	private $relation_type = false;
	private $exists        = false;
	
/**
 * Check if any of the models is modified
 *
 * @see    ModelCollection::check_modified()
 * @return boolean
 */
	public function modified() {
		$this->check_modified();
		return $this->modified;
	}

/**
 * Check if any of the models exists in the table
 *
 * @see    ModelCollection::check_modified()
 * @return boolean
 */	
	public function exists() {
		$this->load();
		$this->check_exists();
		return $this->exists;
	}

/**
 * Adds a model to the Collection
 *
 * Before adding the model to the Collection, sets model's relation_type to a
 * valid type.
 * 
 * @see    ModelCollection::relation_type()
 * @param  Model $item
 * @return void
 */
	public function add($item) {
		$item->relation_type($this->relation_type);
		parent::add($item);
	}

/**
 * Saves all the Models in the Collection, if it's modified
 * 
 * This method loops through the collection, and saves every Model with
 * Model::save(). Be aware, if any save operation returns false, the loop
 * breaks, and this method returns false. Also remember that saving huge
 * Collections generate a number of queries equal to the number of Models
 * contained.
 * 
 * @see    ModelCollection::modified()
 * @param  Model $related_model 
 * @return boolean
 */
	public function save($related_model = null) {
		if ($this->modified()) {
			// $connection = ModelConnection::instance();
			$return     = true;
			
			// if ($connection->multiple_create() === true) {
			// 	$data = array();
			// 	foreach ($this->items as $model) {
			// 		$data[] = $model->data;
			// 	}
			// 	$connection
			// } else {
				foreach ($this->items as $model) {
					$return = $model->save($related_model);
					if ($return === false) {
						break;
					}
				}	
			// }
			
			return $return;
		}
	}

/**
 * Sets a relation type to this Collection and all the Models
 *
 * Loops through all the Models and sets their relation type. Returns the
 * current type.
 * 
 * @param  string $relation_type 
 * @return string
 */	
	public function relation_type($relation_type = null) {
		if ($relation_type !== null) {
			$this->relation_type = $relation_type;
			foreach ($this->items as $item) {
				$item->relation_type($relation_type);
			}
		}
		return $this->relation_type;
	}

/**
 * Is this a Collection of relations?
 *
 * @return boolean
 */
	public function relation() {
		if ($this->relation_type !== false) {
			return true;
		} else {
			return false;
		}
	}

/**
 * Checks if any of the Models are modified
 *
 * If any of the Models in this Collection is modified, the Collection counts
 * as being modified.
 * 
 * @return void
 */
	private function check_modified() {
		if ($this->modified === false) {
			foreach ($this->items as $model) {
				if ($model->modified() === true) {
					$this->modified = true;
					break;
				}
			}
		}
	}

/**
 * Checks if any of the Models exist in the database
 * 
 * If any of the Models in this Collection exists in database, the Collection
 * counts as existing.
 *
 * @return void
 */
	private function check_exists() {
		if ($this->exists === false) {
			foreach ($this->items as $model) {
				if ($model->exists() === true) {
					$this->exists = true;
					break;
				}
			}
		}
	}
}

?>