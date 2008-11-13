<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DS.'core/model/base.php';

class User extends \Prank\Model\Base {
	public $has_many = 'cars';
	public $has_one  = 'profile';
}

?>