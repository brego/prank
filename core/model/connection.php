<?php
/**
 * Facilitates the communication with database.
 *
 * Singelton. Establishes a connection with the database, and is responsible 
 * for all communication with it (through adapters).
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

class Connection {
	private static $instance   = null;
	private        $connection = null;
	private        $adapter    = null;
	
	public static function instance() {
		if(self::$instance === null) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
/**
 * Constructor.
 *
 * @todo   Implement some sort of environment-detection - use different configs!
 * @todo   Fuck tha exception away from here.
 * @return void
 */
	private function __construct() {
		$config = ::Config::instance();
		require_once ::c('CONFIG').'db.php';
		$params = $config->db[::c('status')];
		$this->adapter = 'Prank::Model::Adapters::'.ucfirst($params['type']);
		$dsn           = $params['type'].':host='.$params['host'].';dbname='.$params['db'];
		if (class_exists($this->adapter)) {
			$this->connection = new $this->adapter($dsn, $params['user'], $params['password']);
		} else {
			$this->connection = new PDO($dsn, $params['user'], $params['password']);
		}
	}
	
	public function __call($method, $params) {
		if(method_exists($this->connection, $method)) {
			return call_user_func_array(array($this->connection, $method), $params);
		} else {
			throw new Prank::Model::Exceptions::UnknownMethod;
		}
	}
}

?>