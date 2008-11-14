<?php

require_once dirname(dirname(dirname(__FILE__))).DS.'core/registry.php';

class RegistryTestCase extends PrankTestCase {
	public function test_instance() {
		$this->assert_identical(Registry::instance(), Registry::instance());
	}
	
	public function test_registry() {
		$regisry = Registry::instance();
		$regisry->test_object = new stdClass;
		$this->assert_is_a($regisry->test_object, 'stdClass');
	}
}

?>