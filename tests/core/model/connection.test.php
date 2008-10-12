<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/model/connection.php';

use Prank::Model::Connection;

class ModelConnectionTestCase extends PrankTestCase {
	
	public function setup() {
		$this->setup_prank_spine();
	}
	
	public function teardown() {
		$this->teardown_prank_spine();
	}	
	
	public function test_instance() {
		$this->assert_equal(Connection::instance(), Connection::instance());
	}

}

?>