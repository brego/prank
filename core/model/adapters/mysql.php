<?php
/**
 * MySQL adapter
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
 * MySQL adapter
 *
 * Provides methods for basic interaction with MySQL databases. Extends upon
 * PDO base class.
 * 
 * @package    Prank
 * @subpackage Model
 */
class ModelAdaptersMysql extends PDO implements ModelAdapter {
	private $columns = array();

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
		$result = parent::exec($query);
		if ($result === false) {
			$error = $this->errorInfo();
			throw new Exception('Database error: '.$error[2]);
		}
		return $result;
	}

/**
 * Execute PDO::query, with error detection
 *
 * @return mixed
 */
	public function query() {
		$arguments = func_get_args();
		$result = parent::query($arguments[0]);
		if ($result === false) {
			$error = $this->errorInfo();
			throw new Exception('Database error: '.$error[2]);
		} else {
			unset($arguments[0]);
			call_user_func_array(array($result, 'setFetchMode'), $arguments);
		}
		return $result;
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
		if (array_search($column, $this->columns[$table]) !== false) {
			return true;
		} else {
			return false;
		}
	}

/**
 * Fetches column information from the table
 *
 * Always fetches the column informtion from the table, and saves them in the
 * cache.
 * 
 * @param  string $table 
 * @return void
 */
	public function fetch_columns($table) {
		$this->columns[$table] = array();
		foreach ($this->query('show columns from '.$table.';') as $row) {
			$this->columns[$table][] = $row[0];
		}
		return $this->columns[$table];
	}

/**
 * Fetches column informtion from the cache
 *
 * Tries to fetch column informtion from the cache, but calls
 * Mysql::fetch_columns if there's no cache data for this table.
 * 
 * @param  string $table 
 * @return void
 */
	public function columns($table) {
		if (!isset($this->columns[$table])) {
			$this->fetch_columns($table);
		}
		return $this->columns[$table];
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
		$prepared_data = array();
		foreach ($data as $column => $value) {
			$prepared_data[] = $column." = '".$value."'";
		}
		return $this->exec('insert into '.$table.' set '.implode(', ', $prepared_data).';');
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
		$prepared_data = array();
		foreach ($data as $column => $value) {
			$prepared_data[] = $column." = '".$value."'";
		}
		return $this->exec('update '.$table.' set '.implode(', ', $prepared_data).' where '.$condition.';');
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
 * Returns current time in database-specific format
 *
 * @return string
 */	
	public function now() {
		return date('Y-m-d H:i:s');
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
		$query = "select * from `".$info['foreign']."` where `".$info['local_id']."`='".$info['id']."' limit 1;";
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
		$query = "select * from `".$info['foreign']."` where `id`='".$info['id']."';";
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
		$query =  "SELECT * FROM `".$info['local']."`, `".$info['foreign']."`, `".$info['join']."` WHERE ".$info['local'].".id = '".$info['id']."' AND ".$info['join'].".".$info['local_id']." = ".$info['local'].".id AND ".$info['join'].".".$info['foreign_id']." = ".$info['foreign'].".id;";
		
		$ret = $this->query($query, PDO::FETCH_ASSOC);
		return $ret;
	}
	
	
	public function has_many_create($table, $data, $relation) {
		$foreign = Inflector::singularize($relation->table()).'_id';
		if (!isset($data[$foreign])) {
			$data[$foreign] = $relation->id;
		}
		$prepared_data = array();
		foreach ($data as $column => $value) {
			$prepared_data[] = $column."='".$value."'";
		}
		
		return $this->exec('insert into '.$table.' set '.implode(', ', $prepared_data).';');
	}
	
	public function has_one_create($table, $data, $relation) {
		$foreign = Inflector::singularize($relation->table()).'_id';
		if (!isset($data[$foreign])) {
			$data[$foreign] = $relation->id;
		}
		$prepared_data = array();
		foreach ($data as $column => $value) {
			$prepared_data[] = $column."='".$value."'";
		}
		
		return $this->exec('insert into '.$table.' set '.implode(', ', $prepared_data).';');
	}
	
	public function belongs_to_create($table, $data, $relation) {
		$prepared_data = array();
		foreach ($data as $column => $value) {
			$prepared_data[] = $column." = '".$value."'";
		}
		$return = $this->exec('insert into '.$table.' set '.implode(', ', $prepared_data).';');
		
		$foreign = Inflector::singularize($table).'_id';
		if (!isset($relation->$foreign)) {
			$relation->$foreign = $this->lastInsertId();
		}
		
		return $return;
	}
	
	public function has_and_belongs_to_many_create($table, $data, $relation) {
		return true;
	}
	
	public function has_many_update($table, $data, $relation) {
		return true;
	}
	
	public function has_one_update($table, $data, $relation) {
		return true;
	}
	
	public function belongs_to_update($table, $data, $relation) {
		return true;
	}
	
	public function has_and_belongs_to_many_update($table, $data, $relation) {
		return true;
	}
}

?>