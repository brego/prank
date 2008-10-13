<?php
/**
 * Base class for sets of Models.
 *
 * Iterator. Used as a base class for collections of Models. Creates methods 
 * for working with sets.
 *
 * PHP version 5.3.
 *
 * @filesource
 * @copyright  Copyright (c) 2008, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk Prank's project page
 * @package    Prank
 * @subpackage Core.Model
 * @since      Prank 0.10
 * @version    Prank 0.10
 */

namespace Prank::Model;

class Set extends ::Collection {
	protected $item_name = 'model';
	
	public function item_name($name=null) {
		if ($name === null) {
			return $this->item_name;
		} else {
			$this->item_name = $name;
		}
	}
}

?>
