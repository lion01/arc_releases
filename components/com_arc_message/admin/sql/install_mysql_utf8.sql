-- package     Arc
-- subpackage  Message
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_msg_messages` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`handler` varchar(50) NOT NULL,
	`author` varchar(50) NOT NULL,
	`created` datetime NOT NULL,
	`applies_on` datetime default NULL,
	`last_modified` datetime default NULL,
	`last_modified_by` varchar(20) default NULL,
	`data_hash` VARCHAR( 32 ) NULL,
	PRIMARY KEY (`id`),
	INDEX (`author`),
	INDEX (`data_hash`)
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_msg_data` (
	`msg_id` int(10) unsigned NOT NULL auto_increment,
	`col_id` varchar(50) NOT NULL,
	`data` varchar(4095) NOT NULL,
	`valid_from` datetime NOT NULL,
	`valid_to` datetime default NULL,
	INDEX (`msg_id`),
	INDEX (`col_id`),
	INDEX (`data`(255)),
	FOREIGN KEY (`msg_id`) REFERENCES `#__apoth_msg_messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_msg_tags` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`parent` int(10) unsigned NOT NULL,
	`label` varchar(50) NOT NULL,
	`category` enum('folder','attribute','user') NOT NULL default 'attribute',
	`enabled` tinyint(1) NOT NULL default '1',
	`order` int(11) NULL default NULL,
	PRIMARY KEY (`id`),
	INDEX (`parent`),
	FOREIGN KEY (`parent`) REFERENCES `#__apoth_msg_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO `#__apoth_msg_tags` (`id`, `parent`, `label`, `category`, `enabled`, `order`) VALUES
( 1,  1, 'Folders',       'folder',    1, 1),
( 2,  2, 'Attributes',    'attribute', 1, NULL),
( 3,  3, 'User tags',     'user',      1, NULL),
( 5,  1, 'Searched',      'folder',    1, 4),
(10,  1, 'Messaging',     'folder',    0, 3),
(11, 10, 'Announcements', 'folder',    0, 1),
(13, 10, 'Archive',       'folder',    0, 2),
(14, 10, 'Bin',           'folder',    0, 3);

CREATE TABLE `#__apoth_msg_tag_map` (
	`msg_id` int(10) unsigned NOT NULL auto_increment,
	`person_id` varchar(20) default NULL,
	`tag_id` int(10) unsigned NOT NULL,
	`from_channel` int(10) unsigned default NULL,
	`valid_from` datetime NOT NULL,
	`valid_to` datetime default NULL,
	INDEX (`msg_id`),
	INDEX (`person_id`),
	INDEX (`tag_id`),
	FOREIGN KEY (`msg_id`) REFERENCES `#__apoth_msg_messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`person_id`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`tag_id`) REFERENCES `#__apoth_msg_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_msg_threads` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`msg_id` int(10) unsigned NOT NULL,
	`order` int(10) unsigned NOT NULL,
	PRIMARY KEY (`id`,`msg_id`),
	INDEX (`msg_id`),
	FOREIGN KEY (`msg_id`) REFERENCES `#__apoth_msg_messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_msg_rules` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`handler` varchar(50) NOT NULL,
	`check` varchar(50) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `#__apoth_msg_rules` (`id`, `handler`, `check`) VALUES
(1, 'message',   'first'),
(2, 'message',   'method'),
(3, 'message',   'time'),
(4, 'behaviour', 'color'),
(5, 'behaviour', 'studentTutor'),
(6, 'behaviour', 'studentYear'),
(7, 'behaviour', 'group'),
(8, 'people',    'person'),
(9, 'behaviour', 'action');

CREATE TABLE `#__apoth_msg_rule_param_sets` (
	`id` int(10) unsigned NOT NULL,
	`rule_id` int(10) unsigned NOT NULL,
	`type` enum('value','variable') NOT NULL,
	`data` varchar(50) NOT NULL,
	`negate` tinyint(1) unsigned NOT NULL default '0',
	`order` int(10) unsigned NOT NULL,
	INDEX (`rule_id`),
	INDEX (`id`),
	FOREIGN KEY (`rule_id`) REFERENCES `#__apoth_msg_rules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_msg_channels` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`default_folder` int(11) NOT NULL,
	`name` varchar(50) NOT NULL,
	`description` text,
	`exclusive` tinyint(1) unsigned NOT NULL default '0',
	`privacy` tinyint(2) unsigned NOT NULL default '0' COMMENT '0=global,1=public,2=private',
	`created_by` varchar(20) NOT NULL,
	`valid_from` datetime NOT NULL,
	`valid_to` datetime default NULL,
	PRIMARY KEY (`id`),
	INDEX (`created_by`),
	FOREIGN KEY (`created_by`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_msg_channel_rules` (
	`channel_id` int(10) unsigned NOT NULL,
	`param_set_id` int(10) unsigned default NULL,
	INDEX (`channel_id`),
	INDEX (`param_set_id`),
	FOREIGN KEY (`channel_id`) REFERENCES `#__apoth_msg_channels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`param_set_id`) REFERENCES `#__apoth_msg_rule_param_sets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_msg_channel_subscribers` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`person_id` varchar(20) default NULL,
	`channel_id` int(10) unsigned NOT NULL,
	`folder` int(11) default NULL,
	`valid_from` datetime default NULL,
	`valid_to` datetime default NULL,
	PRIMARY KEY (`id`),
	INDEX (`person_id`),
	INDEX (`channel_id`),
	FOREIGN KEY (`channel_id`) REFERENCES `#__apoth_msg_channels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO `#__apoth_sys_roles` (`id`, `parent`, `role`) VALUES
(50,  1, 'message'),
(51, 50, 'channel'),
(52, 51, 'owner'),
(53, 51, 'accessor');