<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/controller/base.php';

class TestController extends ControllerBase {
	public function action_one() {
	}
}

class ControllerBaseTestCase extends PrankTestCase {
	
	public function setup() {
	}
	
	public function teardown() {
	}

	public function test___set() {
		$test = new TestController;
		$test->test_view_string = 'test';
		$test->test_view_array  = array('one', 'two', 'three');
		
		$this->assert_equal(TestController::$view_variables['test_view_string'], 'test');
		$this->assert_equal(TestController::$view_variables['test_view_array'], array('one', 'two', 'three'));
	}
}

?>