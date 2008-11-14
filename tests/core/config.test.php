<?php

//require_once dirname(dirname(dirname(__FILE__))).DS.'core/config.php';

class ConfigTestCase extends PrankTestCase {
	public $instance = null;
	
	public function setup() {
		$this->instance = new Config;
	}
	
	public function teardown() {
		$this->instance = null;
	}
	
	public function test_rgular_set_get() {
		$this->instance->test3 = true;
		$this->assert_true($this->instance->test3);
	}
}

?>