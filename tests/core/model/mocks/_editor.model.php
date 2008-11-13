<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DS.'core/model/base.php';

class Editor extends \Prank\Model\Base {
	public $belongs_to = array('author');
}

?>