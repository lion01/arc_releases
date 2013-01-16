-- package     Arc
-- subpackage  Assessment
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_ass_assessments` (
	`id` INT ( 10 ) NOT NULL AUTO_INCREMENT,
	`parent` INT ( 10 ) ,
	`title` VARCHAR ( 255 ) ,
	`color` VARCHAR ( 20 ) ,
	`always_show` TINYINT ( 1 ) NOT NULL DEFAULT '0' ,
	`group_specific` TINYINT ( 1 ) NOT NULL DEFAULT '1',
	`ext_source` VARCHAR ( 20 ) ,
	`ext_id` VARCHAR ( 50 ) ,
	`locked` TINYINT NOT NULL DEFAULT '0' ,
	`locked_by` VARCHAR ( 20 ) ,
	`locked_on` DATETIME ,
	`created_by` VARCHAR ( 20 ) NOT NULL ,
	`created_on` DATETIME ,
	`modified_by` VARCHAR ( 20 ) ,
	`modified_on` DATETIME ,
	`valid_from` DATETIME NOT NULL ,
	`valid_to` DATETIME ,
	`deleted` TINYINT ( 1 ) NOT NULL DEFAULT '0',
	`allow_teacher` TINYINT ( 1 ) UNSIGNED NOT NULL DEFAULT '0' ,
	`allow_parent`  TINYINT ( 1 ) UNSIGNED NOT NULL DEFAULT '0' ,
	`allow_student` TINYINT ( 1 ) UNSIGNED NOT NULL DEFAULT '0' ,
	`allow_buddy`   TINYINT ( 1 ) UNSIGNED NOT NULL DEFAULT '0' ,
	PRIMARY KEY  (`id`)
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_ass_course_map` (
	`group` INT ( 10 ) ,
	`assessment` INT ( 10 ) ,
	INDEX ( `group` ) ,
	INDEX ( `assessment` ) ,
	FOREIGN KEY ( `group` ) REFERENCES #__apoth_cm_courses( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `assessment` ) REFERENCES #__apoth_ass_assessments( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDb;

CREATE TABLE `#__apoth_ass_aspect_types` (
	`id` INT ( 10 ) NOT NULL AUTO_INCREMENT ,
	`type` VARCHAR ( 50 ) ,
	`description` VARCHAR ( 100 ) ,
	`commentable` TINYINT ( 1 ) ,
	`answer_type` ENUM( 'mark', 'text' ) NULL ,
	`default_mark_style` VARCHAR( 20 ) NULL ,
	PRIMARY KEY ( `id` ) ,
	INDEX ( `default_mark_style` ) ,
	FOREIGN KEY (`default_mark_style`) REFERENCES jos_apoth_sys_markstyles(`style`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDb;

CREATE TABLE `#__apoth_ass_aspect_instances` (
	`id` INT ( 10 ) NOT NULL AUTO_INCREMENT ,
	`assessment_id` INT ( 10 ) ,
	`parent_aspect_id` INT ( 10 ) ,
	`aspect_type_id` INT ( 10 ) ,
	`title` VARCHAR ( 50 ) ,
	`text` TEXT ,
	`display_style` VARCHAR ( 20 ) ,
	`mark_style` VARCHAR( 20 ) NULL ,
	`boundaries` TEXT ,
	`range_min` INT ( 3 ) ,
	`range_max` INT ( 3 ) ,
	`color` VARCHAR ( 25 ) ,
	`shown` TINYINT ( 1 ) DEFAULT '0' ,
	`created_by` VARCHAR ( 20 ) NOT NULL ,
	`created_on` DATETIME ,
	`modified_by` VARCHAR ( 20 ) ,
	`modified_on` DATETIME ,
	`valid_from` DATETIME ,
	`valid_to` DATETIME ,
	`deleted` TINYINT ( 1 ) NOT NULL DEFAULT '0',
	PRIMARY KEY ( `id` ) ,
	INDEX ( `assessment_id` ) ,
	INDEX ( `aspect_type_id` ) ,
	INDEX (`mark_style`) ,
	FOREIGN KEY ( `assessment_id` ) REFERENCES #__apoth_ass_assessments( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY ( `aspect_type_id` ) REFERENCES #__apoth_ass_aspect_types( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`mark_style`) REFERENCES #__apoth_sys_markstyles(`style`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_ass_aspect_mark` (
	`aspect_id` INT ( 11 ) NOT NULL ,
	`pupil_id` VARCHAR ( 20 ) NOT NULL ,
	`group_id` INT( 10 ) NULL ,
	`value` DECIMAL( 5, 2 ) NOT NULL DEFAULT '0' ,
	`last_modified` DATETIME NOT NULL ,
	INDEX( `pupil_id` ) ,
	INDEX( `group_id` ) ,
	INDEX( `aspect_id` ) ,
	FOREIGN KEY ( `aspect_id` ) REFERENCES #__apoth_ass_aspect_instances( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `group_id` )  REFERENCES #__apoth_cm_courses( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `pupil_id` )  REFERENCES #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_ass_aspect_text` (
	`aspect_id` 	INT( 11 ) NOT NULL ,
	`pupil_id` VARCHAR ( 20 ) NOT NULL ,
	`group_id` INT( 10 ) NULL ,
	`value` TEXT NOT NULL ,
	`last_modified` DATETIME NOT NULL ,
	INDEX( `pupil_id` ) ,
	INDEX( `group_id` ) ,
	INDEX( `aspect_id` ) ,
	FOREIGN KEY ( `aspect_id` ) REFERENCES #__apoth_ass_aspect_instances( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `group_id` )  REFERENCES #__apoth_cm_courses( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `pupil_id` )  REFERENCES #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;


CREATE TABLE `#__apoth_ass_buddies` (
	`assessment_id` INT( 10 ) NOT NULL,
	`buddy` VARCHAR( 128 ) NOT NULL ,
	`pupil` VARCHAR( 128 ) NULL ,
	INDEX( `assessment_id` ) ,
	INDEX( `pupil` ) ,
	INDEX( `buddy` )
) TYPE = MYISAM;

INSERT INTO `#__apoth_ass_aspect_types`
(`id`, `type`, `description`, `commentable`, `answer_type`, `default_mark_style`)
VALUES
(1,  'attitude',         'Attitude grade',                1, 'mark', 'grades_limited'),
(2,  'clp',              'Current level of performance',  1, 'mark', 'grades'),
(3,  'target',           'Target level',                  1, 'mark', 'grades'),
(4,  'grades',           'Grades',                        1, 'mark', 'grades'),
(5,  'levels',           'Levels',                        1, 'mark', 'levels'),
(6,  'bands',            'Bands',                         1, 'mark', 'bands'),
(7,  'comment',          'Comment',                       0, 'text',  NULL),
(8,  'question_section', 'Question section',              1, 'mark', 'percent'),
(9,  'question',         'Question',                      1, 'mark', 'score'),
(10, 'boolean',          'Yes/No, True/False, Boolean',   1, 'mark', 'boolean'),
(11, 'splitbands',       'Split Bands',                   1, 'mark', 'splitbands')
(12, 'grades_limited',   'Limited grades',                1, 'mark', 'grades_limited'),
(13, 'passfail',         'Pass/Fail',                     1, 'mark', 'passfail'),
(14, 'citizenship',      'Exceed/Achieve/Fail',           1, 'mark', 'citizenship');

INSERT INTO `#__apoth_sys_roles` (`id`, `parent`, `role`) VALUES
(70,  1, 'assessment'),
(71, 70, 'owner'),
(72, 70, 'editor'),
(73, 70, 'accessor');