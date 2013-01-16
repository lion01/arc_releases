-- package     Arc
-- subpackage  Staff
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_sm_staff` (
	`person_id` VARCHAR( 20 ) NOT NULL ,
	`school_lea_number` INT( 3 ) NOT NULL ,
	`school_dfes_number` INT( 4 ) NOT NULL ,
	`type` VARCHAR( 25 ) NOT NULL ,
	`job_title` VARCHAR( 50 ) NOT NULL ,
	`crb_check_indicator` VARCHAR( 1 ) NOT NULL DEFAULT '0' ,
	`crb_check_date` DATE NULL ,
	`account_number` VARCHAR( 8 ) NULL ,
	`sort_code` VARCHAR( 10 ) NULL ,
	`salary` VARCHAR( 6 ) NULL ,
	`wage_per_hour` VARCHAR( 6 ) NULL ,
	`payroll_number` VARCHAR( 12 ) NULL ,
	`holiday_entitlement` TINYINT( 2 ) NOT NULL DEFAULT '0' ,
	`holiday_taken` TINYINT( 2 ) NOT NULL DEFAULT '0' ,
	`pregnant_indicator` VARCHAR( 1 ) NOT NULL DEFAULT '0' ,
	`part_time_indicator` VARCHAR( 1 ) NOT NULL DEFAULT '0' ,
	`date_of_entry` DATE NOT NULL ,
	`date_of_leaving` DATE NULL ,
	PRIMARY KEY ( `person_id` , `school_lea_number` ) ,
	UNIQUE INDEX ( `person_id` ) ,
	FOREIGN KEY (person_id) REFERENCES #__apoth_ppl_people(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;
