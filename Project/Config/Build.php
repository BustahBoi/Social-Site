<?php
namespace Project\Config;
use Project\Controllers\DatabaseController;
require_once '..\Config\Autoloader.php';

$connection = parse_ini_file(realpath(__DIR__ . '/Config.ini'), true);
$data = new DatabaseController($connection['Connection']);
$data->createConnection();

$data->dropTable("post");
$data->dropTable("topic");
$data->dropTable("userMessages");
$data->dropTable("profile");
$data->dropTable("users");
$data->dropTable("messages");


$sql = "create table users (
			userId int auto_increment,
			userName varchar(20) not null,
			userPassword varchar(255) not null,
			fulltext key userName(userName),
			primary key(userId)
			)ENGINE=InnoDB;
			";

$result = $data->db->query($sql);

$sql = "create table profile(
            profileId int auto_increment,
            userId int not null,
            profilePic text,
            profileLocation varchar(30),
            profileAbout text,
            profileMotto varchar(80),
            foreign key(userId) references users(userId),
            primary key(profileId)
            )";

$result = $data->db->query($sql);

$sql = "create table messages (
			messageId int auto_increment,
			messageSubject varchar(255),
			messageBody text,
			messageTime datetime,
			fulltext(messageSubject, messageBody),
			primary key(messageId)
			)ENGINE=InnoDB;
			";

$result = $data->db->query($sql);

$sql = "create table userMessages (
			messageId int not null auto_increment,
			userIdFrom int,
			userIdTo int,
			primary key(messageId),
			foreign key(messageId) references messages(messageId),
			foreign key(userIdFrom) references users(userId),
			foreign key(userIdTo) references users(userId)
			)ENGINE=InnoDB;
			";

$result = $data->db->query($sql);

$sql = "create table topic (
			topicNo int not null auto_increment,
			userId int,
			topicTitle varchar(255),
			topicDate datetime,
			topicContent text,
			primary key(topicNo),
			fulltext(topicTitle, topicContent),
			foreign key(userId) references users(userId)
			)ENGINE=InnoDB;
			";

$result = $data->db->query($sql);

$sql = "create table post (
			postNo int auto_increment,
			topicNo int,
			userId int,
			postDate datetime,
			postContent text,
			primary key(postNo),
			fulltext key(postContent),
			foreign key(userId) references users(userId),
			foreign key(topicNo) references topic(topicNo)
			)ENGINE=InnoDB;
			";

$result = $data->db->query($sql);

