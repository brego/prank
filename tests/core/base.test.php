<?php

require_once dirname(dirname(dirname(__FILE__))).DS.'core/base.php';

class BaseTestCase extends PrankTestCase {
	
	public function setup() {
		$this->setup_prank_spine();
	}
	
	public function teardown() {
		$this->teardown_prank_spine();
	}
	
	public function test__() {
		$string = 'I am printing';
		ob_start();
		_($string);
		$result = ob_get_clean();
		$this->assert_equal($result, $string);
	}
	
	public function test_d() {
		$string = 'I am printing';
		ob_start();
		d($string);
		$result = ob_get_clean();
		$this->assert_equal($result, "<pre>\nstring(13) \"$string\"\n</pre>\n");
	}

	public function test_array_cleanup() {
		$array_dirty = array('hello', '', 'kitty', '', 'turtle', '');
		$array_clean = array('hello', 'kitty', 'turtle');

		$this->assert_equal($array_clean, array_cleanup($array_dirty));
	}
	
	public function test_c() {
		$registry = Registry::instance();
		
		$this->assert_equal(c()->ds, $registry->config->ds);
		
		$this->assert_equal(c('ds'), $registry->config->ds);
	}
	
	public function test_a() {
		$this->assert_equal(a('one', 'two', 'three'), array('one', 'two', 'three'));
	}
	
	public function test_s() {
		$this->assert_equal(s(array('b', 'c', 'a')), array('a', 'b', 'c'));
		$this->assert_equal(s('b', 'c', 'a'), array('a', 'b', 'c'));
	}
	
	public function test_to_yaml() {
		$string = "---\na: \n  one: two\n";
		if (function_exists('syck_dump')) {
			$string = "--- %YAML:1.0 \n\"a\": {\"one\": \"two\"}\n";
		}
		$this->assert_equal(to_yaml(array('a'=>array('one'=>'two'))), $string);
	}
	
	public function test_from_yaml() {
		$string = "a: \n  one: two \n";
		$this->assert_equal(from_yaml($string), array('a'=>array('one'=>'two')));
	}
	
	public function test_up() {
		$this->assert_equal(up('someSmallText'), 'SOMESMALLTEXT');
	}
	
	public function test_down() {
		$this->assert_equal(down('sOMEsMALLtEXT'), 'somesmalltext');
	}
	
	public function test_file_path() {
		$test     = file_path('one/', 'two', 'three');
		$ds       = DIRECTORY_SEPARATOR;
		$expected = 'one'.$ds.'two'.$ds.'three';
		
		$this->assert_equal($test, $expected);
	}
}

?>