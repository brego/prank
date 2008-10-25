<?php

class ModelExceptionsUnknownproperty extends Exception {
	function __construct($property, $model) {
		parent::__construct($property);
		$model = get_class($model);
		$this->message  = 'Unknown property "'.$property.'" in class "'.$model.'"';
	}
}


?>