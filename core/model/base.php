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

namespace Prank::Model;

class Base {
	private $table      = null;
	private $connection = null;
	private $columns    = null;
	private $data       = array();
	private $hollow     = true;
	
	public function __construct($data = null) {
		$this->table      = ::Inflector::tabelize(get_called_class());
		$this->connection = Connection::instance();
		$this->columns    = $this->connection->columns($this->table);
		
		foreach ($this->columns as $column) {
			if (isset($data[$column])) {
				$this->$column = $data[$column];
			} else {
				$this->data[$column] = '';
			}
		}
	}

	public function save() {
		if ($this->id !== '') {
			$this->connection->update($this->table, $this->data, 'id='.$this->id);
		} else {
			$this->connection->insert($this->table, $this->data);
		}
	}
	
	public function __call($method, $arguments) {
		if ($method === 'delete' && $this->id !== '') {
			return $this->connection->delete($this->table, 'id='.$this->id);
		} else {
			return false;
		}
	}
	
	public function __set($variable, $value) {
		if ($this->connection->is_column_of($variable, $this->table)) {
			$this->hollow = false;
			$this->data[$variable] = $value;
		}
	}
	
	public function __get($variable) {
		if (isset($this->data[$variable])) {
			return $this->data[$variable];
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
 * @todo  Implement more dynamic finders (multiple arguments etc)
 * @param string $method 
 * @param string $arguments 
 * @return void
 */
	public static function __callStatic($method, $arguments) {
		$connection = Prank::Model::Connection::instance();
		$model      = get_called_class();
		$table      = ::Inflector::tabelize(get_called_class());


		if (substr($method, 0, 8) == 'find_by_') {
			$column = ::down(substr($method, 8));
			if ($connection->is_column_of($column, $table)) {
				$result = $connection->query('select * from '.$table.' where '.$column.' = '.$arguments[0], PDO::FETCH_ASSOC);
				return self::prepare_result($model, $result);
			}
		} elseif ($method === 'find_all') {
			$result = $connection->query('select * from '.$table.';', PDO::FETCH_ASSOC);
			return self::prepare_result($model, $result);
		} elseif ($method === 'delete') {
			return $connection->delete($table, 'id='.$arguments[0]);
		} else {
			return false;
		}
	}
	
	private static function prepare_result($model, $result) {
		if ($result->rowCount() > 1) {
			$set = new Prank::Model::Set;
			foreach($result as $row) {
				$set->add(new $model($row));
			}
			return $set;
		} elseif ($result->rowCount() == 1) {
			return new $model($result->fetch());
		} else {
			
		}
	}
}

?>
