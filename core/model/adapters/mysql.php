<?php
/**
 * MySQL adapter.
 *
 * Provides methods for basic interaction with MySQL databases. Extends upon
 * PDO base class,
 *
 * PHP version 5.3.
 *
 * @filesource
 * @copyright  Copyright (c) 2008, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk Prank's project page
 * @package    Prank
 * @subpackage Core.Model.Adapters
 * @since      Prank 0.10
 * @version    Prank 0.10
 */

class ModelAdaptersMysql extends PDO implements ModelAdapter {
	private $columns = array();

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
	public function insert($table, $data) {
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
 * @param  string $table 
 * @param  string $id_name 
 * @param  string $id 
 * @return PDOStatement
 */
	public function has_many_query($table, $id_name, $id) {
		$query = "select * from `".$table."` where `".$id_name."`='".$id."';";
		return $this->query($query, PDO::FETCH_ASSOC);
	}

/**
 * Warapper for a relational one-to-one (foreign) query
 *
 * @param  string $table 
 * @param  string $id_name 
 * @param  string $id 
 * @return PDOStatement
 */
	public function has_one_query($table, $id_name, $id) {
		$query = "select * from `".$table."` where `".$id_name."`='".$id."' limit 1;";
		return $this->query($query, PDO::FETCH_ASSOC);
	}

/**
 * Wrapper for a relational one-to-one (local) query
 *
 * @param  string $table
 * @param  string $id 
 * @return PDOStatement
 */
	public function belongs_to_query($table, $id) {
		$query = "select * from `".$table."` where `id`='".$id."';";
		return $this->query($query, PDO::FETCH_ASSOC);
	}
}

?>