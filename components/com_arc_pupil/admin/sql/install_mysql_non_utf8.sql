-- package     Arc
-- subpackage  Pupil
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_pup_pupils` (
	`upn` VARCHAR( 16 ) NOT NULL ,
	`person_id` VARCHAR( 20 ) NOT NULL ,
	`school_lea_number` INT( 3 ) NOT NULL ,
	`school_dfes_number` INT( 4 ) NOT NULL ,
	`birth_certificate_indicator` VARCHAR( 1 ) NOT NULL DEFAULT '0' ,
	`connextions_agreement` VARCHAR( 1 ) NOT NULL DEFAULT '0' ,
	`free_school_meal_eligibility` VARCHAR( 1 ) NOT NULL DEFAULT '0' ,
	`free_school_meal_review_date` DATE NULL ,
	`free_school_meal_taken` VARCHAR( 1 ) NOT NULL DEFAULT '0' ,
	`free_school_transport_eligibility` VARCHAR( 1 ) NOT NULL DEFAULT '0' ,
	`free_school_transport_review_date` DATE NULL ,
	`gifted_talented_indicator` VARCHAR( 1 ) NOT NULL DEFAULT '0' ,
	`in_care_indicator` VARCHAR( 1 ) NOT NULL DEFAULT '0' ,
	`in_care_caring_authority_code` VARCHAR( 4 ) NULL ,
	`child_protection_register_indicator` VARCHAR( 1 ) NOT NULL DEFAULT '0' ,
	`pregnant_indicator` VARCHAR( 1 ) NOT NULL DEFAULT '0' ,
	`part_time_indicator` VARCHAR( 1 ) NOT NULL DEFAULT '0' ,
	`date_of_entry` DATE NOT NULL ,
	`date_of_leaving` DATE NULL ,
	`leaving_destination` VARCHAR( 50 ) NULL ,
	PRIMARY KEY ( `upn` ) ,
	UNIQUE INDEX ( `person_id` ) ,
	FOREIGN KEY (person_id) REFERENCES #__apoth_ppl_people(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;
