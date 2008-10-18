<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DS.'core/model/base.php';

class Profile extends ModelBase {
	protected $belongs_to = 'user';
}

?>