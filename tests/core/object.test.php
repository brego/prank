<?php

require_once dirname(dirname(dirname(__FILE__))).DS.'core/object.php';

class TestObject extends \Prank\Core\Object {
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

	public function test_responds() {
		$object = new TestObject;
		$this->assert_true($object->responds('i_am_public'));
		$this->assert_false($object->responds('i_am_so_private'));
		
		$this->assert_true(TestObject::responds('static_public'));
		$this->assert_false(TestObject::responds('static_private'));
	}

	public function test_object_extending() {
		$object = new TestObject;
		$this->assert_false($object->responds('return_true'));
		$object->extend('return_true', function() {return true;});
		$this->assert_true($object->return_true());
		$this->assert_false(TestObject::responds('return_true'));
		
		$object->extend('return_self', function($object) {return $object;});
		$this->assert_identical($object, $object->return_self());
	}
	
	public function test_class_extending() {
		TestObject::extend('return_false', function() {return false;});
		$this->assert_true(TestObject::responds('return_false'));
		$this->assert_false(TestObject::return_false());
		
		TestObject::extend('return_class', function($class) {return $class;});
		$this->assert_equal(TestObject::return_class(), 'TestObject');
		
		$object = new TestObject;
		$this->assert_false($object->responds('return_false'));
		$this->assert_false($object->responds('return_class'));
	}	
}

?>