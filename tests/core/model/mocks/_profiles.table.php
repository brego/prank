<?php

$this->db->exec("CREATE TABLE IF NOT EXISTS `profiles` (
`id` int(11) NOT NULL auto_increment,
`user_id` int(11) NOT NULL,
`title` varchar(255) default NULL,
`text` text default NULL,
`created_at` datetime default NULL,
`updated_at` datetime default NULL,
PRIMARY KEY (`id`) 
) ENGINE=InnoDB");

foreach ($ids as $id) {
	$this->db->exec("INSERT INTO `profiles` SET `user_id`='".$id."', `title`='title".$id."', `text`='Some randomized text for user ".$id."', created_at=NOW();");
}
// var_dump($this->db->errorInfo());
?>