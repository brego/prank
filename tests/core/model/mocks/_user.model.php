<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DS.'core/model/base.php';

class User extends ModelBase {
	protected $has_many = 'cars';
	protected $has_one  = 'profile';
}

?>