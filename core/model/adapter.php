<?php
/**
 * Basic interaction methods interface
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

interface ModelAdapter {
	
	public function is_column_of($column, $table);
	
	public function fetch_columns($table);

	public function columns($table);

	public function insert($table, $data);
	
	public function update($table, $data, $condition);
	
	public function delete($table, $condition);
	
	public function now();
	
	public function has_many_query($table, $id_name, $id);
	
	public function has_one_query($table, $id_name, $id);
		
	public function belongs_to_query($table, $id);
}

?>