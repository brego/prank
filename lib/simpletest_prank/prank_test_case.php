<?php

require_once dirname(dirname(dirname(__FILE__))).DS.'core'.DS.'boot.php';
require_once dirname(dirname(dirname(__FILE__))).DS.'core'.DS.'inflector.php';
require_once dirname(dirname(dirname(__FILE__))).DS.'core'.DS.'config.php';

class PrankTestCase extends UnitTestCase {
	public $instance                = null;
	public $app_dir                 = null;
	public $config_dir              = null;
	public $webroot_dir             = null;
	public $controllers_dir         = null;

	public $config_file             = null;
	public $db_config_file          = null;
	public $routes_config_file      = null;
	public $index_file              = null;
	public $default_controller_file = null;

	public function __call($method, $args) {
		$exceptions = array('setup' => 'setUp', 'teardown' => 'tearDown');
		
		if (array_search($method, array_keys($exceptions)) !== false) {
			$method_camelized = $exceptions[$method];
		} else {
			$words = explode('_', strtolower($method));
			for($i = 1; $i < count($words); $i++){
				$words[$i] = strtoupper(substr($words[$i], 0, 1)) . substr($words[$i], 1);
			}
			$method_camelized = implode('', $words);
		}
		
		if (method_exists($this, $method_camelized)) {
			call_user_func_array(array($this, $method_camelized), $args);
		} else {
			die('Method '.$method_camelized.' not found in UnitTestCase...');
		}
	}
	
	public function setup_prank_spine() {
		$this->app_dir                 = ROOT.'tests'.DS.'tmp'.DS.'app'.DS;
		$this->config_dir              = $this->app_dir.'config'.DS;
		$this->webroot_dir             = $this->app_dir.'webroot'.DS;
		$this->controllers_dir         = $this->app_dir.'controllers'.DS;
		
		$this->config_file             = $this->config_dir.'app.yml';
		$this->db_config_file          = $this->config_dir.'db.yml';
		$this->routes_config_file      = $this->config_dir.'routes.php';
		$this->index_file              = $this->webroot_dir.'index.php';
		$this->default_controller_file = $this->controllers_dir.'default.controller.php';
		
		mkdir($this->app_dir);
		mkdir($this->config_dir);
		mkdir($this->webroot_dir);
		mkdir($this->controllers_dir);
		
		// copy(ROOT.'app'.DS.'config'.DS.'routes.php', $this->routes_config_file);
		file_put_contents($this->routes_config_file, ' ');
		
		$org_app_config           = $this->from_yaml(file_get_contents(ROOT.'app'.DS.'config'.DS.'app.yml'));
		$org_app_config['state']  = 'test';
		$org_app_config['config'] = $this->config_dir;
		file_put_contents($this->config_file, $this->to_yaml($org_app_config));
		
		$org_db_config_file = file_get_contents(ROOT.'app'.DS.'config'.DS.'db.yml');
		file_put_contents($this->db_config_file, $org_db_config_file);
				
		$default_controller_file = "<?php\n".
			"class DefaultController extends ControllerBase {\n".
			"public function index() { }\n".
			"}";
		file_put_contents($this->default_controller_file, $default_controller_file);
		
		file_put_contents($this->index_file, ' ');
		
		Config::setup($this->index_file);
	}
	
	public function teardown_prank_spine() {
		$this->instance = null;
		
		$this->rm($this->app_dir);
	}
	
	private function to_yaml($variable) {
		if (function_exists('syck_dump')) {
			return syck_dump($variable);
		} else {
			if (class_exists('Spyc') === false) {
				require ROOT.'lib'.DS.'spyc'.DS.'spyc.php';
			}
			return Spyc::YAMLDump($variable);
		}
	}

	private function from_yaml($yaml) {
		if (function_exists('syck_load')) {
			return syck_load($yaml);
		} else {
			if (class_exists('Spyc') === false) {
				require ROOT.'lib'.DS.'spyc'.DS.'spyc.php';
			}
			return Spyc::YAMLLoad($yaml);
		}
	}
	
	private function rm($target) {
		if (is_file($target)) {
			if (is_writable($target)) {
				if (unlink($target)) {
					return true;
				}
			}
			return false;
		}
		if (is_dir($target)) {
			if (is_writable($target)) {
				foreach(new DirectoryIterator($target) as $object) {
					if ($object->isDot()) {
						unset($object);
						continue;
					}
					if ($object->isFile()) {
						$this->rm($object->getPathName());
					} elseif ($object->isDir()) {
						$this->rm($object->getRealPath());
					}
					unset($object);
				}
				if (rmdir($target)) {
					return true;
				}
			}
			return false;
		}
	}
}

?>
