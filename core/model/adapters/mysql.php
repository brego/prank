<?php
/**
 * MySQL adapter.
 *
 * Provides methods for basic interaction with MySQL databases.
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

namespace Prank::Model::Adapters;

class Mysql extends PDO {
	private $columns = array();
	
	public function __construct($params, $user, $password) {
		parent::__construct($params, $user, $password);
	}
	
	public function is_column_of($column, $table) {
		$this->columns($table);
		if (array_search($column, $this->columns[$table]) !== false) {
			return true;
		} else {
			return false;
		}
	}
	
	public function fetch_columns($table) {
		foreach ($this->query('show columns from '.$table.';') as $row) {
			$this->columns[$table][] = $row[0];
		}
		return $this->columns[$table];
	}
	
	public function columns($table) {
		if (!isset($this->columns[$table])) {
			$this->fetch_columns($table);
		}
		return $this->columns[$table];
	}
	
	public function rowCount() {
		return parent::rowCount();
	}
	
	public function insert($table, $data) {
		$prepared_data = array();
		foreach ($data as $column => $value) {
			$prepared_data[] = $column." = '".$value."'";
		}
		return $this->exec('insert into '.$table.' set '.implode(', ', $prepared_data).';');
	}
	
	public function update($table, $data, $condition) {
		$prepared_data = array();
		foreach ($data as $column => $value) {
			$prepared_data[] = $column." = '".$value."'";
		}
		return $this->exec('update '.$table.' set '.implode(', ', $prepared_data).' where '.$condition.';');
	}
	
	public function delete($table, $condition) {
		return $this->exec('delete from '.$table.' where '.$condition.';');
	}
}

?>