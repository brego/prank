<?php

$this->db->exec("CREATE TABLE IF NOT EXISTS `editors` (
`id` int(11) NOT NULL auto_increment,
`author_id` int(11) NOT NULL,
`name` varchar(255) default NULL,
PRIMARY KEY (`id`) 
) ENGINE=InnoDB");

?>