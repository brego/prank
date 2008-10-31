<?php

require_once dirname(dirname(dirname(__FILE__))).DS.'core'.DS.'boot.php';

class BootTestCase extends PrankTestCase {
	
	public function setup() {
		$this->setup_prank_spine();
	}
	
	public function teardown() {
		$this->teardown_prank_spine();
	}
	
	public function test_load_base_libs() {
		$this->assert_true(class_exists('Config'));
		$this->assert_true(class_exists('Inflector'));
		
		$this->assert_true(function_exists('__autoload'));
		$this->assert_true(function_exists('_'));
		$this->assert_true(function_exists('d'));
		$this->assert_true(function_exists('array_cleanup'));
		$this->assert_true(function_exists('c'));
		$this->assert_true(function_exists('up'));
		$this->assert_true(function_exists('down'));
		$this->assert_true(function_exists('to_controller'));
		$this->assert_true(function_exists('is_controller'));
		$this->assert_true(function_exists('load_controller'));
		$this->assert_true(function_exists('is_action_of'));
		$this->assert_true(function_exists('url'));
		$this->assert_true(function_exists('css'));
		$this->assert_true(function_exists('_css'));
		$this->assert_true(function_exists('compress_css'));
		$this->assert_true(function_exists('javascript'));
		$this->assert_true(function_exists('_javascript'));
		$this->assert_true(function_exists('compress_javascript'));
	}
	
	public function test___construct() {
		$this->assert_equal(c('ds'),          DS);
		$this->assert_equal(c('app'),         $this->app_dir);
		$this->assert_equal(c('core'),        ROOT.'core'.DS);
		$this->assert_equal(c('models'),      $this->app_dir.'models'.DS);
		$this->assert_equal(c('views'),       $this->app_dir.'views'.DS);
		$this->assert_equal(c('controllers'), $this->app_dir.'controllers'.DS);
		$this->assert_equal(c('webroot'),     $this->app_dir.'webroot'.DS);
		$this->assert_equal(c('state'),      'test');
	}
	
	public function test_parse_url() {
		$this->assert_equal(Boot::$path,       array());
		$this->assert_equal(Boot::$url,        null);
		$this->assert_equal(Boot::$controller, 'default');
		$this->assert_equal(Boot::$action,     'index');
		$this->assert_equal(Boot::$params,     array());
		
		$this->instance->parse_url('argument1/argument2/argument3/');
		$this->assert_equal(Boot::$path,       array('argument1', 'argument2', 'argument3'));
		$this->assert_equal(Boot::$url,        array('argument1', 'argument2', 'argument3', ''));
		$this->assert_equal(Boot::$controller, 'default');
		$this->assert_equal(Boot::$action,     'index');
		$this->assert_equal(Boot::$params,     array('argument1', 'argument2', 'argument3'));
		
		$test_controller_file = "<?php\n".
			"class CoolController {\n".
			"public function index() { }\n".
			"public function an_action() { }\n".
			"}";
		file_put_contents($this->controllers_dir.'cool.controller.php', $test_controller_file);
		
		$this->instance->parse_url('cool/an_action/argument3/');
		$this->assert_equal(Boot::$path,       array(2=>'argument3'));
		$this->assert_equal(Boot::$url,        array('cool', 'an_action', 'argument3', ''));
		$this->assert_equal(Boot::$controller, 'cool');
		$this->assert_equal(Boot::$action,     'an_action');
		$this->assert_equal(Boot::$params,     array(2=>'argument3'));
		
		unlink($this->controllers_dir.'cool.controller.php');
	}
}

?>