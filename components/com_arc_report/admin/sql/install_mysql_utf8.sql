-- package     Arc
-- subpackage  Report
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

-- package     Arc
-- subpackage  Report
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE #__apoth_rpt_group_map(
	`rpt_group_id` INT( 10 ) NOT NULL,
	`lookup_group_id` INT( 10 ) NOT NULL,
	PRIMARY KEY ( `rpt_group_id` ),
	INDEX ( `lookup_group_id` ),
	FOREIGN KEY ( `rpt_group_id` ) REFERENCES `#__apoth_cm_courses`( `id` ),
	FOREIGN KEY ( `lookup_group_id` ) REFERENCES `#__apoth_cm_courses`( `id` )
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_report_layouts (
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR( 50 ) NOT NULL,
	`description` TEXT NULL,
	`print_page_size` VARCHAR( 10 ) NOT NULL DEFAULT "A4",
	`print_page_limit` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT 8,
	`print_default_font` VARCHAR( 20 ) NOT NULL DEFAULT "Arial",
	`print_default_font_size` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT 10,
	PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_sections(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`subreport` BOOLEAN NOT NULL DEFAULT FALSE,
	`web_displayed` BOOLEAN NOT NULL DEFAULT TRUE,
	`web_height` SMALLINT( 5 ) NULL,
	`print_displayed` BOOLEAN NOT NULL DEFAULT TRUE,
	`print_relative` BOOLEAN NULL, 
	`print_t` SMALLINT( 5 ) UNSIGNED NULL,
	`print_r` SMALLINT( 5 ) UNSIGNED NULL,
	`print_b` SMALLINT( 5 ) UNSIGNED NULL,
	`print_l` SMALLINT( 5 ) UNSIGNED NULL,
	`print_border` BOOLEAN NULL,
	PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_report_layout_sections(
	`layout_id` INT( 11 ) UNSIGNED NOT NULL,
	`section_id` INT( 11 ) UNSIGNED NOT NULL,
	`order` SMALLINT( 5 ) UNSIGNED NOT NULL,
	PRIMARY KEY (`layout_id`, `section_id`, `order` ),
	INDEX (`section_id`),
	FOREIGN KEY (`layout_id`)  REFERENCES `#__apoth_rpt_report_layouts`(  `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`section_id`) REFERENCES `#__apoth_rpt_sections`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_field_types(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`type` VARCHAR( 20 ) NOT NULL,
	`lookup_source` VARCHAR( 50 ) NULL,
	PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_fields(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`type_id` INT( 11 ) UNSIGNED NOT NULL,
	`name` VARCHAR( 50 ) NOT NULL,
	`required` BOOLEAN NOT NULL DEFAULT FALSE,
	`web_displayed` BOOLEAN NOT NULL DEFAULT TRUE, 
	`web_part` ENUM('brief_1', 'brief_2', 'more') NULL,
	`web_default` VARCHAR( 50 ) NULL,
	`web_t` SMALLINT( 5 ) UNSIGNED NULL,
	`web_r` SMALLINT( 5 ) UNSIGNED NULL,
	`web_b` SMALLINT( 5 ) UNSIGNED NULL,
	`web_l` SMALLINT( 5 ) UNSIGNED NULL,
	`print_displayed` BOOLEAN NOT NULL DEFAULT TRUE,
	`print_font` VARCHAR( 20 ) NULL,
	`print_font_size` TINYINT( 3 ) UNSIGNED NULL,
	`print_text_align` ENUM('l', 'c', 'r') NULL,
	`print_t` SMALLINT( 5 ) UNSIGNED NULL,
	`print_r` SMALLINT( 5 ) UNSIGNED NULL,
	`print_b` SMALLINT( 5 ) UNSIGNED NULL,
	`print_l` SMALLINT( 5 ) UNSIGNED NULL,
	`print_pad_t` SMALLINT( 5 ) UNSIGNED NULL,
	`print_pad_r` SMALLINT( 5 ) UNSIGNED NULL,
	`print_pad_b` SMALLINT( 5 ) UNSIGNED NULL,
	`print_pad_l` SMALLINT( 5 ) UNSIGNED NULL,
	`print_border` BOOLEAN NULL,
	PRIMARY KEY (`id`),
	INDEX (`type_id`),
	FOREIGN KEY (`type_id`) REFERENCES `#__apoth_rpt_field_types`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_section_fields(
	`section_id` INT( 11 ) UNSIGNED NOT NULL,
	`field_id` INT( 11 ) UNSIGNED NOT NULL,
	`order` SMALLINT( 5 ) UNSIGNED NOT NULL,
	PRIMARY KEY( `section_id`, `field_id`, `order` ),
	INDEX (`field_id`),
	FOREIGN KEY (`section_id`) REFERENCES `#__apoth_rpt_sections`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`field_id`)   REFERENCES `#__apoth_rpt_fields`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_cycles(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR( 50 ) NOT NULL,
	`active_from` DATETIME NOT NULL,
	`active_to` DATETIME NOT NULL,
	`layout_id` INT( 11 ) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX ( `layout_id` ),
	FOREIGN KEY (`layout_id` ) REFERENCES `#__apoth_rpt_report_layouts`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_section_config(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`cycle_id` INT( 11 ) UNSIGNED NULL,
	`rpt_group_id` INT( 10 ) NULL,
	`section_id` INT( 11 ) UNSIGNED NULL,
	PRIMARY KEY (`id`),
	UNIQUE (`cycle_id`, `rpt_group_id` ),
	INDEX (`rpt_group_id` ),
	FOREIGN KEY (`cycle_id`) REFERENCES `#__apoth_rpt_cycles`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`rpt_group_id`) REFERENCES `#__apoth_rpt_group_map`( `rpt_group_id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`section_id`) REFERENCES `#__apoth_rpt_sections`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_field_config(
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
	FOREIGN KEY (`cycle_id`) REFERENCES `#__apoth_rpt_cycles`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`layout_id`) REFERENCES `#__apoth_rpt_report_layouts`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`section_id`) REFERENCES `#__apoth_rpt_sections`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`field_id`) REFERENCES `#__apoth_rpt_fields`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`rpt_group_id`) REFERENCES `#__apoth_rpt_group_map`( `rpt_group_id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_events(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR( 50 ) NOT NULL,
	`check_source` VARCHAR( 50 ) NOT NULL,
	`icon` VARCHAR( 100 ) NULL,
	`target_action` VARCHAR( 50 ) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_event_config(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` VARCHAR( 100 ),
	`cycle_id` INT( 11 ) UNSIGNED NULL,
	`event_id` INT( 11 ) UNSIGNED NULL,
	`data` TEXT,
	`start_time` DATETIME,
	`end_time` DATETIME,
	PRIMARY KEY (`id`),
	INDEX ( `cycle_id` ),
	INDEX ( `event_id` ),
	FOREIGN KEY (`cycle_id`) REFERENCES `#__apoth_rpt_cycles`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`event_id`) REFERENCES `#__apoth_rpt_events`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_merge_words(
	`word` VARCHAR( 20 ) NOT NULL,
	`handler` VARCHAR( 50 ),
	`datum` VARCHAR( 50 ),
	PRIMARY KEY ( `word` )
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_merge_word_opts(
	`word` VARCHAR( 20 ) NOT NULL,
	`opt_id` SMALLINT( 5 ) UNSIGNED NOT NULL,
	`option` VARCHAR( 50 ) NOT NULL,
	INDEX ( `word` ),
	FOREIGN KEY ( `word` ) REFERENCES `#__apoth_rpt_merge_words`( `word` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_statements(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`text` TEXT,
	PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_statement_config(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`field_config_id` INT( 11 ) UNSIGNED NOT NULL,
	`statement_id` INT( 11 ) UNSIGNED NOT NULL,
	`order` SMALLINT( 5 ) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE ( `field_config_id`, `statement_id` ),
	INDEX ( `statement_id` ),
	FOREIGN KEY ( `field_config_id` ) REFERENCES `#__apoth_rpt_fields`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY ( `statement_id` ) REFERENCES `#__apoth_rpt_statements`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_subreport_statuses(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` VARCHAR( 20 ),
	PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_subreports(
	`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`cycle_id` INT( 11 ) UNSIGNED NOT NULL,
	`rpt_group_id` INT( 10 ) NOT NULL,
	`person_id` VARCHAR( 20 ) NOT NULL,
	`author_id` VARCHAR( 20 ) NOT NULL,
	`status_id` INT( 11 ) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX( `cycle_id` ),
	INDEX( `rpt_group_id` ),
	INDEX( `person_id` ),
	INDEX( `author_id` ),
	INDEX( `status_id` ),
	FOREIGN KEY ( `cycle_id` ) REFERENCES `#__apoth_rpt_cycles`( `id` ) ON DELETE RESTRICT ON UPDATE CASCADE,
	FOREIGN KEY ( `rpt_group_id` ) REFERENCES `#__apoth_rpt_group_map`( `rpt_group_id` ) ON DELETE RESTRICT ON UPDATE CASCADE,
	FOREIGN KEY ( `person_id` ) REFERENCES `#__apoth_ppl_people`( `id` ) ON DELETE RESTRICT ON UPDATE CASCADE,
	FOREIGN KEY ( `author_id` ) REFERENCES `#__apoth_ppl_people`( `id` ) ON DELETE RESTRICT ON UPDATE CASCADE,
	FOREIGN KEY ( `status_id` ) REFERENCES `#__apoth_rpt_subreport_statuses`( `id` ) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_subreport_data(
	`subreport_id` INT( 11 ) UNSIGNED NOT NULL,
	`field_id` INT( 11 ) UNSIGNED NOT NULL,
	`value` TEXT,
	PRIMARY KEY ( `subreport_id`, `field_id` ),
	INDEX ( `field_id` ),
	FOREIGN KEY ( `subreport_id` ) REFERENCES `#__apoth_rpt_subreports`( `id` ) ON DELETE RESTRICT ON UPDATE CASCADE,
	FOREIGN KEY ( `field_id` ) REFERENCES `#__apoth_rpt_fields`( `id` ) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE #__apoth_rpt_subreport_status_log(
	`subreport_id` INT( 11 ) UNSIGNED NOT NULL,
	`person_id` VARCHAR( 20 ),
	`new_status_id` INT( 11 ) UNSIGNED NOT NULL,
	`time` DATETIME NOT NULL,
	`comment` TEXT null,
	INDEX( `subreport_id` ),
	INDEX( `person_id` ),
	FOREIGN KEY ( `subreport_id` ) REFERENCES `#__apoth_rpt_subreports`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY ( `person_id` ) REFERENCES `#__apoth_ppl_people`( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;
