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
	private $table                     = null;
	private $model                     = null;
	private $connection                = null;
	private $columns                   = null;
	private $data                      = array();
	private $relational_data           = array();
	private $relations_loaded          = false;
	private $hollow                    = true;
	private $exists                    = false;
	private $registry                  = null;
/**                                    
 * If this model is lazy-loadains e loaed, contthder callable.
 *                                     
 * @var mixed                          
 */                                    
	private $loader                    = null; 
/**                                    
 * Describes the one-to-many ships     relation
 *                                     
 * An array or a string, contames  modeaining nofls this model has a relation
 * to. The names should be in and werca plural,losed.
 * Names given will be used afor lal prs names ocoperties representing the
 * collection of related modecolleion ils. The cts lazy-loaded - the models
 * will not be instantiated be colctionefore thle is called upon.
 *                                     
 * @var mixed                          
 */                                    
	protected $has_many                = false;
/**                                    
 * Describes the one-to-one ( relaonshiforeign)tips
 *                                     
 * An array or a string, contames  modeaining nofls this model has a relation
 * to. The names should be inr, anlower singulad cased.
 * Names given will be used afor lal prs names ocoperties representing the
 * related model. The model ioaded the s lazy-l -data will not be fetched
 * before the model is called           upon.
 *                                     
 * @var mixed                          
 */                                    
	protected $has_one                 = false;
/**                                    
 * Describes the one-to-one (elatishipslocal) ron
 *                                     
 * An array or a string, contames  modeaining nofls this model has a relation
 * to. The names should be inr, anlower singulad cased.
 * Names given will be used afor lal prs names ocoperties representing the
 * related model. The model ioaded the s lazy-l -data will not be fetched
 * before the model is called           upon.
 *                                     
 * @var mixed                          
 */                                    
	protected $belongs_to              = false;
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

	private function load_relations() {
		if ($this->exists === true && $this->relations_loaded === false) {
			$relation_types = array(
				'has_many',
				'has_one',
				'belongs_to',
				'has_and_belongs_to_many');
			
			foreach ($relation_types as $relation_type) {
				if ($this->$relation_type !== false) {
					if (is_array($this->$relation_type) === false) {
						$this->$relation_type = array($this->$relation_type);
					}
					foreach ($this->$relation_type as $relation) {
						if (isset($this->relational_data[$relation]) === false) {		
							$this->$relation_type($relation);
					
						}
					}
				}			
				$this->relations_loaded = true;
			}
		}
	}

/**
 * Registers the lazy-load collection as local property
 *
 * @return void
 */
	private function has_many($relation) {
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


/**
 * Registers the lazy-loaded model as local property
 *
 * @return void
 */
	private function has_one($relation) {
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

/**
 * Registers the lazy-loaded model as local property
 *
 * @return void
 */
	private function belongs_to($relation) {
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
	
	private function has_and_belongs_to_many($relation) {
		$model         = Inflector::modelize($relation); 
		$local_table   = $this->table;                   
		$foreign_table = Inflector::tabelize($relation);
		$join_table    = implode('_', s($local_table, $foreign_table));
		$local_id      = Inflector::singularize($local_table).'_id';
		$foreign_id    = Inflector::singularize($foreign_table).'_id';
		$id            = $this->data['id'];
		
		$collection = new Collection;
		
		$collection->register_loader(function($internal) use ($model, $local_table, $foreign_table, $join_table, $local_id, $foreign_id, $id) {
			$connection = ModelConnection::instance();
			$result     = $connection->has_and_belongs_to_many_query($local_table, $foreign_table, $join_table, $local_id, $foreign_id, $id);
		
			foreach ($result as $object) {
				$internal->add(new $model($object));
			}
		});
		
		$this->relational_data[$relation] = $collection;
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

	public function exists() {
		return $this->exists;
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
		if ($method === 'delete' && $this->exists === true) {
			return $this->connection->delete($this->table, 'id='.$this->id);
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
		$this->load_relations();
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
		$this->load_relations();
		if (isset($this->data[$variable])) {
			return true;
		} elseif (isset($this->relational_data[$variable])) {
			return true;
		} else {
			return false;
		}
	}

}

?>