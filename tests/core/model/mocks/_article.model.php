<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DS.'core/model/base.php';

class Article extends \Prank\Model\Base {
	public $has_and_belongs_to_many = 'authors';
	public $validates_presence_of   = 'name';
	public $validates_length_of     = array('name' => array('min'=>2, 'max'=>40));
}

?>