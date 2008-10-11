<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/controller/base.php';

class TestController extends Prank::Controller::Base {
	public function action_one() {
	}
}

class ControllerBaseTestCase extends PrankTestCase {
	
	public function setup() {
	}
	
	public function teardown() {
	}	
	
	public function test___call() {
		$test = new TestController;
		$test->set_action('action_one');
		$test->set_view('action_one');
		$test->set_params(array('one', 'two', 'three'));
		$test->set_shortname('test');
		
		$this->assert_equal($test->action,    'action_one');
		$this->assert_equal($test->view,      'action_one');
		$this->assert_equal($test->params,    array('one', 'two', 'three'));
		$this->assert_equal($test->shortname, 'test');
	}

	public function test___set() {
		$test = new TestController;
		$test->test_view_string = 'test';
		$test->test_view_array  = array('one', 'two', 'three');
		
		$this->assert_equal($test->view_variables['test_view_string'], 'test');
		$this->assert_equal($test->view_variables['test_view_array'], array('one', 'two', 'three'));
	}
}

?>