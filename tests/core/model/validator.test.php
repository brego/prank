<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/model/validator.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/object.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/model/base.php';
require_once 'mocks/_animal.model.php';

class ModelValidatorTestCase extends PrankTestCase {
	
	public function setup() {
		$this->setup_prank_spine();
		$this->db = ModelConnection::instance();
		require 'mocks/_animals.table.php';
	}
	
	public function teardown() {
		$this->teardown_prank_spine();
		$this->db->exec('DROP TABLE `animals`;');
	}

	public function test_validate() {
		$animal = new Animal;
		$animal->name = 'Alfred';
		$this->assert_true ($animal->validates());
		$this->assert_equal($animal->errors(), array());
		
		$animal = new Animal;
		$this->assert_false($animal->validates());
		$this->assert_equal($animal->errors(), array('name'=>array('validate_presence_of', 'validate_length_of')));
		$animal->name = 'LongNameAbove10Characters';
		$this->assert_false($animal->validates());
		$this->assert_equal($animal->errors(), array('name'=>array('validate_length_of')));
		$animal->name = 1;
		$this->assert_false($animal->validates());
		$this->assert_equal($animal->errors(), array('name'=>array('validate_length_of')));
	}

}

?>