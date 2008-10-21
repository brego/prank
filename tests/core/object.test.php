<?php

require_once dirname(dirname(dirname(__FILE__))).DS.'core/object.php';

class TestObject extends Object {
	private function i_am_so_private() {}
	public function i_am_public() {}
	
	private static function static_private() {}
	public static function static_public() {}
}


class ObjectTestCase extends PrankTestCase {
	
	public function setup() {
	}
	
	public function teardown() {
	}
	
	public function test_extending() {
		Object::extend('return_true', function(){return true;});
		$test = new Object;
		$this->assert_true($test->return_true());
		$this->assert_true(Object::return_true());
		
		Object::extend('return_self', function($object){return $object;});
		$test = new Object;
		$this->assert_equal($test->return_self(), $test);
		
		Object::extend('return_new_self', function($class){return new $class;});
		$test = new Object;
		$this->assert_equal($test->return_new_self(), new Object);
	}
	
	public function test_responds() {
		$test = new TestObject;
		$this->assert_true($test->responds('i_am_public'));
		$this->assert_false($test->responds('i_am_so_private'));
		
		$this->assert_true(TestObject::responds('static_public'));
		$this->assert_false(TestObject::responds('static_private'));
	}	
}

?>