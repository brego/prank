<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DS.'core/model/base.php';

class Author extends ModelBase {
	protected $has_and_belongs_to_many = 'articles';
	protected $has_one = array('editor');
}

?>