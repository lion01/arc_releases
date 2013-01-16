-- package     Arc
-- subpackage  Planner
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_plan_tasks` (
	`id`           INT( 10 )      NOT NULL AUTO_INCREMENT ,
	`parent`       INT( 10 )      NOT NULL ,
	`title`        VARCHAR( 255 ) NOT NULL ,
	`color`        VARCHAR( 25 )  NOT NULL DEFAULT "aquamarine",
	`micro`        TINYINT( 1 )   NOT NULL DEFAULT 0 ,
	`text_1`       TEXT           NULL ,
	`text_2`       TEXT           NULL ,
	`duration`     INT( 10 )      NULL ,
	`evidence_num` INT( 10 )      NULL ,
	`complete`     TINYINT( 1 )   NOT NULL DEFAULT 0 ,
	`progress`     TINYINT( 3 )   NULL ,
	`order`        TINYINT( 3 )   NOT NULL ,
	`template`     VARCHAR( 50 )  NULL ,
 	`deleted`      TINYINT( 1 )   NOT NULL DEFAULT 0 ,
	`deleted_on`   DATETIME       NULL ,
	PRIMARY KEY    ( `id` ) ,
	INDEX          ( `parent` ) ,
	INDEX          ( `progress` ) ,
	FOREIGN KEY    ( `parent` ) REFERENCES #__apoth_plan_tasks( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) TYPE = InnoDB ;

SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO `#__apoth_plan_tasks` VALUES
	( 1, 1, 'Root Task', '', 0, 'Root task from which all others inherit', NULL, NULL, NULL, 0, NULL, 1, NULL, 0, NULL )
