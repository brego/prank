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
		
		$this->config_file             = $this->config_dir.'app.php';
		$this->db_config_file          = $this->config_dir.'db.php';
		$this->index_file              = $this->webroot_dir.'index.php';
		$this->default_controller_file = $this->controllers_dir.'default.controller.php';
		
		mkdir($this->app_dir);
		mkdir($this->config_dir);
		mkdir($this->webroot_dir);
		mkdir($this->controllers_dir);
		
		$sample_app_config = "<?php\n".
			"c('MODELS',      c('APP').'models'.c('DS'));\n".
			"c('VIEWS',       c('APP').'views'.c('DS'));\n".
			"c('CONTROLLERS', c('APP').'controllers'.c('DS'));\n".
			"c('CONFIG',      c('APP').'config'.c('DS'));\n".
			"c('WEBROOT',     c('APP').'webroot'.c('DS'));\n".
			"c('state',      'test');\n".
			"?>";
		file_put_contents($this->config_file, $sample_app_config);
		
		$org_db_config_file = file_get_contents(ROOT.'app'.DS.'config'.DS.'db.php');
		file_put_contents($this->db_config_file, $org_db_config_file);
				
		$default_controller_file = "<?php\n".
			"class DefaultController extends Prank::Controller::Base {\n".
			"public function index() { }\n".
			"}";
		file_put_contents($this->default_controller_file, $default_controller_file);
		
		file_put_contents($this->index_file, ' ');
		
		$this->instance = Boot::run($this->index_file);
	}
	
	public function teardown_prank_spine() {
		$this->instance = null;
		
		unlink($this->config_file);
		unlink($this->db_config_file);
		unlink($this->index_file);
		unlink($this->default_controller_file);
		
		rmdir($this->config_dir);
		rmdir($this->webroot_dir);
		rmdir($this->controllers_dir);
		rmdir($this->app_dir);
	}
}

?>