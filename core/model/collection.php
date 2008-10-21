<?php

class ModelCollection extends Collection {
	private $modified      = false;
	private $relation_type = false;
	
	public function modified() {
		$this->check_modified();
		return $this->modified;
	}
	
	public function relation_type($relation_type = null) {
		if ($relation_type !== null) {
			$this->relation_type = $relation_type;
		}
		return $this->relation_type;
	}
	
	public function relation() {
		// if ($this->relation_type !== false) {
		// 		return true;
		// 	} else {
		// 		return false;
		// 	}
		return $this->relation_type;
	}
	
	private function check_modified() {
		if ($this->modified === false) {
			foreach ($this->items as $model) {
				if ($model->modified() === true) {
					$this->modified = true;
					break;
				}
			}
		}
	}
}

?>