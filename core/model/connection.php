<?php
/**
 * Facilitates the communication with database.
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
		$config = Config::instance();
		require c('CONFIG').'db.php';
		$params = $config->db[c('state')];
		
		$adapter_class = 'ModelAdapters'.ucfirst($params['type']);
		$dsn           = $params['type'].':host='.$params['host'].';dbname='.$params['db'];
		
		if (class_exists($adapter_class)) {
			$this->adapter = new $adapter_class($dsn, $params['user'], $params['password']);
		} else {
			throw new Exception('Adapter of type '.$params['type'].' not found.');
		}
	}

/**
 * Returns a query result wrapped in a specified model or a set
 *
 * Executes the $query, and if the result is a single row, a new $model is
 * returned with that row's data. If the result contains more than one row,
 * a Model::Set is created, and filled with new $model's corresponding to the
 * returned rows.
 * If the result is empty, false is returned.
 * 
 * @param  string $query 
 * @param  string $model 
 * @return mixed
 */	
	public function query_wrapped($query, $model) {
		$result = $this->query($query, PDO::FETCH_ASSOC);
		
		if ($result !== false) {
			if ($result->rowCount() > 1) {
				$set = new ModelCollection;
				$set->item_name(Inflector::underscore($model));
				foreach($result as $row) {
					$set->add(new $model($row));
				}
				return $set;
			} elseif ($result->rowCount() == 1) {
				return new $model($result->fetch());
			} elseif ($result->rowCount() == 0) {
				return false;
			} else {
				throw new Exception('Illogical count of rows returned: '.$result->rowCount());
			}
		} else {
			return false;
		}
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
		} elseif (method_exists($this->adapter, Inflector::camelback($method))) {
			return call_user_func_array(array($this->adapter, Inflector::camelback($method)), $params);
		} else {
			throw new Exception('Unknown method has been called - '.$method);
		}
	}
}

?>