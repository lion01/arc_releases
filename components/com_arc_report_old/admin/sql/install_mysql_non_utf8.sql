-- package     Arc
-- subpackage  Report
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_rpt_cycles` (
	`id` INT( 11 ) NOT NULL AUTO_INCREMENT,
	`valid_from` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`valid_to` DATETIME DEFAULT NULL,
	`year_group` INT( 10 ) DEFAULT NULL,
	`allow_multiple` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`rechecker` ENUM('first','last') NOT NULL DEFAULT 'first',
	PRIMARY KEY  (`id`)
) TYPE=InnoDB;

CREATE TABLE `#__apoth_rpt_cycles_groups` (
	`cycle` INT( 11 ) NOT NULL ,
	`group` INT( 10 ) NOT NULL ,
	PRIMARY KEY ( `cycle` , `group` ),
	INDEX(`group`),
	FOREIGN KEY (`cycle`) REFERENCES #__apoth_rpt_cycles(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`group`) REFERENCES #__apoth_cm_courses(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) TYPE = InnoDB ;

CREATE TABLE `#__apoth_rpt_reports` (
	`id` INTEGER NOT NULL AUTO_INCREMENT ,
	`cycle` INTEGER NOT NULL ,
	`student` VARCHAR ( 20 ) ,
	`group` INT ( 10 ) ,
	`author` VARCHAR ( 20 ) ,
	`last_modified` DATETIME ,
	`last_modified_by` VARCHAR ( 20 ) ,
	`checked_by_first` VARCHAR( 20 ) DEFAULT NULL ,
	`checked_by` VARCHAR ( 20 ) ,
	`checked_on` DATETIME ,
	`status` ENUM ('draft', 'submitted', 'rejected', 'approved', 'final') ,
	`feedback` TEXT ,
	`stat_1` VARCHAR ( 5 ) ,
	`stat_2` VARCHAR ( 5 ) ,
	`stat_3` VARCHAR ( 5 ) ,
	`stat_4` VARCHAR ( 5 ) ,
	`stat_5` VARCHAR ( 5 ) ,
	`stat_6` VARCHAR ( 5 ) ,
	`stat_7` VARCHAR ( 5 ) ,
	`stat_8` VARCHAR ( 5 ) ,
	`stat_9` VARCHAR ( 5 ) ,
	`stat_10` VARCHAR ( 5 ) ,
	`flag_1` TINYINT ( 1 ) UNSIGNED ,
	`flag_2` TINYINT ( 1 ) UNSIGNED ,
	`text_1` TEXT ,
	`text_2` TEXT ,
	`text_3` TEXT ,
	`text_4` TEXT ,
	PRIMARY KEY ( `id` ) ,
	INDEX ( `cycle` ) ,
	INDEX ( `group` ) ,
	INDEX ( `student` ) ,
	INDEX ( `author` ) ,
	INDEX ( `last_modified_by` ) ,
	INDEX ( `checked_by` ) ,
	FOREIGN KEY ( `cycle` ) REFERENCES #__apoth_rpt_cycles( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `group` ) REFERENCES #__apoth_cm_courses( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `student` ) REFERENCES          #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `author` ) REFERENCES           #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `last_modified_by` ) REFERENCES #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `checked_by` ) REFERENCES       #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_rpt_admins` (
	`cycle` INT NOT NULL ,
	`group` INT ( 10 ) ,
	`person` VARCHAR ( 20 ) ,
	`valid_from` DATETIME ,
	`valid_to` DATETIME ,
	INDEX ( `cycle` ) ,
	INDEX ( `group` ) ,
	INDEX ( `person` ) ,
	FOREIGN KEY ( `group` )  REFERENCES #__apoth_cm_courses( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `person` ) REFERENCES #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `cycle` )  REFERENCES #__apoth_rpt_cycles( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDb;

CREATE TABLE `#__apoth_rpt_peers` (
	`cycle` INT NOT NULL ,
	`group` INT ( 10 ) ,
	`person` VARCHAR ( 20 ) ,
	`valid_from` DATETIME ,
	`valid_to` DATETIME ,
	INDEX ( `group` ) ,
	INDEX ( `person` ) ,
	FOREIGN KEY ( `group` )  REFERENCES #__apoth_cm_courses( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `person` ) REFERENCES #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDb;

CREATE TABLE `#__apoth_rpt_style` (
	`cycle` INT NOT NULL ,
	`group` INT ( 10 ) ,
	`twin` INT ( 10 ) ,
	`use_parent_statements` TINYINT( 1 ) NOT NULL DEFAULT '1' ,
	`page_style` VARCHAR ( 20 ) ,
	`mark_style` VARCHAR ( 20 ) ,
	`blurb_1` TEXT ,
	`blurb_2` TEXT ,
	`print_name` VARCHAR ( 50 ) ,
	`valid_from` DATETIME ,
	`valid_to` DATETIME ,
	PRIMARY KEY ( `group`, `cycle` ) ,
	INDEX ( `twin` ) ,
	INDEX ( `cycle` ) ,
	FOREIGN KEY ( `group` )  REFERENCES #__apoth_cm_courses( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `cycle` )  REFERENCES #__apoth_rpt_cycles( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDb;

CREATE TABLE `#__apoth_rpt_style_fields` (
	`cycle` INT( 11 ) NOT NULL ,
	`group` INT( 10 ) NOT NULL ,
	`template` VARCHAR( 50 ) NOT NULL ,
	`field` VARCHAR( 50 ) NOT NULL ,
	`title` VARCHAR(50) DEFAULT NULL ,
	`lookup_type` VARCHAR( 20 ) NOT NULL ,
	`lookup_id` VARCHAR( 30 ) NULL ,
	`start_date` DATETIME DEFAULT NULL ,
	`end_date` DATETIME DEFAULT NULL ,
	PRIMARY KEY ( `cycle` , `group` , `template` , `field` ) ,
	INDEX ( `group` ),
	FOREIGN KEY ( `cycle` ) REFERENCES #__apoth_rpt_cycles( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `group` ) REFERENCES #__apoth_cm_courses( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_rpt_statements` (
	`id` INT AUTO_INCREMENT ,
	`field` VARCHAR ( 25 ) ,
	`keyword` VARCHAR ( 50 ) ,
	`text` TEXT ,
	`range_min` VARCHAR ( 5 ) ,
	`range_max` VARCHAR ( 5 ) ,
	`range_of` VARCHAR ( 25 ) ,
	`color` VARCHAR ( 25 ) ,
	PRIMARY KEY ( `id` )
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_rpt_statements_map` (
	`statement_id` 	INT( 11 ) NOT NULL ,
	`group_id` INT( 10 ) NOT NULL ,
	`cycle_id` INT( 11 ) NOT NULL ,
	`order` INT( 11 ) NULL ,
	PRIMARY KEY ( `statement_id`, `group_id`, `cycle_id` ) ,
	INDEX( `group_id` ) ,
	INDEX( `cycle_id` ) ,
	FOREIGN KEY ( `statement_id` )  REFERENCES #__apoth_rpt_statements( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `group_id` )  REFERENCES #__apoth_cm_courses( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `cycle_id` )  REFERENCES #__apoth_rpt_cycles( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;


CREATE TABLE `#__apoth_rpt_merge_words` (
	`id` INT( 10 ) NOT NULL AUTO_INCREMENT,
	`word` VARCHAR( 128 ) NOT NULL ,
	`male` VARCHAR( 128 ) NULL ,
	`female` VARCHAR( 128 ) NULL ,
	`neuter` VARCHAR( 128 ) NULL ,
	`property` VARCHAR( 20 ) NULL ,
	PRIMARY KEY ( `id`) ,
	INDEX( `word` )
) TYPE = MYISAM;

INSERT INTO `#__apoth_rpt_merge_words` 
( `id`, `word` , `male` , `female` , `neuter` , `property` )
VALUES
(  '1', '.', NULL , NULL , CHAR(149), NULL ) ,
(  '2', 'Name', NULL, NULL, NULL, 'Name') ,
(  '3', 'He/She', 'He', 'She', 'This child', NULL) ,
(  '4', 'he/she', 'he', 'she', 'they', NULL) ,
(  '5', 'Him/Her', 'Him', 'Her', 'Their', NULL) ,
(  '6', 'him/her', 'him', 'her', 'their', NULL) ,
(  '7', 'His/Her', 'His', 'Her', 'Their', NULL) ,
(  '8', 'his/her', 'his', 'her', 'their', NULL) ,
(  '9', 'Himself/Herself', 'Himself', 'Herself', 'Themself', NULL) ,
( '10', 'himself/herself', 'himself', 'herself', 'themself', NULL) ,
( '11', 'son/daughter', 'son', 'daughter', 'child', NULL) ,
( '12', 'Subject', NULL, NULL, NULL, 'Subject') ,
( '13', 'subject', NULL, NULL, NULL, 'subject') ,
( '14', 'grade', NULL, NULL, NULL, 'grade') ,
( '15', 'clp', NULL, NULL, NULL, 'clp') ,
( '16', 'clp2', NULL, NULL, NULL, 'clp2');

INSERT INTO `#__apoth_sys_roles` (`id`, `parent`, `role`) VALUES
(60,  1, 'report'),
(61, 60, 'admin'),
(62, 60, 'peer'),
(63, 60, 'last editor'),
(64, 60, 'author'),
(65, 60, 'student');