-- package     Arc
-- subpackage  Course
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_cm_types` (
	`type` varchar(10) NOT NULL,
	`description` varchar(50) NOT NULL,
	PRIMARY KEY (`type`)
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_cm_courses` (
	`id` int(10) NOT NULL auto_increment,
	`type` varchar(10) NOT NULL,
	`src` TINYINT( 3 ) UNSIGNED NULL DEFAULT 1,
	`ext_course_id` varchar(20) default NULL,
	`ext_type` varchar(10) default NULL,
	`ext_course_id_2` varchar(20) default NULL,
	`shortname` varchar(15) NOT NULL,
	`fullname` varchar(50) NOT NULL,
	`description` text,
	`parent` int(10) NOT NULL default '0',
	`sortorder` int(10) NOT NULL default '0',
	`start_date` date default NULL,
	`end_date` date default NULL,
	`time_created` datetime default NULL,
	`time_modified` datetime default NULL,
	`reportable` int(11) default NULL,
	`year` tinyint(2) unsigned default NULL,
	`created_by` varchar(20) default NULL,
	`modified_by` varchar(20) default NULL,
	`deleted` tinyint(1) NOT NULL default '0',
	PRIMARY KEY  (`id`),
	INDEX (`type`),
	INDEX (`src`),
	INDEX (`shortname`),
	INDEX (`fullname`),
	INDEX (`parent`),
	INDEX (`year`),
	FOREIGN KEY (`type`) REFERENCES `#__apoth_cm_types` (`type`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`src`) REFERENCES `#__apoth_sys_data_sources` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	FOREIGN KEY (`parent`) REFERENCES `#__apoth_cm_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO `#__apoth_cm_types` (`type`, `description`) VALUES
('extra',    'Extra curricular'),
('non',      'Non-timetabled node'),
('normal',   'Regular lesson'),
('pastoral', 'Registration'),
('pseudo',   'Linked duplicates of existing courses');

SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO `#__apoth_cm_courses` (`id`, `type`, `ext_course_id`, `ext_type`, `ext_course_id_2`, `shortname`, `fullname`, `description`, `parent`, `sortorder`, `start_date`, `end_date`, `time_created`, `time_modified`, `reportable`, `year`, `created_by`, `modified_by`, `deleted`) VALUES
(1, 'non', NULL, 'Root',    NULL, 'Root', 'Root',     'Root course from which everything else inherits', 1, 0, '1970-01-01', NULL, NULL, NULL, 0,    NULL, NULL, NULL, 0),
(2, 'non', NULL, 'subject', NULL, 'Pa',   'Pastoral', 'Pastoral subject which holds all tutor groups',   1, 1, '1970-01-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `#__apoth_cm_pseudo_map` (
	`course` int(10) NOT NULL,
	`twin` int(10) NOT NULL,
	INDEX (`course`),
	INDEX (`twin`),
	FOREIGN KEY (`course`) REFERENCES `#__apoth_cm_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`twin`) REFERENCES `#__apoth_cm_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_cm_pastoral_map` (
	`course` int(10) NOT NULL,
	`pastoral_course` int(10) NOT NULL,
	INDEX (`course`),
	INDEX (`pastoral_course`),
	FOREIGN KEY (`course`) REFERENCES `#__apoth_cm_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`pastoral_course`) REFERENCES `#__apoth_cm_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_cm_courses_ancestry` (
	`id` int(10) NOT NULL default '0',
	`ancestor` int(10) NOT NULL default '0',
	PRIMARY KEY  (`id`,`ancestor`),
	INDEX (`ancestor`),
	FOREIGN KEY (`id`) REFERENCES `#__apoth_cm_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`ancestor`) REFERENCES `#__apoth_cm_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;