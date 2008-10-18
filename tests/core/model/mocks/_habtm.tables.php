<?php

$this->db->exec("CREATE TABLE `authors` (
`id` int(11) NOT NULL auto_increment,
`name` varchar(75) default NULL,
PRIMARY KEY (`id`) 
) ENGINE=InnoDB");

$this->db->exec("CREATE TABLE `articles` (
`id` int(11) NOT NULL auto_increment,
`name` varchar(75) default NULL,
`body` text,
PRIMARY KEY (`id`) 
) ENGINE=InnoDB");

$this->db->exec("CREATE TABLE `articles_authors` (
`author_id` int(11),
`article_id` int(11)
) ENGINE=InnoDB");

$this->db->exec("INSERT INTO authors SET name='John';");
$john = $this->db->lastInsertId();
$this->db->exec("INSERT INTO authors SET name='Patric';");
$patric = $this->db->lastInsertId();

$this->db->exec("INSERT INTO articles SET name='Fantasia', body='John & Patrics fantastic article...';");
$fantasia = $this->db->lastInsertId();
$this->db->exec("INSERT INTO articles SET name='Robinson', body='This one is Johns';");
$robinson = $this->db->lastInsertId();

$this->db->exec("INSERT INTO articles_authors SET author_id='$john', article_id='$fantasia';");
$this->db->exec("INSERT INTO articles_authors SET author_id='$patric', article_id='$fantasia';");
$this->db->exec("INSERT INTO articles_authors SET author_id='$john', article_id='$robinson';");


?>