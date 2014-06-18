<?php
/**
 * Model Validation class
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
 */

/**
 * Model Validation class
 * 
 * Contains only static methods: basic framework for validating the model, and
 * validator methods themselves.
 *
 * @package    Prank
 * @subpackage Model
 */
class ModelValidator {
	private static $results = array();

/**
 * Private constructor - this is a static class
 *
 * @return void
 */
	private function __construct() {}
	
/**
 * Checks if given model is valid
 *
 * If every validation passes, returns true. Else, returns an array of names of
 * failed validations in following format:
 * array(field=>array(validation[, ...]))
 * 
 * @param  Model $model 
 * @return mixed
 */
	public static function validate($model) {
		$model->validate();
		$failures = array();
		foreach (self::$results[get_class($model)] as $field => $validations) {
			foreach ($validations as $validation => $result) {
				if ($result === false) {
					$failures[$field][] = $validation;
				}
			}
		}
		
		if (empty($failures) !== true) {
			return $failures;
		} else {
			return true;
		}
	}
	
/**
 * Registers a result of a validation
 *
 * @param  Model   $model 
 * @param  string  $method 
 * @param  string  $field 
 * @param  boolean $result 
 * @return void
 */
	private static function register_result($model, $method, $field, $result) {
		self::$results[get_class($model)][$field][$method] = $result;
	}

/**
 * Checks if the $field is present & nonempty in $model
 *
 * @param  Model   $model 
 * @param  string  $field 
 * @return boolean
 */	
	public static function validate_presence_of($model, $field) {
		$result = isset($model->$field);
		
		self::register_result($model, 'validate_presence_of', $field, $result);
		return $result;
	}

/**
 * Checks if $field is between $min and $max
 *
 * @param  Model   $model 
 * @param  string  $field 
 * @param  integer $min 
 * @param  integer $max
 * @return boolean
 */
	public static function validate_length_of($model, $field, $min, $max) {
		if (isset($model->$field)) {
			$length = strlen($model->$field);
			if ($length <= $max && $length >= $min) {
				$result = true;
			} else {
				$result = false;
			}
		} else {
			$result = false;
		}
		
		self::register_result($model, 'validate_length_of', $field, $result);
		return $result;
	}
}

?>