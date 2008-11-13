<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DS.'core/model/base.php';

class Profile extends \Prank\Model\Base {
	public $belongs_to = array('user');
}

?>