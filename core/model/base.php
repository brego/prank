<?php
/**
 * Baseclass for all models.
 *
 * All models extend this base class. Contains methods basic CRUD, but also 
 * advnced finders and, in the future, assotiations.
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

class ModelBase extends Object {
	private $table           = null;
	private $connection      = null;
	private $columns         = null;
	private $data            = array();
	private $relational_data = array();
	private $relationals_loaded = false;
	private $hollow          = true;
	private $exists_in_table = false;
	private $registry        = null;
/**
 * If this model is lazy-loaded, contains the loader callable.
 *
 * @var mixed
 */
	private $loader          = null;	
/**
 * Describes the one-to-many relationships
 * 
 * An array or a string, containing names of models this model has a relation
 * to. The names should be in plural, and lowercased.
 * Names given will be used as names for local properties representing the
 * collection of related models. The collection is lazy-loaded - the models
 * will not be instantiated before the collection is called upon.
 *
 * @var mixed
 */
	protected $has_many      = false;
/**
 * Describes the one-to-one (foreign) relationships
 *
 * An array or a string, containing names of models this model has a relation
 * to. The names should be in singular, and lowercased.
 * Names given will be used as names for local properties representing the
 * related model. The model is lazy-loaded - the data will not be fetched
 * before the model is called upon.
 * 
 * @var mixed
 */
	protected $has_one       = false;
/**
 * Describes the one-to-one (local) relationships
 *
 * An array or a string, containing names of models this model has a relation
 * to. The names should be in singular, and lowercased.
 * Names given will be used as names for local properties representing the
 * related model. The model is lazy-loaded - the data will not be fetched
 * before the model is called upon.
 * 
 * @var mixed
 */
	protected $belongs_to    = false;

/**
 * Constructor
 * 
 * Finds the name of the class, and uses it to identify the table it coresponds
 * to. Connects to Connection, to get DB access. Creates properities simulating
 * columns of the table.
 * If $data is provided, it's expected to be an array of key-value pairs with
 * keys corresponding to the column names. The $data will be used as initial
 * values for the Model.
 * Be cautious though - if you provide $data and you include a key named 'id'
 * in it, the Model will assume that this is a representation of an existing
 * database row.
 *
 * @param  string $data 
 * @return void
 */	
	public function __construct($data = null) {
		$this->table      = Inflector::tabelize(get_called_class());
		$this->connection = ModelConnection::instance();
		$this->columns    = $this->connection->columns($this->table);
		$this->registry   = ModelRegistry::instance();
		
		// print 'new model '.get_called_class()."\n";
		
		if ($data !== null && isset($data['id'])) {
			$this->exists_in_table = true;
		}
		
		foreach ($this->columns as $column) {
			if (isset($data[$column])) {
				$this->$column = $data[$column];
			}
		}
		
		// if ($this->exists_in_table === true) {
		// 	$model = strtolower(get_called_class());
		// 	$this->registry->register($model, $this->id, &$this);
		// }
		
		// $this->has_many();
		// $this->belongs_to();
		// $this->has_one();
	}


/**
 * Registers a lazyload function
 * 
 * Function is expected to be a callable (lambda), accepting one parameter,
 * which will be used to pass reference to the model. Loader function will
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
	private function load() {
		if ($this->loader !== null) {
			$loader = $this->loader;
			$loader($this);
			$this->loader = null;
		}
		if ($this->relationals_loaded === false) {
			$this->has_many();
			$this->has_one();
			$this->belongs_to();
			$this->relationals_loaded = true;
		}
	}

/**
 * Registers the lazy-load collection as local property
 *
 * @return void
 */
	private function has_many() {
		if ($this->has_many !== false && $this->exists_in_table === true) {
			if (is_array($this->has_many) === false) {
				$this->has_many = array($this->has_many);
			}
			foreach ($this->has_many as $relation) {
				if (isset($this->relational_data[$relation]) === false) {
					$model      = Inflector::modelize($relation);
					$table      = Inflector::tabelize($relation);
					$id_name    = Inflector::singularize($this->table).'_id';
					$id         = $this->data['id'];
					$collection = new Collection;
				
					$collection->register_loader(function($internal) use ($model, $table, $id_name, $id) {
						$connection = ModelConnection::instance();
						$result     = $connection->has_many_query($table, $id_name, $id);
					
						foreach ($result as $object) {
							$internal->add(new $model($object));
						}
					});
				
					$this->relational_data[$relation] = $collection;
				}
			}
		}
	}


/**
 * Registers the lazy-loaded model as local property
 *
 * @return void
 */
	private function has_one() {
		if ($this->has_one !== false && $this->exists_in_table === true) {
			if (is_array($this->has_one) === false) {
				$this->has_one = array($this->has_one);
			}
			foreach ($this->has_one as $relation) {
				if (isset($this->relational_data[$relation]) === false) {
					$model   = Inflector::modelize($relation);
					$table   = Inflector::tabelize($relation);
					$id_name = Inflector::singularize($this->table).'_id';
				 	$id      = $this->data['id'];
					$model   = new $model;
					
					$model->register_loader(function($internal) use ($table, $id_name, $id) {
						$connection = ModelConnection::instance();
						$result     = $connection->has_one_query($table, $id_name, $id);
						$result     = $result->fetch();
											
						foreach ($result as $variable => $value) {
							$internal->$variable = $value;
						}
					});
					
					$this->relational_data[$relation] = $model;
				}
			}
		}
	}

