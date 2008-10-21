<?php

class ModelRelations {
	private $relational_data = array();
	
	public function relations() {
		return $this->relational_data;
	}
	
	private function __construct($model, $data, $relations) {
		
		foreach (array_keys($relations) as $relation_type) {
			if ($relations[$relation_type] !== false) {
				if (is_array($relations[$relation_type]) === false) {
					$relations[$relation_type] = a($relations[$relation_type]);
				}
				foreach ($relations[$relation_type] as $relation) {
					if (isset($this->relational_data[$relation]) === false) {
						
						$config = $this->config($relation, $data, $model, $relation_type);
						if ($relation_type == 'has_many' || $relation_type == 'has_and_belongs_to_many') {
							$output = new ModelCollection;
						} else {
							$output = new $config['model'];
						}
						
						$output->relation_type($relation_type);
						
						$output->register_loader(function($internal) use($config) {
							$connection = ModelConnection::instance();
							$method     = $config['type'].'_query';
							$result     = $connection->$method($config);
							if ($config['type'] == 'has_many' || $config['type'] == 'has_and_belongs_to_many') {
								foreach ($result as $object) {
									$internal->add(new $config['model']($object));
								}
							} else {
								foreach ($result as $variable => $value) {
									$internal->$variable = $value;
								}								
							}
						});
						
						$this->relational_data[$relation] = $output;
					}
				}
			}
		}
	}
	
	private function config($relation, $data, $model, $relation_type) {
		$config = array(
			'model'   => Inflector::modelize($relation),
			'local'   => $model->table(),
			'foreign' => Inflector::tabelize($relation),
			'id'      => $data['id'],
			'type'    => $relation_type);
		$config['local_id']   = Inflector::singularize($config['local']).'_id';
		$config['foreign_id'] = Inflector::singularize($config['foreign']).'_id';
		$config['join']       = implode('_', s($config['local'], $config['foreign']));
		if ($relation_type == 'belongs_to') {
			$config['id'] = $data[$config['foreign_id']];
		}
		return $config;
	}
	
	public static function load($model, $data, $relations) {
		$relations = new self($model, $data, $relations);
		return $relations->relations();
	}
}

?>