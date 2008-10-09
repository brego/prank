<?php

require_once dirname(dirname(dirname(__FILE__))).DS.'core/collection.php';

class CollectionTestCase extends PrankTestCase {
	public $collection = null;
	
	public function setup() {
		$this->collection = new Collection;
		$test1 = new stdClass;
		$test1->test = 'a';
		$this->collection->add($test1);
		$test2 = new stdClass;
		$test2->test = 'b';
		$this->collection->add($test2);
	}
	
	public function teardown() {
		$this->collection = null;
	}
	
	public function test___construct() {
		$test = new Collection(array(new stdClass, new stdClass));
		$this->assert_equal(count($test), 2);
	}
	
	public function test_each() {
		$this->collection->each(function($item){$item->test = strtoupper($item->test);});
		foreach ($this->collection as $object) {
			$this->assert_equal($object->test, strtoupper($object->test));
		}

		$function = function($item) {
			$item->test = strtolower($item->test);
		};
		
		$this->collection->each($function);
		foreach ($this->collection as $object) {
			$this->assert_equal($object->test, strtolower($object->test));
		}
	}

	public function test_each_single_parameter() {		
		$this->collection->each(function($something){$something->test = strtoupper($something->test);});
		foreach ($this->collection as $object) {
			$this->assert_equal($object->test, strtoupper($object->test));
		}
	}
	
	public function test_each_custom_parameters() {	
		$this->collection->each(function($test, $item){$item->test = strtoupper($test);});
		foreach ($this->collection as $object) {
			$this->assert_equal($object->test, strtoupper($object->test));
		}
	}
	
	public function test_each_return() {
		$result = $this->collection->each(function($test){return $test;});
		$this->assert_equal($result, array('a', 'b'));
		
		$result = $this->collection->each(function($test){});
		$this->assert_equal($result, null);
	}
	
	public function test_count() {
		$this->assert_equal(count($this->collection), 2);
	}
	
	public function test_add() {
		$this->collection->add(new stdClass);
		$this->assert_equal(count($this->collection), 3);
	}
	// Commented for now...
	// public function test_clear() {
	// 	$this->collection->clear();
	// 	$this->assert_equal(count($this->collection), 0);
	// }
}

?>