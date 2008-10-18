<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DS.'core/model/base.php';

class Article extends ModelBase {
	protected $has_and_belongs_to_many = 'authors';
}

?>