<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/model/connection.php';
require_once 'mocks/_user.model.php';

class ModelConnectionTestCase extends PrankTestCase {
	
	public function setup() {
		$this->setup_prank_spine();
		$this->db = ModelConnection::instance();
		require 'mocks/_users.table.php';
	}
	
	public function teardown() {
		$this->teardown_prank_spine();
		$this->db->exec('DROP TABLE `users`;');
	}	
	
	public function test_instance() {
		$this->assert_identical(ModelConnection::instance(), ModelConnection::instance());
	}

}

?>