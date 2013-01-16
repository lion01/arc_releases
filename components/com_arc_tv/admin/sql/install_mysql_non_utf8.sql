-- package     Arc
-- subpackage  TV
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_tv_access_groups` (
	`video_id` int(11) unsigned NOT NULL,
	`group_id` int(10) NOT NULL,
	PRIMARY KEY (`video_id`, `group_id`),
	INDEX (`group_id`),
	FOREIGN KEY (`group_id`) REFERENCES `#__apoth_cm_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_tv_access_people` (
	`video_id` int(11) unsigned NOT NULL,
	`person_id` varchar(20) NOT NULL,
	PRIMARY KEY (`video_id`, `person_id`),
	INDEX (`person_id`),
	FOREIGN KEY (`person_id`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_tv_access_years` (
	`video_id` int(11) unsigned NOT NULL,
	`year` smallint(5) unsigned NOT NULL,
	PRIMARY KEY (`video_id`,`year`),
	INDEX (`year`)
) ENGINE=InnoDB;


INSERT INTO `#__apoth_sys_roles` (`id`, `parent`, `role`) VALUES
(70,  1, 'tv'),
(71, 70, 'moderator'),
(72, 70, 'owner');