/**
 * Registers the lazy-loaded model as local property
 *
 * @return void
 */
	private function belongs_to() {
		if ($this->belongs_to !== false && $this->exists_in_table === true) {
			if (is_array($this->belongs_to) === false) {
				$this->belongs_to = array($this->belongs_to);
			}
			foreach ($this->belongs_to as $relation) {
				if (isset($this->relational_data[$relation]) === false) {
					$model   = Inflector::modelize($relation);
					$table   = Inflector::tabelize($relation);
					$id_name = Inflector::singularize($relation).'_id';
				 	$id      = $this->data[$id_name];
					$model   = new $model;
				
					$model->register_loader(function($internal) use ($table, $id) {
						$connection = ModelConnection::instance();
						$result     = $connection->belongs_to_query($table, $id);
						$result     = $result->fetch();					
					
						foreach ($result as $variable => $value) {
							$internal->$variable = $value;
						}
					});
				
					$this->relational_data[$relation] = $model;
				}
			}
		}
	}

/**
 * Is the Model empty?
 *
 * @return boolean
 */
	public function hollow() {
		return $this->hollow;
	}

/**
 * Returns fields of a Model 
 * 
 * Returns the names of the fields of this Model (which correspond to the
 * columns of the table it represents).
 *
 * @return array
 */
	public function fields() {
		return $this->columns;
	}

/**
 * Saves current Model in the table
 * 
 * If the Model represents a new data set, it will be added as a new row to the
 * table. If, on the other hand it represents an existing row, the existing row
 * will be updated. Upon insertion, the id field gets filled.
 *
 * @return void
 */
	public function save() {
		if ($this->exists_in_table === true) {
			if ($this->connection->is_column_of('updated_at', $this->table)) {
				$this->updated_at = $this->connection->now();
			}
			$this->connection->update($this->table, $this->data, 'id='.$this->id);
		} else {
			if ($this->connection->is_column_of('created_at', $this->table)) {
				$this->created_at = $this->connection->now();
			}
			$this->connection->insert($this->table, $this->data);
			$this->id = $this->connection->last_insert_id();
		}
	}

/**
 * Method overloading
 * 
 * Supports dynamic methods. Currently: delete (deletes current model from the
 * database).
 *
 * @param  string $method 
 * @param  string $arguments 
 * @return mixed
 */
	public function __call($method, $arguments) {
		if ($method === 'delete' && $this->exists_in_table === true) {
			return $this->connection->delete($this->table, 'id='.$this->id);
		} else {
			throw new Exception('Unknown method "'.$method.'" called.');
		}
	}

/**
 * Property overloading - setter
 * 
 * Sets value of a Model field to the specified value - if the field exists.
 * Setting a field this way makes the model non-hollow.
 *
 * @param  string $variable 
 * @param  string $value
 * @return void
 */	
	public function __set($variable, $value) {
		if ($this->connection->is_column_of($variable, $this->table)) {
			$this->hollow = false;
			$this->data[$variable] = $value;
		}
	}

/**
 * Property overloading - getter
 *
 * Returns value of the field. Be advised that __get calls on a model result in
 * the lazy-load being run (if applicable).
 * 
 * @param  string $variable 
 * @return void
 */
	public function __get($variable) {
		$this->load();
		if (isset($this->data[$variable])) {
			return $this->data[$variable];
		} elseif (isset($this->relational_data[$variable])) {
			return $this->relational_data[$variable];
		}
	}

/**
 * You can always check if a field is defined by isset
 *
 * Be advised that isset calls on a model result in the lazy-load being run (if
 * applicable).
 * 
 * @param  string $variable 
 * @return void
 */
	public function __isset($variable) {
		$this->load();
		if (isset($this->data[$variable]) || isset($this->relational_data[$variable])) {
			return true;
		} else {
			return false;
		}
	}

/**
 * Dynamic static methods go here.
 *
 * Supported methods:
 * - find_by_*(arg)
 * - find_all()
 * - delete(id)
 *
 * If an unknown method is called, an exception is thrown.
 * 
 * @todo   Implement more dynamic finders (multiple arguments etc)
 * @param  string $method
 * @param  string $arguments
 * @return void
 */
	public static function __callStatic($method, $arguments) {
		$connection = ModelConnection::instance();
		$model      = get_called_class();
		$table      = Inflector::tabelize(get_called_class());


		if (substr($method, 0, 8) == 'find_by_') {
			$column = down(substr($method, 8));
			if ($connection->is_column_of($column, $table)) {
				$query  = 'select * from `'.$table.'` where `'.$column."`='".$arguments[0]."';";
				return $connection->query_wrapped($query, $model);
			}
		} elseif ($method === 'find_all') {
			return $connection->query_wrapped('select * from `'.$table.'`;', $model);
		} elseif ($method === 'delete') {
			return $connection->delete($table, 'id='.$arguments[0]);
		} else {
			throw new Exception('Unknown method '.$method.' called.');
		}
	}
}

?>