;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `#__apoth_plan_tasks_ancestry` (
	`id`        INT( 10 )      NOT NULL ,
	`ancestor`  INT( 10 )      NOT NULL ,
	PRIMARY KEY ( `id`, `ancestor` ) ,
	INDEX       ( `ancestor` ) ,
	FOREIGN KEY ( `id` )       REFERENCES #__apoth_plan_tasks( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `ancestor` ) REFERENCES #__apoth_plan_tasks( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_plan_tasks_requirements` (
	`task_id`    INT( 10 )      NOT NULL ,
	`requires`   INT( 10 )      NOT NULL ,
	PRIMARY KEY  ( `task_id`, `requires` ) ,
	INDEX        ( `requires` ) ,
	FOREIGN KEY  ( `task_id` )  REFERENCES #__apoth_plan_tasks( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY  ( `requires` ) REFERENCES #__apoth_plan_tasks( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) TYPE = InnoDB ;

CREATE TABLE `#__apoth_plan_tasks_relations` (
	`task_1`     INT( 10 )    NOT NULL ,
	`task_2`     INT( 10 )    NOT NULL ,
	PRIMARY KEY  ( `task_1`, `task_2` ) ,
	INDEX        ( `task_2` ) ,
	FOREIGN KEY  ( `task_1` ) REFERENCES #__apoth_plan_tasks( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY  ( `task_2` ) REFERENCES #__apoth_plan_tasks( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) TYPE = InnoDB ;

CREATE TABLE `#__apoth_plan_tasks_special` (
	`task_id` INT( 10 ) NOT NULL ,
	PRIMARY KEY  ( `task_id` ) ,
	FOREIGN KEY  ( `task_id` ) REFERENCES #__apoth_plan_tasks( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) TYPE = InnoDB ;

CREATE TABLE `#__apoth_plan_groups` (
	`id`         INT( 10 )    NOT NULL AUTO_INCREMENT ,
	`task_id`    INT( 10 )    NOT NULL ,
	`complete`   TINYINT( 1 ) NOT NULL DEFAULT 0,
	`progress`   TINYINT( 3 ) NULL ,
	`due`        DATETIME     NULL ,
	PRIMARY KEY ( `id` ) ,
	INDEX       ( `task_id` ) ,
	FOREIGN KEY ( `task_id` ) REFERENCES #__apoth_plan_tasks( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) TYPE = InnoDB ;

CREATE TABLE `#__apoth_plan_group_members` (
	`group_id`   INT( 10 )      NOT NULL ,
	`person_id`  VARCHAR( 20 )  NOT NULL ,
	`role`       ENUM( 'admin', 'assistant', 'assignee', 'leader' ) NOT NULL DEFAULT 'assignee' ,
	`valid_from` DATETIME       NOT NULL ,
	`valid_to`   DATETIME       NULL ,
	PRIMARY KEY ( `group_id`, `person_id`, `role` ) ,
	INDEX       ( `person_id` ) ,
	INDEX       ( `role` ) ,
	FOREIGN KEY ( `group_id` )  REFERENCES #__apoth_plan_groups( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `person_id` ) REFERENCES #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) TYPE = InnoDB ;

CREATE TABLE `#__apoth_plan_updates` (
	`id`         INT( 10 )      NOT NULL AUTO_INCREMENT ,
	`group_id`   INT( 10 )      NOT NULL ,
	`comment`    TEXT           NOT NULL ,
	`progress`   TINYINT( 3 )   NULL ,
	`author`     VARCHAR( 20 )  NOT NULL ,
	`date_added` DATETIME       NOT NULL ,
	PRIMARY KEY  ( `id` ) ,
	INDEX        ( `group_id` ) ,
	INDEX        ( `author` ) ,
	FOREIGN KEY  ( `group_id` ) REFERENCES #__apoth_plan_groups( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY  ( `author` )   REFERENCES #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) TYPE = InnoDB ;

CREATE TABLE `#__apoth_plan_update_evidence` (
	`id`         INT( 10 )      NOT NULL AUTO_INCREMENT ,
	`update_id`  INT( 10 )      NOT NULL ,
	`evidence`   VARCHAR( 100 ) NOT NULL ,
	`file_owner` VARCHAR( 20 )  NULL ,
 	PRIMARY KEY ( `id` ) ,
	INDEX       ( `update_id` ) ,
	INDEX       ( `file_owner` ) ,
	FOREIGN KEY ( `update_id` )  REFERENCES #__apoth_plan_updates( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `file_owner` ) REFERENCES #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) TYPE = InnoDB ;

CREATE TABLE `#__apoth_plan_update_microtasks` (
	`task_id`   INT( 10 )       NOT NULL ,
	`update_id` INT( 10 )       NOT NULL ,
	PRIMARY KEY ( `task_id`, `update_id` ) ,
	INDEX       ( `update_id` ) ,
	FOREIGN KEY ( `task_id` )   REFERENCES #__apoth_plan_tasks( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `update_id` ) REFERENCES #__apoth_plan_updates( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) TYPE = InnoDB ;

CREATE TABLE `#__apoth_plan_complete_triggers` (
	`task_id`   INT( 10 )       NOT NULL ,
	`class`     VARCHAR( 255 )  NOT NULL ,
	`func`      VARCHAR( 255 )  NOT NULL ,
	`undo_func` VARCHAR( 255 )  NOT NULL ,
	INDEX       ( `task_id` ) ,
	FOREIGN KEY ( `task_id` ) REFERENCES #__apoth_plan_tasks( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) TYPE = InnoDB ;

CREATE TABLE `#__apoth_plan_category_cols` (
	`id`          INT( 10 )     NOT NULL AUTO_INCREMENT ,
	`cat_id`      INT( 10 )     NOT NULL ,
	`column`      TINYINT( 3 )  NOT NULL ,
	`col_title`   VARCHAR( 25 ) NOT NULL ,
	`task_depth`  TINYINT( 3 )  NOT NULL ,
	`property`    VARCHAR( 25 ) NOT NULL ,
	`type`        VARCHAR( 25 ) NULL ,
	`display_num` INT( 10 )     NULL ,
	PRIMARY KEY  ( `id` ) ,
	INDEX        ( `cat_id` ) ,
	FOREIGN KEY  ( `cat_id` )  REFERENCES #__apoth_plan_tasks( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) TYPE = InnoDB ;

INSERT INTO `#__apoth_sys_roles` (`id`, `parent`, `role`) VALUES
(80,  1, 'planner'),
(81, 80, 'task'),
(82, 81, 'owner'),
(83, 80, 'group'),
(84, 83, 'admin'),
(85, 83, 'assignee'),
(86, 83, 'assistant'),
(87, 83, 'leader');

INSERT INTO `#__apoth_ppl_profile_categories` (`id`, `name`, `component`) VALUES
(NULL, 'SEN', 'planner');