-- package     Arc
-- subpackage  Timetable
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_tt_patterns` (
	`id` int(11) NOT NULL auto_increment,
	`src` TINYINT( 3 ) UNSIGNED NULL DEFAULT 1,
	`ext_model_id` int(11) default NULL,
	`name` varchar(50) NOT NULL,
	`format` text NOT NULL,
	`start_day` smallint(6) NOT NULL default '0',
	`valid_from` datetime default NULL,
	`valid_to` datetime default NULL,
	PRIMARY KEY (`id`),
	INDEX (`src`),
	FOREIGN KEY (`src`) REFERENCES `#__apoth_sys_data_sources` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_tt_pattern_instances` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`pattern` int(11) NOT NULL,
	`start` datetime NOT NULL,
	`end` datetime default NULL,
	`start_index` int(11) NOT NULL,
	`description` text,
	`description_short` varchar(20) default NULL,
	`holiday` tinyint(1) unsigned NOT NULL,
	PRIMARY KEY (`id`),
	INDEX (`start`),
	INDEX (`pattern`),
	FOREIGN KEY (`pattern`) REFERENCES `#__apoth_tt_patterns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_tt_daydetails` (
	`pattern` int(11) NOT NULL,
	`day_type` char(1) NOT NULL,
	`day_section` varchar(30) NOT NULL,
	`day_section_short` varchar(3) default NULL,
	`src` TINYINT( 3 ) UNSIGNED NULL DEFAULT 1,
	`ext_period_id` int(11) default NULL,
	`start_time` time NOT NULL,
	`end_time` time NOT NULL,
	`has_teacher` tinyint(1) default '1',
	`taught` tinyint(1) default '1',
	`registered` tinyint(1) default '1',
	`statutory` tinyint(1) unsigned default '0',
	`valid_from` datetime default NULL,
	`valid_to` datetime default NULL,
	PRIMARY KEY (`pattern`,`day_type`,`day_section`),
	INDEX (`day_section`),
	INDEX (`src`),
	INDEX (`start_time`),
	INDEX (`end_time`),
	FOREIGN KEY (`pattern`) REFERENCES `#__apoth_tt_patterns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`src`) REFERENCES `#__apoth_sys_data_sources` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_tt_timetable` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`course` int(10) default NULL,
	`pattern` int(11) NOT NULL,
	`day` smallint(6) NOT NULL,
	`day_section` varchar(30) NOT NULL,
	`room_id` varchar(50) default NULL,
	`valid_from` datetime default NULL,
	`valid_to` datetime default NULL,
	PRIMARY KEY (`id`),
	INDEX (`course`),
	INDEX (`pattern`),
	INDEX (`day`),
	INDEX (`day_section`),
	FOREIGN KEY (`course`) REFERENCES `#__apoth_cm_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`pattern`) REFERENCES `#__apoth_tt_patterns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`day_section`) REFERENCES `#__apoth_tt_daydetails` (`day_section`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_tt_group_members` (
	`id` int(11) NOT NULL auto_increment,
	`group_id` int(10) NOT NULL,
	`person_id` varchar(20) NOT NULL,
	`role` int(10) unsigned NOT NULL,
	`is_admin` tinyint(1) default '0', /* *** titikaka */
	`is_teacher` tinyint(1) default '0', /* *** titikaka */
	`is_student` tinyint(1) default '0', /* *** titikaka */
	`is_watcher` tinyint(1) default '0', /* *** titikaka */
	`valid_from` datetime NOT NULL,
	`valid_to` datetime default NULL,
	PRIMARY KEY (`id`),
	INDEX (`group_id`),
	INDEX (`person_id`),
	INDEX (`valid_from`),
	INDEX (`valid_to`),
	INDEX (`role`),
	FOREIGN KEY (`group_id`) REFERENCES `#__apoth_cm_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`person_id`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO `#__apoth_sys_roles` (`id`, `parent`, `role`) VALUES
(30,  1, 'group'),
(31, 30, 'supervisor'),
(32, 31, 'admin'),
(33, 31, 'teacher'),
(34, 30, 'participant'),
(35, 34, 'student'),
(36, 34, 'watcher'),
(37, 30, 'peer'),
(38, 37, 'teacher'),
(39, 30, 'ancestor'),
(40, 39, 'admin'),
(41, 39, 'teacher'),
(42, 39, 'student'),
(43, 39, 'watcher'),
(44, 30, 'descendant'),
(45, 44, 'admin'),
(46, 44, 'teacher'),
(47, 44, 'student'),
(48, 44, 'watcher'),
(59, 30, 'successor'),
(60, 59, 'admin'),
(61, 59, 'teacher'),
(62, 59, 'student'),
(63, 59, 'watcher');