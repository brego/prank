<?php

require_once dirname(dirname(dirname(__FILE__))).DS.'core/base.php';

class BaseTestCase extends PrankTestCase {
	
	public function setup() {
		$this->setup_prank_spine();
	}
	
	public function teardown() {
		$this->teardown_prank_spine();
	}
	
	public function test_loader() {
		loader('Prank::Model::Base');
		$this->assert_true(class_exists('Prank::Model::Base'));
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
		$this->assert_equal($result, '<pre>string(13) "'.$string.'"'."\n".'</pre>');
	}

	public function test_array_cleanup() {
		$array_dirty = array('hello', '', 'kitty', '', 'turtle', '');
		$array_clean = array('hello', 'kitty', 'turtle');

		$this->assert_equal($array_clean, array_cleanup($array_dirty));
	}
	
	public function test_c() {
		$var = Config::get('DS');
		$this->assert_equal($var, c('DS'));
		
		c('test', 'a');
		$this->assert_equal(Config::get('test'), 'a');
	}
	
	public function test_rm() {
		$test_directory = ROOT.'tests'.DS.'tmp'.DS.'rm-test-dir'.DS;
		mkdir($test_directory);
		mkdir($test_directory.'some-content-dir');
		rm($test_directory);
		$this->assert_false(is_dir($test_directory));
	}
	
	public function test_up() {
		$this->assert_equal(up('someSmallText'), 'SOMESMALLTEXT');
	}
	
	public function test_down() {
		$this->assert_equal(down('sOMEsMALLtEXT'), 'somesmalltext');
	}
	
	public function test_to_controller() {
		$this->assert_equal(to_controller('hello'), 'HelloController');
	}
	
	public function test_is_controller() {
		$this->assert_true(is_controller('DeFaUlT'));
	}
	
	public function test_load_controller() {
		$this->assert_true(class_exists('DefaultController'));
	}
	
	public function test_is_action_of() {
		$this->assert_true(method_exists('DefaultController', 'index'));
	}
	
	public function test_url() {
		$this->assert_equal(url('/hello/'), '/hello/');
		
		//TEST ME MORE!
		
	}
	
	// should this be moved to a separate class/file?
	
	public function test_css() {
	}
	
	public function test__css() {
	}
	
	public function test_compress_css() {
	}
	
	public function test_javascript() {
	}
	
	public function test__javascript() {
	}
	
	public function test_compress_javascript() {
	}
}

?>