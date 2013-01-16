-- ---------------- --
-- 1.7.max to 1.8.0 --
-- ---------------- --

-- #####  dev_566_rpt_report  #####

-- move old report tables somewhere safe

RENAME TABLE
  jos_apoth_rpt_admins         TO jos_apoth_rpt__old_admins        ,
  jos_apoth_rpt_cycles         TO jos_apoth_rpt__old_cycles        ,
  jos_apoth_rpt_cycles_groups  TO jos_apoth_rpt__old_cycles_groups ,
  jos_apoth_rpt_merge_words    TO jos_apoth_rpt__old_merge_words   ,
  jos_apoth_rpt_peers          TO jos_apoth_rpt__old_peers         ,
  jos_apoth_rpt_reports        TO jos_apoth_rpt__old_reports       ,
  jos_apoth_rpt_statements     TO jos_apoth_rpt__old_statements    ,
  jos_apoth_rpt_statements_map TO jos_apoth_rpt__old_statements_map,
  jos_apoth_rpt_style          TO jos_apoth_rpt__old_style         ,
  jos_apoth_rpt_style_fields   TO jos_apoth_rpt__old_style_fields  ;


-- add new roles
SELECT @reportRole := `id`
FROM `jos_apoth_sys_roles`
WHERE `role` = "report"
  AND `parent` = 1;

UPDATE `jos_apoth_sys_roles`
SET `role` = "checker"
WHERE `role` = "peer"
  AND `parent` = @reportRole;

UPDATE `jos_apoth_sys_roles`
SET `role` = "reader"
WHERE `role` = "last editor"
  AND `parent` = @reportRole;

UPDATE `jos_apoth_sys_roles`
SET `role` = "reportee"
WHERE `role` = "student"
  AND `parent` = @reportRole;

-- clear out obsolete actions

DELETE a.*
FROM `jos_menu` AS m
INNER JOIN `jos_apoth_sys_actions` AS a
   ON a.menu_id = m.id
WHERE `link` LIKE '%com_arc_report%';

DELETE m.*
FROM `jos_menu` AS m
WHERE `link` LIKE '%com_arc_report%'
  AND `link` NOT LIKE 'index.php?option=com_arc_report';


-- add new actions

SELECT @menu_id := id
FROM jos_menu
WHERE `link` LIKE '%index.php?option=com_arc_report%';

INSERT INTO jos_apoth_sys_actions
VALUES
(NULL, @menu_id, NULL, NULL, '', 'apoth_report', 'Report main', 'The Reports main page'),
(NULL, @menu_id, NULL, NULL, 'view=home',       'apoth_report_home', 'Report home', 'The Reports home page - shows upcoming events'),
(NULL, @menu_id, NULL, NULL, 'view=writecheck', 'apoth_report_writecheck', 'Report write and check', "The Reports write and check page - shows user's cycle progress"),
(NULL, @menu_id, NULL, NULL, 'view=overview',   'apoth_report_overview', 'Report overview', 'The Reports overview page - shows statistics on cycle progress'),
(NULL, @menu_id, NULL, NULL, 'view=printshare', 'apoth_report_printshare', 'Report print and share', 'The Reports output page - allows generating reports in ways that can be printed or shared'),
(NULL, @menu_id, NULL, NULL, 'view=admin',      'apoth_report_admin', 'Report admin', 'The Reports admin page - administer a cycle'),
(NULL, @menu_id, NULL, NULL, "view=subreports\r\nactivity=view\r\ncycle=~report.cycle~",  'apoth_report_view_list',       'Report subreport list - view',    'The Reports subreport list - view all subreports a user can see in a cycle'),
(NULL, @menu_id, NULL, NULL, "view=subreports\r\nactivity=write\r\ncycle=~report.cycle~", 'apoth_report_write_list',      'Report subreport list - write',   'The Reports subreport list - edit all subreports a user has in a cycle'),
(NULL, @menu_id, NULL, NULL, "view=subreports\r\nactivity=check\r\ncycle=~report.cycle~", 'apoth_report_check_list',      'Report subreport list - check',   'The Reports subreport list - peer-check all subreports a user has in a cycle'),
(NULL, @menu_id, NULL, NULL, "view=subreport\r\nactivity=view\r\nsubreport=~report.subreport~",   'apoth_report_view_subreport',  'Report single subreport - view',  'The Reports subreport page - view a single subreport'),
(NULL, @menu_id, NULL, NULL, "view=subreport\r\nactivity=write\r\nsubreport=~report.subreport~",  'apoth_report_write_subreport', 'Report single subreport - write', 'The Reports subreport page - edit a single subreport'),
(NULL, @menu_id, NULL, NULL, "view=subreport\r\nactivity=check\r\nsubreport=~report.subreport~",  'apoth_report_check_subreport', 'Report single subreport - check', 'The Reports subreport page - peer-check a single subreport'),

