<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/helpers/base.php';

class HelpersBaseTestCase extends PrankTestCase {
	public function test_url() {
		$controller = Registry::instance()->current_controller->controller;
		
		$this->assert_equal(url('/hello/'), '/hello/');
		
		$this->assert_equal(url('hello'), '/'.$controller.'/hello');
		
		$this->assert_equal(url('/controller/hello'), '/controller/hello');
		
		$test     = url(array('controller'=>'my_controller', 'action'=>'my_action', 'id'=>123));
		$expected = '/my_controller/my_action/123';
		$this->assert_equal($test, $expected);
		
		$test     = url(array('action'=>'my_action', 'id'=>123));
		$expected = '/'.$controller.'/my_action/123';
		$this->assert_equal($test, $expected);
	}
}

?>