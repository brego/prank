<?php

$this->db->exec("CREATE TABLE IF NOT EXISTS `animals` (
`id` int(11) NOT NULL auto_increment,
`name` varchar(255) default NULL,
PRIMARY KEY (`id`) 
) ENGINE=InnoDB");

?>