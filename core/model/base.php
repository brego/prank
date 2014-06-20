<?php
/**
 * Base class for all models
 *
 * @filesource
 * @copyright  Copyright (c) 2008-2014, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk/ Prank's project page
 * @link       http://github.com/brego/prank/ Prank's Git repository
 * @package    Prank
 * @subpackage Model
 * @since      Prank 0.10
 * @version    Prank 0.75
 */

/**
 * Base class for all models
 *
 * All models extend this base class. Contains methods basic CRUD, but also
 * advnced finders and assotiations.
 *
 * @package    Prank
 * @subpackage Model
 */
class ModelBase extends Object implements Serializable, Iterator, Countable {
/**
* Describes the one-to-many relationships
*
* String or array of names of models this model have many of. This property
* will contain a Collection with all the coresponding objects.
*
* @var mixed
*/
	public    $has_many                = false;
/**
* Describes the one-to-one local relationships
*
* String or array of names of models this model has one of. This property
* will contain the coresponding model.
*
* @var mixed
*/
	public    $has_one                 = false;
/**
* Describes the one-to-one foreign relationships
*
* String or array of names of models this model belongs to. This property
* will contain the coresponding model.
*
* @var mixed
*/
	public    $belongs_to              = false;
/**
* Describes the many-to-many relationships
*
* String or array of names of models this model have many of. This property
* will contain a Collection with all the coresponding objects.
*
* @var mixed
*/
	public    $has_and_belongs_to_many = false;
	public    $filter_input            = true;
	public    $escape_output           = true;
	protected $connection              = null;
	protected $hollow                  = true;
	protected $exists                  = false;
	private   $table                   = null;
	private   $model                   = null;
	private   $columns                 = null;
	private   $data                    = array();
	private   $relational_data         = array();
	private   $relations_loaded        = false;
	private   $relations               = array();
	private   $validations             = array();
	private   $relation_type           = false;
	private   $errors                  = array();
	protected $session                 = false;
	private   $key                     = false;

/**
 * Is this model modified (has any data been set)
 *
 * @var boolean
 */
	private   $modified                = false;
/**
 * If this model is lazy-loaded, contains a callable loader function
 *
 * @var mixed                         
 */
	private   $loader                  = null;

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
		$this->model      = get_called_class();
		$this->table      = to_table($this->model);
		$this->connection = ModelConnection::instance();
		$this->columns    = $this->connection->columns($this->table);
		$this->session    = Session::instance();

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
 * Ties in the validator methods.
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
		} elseif (substr($method, 0, 9) === 'validate_') {
			if (method_exists('ModelValidator', $method)) {
				array_unshift($arguments, $this);
				return call_user_func_array(array('ModelValidator', $method), $arguments);
			}
		} else {
			return parent::__call($method, $arguments);
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
			$value = $this->filter_input($variable, $value);
			
			$this->load();
			$this->hollow          = false;
			$this->modified        = true;
			$this->data[$variable] = $value;
		} elseif (array_search($variable, array_keys($this->relations)) !== false) {
			$this->load_relations();
			if ($this->relations[$variable] == 'has_many') {
				if (is_a($value, 'ModelCollection') !== true) {
					$value = new ModelCollection($value);
				}	
				$value->relation_type('has_many');
			}
			if ($this->relations[$variable] == 'has_one' && $this->exists() === true) {
				$id_name = singularize($this->table).'_id';
				$value->$id_name = $this->id;
				$value->relation_type('has_one');
			}
			if ($this->relations[$variable] == 'belongs_to' && $value->exists() === true) {
				$id_name = singularize($value->table()).'_id';
				$this->$id_name = $value->id;
				$value->relation_type('belongs_to');
			}
			if ($this->relations[$variable] == 'has_and_belongs_to_many') {
				if (is_a($value, 'ModelCollection') !== true) {
					$value = new ModelCollection($value);
				}	
				$value->relation_type('has_and_belongs_to_many');
			}
			$value->relation_type($this->relations[$variable]);
			$this->relational_data[$variable] = $value;
			// var_dump("Nope", $variable, $value->exists());
			// if ($this->relations[$variable] == 'belongs_to' && $value->exists() === true) {
			// 	$id_name = singularize($value->table()).'_id';
			// 	var_dump($this->$id_name);
			// }
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
			return $this->escape_output($variable, $this->data[$variable]);
		} elseif (array_search($variable, array_keys($this->relations)) !== false) {
			$this->load_relations();
			if (isset($this->relational_data[$variable])) {
				return $this->relational_data[$variable];
			} else {
				throw new Exception('A relation was not loaded? Name is '.$variable.' in model '.get_class($this).'...');
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
 * Dynamic static methods go here.
 *
 * Supported methods (where name and email are examples of column names):
 * - find(id)
 * - find_by_name(name)
 * - find_by_name_and_order_by_email(name)
 * - find_by_name_and_email(name, email)
 * - find_all()
 * - find_all_and_order_by_name()
 * - find_all_and_order_by_name_and_email_desc()
 * - delete(id)
 *
 * Also calls all the mixins (see the Object class).
 * If an unknown method is called, an exception is thrown.
 *
 * @param  string $method
 * @param  string $arguments
 * @return mixed
 */
	public static function __callStatic($method, $arguments) {
		$connection = ModelConnection::instance();
		$model      = get_called_class();
		$table      = to_table($model);
		$order      = '';
		$limit      = '';


		if (substr($method, -6, 6) == '_limit') {
			$method = str_replace('_and_limit', '', $method);
			$limit = array_pop($arguments);
		}

		if (strpos($method, 'order_by') !== false) {
			$order = substr($method, strpos($method, 'order_by_')+9);
			$arglist = $order;
			if (strpos($order, '_and_') !== false) {
				$order = explode('_and_', $order);
				$order = array_map(function($item) {return str_replace('_desc', ' desc', $item);}, $order);
				$order = implode(', ', $order);
			} else {
				$order = str_replace('_desc', ' desc', $order);	
			}
			$method = str_replace('_and_order_by_'.$arglist, '', $method);
		}

		if ($method === 'find' && is_numeric($arguments[0]) === true) {
			$result = $connection->read($table, $model, "id='".$arguments[0]."'");
			if ($result === false) {
				return new ModelCollection;
			} else {
				return $result;
			}
		} elseif ($method === 'find_by_sql') {
			return $connection->query_to_model($arguments[0], $model);
		} elseif (substr($method, 0, 8) === 'find_by_') {
			$column = down(substr($method, 8));
			if (strpos($column, '_and_') !== false) {
				$columns = explode('_and_', $column);
				if ($connection->are_columns_of($columns, $table)) {
					$arguments = array_map(function($key, $item) {
						return $key."='".$item."'";
						}, $columns, $arguments);
					$condition = implode(' and ', $arguments);
					$result = $connection->read($table, $model, $condition, $order, $limit);
					if ($result === false) {
						return new ModelCollection;
					} else {
						return $result;
					}
				}
			} elseif ($connection->is_column_of($column, $table)) {
				$result = $connection->read($table, $model, '`'.$column."`='".$arguments[0]."'", $order, $limit);
				if ($result === false) {
					return new ModelCollection;
				} else {
					return $result;
				}
			}
		} elseif (substr($method, 0, 8) === 'find_all') {
			$found = $connection->read($table, $model, '', $order);

			if ($found === false) {
				$found = new ModelCollection;
			}

			if (is_a($found, 'ModelCollection') === false) {
				$found = new ModelCollection($found);
			}
			return $found;
		} elseif ($method === 'delete') {
			return $connection->delete($table, 'id='.$arguments[0]);
		} else {
			return parent::__callStatic($method, $arguments);
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
 * Returns a JSON representation of this models data
 *
 * @return string
 */
	public function to_json() {
		use_helper('json.php');
		return to_json($this->data);
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
 * columns of the table it represents), with all their details.
 *
 * @return array
 */
	public function fields() {
		return $this->columns;
	}

/**
 * Does this model has that field
 *
 * @param  string  $field
 * @return boolean
 */
	public function has_field($field) {
		return isset($this->columns[$field]);
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
 * Uses Adapter's filter_string to filter the input
 *
 * Unless the value is explicitly boolean, a string will be returned.
 *
 * @param  string $variable
 * @param  mixed  $value
 * @return mixed
 */
	private function filter_input($variable, $value) {
		if ($this->filter_input === true || in_array($variable, $this->filter_input) === true) {
			return $this->connection->filter_string($value);
		} else {
			return $value;
		}
	}

/**
 * Escapes output with htmlspecialchars
 *
 * @param  string $variable
 * @param  mixed  $value
 * @return mixed
 */
	private function escape_output($variable, $value) {
		if ($this->escape_output === true || in_array($variable, $this->escape_output) === true) {
			return htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8');
		} else {
			return $value;
		}
	}

/**
 * Validate the model
 *
 * If this model has a method called 'validate', model will be passed to
 * ModelValidator::validate() and the validations will be checked, and boolean
 * result returned. Otherwise, returns true.
 *
 * @return boolean
 */
	public function validates() {
		if (method_exists($this, 'validate') === true) {
			$result = ModelValidator::validate($this);
			if ($result === true) {
				$return = true;
			} else {
				$this->errors = $result;
				$return = false;
			}
			return $return;
		} else {
			return true;
		}
	}

/**
 * Retruns the list of errors of validation of this model
 *
 * If no errors were found, or Model::valid() has not yet ben run, returns an
 * empty array. Else returns an array errors.
 *
 * @return array
 */
	public function errors() {
		return $this->errors;
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
			$this->hollow   = false;
			$this->modified = true;
			if (isset($data['id'])) {
				$this->exists = true;
			}
			foreach ($this->columns as $column => $description) {
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
 * This will effectively load the data into this Model.
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
					'model'   => to_model($relation),
					'local'   => $this->table(),
					'foreign' => to_table($relation),
					'id'      => $this->data['id'],
					'type'    => $relation_type);
				$config['local_id']   = singularize($config['local']).'_id';
				$config['foreign_id'] = singularize($config['foreign']).'_id';
				$config['join']       = implode('_', s($config['local'], $config['foreign']));
				if ($relation_type == 'belongs_to') {
					if (isset($this->data[$config['foreign_id']])) {
						$config['id'] = $this->data[$config['foreign_id']];
					} else {
						$config['id'] = null;
					}
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
			
		} elseif ($this->exists === false && $this->relations_loaded === false) {
			foreach ($this->relations as $relation => $relation_type) {
				if ($relation_type == 'has_many' || $relation_type == 'has_and_belongs_to_many') {
					$this->relational_data[$relation] = new ModelCollection;
					$this->relational_data[$relation]->relation_type($relation_type);
				} else {
					$model = to_model($relation);
					$this->relational_data[$relation] = new $model;
					$this->relational_data[$relation]->relation_type($relation_type);
				}
			}
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
		if ($this->validates() === true) {
			foreach ($this->belongs_to as $type => $relation) {
				if (isset($this->relational_data[$relation])) {
					if ($this->relational_data[$relation]->modified() === true) {
						if ($this->relational_data[$relation]->validates() === true) {
							$result = $this->relational_data[$relation]->save($this);
						}
					}
				}
			}
			
			if ($this->exists === true && $this->modified === true) {
				if ($this->connection->is_column_of('updated_at', $this->table)) {
					$this->updated_at = $this->connection->now();
				}
				$result = $this->connection->update($this->table, $this->data, 'id='.$this->id);
				
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
			
			if ($related_model !== null && isset($related_model->id) && isset($this->id)) {
				if ($this->relation_type === 'has_and_belongs_to_many') {
					$relation_table  = implode('_', s($related_model->table(), $this->table()));

					$local          = singularize($this->table()).'_id';
					$local_id       = $this->id;
					$foreign        = singularize($related_model->table()).'_id';
					$foreign_id     = $related_model->id;

					$query = "select * from `$relation_table` where `$foreign`='$foreign_id' and `$local`='$local_id';";
					$relation_exists = $this->connection->query($query);

					if ($relation_exists->rowCount() === 0) {
						$query = "insert into `$relation_table` set `$foreign`='$foreign_id', `$local`='$local_id';";
						$return = $this->connection->exec($query);
					}
				}
			}
			
		
			foreach (array_merge($this->has_one, $this->has_many, $this->has_and_belongs_to_many) as $type => $relation) {
				if (isset($this->relational_data[$relation])) {
					if ($this->relational_data[$relation]->modified() === true) {
						if ($this->relational_data[$relation]->validates() === true) {
							$result = $this->relational_data[$relation]->save($this);
						}
					}	
				}
			}
		
			if ($result !== false) {
				$this->exists   = true;
				$this->modified = false;
				$result         = true;
			}
		} else {
			$result = false;
		}
		return $result;
	}

/**
 * The Serializable interface
 *
 * @return string Serialized representation of a Model.
 */
	public function serialize() {
		$properties = array(
			'model'                   => $this->model,
			'table'                   => $this->table,
			'columns'                 => $this->columns,
			'data'                    => $this->data,
			'relational_data'         => $this->relational_data,
			'has_one'                 => $this->has_one,
			'has_many'                => $this->has_many,
			'belongs_to'              => $this->belongs_to,
			'has_and_belongs_to_many' => $this->has_and_belongs_to_many,
			'modified'                => $this->modified,
			'loader'                  => $this->loader,
			'filter_input'            => $this->filter_input,
			'escape_output'           => $this->escape_output,
			'hollow'                  => $this->hollow,
			'relations_loaded'        => $this->relations_loaded,
			'relations'               => $this->relations,
			'validations'             => $this->validations,
			'relation_type'           => $this->relation_type,
			'errors'                  => $this->errors);
		
		return serialize($properties);
	}
	
/**
 * The Serializable interface
 *
 * @param  string $data Serialized representation of a Model
 * @return void
 */
	public function unserialize($data) {
		$properties = unserialize($data);
		foreach($properties as $property => $value) {
			$this->$property = $value;
		}
		$this->connection = ModelConnection::instance();
	}	
	
/**
 * The Iterator interface
 *
 * This is just a mask, for seemless useage between this and a ModelCollection.
 */
	public function current() {
		return $this;
	}

	public function key() {
		return $this->key;
	}

	public function next() {
		$this->key++;
	}	

	public function rewind() {
		$this->key = 0;
	}

	public function valid() {
		if ($this->key >= 1) {
			return false;
		} else {
			return true;
		}
	}

/**
 * The Countable interface
 *
 * This is just a mask, for seemless useage between this and a ModelCollection.
 */
	public function count() {
		return 1;
	}
}

?>