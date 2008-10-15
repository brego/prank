<?php

/**
* 
*/
class ModelRegistry {
	private static $instance = null;
	private        $models   = array();
	
	public static function instance() {
		if(self::$instance === null) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	public function register($model_name, $id, $model) {
		if ($this->is_registered($model_name, $id) === false) {
			if (isset($this->models[$model_name]) === false) {
				$this->models[$model_name] = array();
			}
			$this->models[$model_name][] = $model;
		}
	}
	
	public function get($model_name, $id) {
		return $this->models[$model_name][$id];
	}
	
	public function is_registered($model_name, $id) {
		if (isset($this->models[$model_name][$id])) {
			return true;
		} else {
			return false;
		}
	}
}


?>