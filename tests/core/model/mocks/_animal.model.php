<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DS.'core/model/base.php';

class Animal extends \Prank\Model\Base {
	public function validate() {
		$this->validate_presence_of('name');
		$this->validate_length_of('name', 2, 10);
	}
}

?>