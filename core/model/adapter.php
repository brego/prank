<?php
/**
 * Basic interaction methods interface
 *
 * @filesource
 * @copyright  Copyright (c) 2008-2010, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk/ Prank's project page
 * @link       http://github.com/brego/prank/ Prank's Git repository
 * @package    Prank
 * @subpackage Model
 * @since      Prank 0.10
 * @version    Prank 0.50
 */

/**
 * Basic interaction methods interface
 * 
 * @package    Prank
 * @subpackage Model
 */
interface ModelAdapter {
	
	public function exec($query);
	
	public function query();
	public function query_to_model($query, $model);
	
	public function last_id();
	
	public function is_column_of($column, $table);
	public function fetch_columns($table);
	public function columns($table);
	
	public function multiple_create();

	public function create($table, $data);
	public function read($table, $model, $condition = '', $order = '', $limit = '');
	public function update($table, $data, $condition);
	public function delete($table, $condition);
	
	public function filter_string($value);
	public function now();
	
	public function has_many_read($info);
	public function has_one_read($info);
	public function belongs_to_read($info);
	public function has_and_belongs_to_many_read($info);
	
	public function has_many_create($table, $data, $relation);
	public function has_one_create($table, $data, $relation);
	public function belongs_to_create($table, $data, $relation);
	public function has_and_belongs_to_many_create($table, $data, $relation);
}

?>