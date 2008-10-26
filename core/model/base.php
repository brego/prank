<?php
/**
 * Baseclass for all models
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
 * Baseclass for all models
 *
 * All models extend this base class. Contains methods basic CRUD, but also 
 * advnced finders and assotiations.
 *
 * @package    Prank
 * @subpackage Model
 */
class ModelBase extends Object {
	private   $table                   = null;
	private   $model                   = null;
	private   $connection              = null;
	private   $columns                 = null;
	private   $data                    = array();
	private   $relational_data         = array();
	private   $relations_loaded        = false;
	private   $relations               = array();
	protected $hollow                  = true;
	protected $exists                  = false;  
	private   $relation_type           = false;
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
	public function __construct($data = false) {
		$this->table      = Inflector::tabelize(get_called_class());
		$this->model      = get_called_class();
		$this->connection = ModelConnection::instance();
		$this->columns    = $this->connection->columns($this->table);
		
		$this->set_data($data);
		
		$this->setup_relation('has_many');
		$this->setup_relation('has_one');
		$this->setup_relation('belongs_to');
		$this->setup_relation('has_and_belongs_to_many');
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
			if ($this->relations[$variable] == 'has_many') {
				if (is_a($value, 'ModelCollection') !== true) {
					$value = new ModelCollection($value);
					$value->relation_type('has_many');
				}
			}
			if ($this->relations[$variable] == 'has_one' && $this->exists() === true) {
				$id_name = Inflector::singularize($this->table).'_id';
				$value->$id_name = $this->id;
				$value->relation_type('has_one');
			}
			if ($this->relations[$variable] == 'belongs_to' && $this->exists() === true) {
				$id_name = Inflector::singularize($value->table()).'_id';
				$this->$id_name = $value->id;
				$value->relation_type('belongs_to');
			}
			if ($this->relations[$variable] == 'has_and_belongs_to_many') {
				if (is_a($value, 'ModelCollection') !== true) {
					$value = new ModelCollection($value);
					$value->relation_type('has_many');
				}
			}
			$value->relation_type($this->relations[$variable]);
			$this->relational_data[$variable] = $value;
		} else {
			throw new ModelExceptionsUnknownproperty($variable, $this);
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
			throw new ModelExceptionsUnknownproperty($variable, $this);
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

/**
 * Is the Model empty?
 *
 * Triggers lazyload.
 * 
 * @return boolean
 */
	public function hollow() {
		$this->load();
		return $this->hollow;
	}

/**
 * Does this Model exist in the table?
 *
 * Triggers lazyload.
 *
 * @return boolean
 */
	public function exists() {
		$this->load();
		return $this->exists;
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
 * Returns an array of this model's data
 *
 * @return array
 */
	public function data() {
		return $this->data;
	}

/**
 * Has the lazyload run?
 *
 * Be aware that if this model is not lazyloaded, this will return false.
 * 
 * @return boolean
 */
	public function loaded() {
		if ($this->loader === null) {
			return false;
		} else {
			return true;
		}
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
 * Name of the table represented by this model
 *
 * @return string
 */
	public function table() {
		return $this->table;
	}

/**
 * What type of a relation is this model?
 *
 * Provide a type to set it (intended for internal use).
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
 * Is this model a relation?
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
 * Sets the data of this model
 *
 * Sets the given data of this Model. It will only work if the model has no
 * data yet. Sets the hollowness and existance accordingly (see
 * ModelBase::hollow() and ModelBase::exists()).
 * 
 * @param  array   $data
 * @return boolean
 */	
	public function set_data($data) {
		if (is_array($data) === true && empty($this->data) === true) {
			$this->hollow = false;
			if (isset($data['id'])) {
				$this->exists = true;
			}
			foreach ($this->columns as $column) {
				if (isset($data[$column])) {
					$this->data[$column] = $data[$column];
				}
			}
			return true;
		} else {
			return false;
		}
	}
	
/**
 * Sets up the relations table of given relation type
 *
 * @param  string $type 
 * @return void
 */
	private function setup_relation($type) {
		if ($this->$type !== false) {
			if (is_array($this->$type) === false) {
				$this->relations[$this->$type] = $type;
				$this->$type                   = array($this->$type);
			} else {
				foreach ($this->$type as $name) {
					$this->relations[$name] = $type;
				}
			}
		} else {
			$this->$type = array();
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
 * Be aware of the fact that any existing relation data will not be overwritten
 * by the loaded data!
 *
 * @return void
 */
	private function load_relations() {
		if ($this->exists === true && $this->relations_loaded === false) {
			
			$relational_data = array();
			
			foreach ($this->relations as $relation => $relation_type) {
				$config = array(
					'model'   => Inflector::modelize($relation),
					'local'   => $this->table(),
					'foreign' => Inflector::tabelize($relation),
					'id'      => $this->data['id'],
					'type'    => $relation_type);
				$config['local_id']   = Inflector::singularize($config['local']).'_id';
				$config['foreign_id'] = Inflector::singularize($config['foreign']).'_id';
				$config['join']       = implode('_', s($config['local'], $config['foreign']));
				if ($relation_type == 'belongs_to') {
					$config['id'] = $this->data[$config['foreign_id']];
				}
				
				if ($relation_type == 'has_many' || $relation_type == 'has_and_belongs_to_many') {
					$output = new ModelCollection;
				} else {
					$output = new $config['model'];
				}

				$output->relation_type($relation_type);

				$output->register_loader(function($internal) use($config) {
					$connection = ModelConnection::instance();
					$method     = $config['type'].'_read';
					$result     = $connection->$method($config);
					if ($config['type'] == 'has_many' || $config['type'] == 'has_and_belongs_to_many') {
						foreach ($result as $object) {
							$internal->add(new $config['model']($object));
						}
					} else {
						$internal->set_data($result);
					}
				});

				$relational_data[$relation] = $output;
			}
			
			$this->relational_data  = array_merge($relational_data, $this->relational_data);
			$this->relations_loaded = true;
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
		
		foreach ($this->belongs_to as $type => $relation) {
			if (isset($this->relational_data[$relation])) {
				if ($this->relational_data[$relation]->modified() === true) {
					$result = $this->relational_data[$relation]->save($this);
				}	
			}
		}
		
		if ($this->exists === true && $this->modified === true) {
			if ($this->connection->is_column_of('updated_at', $this->table)) {
				$this->updated_at = $this->connection->now();
			}
		
			if ($related_model !== null && $this->relation() === true) {
				$method = $this->relation_type.'_update';
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
				$method = $this->relation_type.'_create';
				$result = $this->connection->$method($this->table, $this->data, $related_model);
			} else {
				$result = $this->connection->create($this->table, $this->data);
			}
		
			$this->id = $this->connection->last_id();
		}
		
		foreach (array_merge($this->has_one, $this->has_many, $this->has_and_belongs_to_many) as $type => $relation) {
			if (isset($this->relational_data[$relation])) {
				if ($this->relational_data[$relation]->modified() === true) {
					$result = $this->relational_data[$relation]->save($this);
				}	
			}
		}
		
		if ($result !== false) {
			$this->exists   = true;
			$this->modified = false;
			$result         = true;
		}
		
		return $result;
	}
}

?>