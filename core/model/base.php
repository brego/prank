<?php
/**
 * Baseclass for all models.
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
 * Baseclass for all models.
 *
 * All models extend this base class. Contains methods basic CRUD, but also 
 * advnced finders and assotiations.
 *
 * @package    Prank
 * @subpackage Model
 */
class ModelBase extends Object {
	private $table                     = null;
	private $model                     = null;
	private $connection                = null;
	private $columns                   = null;
	private $data                      = array();
	private $relational_data           = array();
	private $relations_loaded          = false;
	private $relations                 = array();
	private $hollow                    = true;
	private $exists                    = false;
/**                                    
 * If this model is lazy-loaded, contains a callable loader function
 *
 * @var mixed                          
 */                                    
	private $loader                    = null; 
/**
 * Describes the one-to-many relationships
 *
 * String or array of names of models this model have many of. This property
 * will contain a Collection with all the coresponding objects.
 * 
 * @var mixed
 */
	protected $has_many                = false;
/**
 * Describes the one-to-one local relationships
 *
 * String or array of names of models this model has one of. This property
 * will contain the coresponding model.
 * 
 * @var mixed
 */
	protected $has_one                 = false;
/**
 * Describes the one-to-one foreign relationships
 *
 * String or array of names of models this model belongs to. This property
 * will contain the coresponding model.
 * 
 * @var mixed
 */
	protected $belongs_to              = false;
/**
 * Describes the many-to-many relationships
 *
 * String or array of names of models this model have many of. This property
 * will contain a Collection with all the coresponding objects.
 * 
 * @var mixed
 */
	protected $has_and_belongs_to_many = false;

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
		$this->model      = get_called_class();
		$this->connection = ModelConnection::instance();
		$this->columns    = $this->connection->columns($this->table);
		
		if ($data !== null && isset($data['id'])) {
			$this->exists = true;
		}
		
		foreach ($this->columns as $column) {
			if (isset($data[$column])) {
				$this->$column = $data[$column];
			}
		}
		$this->setup_relational_properities();
	}

	public function setup_relational_properities() {
		$types = array('has_many', 'has_one', 'belongs_to', 'has_and_belongs_to_many');
		foreach ($types as $type) {
			if ($this->$type !== false) {
				if (is_array($this->$type) === false) {
					$this->$type = array($this->$type);
				}	
				$this->relations = array_merge($this->relations, $this->$type);
			}
		}
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
	}

/**
 * Loads the relations
 * 
 * Uses ModelRelations class to load all the relations corresponding to this
 * model - but without loading the data (sets lazyloads on collections/models).
 *
 * @return void
 */
	private function load_relations() {
		if ($this->exists === true && $this->relations_loaded === false) {
			$relations = array(
				'has_many' => $this->has_many,
				'has_one'  => $this->has_one,
				'belongs_to' => $this->belongs_to,
				'has_and_belongs_to_many' => $this->has_and_belongs_to_many);
			$this->relational_data  = ModelRelations::load($this, $this->data, $relations);
			$this->relations_loaded = true;
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
 * Does this Model exist in the table?
 *
 * @return boolean
 */
	public function exists() {
		return $this->exists;
	}

/**
 * Name of the table represented by this model
 *
 * @return string
 */
	public function table() {
		return $this->table;
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
		if ($this->exists === true) {
			if ($this->connection->is_column_of('updated_at', $this->table)) {
				$this->updated_at = $this->connection->now();
			}
			$this->connection->update($this->table, $this->data, 'id='.$this->id);
			$this->exists = true;
		} else {
			if ($this->connection->is_column_of('created_at', $this->table)) {
				$this->created_at = $this->connection->now();
			}
			$this->connection->insert($this->table, $this->data);
			$this->id     = $this->connection->last_insert_id();
			$this->exists = true;
		}
	}


/**
 * Method overloading
 * 
 * Supports dynamic methods:
 *  - delete() - deletes current model from the database, returns number of
 *    affected columns or false.
 * 
 * Also calls all the mixins (see the Object class).
 *
 * If an unknown method is called, an exception is thrown.
 * 
 * @param  string $method 
 * @param  string $arguments 
 * @return mixed
 */
	public function __call($method, $arguments) {
		if ($method === 'delete' && $this->exists === true) {
			return $this->connection->delete($this->table, 'id='.$this->data['id']);
		} else {
			return $this->register_extensions($method, $arguments);
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
 * @return mixed
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
			return self::register_static_extensions($method, $arguments);
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
		} elseif (array_search($variable, $this->relations) !== false) {
			$this->relational_data[$variable] = $value;
		} else {
			throw new Exception('Property '.$variable.' is not overloadable');
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
		} elseif (array_search($variable, $this->relations) !== false) {
			$this->load_relations();
			if (isset($this->relational_data[$variable])) {
				return $this->relational_data[$variable];
			}
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
		if (isset($this->data[$variable])) {
			return true;
		} elseif (array_search($variable, $this->relations) !== false) {
			$this->load_relations();
			if (isset($this->relational_data[$variable])) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}

?>