(NULL, @menu_id, NULL, NULL, "view=subreports\r\nformat=raw\r\ntask=showpage\r\npageId=~report.listpage~",              'apoth_report_list_ajax_page',                 'Report subreport list page',          'A page of subreports for the subreport list'),
(NULL, @menu_id, NULL, NULL, "view=subreports\r\nformat=raw\r\ntask=showsingle\r\nsubreport=~report.subreport~",        'apoth_report_list_ajax_single',               'Report subreport list single',        'A single subreport for the subreport list'),
(NULL, @menu_id, NULL, NULL, "view=subreports\r\nformat=raw\r\ntask=showfilterlist",                                    'apoth_report_filterlist_ajax_show',           'Report filter - show option list',    'Get the options for one of the report filters'),
(NULL, @menu_id, NULL, NULL, "view=subreports\r\nformat=raw\r\ntask=setfilterlist",                                     'apoth_report_filterlist_ajax_set',            'Report filter - set option values',   'Set the options for one of the report filters'),
(NULL, @menu_id, NULL, NULL, "view=subreports\r\nformat=raw\r\ntask=getfilterbreadcrumbs",                              'apoth_report_filterlist_ajax_getBreadcrumbs', 'Report filter-sensitive breadcrumbs', 'Get the breadcrumbs for the current filter values'),
(NULL, @menu_id, NULL, NULL, "view=subreport\r\nformat=raw\r\nsubreport=~report.subreport~\r\ntask=showmore",           'apoth_report_ajax_more',                      'Report subreport - more',             'The Reports subreport - show the "more" section of a subreport'),
(NULL, @menu_id, NULL, NULL, "view=subreport\r\nformat=raw\r\nsubreport=~report.subreport~\r\ntask=save\r\ncommit=~report.commit~\r\nstatus=~report.status~", 'apoth_report_ajax_save', 'Report subreport - save', 'The Reports subreport - save a subreport'),
(NULL, @menu_id, NULL, NULL, "view=subreport\r\ntmpl=component\r\nsubreport=~report.subreport~\r\ntask=feedback",           'apoth_report_ajax_feedback',                  'Report subreport - feedback',         'The Reports subreport - show form for feedback on a rejected subreport'),
(NULL, @menu_id, NULL, NULL, "view=subreport\r\ntmpl=component\r\nsubreport=~report.subreport~\r\ntask=savefeedback",       'apoth_report_ajax_feedback_save',             'Report subreport - save feedback',    'The Reports subreport - save feedback on a rejected subreport'),
(NULL, @menu_id, NULL, NULL, "view=subreport\r\ntmpl=component\r\nsubreport=~report.subreport~\r\nfield=~report.field~\r\ntask=statementlist",      'apoth_report_ajax_statement',                 'Report subreport - statements',       'The Reports subreport - show available statements for a subreport'),

(NULL, @menu_id, NULL, NULL, "view=printshare\r\nformat=raw\r\nsubreport=~report.subreport~\r\ntask=preview", 'apoth_report_print_preview', 'Print / share - preview', 'Generate a preview of the subreport as it would appear on the final report');

SELECT @action_id := LAST_INSERT_ID();

SELECT @self := r1.id
FROM jos_apoth_sys_roles AS r1
INNER JOIN jos_apoth_sys_roles AS r2
   ON r2.id = r1.parent
