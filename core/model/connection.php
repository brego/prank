<?php
/**
 * Facilitates the communication with database.
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
 * Facilitates the communication with database.
 *
 * Singelton. Establishes a connection with the database, and is responsible
 * for all communication with it (through adapters).
 *
 * @package    Prank
 * @subpackage Model
 */
class ModelConnection {
	private static $instance = null;
	private        $adapter  = null;

/**
 * Singleton accessor
 *
 * @return Connection
 */	
	public static function instance() {
		if(self::$instance === null) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
/**
 * Constructor
 *
 * Fetches DB configuration dependent on the 'state' setting in app.php. If
 * appropriate adapter is available, it's used - else an exception is thrown.
 *
 * @return void
 */
	private function __construct() {
		$registry      = Registry::instance();
		$params        = $registry->config['db'];
		$adapter_class = 'ModelAdapters'.ucfirst($params['type']);
		$dsn           = $params['type'].':host='.$params['host'].';dbname='.$params['database'];
		
		if (class_exists($adapter_class)) {
			$this->adapter = new $adapter_class($dsn, $params['user'], $params['password'], $params['database']);
		} else {
			throw new Exception('Adapter class '.$adapter_class.' (type '.$params['type'].') not found.');
		}
	}

/**
 * Wrapper around the adapters is_column_of for multiple collumns
 *
 * @param  array   $columns
 * @param  string  $table
 * @return boolean
 */
	public function are_columns_of($columns, $table) {
		foreach ($columns as $column) {
			if ($this->is_column_of($column, $table) === false) {
				return false;
				break;
			}
		}
		return true;
	}

/**
 * Member override
 *
 * If a method is available in the adapter, it'll be called. If an unknown
 * method is called, UnknownMethod will be thrown.
 *
 * @param  string $method
 * @param  string $params
 * @return mixed
 */	
	public function __call($method, $params) {
		if(method_exists($this->adapter, $method)) {
			return call_user_func_array(array($this->adapter, $method), $params);
		} elseif (method_exists($this->adapter, camelback($method))) {
			return call_user_func_array(array($this->adapter, camelback($method)), $params);
		} else {
			throw new Exception('Unknown method has been called - '.$method);
		}
	}
}

?>