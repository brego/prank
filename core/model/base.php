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
	private $relation_type             = false;
/**
 * Is this model modified (has any data been set)
 *
 * @var boolean
 */
	private $modified                  = false;
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
		
		if ($data !== null) {
			$this->hollow = false;
			if (isset($data['id'])) {
				$this->exists = true;
			}
		}
		
		foreach ($this->columns as $column) {
			if (isset($data[$column])) {
				$this->data[$column] = $data[$column];
			}
		}
		
		foreach (array('has_many', 'has_one', 'belongs_to', 'has_and_belongs_to_many') as $type) {
			if ($this->$type !== false) {
				if (is_array($this->$type) === false) {
					$this->$type = array($this->$type);
				}
				foreach ($this->$type as $name) {
					$this->relations[$name] = $type;
				}
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
			$loaded = ModelRelations::load($this, $this->data, $relations);
			$this->relational_data  = array_merge($loaded, $this->relational_data);
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
 * Is this model modified - does it need to be saved
 *
 * @return boolean
 */
	public function modified() {
		return $this->modified;
	}

/**
 * What type of a relation is this model
 *
 * @param  string $relation_type 
 * @return string
 */
	public function relation_type($relation_type = null) {
		if ($relation_type !== null) {
			$this->relation_type = $relation_type;
		}
		return $this->relation_type;
	}
	
/**
 * Is this model a relation
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
 * Saves current Model in the table
 * 
 * If the Model represents a new data set, it will be added as a new row to the
 * table. If, on the other hand it represents an existing row, the existing row
 * will be updated. Upon insertion, the id, created_at and updated_at fields
 * get filled. Upon update, only the updated_at field gets filled.
 * 
 * If insertion/update is succesfull, the exists property is set to true, and
 * the modified property is set to false - so you need to modify the model
 * again to be able to perform a save.
 *
 * @return boolean
 */
	public function save($related_model = null) {
		$result = true;
		
		if ($this->exists === true && $this->modified === true) {
			if ($this->connection->is_column_of('updated_at', $this->table)) {
				$this->updated_at = $this->connection->now();
			}
		
			if ($related_model !== null && $this->relation() === true) {
				$method = $this->relation_type().'_update';
				$result = $this->connection->$method($this->table, $this->data, $related_model);
			} else {
				$result = $this->connection->update($this->table, $this->data, 'id='.$this->id);
			}
		} elseif ($this->modified === true) {
			if ($this->connection->is_column_of('created_at', $this->table)) {
				$this->created_at = $this->connection->now();
			}
			if ($this->connection->is_column_of('updated_at', $this->table)) {
				$this->updated_at = $this->connection->now();
			}
		
			if ($related_model !== null && $this->relation() === true) {
				$method = $this->relation_type().'_insert';
				$result = $this->connection->$method($this->table, $this->data, $related_model);
			} else {
				$result = $this->connection->insert($this->table, $this->data);
			}
		
			$this->id = $this->connection->last_insert_id();
		}
		
		foreach ($this->relations as $relation => $type) {
			if (isset($this->relational_data[$relation])) {
				if ($this->relational_data[$relation]->modified() === true) {
					$result = $this->relational_data[$relation]->save($this);
				}	
			}
		}
		
		if ($result !== false) {
			$this->exists   = true;
			$this->modified = false;
			$result = true;
		}
		
		return $result;
	}

/**
 * Method overloading
 * 
 * Supports dynamic methods:
 *  - delete() - deletes current model from the database, returns boolean true
 *    or false. Upon succesfull deletion the exists property is set to false,
 *    and the modified property to true - no data is deleted from the model
 *    though.
 * 
 * Also calls all the mixins (see the Object class).
 * If an unknown method is called, an exception is thrown.
 * 
 * @param  string $method 
 * @param  string $arguments 
 * @return mixed
 */
	public function __call($method, $arguments) {
		if ($method === 'delete' && $this->exists === true) {
			$result = $this->connection->delete($this->table, 'id='.$this->data['id']);
			if ($result !== false) {
				$this->exists   = false;
				$this->modified = true;
				$result         = true;
			}
			return $result;
		} else {
			return parent::__call($method, $arguments);
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
 * Also calls all the mixins (see the Object class).
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
			return parent::__callStatic($method, $arguments);
		}
	}

/**
 * Property overloading - setter
 * 
 * Sets value of a Model field to the specified value - if the field exists.
 * Setting a field this way makes the model non-hollow.
 * 
 * You can set a value for a relation here - but be aware that in the event of
 * saving, the model will overwrite the relation in the database with the
 * provided one. Also, be aware that if you simply want to add an object to a
 * Collection representing a plural relation, you can use Collection's add
 * method.
 *
 * @param  string $variable 
 * @param  string $value
 * @return void
 */	
	public function __set($variable, $value) {
		if ($this->connection->is_column_of($variable, $this->table)) {
			$this->hollow          = false;
			$this->modified        = true;
			$this->data[$variable] = $value;
		} elseif (array_search($variable, array_keys($this->relations)) !== false) {
			if ($this->relations[$variable] == 'has_one') {
				$id_name = Inflector::singularize($this->table).'_id';
				$value->$id_name = $this->id;
			}
			if ($this->relations[$variable] == 'belongs_to') {
				$id_name = Inflector::singularize($value->table()).'_id';
				$this->$id_name = $value->id;
			}
			$value->relation_type($this->relations[$variable]);
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
		} elseif (array_search($variable, array_keys($this->relations)) !== false) {
			$this->load_relations();
			if (isset($this->relational_data[$variable])) {
				return $this->relational_data[$variable];
			}
		} else {
			throw new Exception('Unknown property '.$variable);
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
		} elseif (array_search($variable, array_keys($this->relations)) !== false) {
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