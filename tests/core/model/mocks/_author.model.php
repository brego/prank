<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DS.'core/model/base.php';

class Author extends \Prank\Model\Base {
	public $has_and_belongs_to_many = 'articles';
	public $has_one = array('editor');
}

?>