WHERE r2.`role` = 'sys'
  AND r1.`role` = 'user';

INSERT INTO jos_apoth_sys_acl
VALUES
(@action_id    , @self, NULL, 1 ),
(@action_id+1  , @self, NULL, 1 ),
(@action_id+2  , @self, NULL, 1 ),
(@action_id+3  , @self, NULL, 1 ),
(@action_id+4  , @self, NULL, 1 ),
(@action_id+5  , @self, NULL, 1 ),
(@action_id+6  , @self, NULL, 1 ),
(@action_id+7  , @self, NULL, 1 ),
(@action_id+8  , @self, NULL, 1 ),
(@action_id+9  , @self, NULL, 1 ),
(@action_id+10 , @self, NULL, 1 ),
(@action_id+11 , @self, NULL, 1 ),
(@action_id+12 , @self, NULL, 1 ),
(@action_id+13 , @self, NULL, 1 ),
(@action_id+14 , @self, NULL, 1 ),
(@action_id+15 , @self, NULL, 1 ),
(@action_id+16 , @self, NULL, 1 ),
(@action_id+17 , @self, NULL, 1 ),
(@action_id+18 , @self, NULL, 1 ),
(@action_id+19 , @self, NULL, 1 ),
(@action_id+20 , @self, NULL, 1 ),
(@action_id+21 , @self, NULL, 1 ),
(@action_id+22 , @self, NULL, 1 );


-- clear out obsolete admin options and update remaining

DELETE c1.*
FROM `jos_components` AS c1
INNER JOIN jos_components AS c2
   ON c2.id = c1.parent
WHERE c2.link = "option=com_arc_report"
  AND c1.admin_menu_link NOT LIKE "%view=settings%";

UPDATE `jos_components` AS c1
INNER JOIN jos_components AS c2
   ON c2.id = c1.parent
SET c1.`admin_menu_img` = "../images/menu/config.png"
WHERE c2.link = "option=com_arc_report"
  AND c1.admin_menu_link LIKE "%view=settings%";

UPDATE `jos_components` AS c1
SET `admin_menu_img` = "../administrator/components/com_arc_core/images/arc_menu_16.png"
 , `name` = "Arc - Report"
 , `admin_menu_alt` = "Arc - Report"
WHERE c1.link = "option=com_arc_report";


-- Create new db schema for reports

DROP TABLE IF EXISTS jos_apoth_rpt_subreport_status_log;
DROP TABLE IF EXISTS jos_apoth_rpt_subreport_data;
DROP TABLE IF EXISTS jos_apoth_rpt_subreports;
DROP TABLE IF EXISTS jos_apoth_rpt_subreport_statuses;
DROP TABLE IF EXISTS jos_apoth_rpt_statement_config;
DROP TABLE IF EXISTS jos_apoth_rpt_statements;
DROP TABLE IF EXISTS jos_apoth_rpt_merge_word_opts;
DROP TABLE IF EXISTS jos_apoth_rpt_merge_words;
DROP TABLE IF EXISTS jos_apoth_rpt_event_config;
DROP TABLE IF EXISTS jos_apoth_rpt_events;
DROP TABLE IF EXISTS jos_apoth_rpt_field_config;
DROP TABLE IF EXISTS jos_apoth_rpt_section_config;
DROP TABLE IF EXISTS jos_apoth_rpt_cycles;
DROP TABLE IF EXISTS jos_apoth_rpt_section_fields;
DROP TABLE IF EXISTS jos_apoth_rpt_fields;
DROP TABLE IF EXISTS jos_apoth_rpt_field_types;
DROP TABLE IF EXISTS jos_apoth_rpt_report_layout_sections;
DROP TABLE IF EXISTS jos_apoth_rpt_sections;
DROP TABLE IF EXISTS jos_apoth_rpt_report_layouts ;
DROP TABLE IF EXISTS jos_apoth_rpt_group_map;

