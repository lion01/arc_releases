-- package     Arc
-- subpackage  Core
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_sys_roles` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`parent` int(10) unsigned NOT NULL,
	`role` varchar(50) NOT NULL,
	PRIMARY KEY (`id`),
	INDEX (`parent`)
) ENGINE=InnoDB;

INSERT INTO `#__apoth_sys_roles` (`id`, `parent`, `role`) VALUES
(1, 1, 'any'),
(2, 1, 'sys'),
(3, 2, 'admin'),
(4, 2, 'operator'),
(5, 2, 'teacher'),
(6, 2, 'user'),
(7, 1, 'public');

CREATE TABLE `#__apoth_sys_roles_ancestry` (
	`id` int(10) unsigned NOT NULL default '0',
	`ancestor` int(10) unsigned NOT NULL default '0',
	PRIMARY KEY (`id`,`ancestor`),
	INDEX (`ancestor`),
	FOREIGN KEY (`id`) REFERENCES `#__apoth_sys_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`ancestor`) REFERENCES `#__apoth_sys_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_sys_actions` (
	`id` int(11) NOT NULL auto_increment,
	`menu_id` int(11) default NULL,
	`option` varchar(50) default NULL,
	`task` varchar(50) default NULL,
	`params` varchar(512) default NULL,
	`name` varchar(100) default NULL,
	`menu_text` varchar(100) default NULL,
	`description` text,
	PRIMARY KEY (`id`),
	INDEX (`menu_id`),
	INDEX (`name`)
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_sys_action_context` (
	`from_id` INT NOT NULL ,
	`to_id` INT NOT NULL ,
	`order` INT UNSIGNED NOT NULL ,
	`category` VARCHAR( 20 ) NOT NULL ,
	`text` VARCHAR( 100 ) NOT NULL ,
	`target` ENUM( 'self', 'blank', 'popup' ) NOT NULL,
	KEY `from_id` (`from_id`),
	KEY `to_id` (`to_id`),
	KEY `order` (`order`),
	FOREIGN KEY (`to_id`) REFERENCES `#__apoth_sys_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`from_id`) REFERENCES `#__apoth_sys_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB

CREATE TABLE `#__apoth_sys_com_roles` (
	`person_id` varchar(20) NOT NULL,
	`role` int(10) unsigned default NULL,
	INDEX (`person_id`)
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_sys_acl` (
	`action` int(11) NOT NULL,
	`role` int(10) unsigned NOT NULL,
	`sees` int(10) unsigned default NULL,
	`allowed` tinyint(1) unsigned NOT NULL default '0',
	PRIMARY KEY (`action`,`role`),
	INDEX (`sees`),
	INDEX (`role`),
	FOREIGN KEY (`action`) REFERENCES `#__apoth_sys_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`role`) REFERENCES `#__apoth_sys_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`sees`) REFERENCES `#__apoth_sys_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `#__apoth_sys_tmp_deletables` (
  `id` varchar(50) NOT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `#__apoth_sys_tmp_tables` (
  `id` varchar(50) NOT NULL,
  `exists` tinyint(1) unsigned NOT NULL default '0',
  `populated` tinyint(1) unsigned NOT NULL default '0',
  `expires` datetime NOT NULL,
  `component` varchar(50) NOT NULL,
  `table` varchar(50) NOT NULL,
  `user` varchar(20) NOT NULL,
  `action` int(10) unsigned default NULL,
  `from` datetime default NULL,
  `to` datetime default NULL,
  PRIMARY KEY  (`id`),
  INDEX (`user`)
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_sys_favourites` (
	`action` int(10) unsigned NOT NULL,
	`role` int(10) unsigned NOT NULL,
	PRIMARY KEY (`action`,`role`),
	INDEX (`role`)
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_sys_log` (
	`id` bigint(20) NOT NULL auto_increment,
	`j_userid` int(11) default NULL,
	`ip_add` varchar(15) default NULL,
	`action_time` datetime NOT NULL,
	`url` text NOT NULL,
	`get_data` text,
	`post_data` text,
	PRIMARY KEY (`id`),
	INDEX (`j_userid`),
	INDEX (`ip_add`)
) ENGINE=MyISAM;

CREATE TABLE `#__apoth_sys_markstyles_info` (
	`style` varchar(20) NOT NULL,
	`label` varchar(100) NOT NULL,
	`format` varchar(20) default NULL,
	`type` varchar(20) NOT NULL,
	PRIMARY KEY  (`style`)
) ENGINE=InnoDB;

INSERT INTO `#__apoth_sys_markstyles_info` (`style`, `label`, `format`, `type`) VALUES
('bands', 'Bands', NULL, 'mark'),
('boolean', 'Yes/No, True/False', NULL, 'mark'),
('btec_pe', 'BTEC PE', NULL, 'mark'),
('citizenship', 'Exceed/Achieve/Fail', NULL, 'mark'),
('comment', 'Comment/Text', NULL, 'text'),
('completed', 'Completed', NULL, 'mark'),
('grades', 'Grades', NULL, 'mark'),
('grades_diploma', 'Grades (Diploma)', NULL, 'mark'),
('grades_limited', 'Grades (Limited)', NULL, 'mark'),
('intervention', 'Intervention', NULL, 'mark'),
('levels', 'Levels', NULL, 'mark'),
('levels - whole', 'Levels (Whole)', NULL, 'mark'),
('passfail', 'Pass/Fail', NULL, 'mark'),
('percent', 'Percent', '[[p]]%', 'numeric'),
('score', 'Score', '[[s]]/[[t]]', 'numeric'),
('splitbands', 'Split Bands', NULL, 'mark'),
('submission', 'Submission Status', NULL, 'mark'),
('traffic lights', 'Poor/Satisfactory/Good', NULL, 'mark'),
('working', 'Working Standard', NULL, 'mark');

CREATE TABLE `#__apoth_sys_markstyles` (
	`style` varchar(20) NOT NULL default '',
	`order` tinyint(5) NOT NULL default '0',
	`mark` varchar(5) default NULL,
	`description` varchar(50) NOT NULL default '',
	`color` varchar(20) default NULL,
	`pc_min` decimal(5,2) default '0.00',
	`pc_max` decimal(5,2) default '0.00',
	`pc_equivalent` decimal(5,2) default '0.00',
	`valid_from` datetime NOT NULL default '0000-00-00 00:00:00',
	`valid_to` datetime default NULL,
	PRIMARY KEY  (`style`,`order`),
	INDEX (`mark`),
	FOREIGN KEY (`style`) REFERENCES `#__apoth_sys_markstyles_info` (`style`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO `#__apoth_sys_markstyles` (`style`, `order`, `mark`, `description`, `color`, `pc_min`, `pc_max`, `pc_equivalent`, `valid_from`, `valid_to`) VALUES
('bands', 0, 'D', 'Distinction', '#00FF00', '80.00', '100.00', '90.00', '1970-01-01 00:00:00', NULL),
('bands', 1, 'M', 'Merit',       '#CCFF00', '60.00', '80.00',  '70.00', '1970-01-01 00:00:00', NULL),
('bands', 2, 'C', 'Credit',      '#FFFF00', '40.00', '60.00',  '50.00', '1970-01-01 00:00:00', NULL),
('bands', 3, 'P', 'Pass',        '#FFCC00', '20.00', '40.00',  '30.00', '1970-01-01 00:00:00', NULL),
('bands', 4, 'U', 'Ungraded',    '#FF0000', '0.00',  '20.00',  '10.00', '1970-01-01 00:00:00', NULL),

('boolean', 0, 'Y', 'Yes', '#00FF00', '50.00', '100.00', '100.00', '1970-01-01 00:00:00', NULL),
('boolean', 1, 'N', 'No',  '#FF0000', '0.00',  '50.00',  '0.00',   '1970-01-01 00:00:00', NULL),

('btec_pe', 0, 'D*', 'Distinction Star', '#00FF00', '95.00', '100.00', '97.00', '1970-01-01 00:00:00', NULL),
('btec_pe', 1, 'D',  'Distinction',      '#00FF00', '80.00', '95.00',  '90.00', '1970-01-01 00:00:00', NULL),
('btec_pe', 2, 'M',  'Merit',            '#CCFF00', '60.00', '80.00',  '70.00', '1970-01-01 00:00:00', NULL),
('btec_pe', 3, 'C',  'Credit',           '#FFFF00', '40.00', '60.00',  '50.00', '1970-01-01 00:00:00', NULL),
('btec_pe', 4, 'P',  'Pass',             '#FFCC00', '20.00', '40.00',  '30.00', '1970-01-01 00:00:00', NULL),
('btec_pe', 5, 'R',  'Referred',         '#FF0000', '0.00',  '20.00',  '10.00', '1970-01-01 00:00:00', NULL),

('citizenship', 0, 'E', 'Exceed',  '#0000FF', '80.00', '100.00', '90.00', '1970-01-01 00:00:00', NULL),
('citizenship', 1, 'A', 'Achieve', '#00FF00', '40.00', '80.00',  '60.00', '1970-01-01 00:00:00', NULL),
('citizenship', 2, 'F', 'Fail',    '#FF0000', '0.00',  '40.00',  '20.00', '1970-01-01 00:00:00', NULL),

('completed', 1, 'Y', 'Yes',     NULL, '65.00', '100.00', '100.00', '1970-01-01 00:00:00', NULL),
('completed', 2, 'L', 'Late',    NULL, '25.00', '65.00',  '60.00',  '1970-01-01 00:00:00', NULL),
('completed', 3, 'A', 'Absence', NULL, '10.00', '25.00',  '20.00',  '1970-01-01 00:00:00', NULL),
('completed', 4, 'N', 'No',      NULL, '0.00',  '10.00',  '0.00',   '1970-01-01 00:00:00', NULL),

('grades', 0, 'A*', 'A*', '#00FF00', '90.00', '100.00', '95.00', '1970-01-01 00:00:00', NULL),
('grades', 1, 'A', 'A',   '#99FF00', '80.00', '90.00',  '85.00', '1970-01-01 00:00:00', NULL),
('grades', 2, 'B', 'B',   '#CCFF00', '70.00', '80.00',  '75.00', '1970-01-01 00:00:00', NULL),
('grades', 3, 'C', 'C',   '#FFFF99', '60.00', '70.00',  '65.00', '1970-01-01 00:00:00', NULL),
('grades', 4, 'D', 'D',   '#FFFF00', '50.00', '60.00',  '55.00', '1970-01-01 00:00:00', NULL),
('grades', 5, 'E', 'E',   '#FFCC99', '40.00', '50.00',  '45.00', '1970-01-01 00:00:00', NULL),
('grades', 6, 'F', 'F',   '#FFCC00', '30.00', '40.00',  '35.00', '1970-01-01 00:00:00', NULL),
('grades', 7, 'G', 'G',   '#FF9900', '20.00', '30.00',  '25.00', '1970-01-01 00:00:00', NULL),
('grades', 8, 'U', 'U',   '#FF0000', '0.01',  '20.00',  '10.00', '1970-01-01 00:00:00', NULL),
('grades', 9, 'N', 'N',   '#000000', '0.00',  '0.01',   '0.00',  '1970-01-01 00:00:00', NULL),

('grades_diploma', 0, 'A*', 'A*', '#00FF0',  '90.00', '100.00', '95.00', '1970-01-01 00:00:00', NULL),
('grades_diploma', 1, 'A',  'A',  '#99FF00', '80.00', '90.00',  '85.00', '1970-01-01 00:00:00', NULL),
('grades_diploma', 2, 'B',  'B',  '#CCFF00', '70.00', '80.00',  '75.00', '1970-01-01 00:00:00', NULL),
('grades_diploma', 3, 'C',  'C',  '#FFFF99', '60.00', '70.00',  '65.00', '1970-01-01 00:00:00', NULL),
('grades_diploma', 4, 'U',  'U',  '#FF0000', '0.00',  '60.00',  '10.00', '1970-01-01 00:00:00', NULL),

('grades_limited', 1, 'A', 'A', '#99FF00', '80.00', '100.00', '85.00', '1970-01-01 00:00:00', NULL),
('grades_limited', 2, 'B', 'B', '#CCFF00', '70.00', '80.00',  '75.00', '1970-01-01 00:00:00', NULL),
('grades_limited', 3, 'C', 'C', '#FFFF99', '60.00', '70.00',  '65.00', '1970-01-01 00:00:00', NULL),
('grades_limited', 4, 'D', 'D', '#FFFF00', '50.00', '60.00',  '55.00', '1970-01-01 00:00:00', NULL),
('grades_limited', 5, 'E', 'E', '#FFCC99', '0.00',  '50.00',  '45.00', '1970-01-01 00:00:00', NULL),

('intervention', 0, 'Yes', 'Yes',            NULL, '0.00',  '40.00',  '20.00', '1970-01-01 00:00:00', NULL),
('intervention', 1, 'No',  'No',             NULL, '40.00', '80.00',  '60.00', '1970-01-01 00:00:00', NULL),
('intervention', 2, 'N/A', 'Not Applicable', NULL, '80.00', '100.00', '90.00', '1970-01-01 00:00:00', NULL),

('levels', 0, '8.8',  '8.8', '#00FF00', '86.00', '100.00', '88.00', '1970-01-01 00:00:00', NULL),
('levels', 1, '8.5',  '8.5', '#00FF00', '83.00', '86.00',  '85.00', '1970-01-01 00:00:00', NULL),
('levels', 2, '8.2',  '8.2', '#00FF00', '81.00', '83.00',  '82.00', '1970-01-01 00:00:00', NULL),
('levels', 3, '8.0',  '8.0', '#00FF00', '79.00', '81.00',  '80.00', '1970-01-01 00:00:00', NULL),
('levels', 4, '7.8',  '7.8', '#99FF00', '76.00', '79.00',  '78.00', '1970-01-01 00:00:00', NULL),
('levels', 5, '7.5',  '7.5', '#99FF00', '73.00', '76.00',  '75.00', '1970-01-01 00:00:00', NULL),
('levels', 6, '7.2',  '7.2', '#99FF00', '71.00', '73.00',  '72.00', '1970-01-01 00:00:00', NULL),
('levels', 7, '7.0',  '7.0', '#99FF00', '69.00', '71.00',  '70.00', '1970-01-01 00:00:00', NULL),
('levels', 8, '6.8',  '6.8', '#CCFF00', '66.00', '69.00',  '68.00', '1970-01-01 00:00:00', NULL),
('levels', 9, '6.5',  '6.5', '#CCFF00', '63.00', '66.00',  '65.00', '1970-01-01 00:00:00', NULL),
('levels', 10, '6.2', '6.2', '#CCFF00', '61.00', '63.00',  '62.00', '1970-01-01 00:00:00', NULL),
('levels', 11, '6.0', '6.0', '#CCFF00', '59.00', '61.00',  '60.00', '1970-01-01 00:00:00', NULL),
('levels', 12, '5.8', '5.8', '#FFFF99', '56.00', '59.00',  '58.00', '1970-01-01 00:00:00', NULL),
('levels', 13, '5.5', '5.5', '#FFFF99', '53.00', '56.00',  '55.00', '1970-01-01 00:00:00', NULL),
('levels', 14, '5.2', '5.2', '#FFFF99', '51.00', '53.00',  '52.00', '1970-01-01 00:00:00', NULL),
('levels', 15, '5.0', '5.0', '#FFFF99', '49.00', '51.00',  '50.00', '1970-01-01 00:00:00', NULL),
('levels', 16, '4.8', '4.8', '#FFFF00', '46.00', '49.00',  '48.00', '1970-01-01 00:00:00', NULL),
('levels', 17, '4.5', '4.5', '#FFFF00', '43.00', '46.00',  '45.00', '1970-01-01 00:00:00', NULL),
('levels', 18, '4.2', '4.2', '#FFFF00', '41.00', '43.00',  '42.00', '1970-01-01 00:00:00', NULL),
('levels', 19, '4.0', '4.0', '#FFFF00', '39.00', '41.00',  '40.00', '1970-01-01 00:00:00', NULL),
('levels', 20, '3.8', '3.8', '#FFCC99', '36.00', '39.00',  '38.00', '1970-01-01 00:00:00', NULL),
('levels', 21, '3.5', '3.5', '#FFCC99', '33.00', '36.00',  '35.00', '1970-01-01 00:00:00', NULL),
('levels', 22, '3.2', '3.2', '#FFCC99', '31.00', '33.00',  '32.00', '1970-01-01 00:00:00', NULL),
('levels', 23, '3.0', '3.0', '#FFCC99', '29.00', '31.00',  '30.00', '1970-01-01 00:00:00', NULL),
('levels', 24, '2.8', '2.8', '#FFCC00', '26.00', '29.00',  '28.00', '1970-01-01 00:00:00', NULL),
('levels', 25, '2.5', '2.5', '#FFCC00', '23.00', '26.00',  '25.00', '1970-01-01 00:00:00', NULL),
('levels', 26, '2.2', '2.2', '#FFCC00', '21.00', '23.00',  '22.00', '1970-01-01 00:00:00', NULL),
('levels', 27, '2.0', '2.0', '#FFCC00', '19.00', '21.00',  '20.00', '1970-01-01 00:00:00', NULL),
('levels', 28, '1.8', '1.8', '#FF9900', '16.00', '19.00',  '18.00', '1970-01-01 00:00:00', NULL),
('levels', 29, '1.5', '1.5', '#FF9900', '13.00', '16.00',  '15.00', '1970-01-01 00:00:00', NULL),
('levels', 30, '1.2', '1.2', '#FF9900', '11.00', '13.00',  '12.00', '1970-01-01 00:00:00', NULL),
('levels', 31, '1.0', '1.0', '#FF9900', '9.00',  '11.00',  '10.00', '1970-01-01 00:00:00', NULL),
('levels', 32, '0.8', '0.8', '#FF0000', '6.00',  '9.00',   '8.00',  '1970-01-01 00:00:00', NULL),
('levels', 33, '0.5', '0.5', '#FF0000', '3.00',  '6.00',   '5.00',  '1970-01-01 00:00:00', NULL),
('levels', 34, '0.2', '0.2', '#FF0000', '1.00',  '3.00',   '2.00',  '1970-01-01 00:00:00', NULL),
('levels', 35, '0',   '0',   '#FF0000', '0.01',  '1.00',   '0.01',  '1970-01-01 00:00:00', NULL),
('levels', 36, 'N',   'N',   '#000000', '0.00',  '0.01',   '0.00',  '1970-01-01 00:00:00', NULL),

('levels - whole', 0, '8', '8', '#00FF00', '75.00', '100.00', '80.00', '1970-01-01 00:00:00', NULL),
('levels - whole', 1, '7', '7', '#99FF00', '65.00', '75.00',  '70.00', '1970-01-01 00:00:00', NULL),
('levels - whole', 2, '6', '6', '#CCFF00', '55.00', '65.00',  '60.00', '1970-01-01 00:00:00', NULL),
('levels - whole', 3, '5', '5', '#FFFF99', '45.00', '55.00',  '50.00', '1970-01-01 00:00:00', NULL),
('levels - whole', 4, '4', '4', '#FFFF00', '35.00', '45.00',  '40.00', '1970-01-01 00:00:00', NULL),
('levels - whole', 5, '3', '3', '#FFCC99', '25.00', '35.00',  '30.00', '1970-01-01 00:00:00', NULL),
('levels - whole', 6, '2', '2', '#FFCC00', '15.00', '25.00',  '20.00', '1970-01-01 00:00:00', NULL),
('levels - whole', 7, '1', '1', '#FF9900', '1.00',  '15.00',  '10.00', '1970-01-01 00:00:00', NULL),
('levels - whole', 8, '0', '0', '#FF0000', '0.01',  '1.00',   '0.01',  '1970-01-01 00:00:00', NULL),
('levels - whole', 9, 'N', 'N', '#000000', '0.00',  '0.01',   '0.00',  '1970-01-01 00:00:00', NULL),

('passfail', 0, 'P', 'Pass', '#00FF00', '50.00', '100.00', '100.00', '1970-01-01 00:00:00', NULL),
('passfail', 1, 'F', 'Fail', '#FF0000', '0.00',  '50.00',  '0.00',   '1970-01-01 00:00:00', NULL),

('percent', 0, '0',   '[[p]]%', NULL, NULL,   '0.00',   '0.00',   '1970-01-01 00:00:00', NULL),
('percent', 1, '100', '[[p]]%', NULL, '0.00', '100.00', '100.00', '1970-01-01 00:00:00', NULL),

('score', 0, '0',  '[[s]]/[[t]]', NULL, NULL, '0.00',   '0.00',   '1970-01-01 00:00:00', NULL),
('score', 1, '10', '[[s]]/[[t]]', NULL, NULL, '100.00', '100.00', '1970-01-01 00:00:00', NULL),

('splitbands', 0, 'D1', 'Distinction 1', '#00FF00', '90.00', '100.00', '95.00', '1970-01-01 00:00:00', NULL),
('splitbands', 1, 'D2', 'Distinction 2', '#00FF00', '80.00', '90.00',  '85.00', '1970-01-01 00:00:00', NULL),
('splitbands', 2, 'M1', 'Merit 1',       '#CCFF00', '70.00', '80.00',  '75.00', '1970-01-01 00:00:00', NULL),
('splitbands', 3, 'M2', 'Merit 2',       '#CCFF00', '60.00', '70.00',  '65.00', '1970-01-01 00:00:00', NULL),
('splitbands', 4, 'C1', 'Credit 1',      '#FFFF00', '50.00', '60.00',  '55.00', '1970-01-01 00:00:00', NULL),
('splitbands', 5, 'C2', 'Credit 2',      '#FFFF00', '40.00', '50.00',  '45.00', '1970-01-01 00:00:00', NULL),
('splitbands', 6, 'P1', 'Pass 1',        '#FFCC00', '30.00', '40.00',  '35.00', '1970-01-01 00:00:00', NULL),
('splitbands', 7, 'P2', 'Pass 2',        '#FFCC00', '20.00', '30.00',  '25.00', '1970-01-01 00:00:00', NULL),
('splitbands', 8, 'U',  'Ungraded',      '#FF0000', '0.00',  '20.00',  '10.00', '1970-01-01 00:00:00', NULL),

('submission', 0, 'OT', 'On time',       '#CC55FF', '65.00', '100.00', '80.00', '1970-01-01 00:00:00', NULL),
('submission', 1, 'L',  'Late',          '#CCFFFF', '35.00', '65.00',  '50.00', '1970-01-01 00:00:00', NULL),
('submission', 2, 'NS', 'Not submitted', '#0000DC', '0.00',  '35.00',  '20.00', '1970-01-01 00:00:00', NULL),

('traffic lights', 0, 'G', 'Good',         '#00FF00', '65.00', '100.00', '80.00', '1970-01-01 00:00:00', NULL),
('traffic lights', 1, 'S', 'Satisfactory', '#FFCC00', '35.00', '65.00',  '50.00', '1970-01-01 00:00:00', NULL),
('traffic lights', 2, 'P', 'Poor',         '#FF0000', '0.00',  '35.00',  '20.00', '1970-01-01 00:00:00', NULL),

('working', 1, 'WB', 'Working Beyond',  NULL, '75.00', '100.00', '80.00', '1970-01-01 00:00:00', NULL),
('working', 2, 'WA', 'Working At',      NULL, '25.00', '75.00',  '50.00', '1970-01-01 00:00:00', NULL),
('working', 3, 'WT', 'Working Towards', NULL, '0.00',  '25.00',  '20.00', '1970-01-01 00:00:00', NULL);

CREATE TABLE `#__apoth_sys_data_sources` (
	`id` TINYINT( 3 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR( 20 ),
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `#__apoth_sys_data_sources`
VALUES
(1, 'arc'),
(2, 'csv'),
(3, 'MIStA - SIMS');

CREATE TABLE `#__apoth_sys_import_batches` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`created` datetime NOT NULL,
	`component` varchar(50) NOT NULL,
	`class` varchar(50) NOT NULL,
	`callback` varchar(50) NOT NULL,
	`params` text NOT NULL,
	`done` tinyint(1) NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `created` (`created`)
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_sys_import_queue` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`batch_id` int(10) unsigned NOT NULL,
	`src` TINYINT( 3 ) UNSIGNED NULL DEFAULT 1,
	`call` varchar(50) NOT NULL,
	`params` text NOT NULL,
	`taken` tinyint(1) NOT NULL,
	`ready` tinyint(1) NOT NULL default '0',
	PRIMARY KEY (`id`),
	INDEX `created` (`batch_id`),
	INDEX (`src`),
	FOREIGN KEY (`batch_id`) REFERENCES `jos_apoth_sys_import_batches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`src`) REFERENCES `jos_apoth_sys_data_sources` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
