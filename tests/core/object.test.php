<?php

require_once dirname(dirname(dirname(__FILE__))).DS.'core/object.php';

class ObjectTestCase extends PrankTestCase {
	
	public function setup() {
	}
	
	public function teardown() {
	}
	
	public function test_extending() {
		Object::extend('return_true', function(){return true;});
		$test = new Object;
		$this->assert_true($test->return_true());
		
		Object::extend('return_self', function($object){return $object;});
		$test = new Object;
		$this->assert_equal($test->return_self(), $test);
	}
}

?>