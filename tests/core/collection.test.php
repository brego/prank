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
	
	public function test_lazy_load() {
		$test = new Collection;
		$test->register_loader(function($collection){
			$collection->add(new stdClass);
			$collection->add(new stdClass);
		});
		$this->assert_equal(count($test), 2);
		
		$test2 = new Collection;
		$test2->register_loader(function($collection){
			$collection->add(new stdClass);
			$collection->add(new stdClass);
		});
		foreach ($test2 as $object) {
			$this->assert_is_a($object, 'stdClass');
		}
		
		$test3 = new Collection;
		$test3->register_loader(function($collection){
			$collection->add(new stdClass);
			$collection->add(new stdClass);
		});
		$return = $test3->each(function($object){
			return $object;
		});
		foreach ($return as $object) {
			$this->assert_is_a($object, 'stdClass');
		}
		
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
	
	public function test_each_item_name() {
		$books = new Collection();
		$books->item_name('book');
		
		$test1 = new stdClass;
		$test1->name   = 'Test One';
		$test1->author = 'Test Ones Author';
		$test2 = new stdClass;
		$test2->name   = 'Test Two';
		$test2->author = 'Test Twos Author';
		
		$books->add($test1);
		$books->add($test2);
		
		$this->assert_equal(count($books), 2);
		
		$result = $books->each(function($book){return $book->name;});
		$this->assert_equal($result, array('Test One', 'Test Two'));
		
		$result = $books->each(function($name){return $name;});
		$this->assert_equal($result, array('Test One', 'Test Two'));
		
		$result = $books->each(function($book, $name){return $name;});
		$this->assert_equal($result, array('Test One', 'Test Two'));
	}
	
	public function test_count() {
		$this->assert_equal(count($this->collection), 2);
	}
	
	public function test_add() {
		$this->collection->add(new stdClass);
		$this->assert_equal(count($this->collection), 3);
	}
	
	public function test_clear() {
		$this->collection->clear();
		$this->assert_equal(count($this->collection), 0);
	}
}

?>