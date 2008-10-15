<?php

$this->db->exec("CREATE TABLE `users` ( 
`id` int(11) NOT NULL auto_increment, 
`email` varchar(255) default NULL, 
`password` varchar(40) default NULL, 
`name` varchar(255) default NULL,
`admin` tinyint(1) default '0', 
`created_at` datetime default NULL,
`updated_at` datetime default NULL,
PRIMARY KEY (`id`) 
) ENGINE=InnoDB");
$ids = array();
$this->db->exec("INSERT INTO `users` SET email='test1@email.com', password='testpassword1', name='test1', created_at=NOW();");
$ids[] = $this->db->lastInsertId();
$this->db->exec("INSERT INTO `users` SET email='test2@email.com', password='testpassword2', name='test2', created_at=NOW();");
$ids[] = $this->db->lastInsertId();

?>