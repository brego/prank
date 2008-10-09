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
		loader('Prank::Controller::Base');
		$this->assert_true(class_exists('Prank::Controller::Base'));
	}
	
	public function test__() {
	}
	
	public function test_d() {
	}

	public function test_array_cleanup() {
		$array_dirty = array('hello', '', 'kitty', '', 'turtle', '');
		$array_clean = array('hello', 'kitty', 'turtle');

		$this->assert_equal($array_clean, array_cleanup($array_dirty));
	}
	
	public function test_c() {
	}
	
	public function test_rm() {
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