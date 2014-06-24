<?php
/**
 * Exception for Unknown property
 *
 * @filesource
 * @copyright  Copyright (c) 2008-2014, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk/ Prank's project page
 * @link       http://github.com/brego/prank/ Prank's Git repository
 * @package    Prank
 * @subpackage Model
 * @since      Prank 0.20
 * @version    Prank 0.75
 * @todo       Do we really need this? The whole error handling sucks ATM.
 */

/**
 * @package    Prank
 * @subpackage Model
 */
class ModelExceptionsUnknownproperty extends Exception {
	function __construct($property, $model) {
		parent::__construct($property);
		$model = get_class($model);
		$this->message  = 'Unknown property "'.$property.'" in class "'.$model.'"';
	}
}


?>