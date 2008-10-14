<?php

$this->db->exec("CREATE TABLE `cars` (
`id` int(11) NOT NULL auto_increment,
`user_id` int(11) NOT NULL,
`model` varchar(255) default NULL
`created_at` datetime default NULL,
`updated_at` datetime default NULL,
PRIMARY KEY (`id`) 
) ENGINE=InnoDB");

foreach ($ids as $id) {
	$this->db->exec("INSERT INTO `cars` SET user_id='".$id."', model='Ford".$id."', created_at=NOW();");
	$this->db->exec("INSERT INTO `cars` SET user_id='".$id."', model='Honda".$id."', created_at=NOW();");
	$this->db->exec("INSERT INTO `cars` SET user_id='".$id."', model='Mustang".$id."', created_at=NOW();");
	$this->db->exec("INSERT INTO `cars` SET user_id='".$id."', model='Audi".$id."', created_at=NOW();");
	$this->db->exec("INSERT INTO `cars` SET user_id='".$id."', model='Ferrari".$id."', created_at=NOW();");
}

?>