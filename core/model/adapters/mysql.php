<?php
/**
 * MySQL adapter
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
 * MySQL adapter
 *
 * Provides methods for basic interaction with MySQL databases. Extends upon
 * PDO base class.
 *
 * @package    Prank
 * @subpackage Model
 */
class ModelAdaptersMysql extends PDO implements ModelAdapter {
	private $columns    = [];
	private $data_types = [
		'binary'    => 'blob',
		'boolean'   => 'tinyint',
		'date'      => 'date',
		'datetime'  => 'datetime',
		'decimal'   => 'decimal',
		'float'     => 'float',
		'integer'   => 'int',
		'string'    => 'varchar',
		'text'      => 'text',
		'time'      => 'time'];
	private $default_data_sizes = [
		'boolean' => 1,
		'integer' => 11,
		'string'  => 255];
	private $query_log = [];
	private $database  = null;

/**
 * Extending the constructor
 *
 * Mostly to set the UTF-8 straight.
 *
 * @param  string $dsn
 * @param  string $user
 * @param  string $password
 * @param  string $database
 * @return void
 */
	public function __construct($dsn, $user, $password, $database) {
		parent::__construct($dsn, $user, $password);
		$this->database = $database;
		$this->exec('SET NAMES utf8;');
	}

/**
 * This Adapter supports multiple create statements.
 *
 * @return boolean
 */
	public function multiple_create() {
		return true;
	}

/**
 * Execute PDO::exec, with error detection
 *
 * @param  string $query
 * @return mixed
 */
	public function exec($query) {
		$this->query_log[] = $query;
		$result = parent::exec($query);
		if ($result === false) {
			$error = $this->errorInfo();
			throw new Exception('Database error: '.$error[2]."\nQuery: $query\n\n");
		}
		return $result;
	}

/**
 * Execute PDO::query, with error detection
 *
 * @return mixed
 */
	public function query() {
		$arguments         = func_get_args();
		$this->query_log[] = $arguments[0];
		$result            = parent::query($arguments[0]);
		if ($result === false) {
			$error = $this->errorInfo();
			throw new Exception('Database error: '.$error[2]."\nQuery: $arguments[0]\n\n");
		} else {
			unset($arguments[0]);
			call_user_func_array(array($result, 'setFetchMode'), $arguments);
		}
		return $result;
	}

/**
 * Execute a query, and push the results into a Model or a collection thereof.
 *
 * @param  string $query A standard SQL query
 * @param  string $model Name of the Model
 * @return mixed  A Model corresponding to $model, or a ModelCollection of models.
 */
	public function query_to_model($query, $model) {
		$result = $this->query($query, PDO::FETCH_ASSOC);
		if ($result->rowCount() == 1) {
			$result = new $model($result->fetch());
		} elseif ($result->rowCount() > 1) {
			$collection = new ModelCollection;
			foreach ($result as $row) {
				$collection->add(new $model($row));
			}
			$result = $collection;
		} else {
			$result = false;
		}
		return $result;
	}

/**
 * Prepares a data array for sql use
 *
 * Returns an array of column='value' items for sql insertion/update.
 *
 * @param  array $data
 * @return array
 */
	private function prepare_data($data) {
		$prepare_data = [];
		foreach ($data as $column => $value) {
			$prepared_data[] = '`' . $column . "` = " . parent::quote($value);
		}
		return $prepared_data;
	}

/**
 * Filters a string for security
 *
 * @param  string $value
 * @return mixed
 */
	public function filter_string($value) {
		if (is_bool($value) === false) {
			$value   = trim($value);
			$search  = ["\x00",  "\n",  "\r",  '\\',   "'",  '"',  "\x1a"];
			$replace = ["\\x00", "\\n", "\\r", '\\\\', "\'", '\"', "\\\\x1a"];
			$value   = str_replace($search, $replace, $value);
		}

		return $value;
	}

/**
 * Returns current time in database-specific format
 *
 * @return string
 */	
	public function now() {
		return date('Y-m-d H:i:s');
	}

/**
 * Returns last inserted id field
 *
 * @return integer
 */
	public function last_id() {
		return $this->lastInsertId();
	}

/**
 * Checks if $column belongs to $table
 *
 * @param  string  $column
 * @param  string  $table
 * @return boolean
 */	
	public function is_column_of($column, $table) {
		$this->columns($table);
		if (isset($this->columns[$table][$column]) === true) {
			return true;
		} else {
			return false;
		}
	}

/**
 * Fetches column informtion from the cache
 *
 * Tries to fetch column informtion from the cache, but calls
 * Mysql::fetch_schemas if there's no cache data for this table.
 *
 * @param  string $table
 * @return array  Columns belonging to the $table
 */
	public function columns($table) {
		if (count($this->columns) === 0) {
			$this->columns = $this->fetch_schemas();
		}
		return $this->columns[$table];
	}

/**
 * Fetch database schema for all tables in the current dabase
 *
 * Uses MySQL's information_schema.columns to find info about the columns.
 *
 * @return array Schema
 */
	public function fetch_schemas() {
		$database = $this->database;
		$types    = array_flip($this->data_types);
		$columns  = [];
		$query    = "SELECT
				TABLE_NAME     AS `table`,
				COLUMN_NAME    AS `column`,
				COLUMN_TYPE    AS `type`,
				IS_NULLABLE    AS `null`,
				COLUMN_DEFAULT AS `default`
			FROM
				information_schema.columns
			WHERE
				table_schema = '$database';";


		foreach ($this->query($query, PDO::FETCH_ASSOC) as $row) {
			$type  = explode('(', $row['type']);
			$limit = '';

			if (count($type) === 2) {
				$limit = (int)str_replace(')', '', $type[1]);
				$type  = $types[$type[0]];
			} else {
				$type = $types[$type[0]];
			}
			
			$columns[$row['table']][$row['column']]          = [];
			$columns[$row['table']][$row['column']]['type']  = $type;
			$columns[$row['table']][$row['column']]['limit'] = $limit;

			if ($row['null'] == 'NO') {
				$columns[$row['table']][$row['column']]['null'] = false;
			} else {
				$columns[$row['table']][$row['column']]['null'] = true;
			}

			if ($row['default'] == 'NULL') {
				$columns[$row['table']][$row['column']]['default'] = null;
			} else {
				$columns[$row['table']][$row['column']]['default'] = $row['default'];
			}
		}

		return $columns;
	}

/**
 * Inserts $data into the $table
 *
 * $data is supposed to be an array of key-value pairs with keys corresponding
 * to the table columns.
 *
 * @param  string $table
 * @param  string $data
 * @return mixed  Number of affected collumns, or false
 */
	public function create($table, $data) {
		return $this->exec('insert into '.$table.' set '.implode(', ', $this->prepare_data($data)).';');
	}

/**
 * Reads a from the table
 *
 * @param  string $table
 * @param  string $model
 * @param  string $condition
 * @param  string $order
 * @return mixed
 */
	public function read($table, $model, $condition = '', $order = '', $limit = '') {
		$query = 'select * from '.$table;
		if ($condition !== '') {
			$query .=' where '.$condition;
		}
		if ($order !== '') {
			$query .=' order by '.$order;
		}
		if ($limit !== '') {
			$query .=' limit '.$limit;
		}
		$query .= ';';
		$result = $this->query($query, PDO::FETCH_ASSOC);
		if ($result->rowCount() == 1) {
			$result = new $model($result->fetch());
		} elseif ($result->rowCount() > 1) {
			$collection = new ModelCollection;
			foreach ($result as $row) {
				$collection->add(new $model($row));
			}
			$result = $collection;
		} else {
			return false;
		}
		return $result;
	}

/**
 * Updates a record in the table
 *
 * @param  string $table
 * @param  string $data
 * @param  string $condition
 * @return mixed  Number of affected collumns, or false
 */
	public function update($table, $data, $condition) {
		return $this->exec('update '.$table.' set '.implode(', ', $this->prepare_data($data)).' where '.$condition.';');
	}

/**
 * Deletes from table, with $condition
 *
 * @param  string $table
 * @param  string $condition
 * @return mixed  Number of affected collumns, or false
 */
	public function delete($table, $condition) {
		return $this->exec('delete from '.$table.' where '.$condition.';');
	}
	
/**
 * Warapper for a relational one-to-many query
 *
 * @param  array $info
 * @return PDOStatement
 */
	public function has_many_read($info) {
		$query = "select * from `".$info['foreign']."` where `".$info['local_id']."`='".$info['id']."';";
		return $this->query($query, PDO::FETCH_ASSOC);
	}

/**
 * Warapper for a relational one-to-one (foreign) query
 *
 * @param  array $info
 * @return array
 */
	public function has_one_read($info) {
		$query  = "select * from `".$info['foreign']."` where `".$info['local_id']."`='".$info['id']."' limit 1;";
		$result = $this->query($query, PDO::FETCH_ASSOC);
		return $result->fetch();
	}

/**
 * Wrapper for a relational one-to-one (local) query
 *
 * @param  array $info
 * @return array
 */
	public function belongs_to_read($info) {
		$query  = "select * from `".$info['foreign']."` where `id`='".$info['id']."' limit 1;";
		$result = $this->query($query, PDO::FETCH_ASSOC);
		return $result->fetch();
	}

/**
 * Wrapper for a relational many-to-many query
 *
 * @param  array $info
 * @return mixed
 */
	public function has_and_belongs_to_many_read($info) {
		$query = "SELECT * FROM `".$info['local']."`, `".$info['foreign']."`, `".$info['join']."` WHERE ".$info['local'].".id = '".$info['id']."' AND ".$info['join'].".".$info['local_id']." = ".$info['local'].".id AND ".$info['join'].".".$info['foreign_id']." = ".$info['foreign'].".id;";
		return $this->query($query, PDO::FETCH_ASSOC);
	}

/**
 * Creates a new has_many relation
 *
 * @param  string $table
 * @param  string $data
 * @param  string $relation
 * @return void
 */
	public function has_many_create($table, $data, $relation) {
		$foreign = singularize($relation->table()).'_id';
		if (!isset($data[$foreign])) {
			$data[$foreign] = $relation->id;
		}
		return $this->exec('insert into '.$table.' set '.implode(', ', $this->prepare_data($data)).';');
	}

/**
 * Create a new has_one relation
 *
 * @param  string $table
 * @param  string $data
 * @param  string $relation
 * @return void
 */
	public function has_one_create($table, $data, $relation) {
		$foreign = singularize($relation->table()).'_id';
		if (!isset($data[$foreign])) {
			$data[$foreign] = $relation->id;
		}
		return $this->exec('insert into '.$table.' set '.implode(', ', $this->prepare_data($data)).';');
	}

/**
 * Create a new belongs_to relation
 *
 * @param  string $table
 * @param  string $data
 * @param  string $relation
 * @return void
 */
	public function belongs_to_create($table, $data, $relation) {
		$return = $this->exec('insert into '.$table.' set '.implode(', ', $this->prepare_data($data)).';');
		
		$foreign = singularize($table).'_id';
		if (!isset($relation->$foreign)) {
			$relation->$foreign = $this->last_id();
		}
		
		return $return;
	}

/**
 * Create a new has_and_belongs_to_many relation
 *
 * @param  string $table
 * @param  string $data
 * @param  string $relation
 * @return void
 */
	public function has_and_belongs_to_many_create($table, $data, $relation) {
		$return = $this->exec('insert into '.$table.' set '.implode(', ', $this->prepare_data($data)).';');

		$relation_table = implode('_', s($table, $relation->table()));
		$local          = singularize($table).'_id';
		$local_id       = $this->last_id();
		$foreign        = singularize($relation->table()).'_id';
		$foreign_id     = $relation->id;

		$query = "select * from `$relation_table` where `$foreign`='$foreign_id' and `$local`='$local_id';";
		$relation_exists = $this->query($query);

		if ($relation_exists->rowCount() === 0) {
			$query  = "insert into ".$relation_table." set ".$foreign."='".$foreign_id."', ".$local."='".$local_id."';";
			$return = $this->exec($query);
		}
		return $return;
	}

/**
 * undocumented function
 *
 * @param  array $info
 * @return integer
 */
	public function has_many_count($info) {
		throw new Exception('Method has_many_count is not implemented in the ModelAdaptersMysql yet.');
	}

/**
 * Counts foreign members of a HABTM
 *
 * To be used from a callable on ModelCollection::count() calls, where the
 * collection contains HABTM members.
 *
 * This is more efficient than loading the whole collection from the db.
 *
 * @param  array   $info Relation confinguration (built in the ModelBase)
 * @return integer Number of foreign members
 */
	public function has_and_belongs_to_many_count($info) {
		$query = "SELECT
				count(*) as count
			FROM
				${info['local']},
				${info['foreign']},
				${info['join']}
			WHERE
				${info['local']}.id = ${info['id']}
			AND
				${info['join']}.${info['local_id']} = ${info['local']}.id
			AND
				${info['join']}.${info['foreign_id']} = ${info['foreign']}.id;";
		$result = $this->query($query, PDO::FETCH_ASSOC);
		$result = $result->fetch();
		return $result['count'];
	}

/**
 * Checks if table exists in the DB
 *
 * @param  string  $table Table name
 * @return boolean
 */
	public function table_exists($table) {
		$return = ($this->query("SHOW TABLES LIKE '$table'")->rowCount() > 0);
		return $return;
	}

/**
 * undocumented function
 *
 * @return array
 */
	public function get_query_log() {
		return $this->query_log;
	}
}


?>