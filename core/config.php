<?php
/**
 * Config
 *
 * @filesource
 * @copyright  Copyright (c) 2008-2010, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk/ Prank's project page
 * @link       http://github.com/brego/prank/ Prank's Git repository
 * @package    Prank
 * @subpackage Core
 * @since      Prank 0.10
 * @version    Prank 0.50
 */

/**
 * Core config class
 *
 * @package    Prank
 * @subpackage Core
 * @todo       Jesus Christ! It's a lion! Get in the car! This monster needs a
 *             serious rewrite, and fast. Also, YAML is so 2009 - we just may
 *             need to drop it in favor of, say, PHP?
 */
class Config implements ArrayAccess {
	protected $config = null;

/**
 * Constructor
 * 
 * If $start_point is set, calls automatically on Config::setup.
 *
 * @param  string $start_point 
 * @param  string $config_dir 
 * @return void
 */
	public function __construct($start_point, $config_dir = false) {
		$this->setup($start_point, $config_dir);
	}

/**
 * Setups the configuration for Prank
 *
 * $start_point needs to be the an output of the __FILE__ from the starting
 * point of the application. It will be used to determine full paths for
 * directories.
 * 
 * @param  string $start_point 
 * @return void
 */
	public function setup($start_point, $config_dir = false) {
		
		// Default internal Prank config array
		$config          = array();
		$config['ds']    = DIRECTORY_SEPARATOR;
		$config['ps']    = PATH_SEPARATOR;
		$config['app']   = dirname(dirname($start_point)).$config['ds'];
		$config['core']  = dirname(__FILE__).$config['ds'];
		$config['prank'] = dirname(dirname(__FILE__)).$config['ds'];		
		$config['lib']   = $config['prank'].'lib'.$config['ds'];

		// Default app config
		$default_app_config = array(
			'state'       => 'development',
			'directories' => array(
				'models'      => 'models',
				'views'       => 'views',
				'controllers' => 'controllers',
				'webroot'     => 'webroot',
				'config'      => 'config',
				'helpers'     => 'helpers'));
		
		// Determining the app config dir
		if ($config_dir === false) {
			$config_dir = $config['app'].'config'.$config['ds'];
		}
		
		// Loading the app config
		$app = array();
		if (is_file($config_dir.'app.php')) {
			$app = array();
			require $config_dir.'app.php';
		}
		$app_config = array_merge($default_app_config, $app);

		// Merging the internal array with app config
		$config['state']       = $app_config['state'];
		$config['models']      = $config['app'].$app_config['directories']['models'].$config['ds'];
		$config['views']       = $config['app'].$app_config['directories']['views'].$config['ds'];
		$config['controllers'] = $config['app'].$app_config['directories']['controllers'].$config['ds'];
		$config['webroot']     = $config['app'].$app_config['directories']['webroot'].$config['ds'];
		$config['config']      = $config['app'].$app_config['directories']['config'].$config['ds'];
		$config['helpers']     = $config['app'].$app_config['directories']['helpers'].$config['ds'];

		// Loading the DB config
		if (is_file($config['config'].'db.php')) {
			$db = array();
			require $config['config'].'db.php';
		} else {
			throw new Exception('Currently Prank requires a database connection. Provide a config/db.php with necessary data.');
		}
		$config['db'] = array();
		foreach ($db[$config['state']] as $key => $value) {
			$config['db'][$key] = $value;
		}
		
		// Assigning the internal array to an internal parameter
		$this->config = $config;
	}

/**
 * Overload for setting of a configuration variable
 * 
 * @param  string $name 
 * @param  mixed  $value 
 * @return void
 */
	public function __set($name, $value) {
		$this->config[$name] = $value;
	}

/**
 * Overload for getting the configuration variable
 *
 * @param  string $name 
 * @return mixed
 */
	public function __get($name) {
		if (isset($this->config[$name])) {
			return $this->config[$name];
		} else {
			throw new Exception('Property '.$name.' is not defined in the configuration.');
		}
	}
	
/**
 * The ArrayAccess interface:
 */

/**
 * Part of the ArrayAccess, checks wheter the $offset exists
 *
 * @param  mixed   $offset 
 * @return boolean
 */
	public function offsetExists($offset) {
		return isset($this->config[$offset]);
	}

/**
 * Part of the ArrayAccess, returns the value at the $offset
 *
 * @param  mixed $offset 
 * @return mixed
 */
	public function offsetGet($offset) {
		return $this->config[$offset];
	}

/**
 * Part of the ArrayAccess, sets a $value at the $offset
 *
 * @param  mixed $offset 
 * @param  mixed $value 
 * @return void
 */
	public function offsetSet($offset, $value) {
		$this->config[$offset] = $value;
	}

/**
 * Part of the ArrayAccess, deletes the value at the $offset
 *
 * @param  mixed $offset 
 * @return void
 */
	public function offsetUnset($offset) {
		unset($this->config[$offset]);
	}
}

?>