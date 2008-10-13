<?php

//require_once dirname(dirname(dirname(__FILE__))).DS.'core/config.php';

class ConfigTestCase extends PrankTestCase {
	public $instance = null;
	
	public function setup() {
		$this->instance = Config::instance();
	}
	
	public function teardown() {
		$this->instance = null;
	}
	
	public function test_instance() {
		$this->assert_equal($this->instance, Config::instance());
	}
	
	public function test_static_set_get() {
		Config::set('test', true);
		$this->assert_true(Config::get('test'));
		$test_array = array('test1' => true, 'test2' => true);
		Config::set($test_array);
		$this->assert_true(Config::get('test1'));
		$this->assert_true(Config::get('test2'));
	}
	
	public function test_rgular_set_get() {
		$this->instance->test3 = true;
		$this->assert_true($this->instance->test3);
	}
}

?>