CREATE TABLE jos_apoth_rpt_group_map(
	`rpt_group_id` INT( 10 ) NOT NULL,
	`lookup_group_id` INT( 10 ) NOT NULL,
	PRIMARY KEY ( `rpt_group_id` ),
	INDEX ( `lookup_group_id` ),
	FOREIGN KEY ( `rpt_group_id` ) REFERENCES `jos_apoth_cm_courses`( `id` ),
	FOREIGN KEY ( `lookup_group_id` ) REFERENCES `jos_apoth_cm_courses`( `id` )
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_report_layouts (
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR( 50 ) NOT NULL,
	`description` TEXT NULL,
	`print_page_size` VARCHAR( 10 ) NOT NULL DEFAULT "A4",
	`print_page_limit` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT 8,
	`print_default_font` VARCHAR( 20 ) NOT NULL DEFAULT "Arial",
	`print_default_font_size` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT 10,
	PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_sections(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR( 50 ) NULL,
	`subreport` BOOLEAN NOT NULL DEFAULT FALSE,
	`web_displayed` BOOLEAN NOT NULL DEFAULT TRUE,
	`web_width` SMALLINT( 5 ) NULL,
	`web_height` SMALLINT( 5 ) NULL,
	`print_displayed` BOOLEAN NOT NULL DEFAULT TRUE,
	`print_relative` BOOLEAN NULL, 
	`print_l` SMALLINT( 5 ) UNSIGNED NULL,
	`print_t` SMALLINT( 5 ) UNSIGNED NULL,
	`print_width` SMALLINT( 5 ) UNSIGNED NULL,
	`print_height` SMALLINT( 5 ) UNSIGNED NULL,
	`print_border` BOOLEAN NULL,
	PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_report_layout_sections(
	`layout_id` INT( 11 ) UNSIGNED NOT NULL,
	`section_id` INT( 11 ) UNSIGNED NOT NULL,
	`order` SMALLINT( 5 ) UNSIGNED NOT NULL,
	PRIMARY KEY (`layout_id`, `section_id`, `order` ),
	INDEX (`section_id`),
	FOREIGN KEY (`layout_id`)  REFERENCES `jos_apoth_rpt_report_layouts`(  `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`section_id`) REFERENCES `jos_apoth_rpt_sections`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_field_types(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`type` VARCHAR( 50 ) NOT NULL,
	`lookup_source` VARCHAR( 50 ) NULL,
	PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_fields(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`type_id` INT( 11 ) UNSIGNED NOT NULL,
	`name` VARCHAR( 50 ) NOT NULL,
	`required` BOOLEAN NOT NULL DEFAULT FALSE,
	`web_displayed` BOOLEAN NOT NULL DEFAULT TRUE, 
	`web_part` ENUM('brief_1', 'brief_2', 'more') NULL,
	`web_default` VARCHAR( 50 ) NULL,
	`web_l` SMALLINT( 5 ) UNSIGNED NULL,
	`web_t` SMALLINT( 5 ) UNSIGNED NULL,
	`web_width` SMALLINT( 5 ) UNSIGNED NULL,
	`web_height` SMALLINT( 5 ) UNSIGNED NULL,
	`print_displayed` BOOLEAN NOT NULL DEFAULT TRUE,
	`print_font` VARCHAR( 20 ) NULL,
	`print_font_size` TINYINT( 3 ) UNSIGNED NULL,
	`print_text_align` ENUM('l', 'c', 'r') NULL,
	`print_l` SMALLINT( 5 ) UNSIGNED NULL,
	`print_t` SMALLINT( 5 ) UNSIGNED NULL,
	`print_width` SMALLINT( 5 ) UNSIGNED NULL,
	`print_height` SMALLINT( 5 ) UNSIGNED NULL,
	`print_pad_t` SMALLINT( 5 ) UNSIGNED NULL,
	`print_pad_r` SMALLINT( 5 ) UNSIGNED NULL,
	`print_pad_b` SMALLINT( 5 ) UNSIGNED NULL,
	`print_pad_l` SMALLINT( 5 ) UNSIGNED NULL,
	`print_border` BOOLEAN NULL,
	PRIMARY KEY (`id`),
	INDEX (`type_id`),
	FOREIGN KEY (`type_id`) REFERENCES `jos_apoth_rpt_field_types`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_section_fields(
	`section_id` INT( 11 ) UNSIGNED NOT NULL,
	`field_id` INT( 11 ) UNSIGNED NOT NULL,
	`order` SMALLINT( 5 ) UNSIGNED NOT NULL,
	PRIMARY KEY( `section_id`, `field_id`, `order` ),
	INDEX (`field_id`),
	FOREIGN KEY (`section_id`) REFERENCES `jos_apoth_rpt_sections`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`field_id`)   REFERENCES `jos_apoth_rpt_fields`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_cycles(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR( 50 ) NOT NULL,
	`active_from` DATETIME NOT NULL,
	`active_to` DATETIME NOT NULL,
	`layout_id` INT( 11 ) UNSIGNED NOT NULL,
	`self_report` BOOLEAN DEFAULT 0,
	PRIMARY KEY (`id`),
	INDEX ( `layout_id` ),
	FOREIGN KEY (`layout_id` ) REFERENCES `jos_apoth_rpt_report_layouts`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_section_config(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`cycle_id` INT( 11 ) UNSIGNED NULL,
	`rpt_group_id` INT( 10 ) NULL,
	`section_id` INT( 11 ) UNSIGNED NULL,
	PRIMARY KEY (`id`),
	UNIQUE (`cycle_id`, `rpt_group_id` ),
	INDEX (`rpt_group_id` ),
	FOREIGN KEY (`cycle_id`) REFERENCES `jos_apoth_rpt_cycles`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`rpt_group_id`) REFERENCES `jos_apoth_rpt_group_map`( `rpt_group_id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`section_id`) REFERENCES `jos_apoth_rpt_sections`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_field_config(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`cycle_id` INT( 11 ) UNSIGNED NULL,
	`layout_id` INT( 11 ) UNSIGNED NULL,
	`section_id` INT( 11 ) UNSIGNED NULL,
	`field_id` INT( 11 ) UNSIGNED NULL,
	`rpt_group_id` INT( 10 ) NULL,
	`data` TEXT,
	PRIMARY KEY (`id`),
	UNIQUE ( `cycle_id`, `layout_id`, `section_id`, `field_id`, `rpt_group_id` ),
	INDEX ( `layout_id` ),
	INDEX ( `section_id` ),
	INDEX ( `field_id` ),
	INDEX ( `rpt_group_id` ),
	FOREIGN KEY (`cycle_id`) REFERENCES `jos_apoth_rpt_cycles`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`layout_id`) REFERENCES `jos_apoth_rpt_report_layouts`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`section_id`) REFERENCES `jos_apoth_rpt_sections`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`field_id`) REFERENCES `jos_apoth_rpt_fields`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`rpt_group_id`) REFERENCES `jos_apoth_rpt_group_map`( `rpt_group_id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_events(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR( 50 ) NOT NULL,
	`check_source` VARCHAR( 50 ) NOT NULL,
	`icon` VARCHAR( 100 ) NULL,
	`target_action` VARCHAR( 50 ) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_event_config(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` VARCHAR( 100 ) NOT NULL,
	`cycle_id` INT( 11 ) UNSIGNED NULL,
	`event_id` INT( 11 ) UNSIGNED NULL,
	`data` TEXT,
	`start_time` DATETIME,
	`end_time` DATETIME,
	PRIMARY KEY (`id`),
	INDEX ( `cycle_id` ),
	INDEX ( `event_id` ),
	FOREIGN KEY (`cycle_id`) REFERENCES `jos_apoth_rpt_cycles`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`event_id`) REFERENCES `jos_apoth_rpt_events`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_merge_words(
	`id` INT( 11 ) UNSIGNED NOT NULL,
	`word` VARCHAR( 20 ) NOT NULL,
	`handler` VARCHAR( 50 ),
	`datum` VARCHAR( 50 ),
	PRIMARY KEY ( `id` )
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_merge_word_opts(
	`word_id` INT( 11 ) UNSIGNED NOT NULL,
	`opt_id` SMALLINT( 5 ) UNSIGNED NOT NULL,
	`option` VARCHAR( 50 ) NOT NULL,
	INDEX ( `word_id` ),
	FOREIGN KEY ( `word_id` ) REFERENCES `jos_apoth_rpt_merge_words`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_statements(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`text` TEXT,
	PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_statement_config(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`field_config_id` INT( 11 ) UNSIGNED NOT NULL,
	`statement_id` INT( 11 ) UNSIGNED NOT NULL,
	`order` SMALLINT( 5 ) UNSIGNED NOT NULL,
	`color` VARCHAR( 50 ) NULL,
	PRIMARY KEY (`id`),
	UNIQUE ( `field_config_id`, `statement_id` ),
	INDEX ( `statement_id` ),
	FOREIGN KEY ( `field_config_id` ) REFERENCES `jos_apoth_rpt_fields`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY ( `statement_id` ) REFERENCES `jos_apoth_rpt_statements`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_subreport_statuses(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` VARCHAR( 20 ),
	PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_subreports(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`cycle_id` INT( 11 ) UNSIGNED NOT NULL,
	`rpt_group_id` INT( 10 ) NOT NULL,
	`reportee_id` VARCHAR( 20 ) NOT NULL,
	`author_id` VARCHAR( 20 ) NULL,
	`status_id` INT( 11 ) UNSIGNED NOT NULL,
	`last_modified_by` VARCHAR( 20 ) NULL,
	PRIMARY KEY (`id`),
	INDEX( `cycle_id` ),
	INDEX( `rpt_group_id` ),
	INDEX( `reportee_id` ),
	INDEX( `author_id` ),
	INDEX( `status_id` ),
	INDEX( `last_modified_by` ),
	FOREIGN KEY ( `cycle_id` ) REFERENCES `jos_apoth_rpt_cycles`( `id` ) ON DELETE RESTRICT ON UPDATE CASCADE,
	FOREIGN KEY ( `rpt_group_id` ) REFERENCES `jos_apoth_cm_courses`( `id` ) ON DELETE RESTRICT ON UPDATE CASCADE,
	FOREIGN KEY ( `reportee_id` ) REFERENCES `jos_apoth_ppl_people`( `id` ) ON DELETE RESTRICT ON UPDATE CASCADE,
	FOREIGN KEY ( `author_id` ) REFERENCES `jos_apoth_ppl_people`( `id` ) ON DELETE RESTRICT ON UPDATE CASCADE,
	FOREIGN KEY ( `status_id` ) REFERENCES `jos_apoth_rpt_subreport_statuses`( `id` ) ON DELETE RESTRICT ON UPDATE CASCADE,
	FOREIGN KEY ( `last_modified_by` ) REFERENCES `jos_apoth_ppl_people`( `id` ) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_subreport_data(
	`subreport_id` INT( 11 ) UNSIGNED NOT NULL,
	`field_id` INT( 11 ) UNSIGNED NOT NULL,
	`value` TEXT,
	PRIMARY KEY ( `subreport_id`, `field_id` ),
	INDEX ( `field_id` ),
	FOREIGN KEY ( `subreport_id` ) REFERENCES `jos_apoth_rpt_subreports`( `id` ) ON DELETE RESTRICT ON UPDATE CASCADE,
	FOREIGN KEY ( `field_id` ) REFERENCES `jos_apoth_rpt_fields`( `id` ) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE jos_apoth_rpt_subreport_status_log(
	`subreport_id` INT( 11 ) UNSIGNED NOT NULL,
	`person_id` VARCHAR( 20 ),
	`new_status_id` INT( 11 ) UNSIGNED NOT NULL,
	`time` DATETIME NOT NULL,
	`comment` TEXT null,
	INDEX( `subreport_id` ),
	INDEX( `person_id` ),
	FOREIGN KEY ( `subreport_id` ) REFERENCES `jos_apoth_rpt_subreports`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY ( `person_id` ) REFERENCES `jos_apoth_ppl_people`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;


-- triggers, procedures and functions
DELIMITER //

DROP TRIGGER IF EXISTS `rpt_subreport_insert`//
CREATE TRIGGER `rpt_subreport_insert` AFTER INSERT ON jos_apoth_rpt_subreports
FOR EACH ROW
INSERT INTO jos_apoth_rpt_subreport_status_log
( `subreport_id`, `person_id`, `new_status_id`, `time`, `comment` )
VALUES
( NEW.id, NEW.last_modified_by, NEW.status_id, NOW(), NULL );
//

DROP TRIGGER IF EXISTS `rpt_subreport_update`//
CREATE TRIGGER `rpt_subreport_update` AFTER UPDATE ON jos_apoth_rpt_subreports
FOR EACH ROW
IF NEW.status_id != OLD.status_id THEN
	INSERT INTO jos_apoth_rpt_subreport_status_log
	( `subreport_id`, `person_id`, `new_status_id`, `time`, `comment` )
	VALUES
	( NEW.id, NEW.last_modified_by, NEW.status_id, NOW(), NULL );
END IF
//

DELIMITER ;

SELECT 1; -- to stop phpmyadmin creating problems


-- populate tables with essential data

INSERT INTO `jos_apoth_cm_types`
VALUES
( 'report', 'Groups for the report system' );

INSERT INTO `jos_apoth_rpt_subreport_statuses`
VALUES
( 1, 'nascent' ),
( 2, 'incomplete' ),
( 3, 'submitted' ),
( 4, 'rejected' ),
( 5, 'approved' );

SELECT @oldMax := MAX( id )
FROM jos_apoth_cm_courses AS c;

INSERT INTO `jos_apoth_cm_courses`
VALUES
( (@oldMax + 1), 'report', 1, NULL, NULL, NULL, 'Report Root', 'Report Root', 'Root of all groups required solely for report writing', (@oldMax + 1), 0, '1970-01-01', NULL, NOW(), NULL, 0, NULL, NULL, NULL, 0 );


-- update profile links panel
UPDATE jos_apoth_home_links
SET `url` = "http://fla91/j_dave_reports/index.php?option=com_arc_report&Itemid=226"
,`text` = "Reports / PLR"
WHERE `id` = 11;

INSERT IGNORE INTO jos_apoth_ppl_profiles
SELECT pro.person_id, 4, 17, 11
FROM `jos_apoth_tt_group_members` AS gm
INNER JOIN `jos_apoth_cm_courses` AS c
   ON c.id = gm.group_id
  AND c.type = "pastoral"
INNER JOIN `jos_apoth_ppl_profiles` AS pro
   ON pro.person_id = gm.person_id
WHERE gm.valid_from < NOW()
  AND gm.valid_to > NOW()
  AND gm.is_student = 1
  AND pro.property = 'ARC';


-- -------------- --
-- 1.8.0 to 1.8.1 --
-- -------------- --

-- #####  dev_600_tv_videotagsauthorfilms  #####

-- new action for owner vids
INSERT INTO `jos_apoth_sys_actions`
(`id`, `menu_id`, `option`, `task`, `params`, `name`, `menu_text`, `description`)
VALUES
(NULL, 399, NULL, NULL, 'view=video\ntask=idssearch', 'arc_tv_uservids', 'User videos', 'View a list of videos owned by a given user');

# -- keep the id of new action
SELECT @newId := LAST_INSERT_ID();

# -- setup the acl for new action
INSERT INTO `jos_apoth_sys_acl` (`action`, `role`, `sees`, `allowed`)
SELECT @newId, role, sees, allowed
FROM jos_apoth_sys_acl AS acl
INNER JOIN jos_apoth_sys_actions AS a
   ON a.id = acl.action
WHERE a.name = 'arc_tv_tag';


-- ------------------- --
-- 1.8.1 to 1.8.1-rc.2 --
-- ------------------- --

CREATE TABLE `jos_apoth_sys_log_times` (
	`log_id` BIGINT( 20 ) NOT NULL,
	`func` VARCHAR( 255 ) NOT NULL,
	`ok` BOOL NOT NULL,
	`time` FLOAT NULL,
	`count` INT UNSIGNED NULL,
	`avg` FLOAT NULL,
	INDEX (`log_id`)
) ENGINE = MYISAM 

