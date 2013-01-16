-- package     Arc
-- subpackage  Attendance
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_att_statistical_meaning` (
	`id` INTEGER NOT NULL AUTO_INCREMENT,
	`meaning` VARCHAR( 50 ) NOT NULL ,
	PRIMARY KEY ( `id` )
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_att_physical_meaning` (
	`id` INTEGER NOT NULL AUTO_INCREMENT,
	`meaning` VARCHAR( 50 ) NOT NULL ,
	PRIMARY KEY ( `id` )
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_att_school_meaning` (
	`id` INTEGER NOT NULL AUTO_INCREMENT,
	`meaning` VARCHAR( 50 ) NOT NULL ,
	PRIMARY KEY ( `id` )
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_att_codes` (
	`code` CHAR( 1 ) NOT NULL DEFAULT '',
	`school_meaning` INTEGER NOT NULL DEFAULT 0 ,
	`statistical_meaning` INTEGER NOT NULL DEFAULT 0 ,
	`physical_meaning` INTEGER NOT NULL DEFAULT 0 ,
	`is_common` TINYINT( 1 ) NOT NULL DEFAULT 0 ,
	`apply_all_day` TINYINT( 1 ) NOT NULL DEFAULT 0,
	`order_id` TINYINT( 2 ) NULL ,
	`valid_from` DATETIME ,
	`valid_to` DATETIME ,
	`image_link` VARCHAR( 100 ) NULL ,
	`type` VARCHAR( 10 ) NOT NULL DEFAULT 'pastoral' ,
	`level` VARCHAR( 100 ) NOT NULL ,
	PRIMARY KEY ( `code`, `type` ),
	INDEX ( `code` ),
	INDEX ( `school_meaning` ) ,
	INDEX ( `statistical_meaning` ) ,
	INDEX ( `physical_meaning` ) ,
	INDEX ( `type` ) ,
	FOREIGN KEY ( `school_meaning` ) REFERENCES #__apoth_att_school_meaning( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `statistical_meaning` ) REFERENCES #__apoth_att_statistical_meaning( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `physical_meaning` ) REFERENCES #__apoth_att_physical_meaning( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `type` ) REFERENCES #__apoth_cm_types( `type` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_att_dailyatt` (
	`date` DATE NOT NULL ,
	`day_section` VARCHAR( 30 ) NOT NULL ,
	`person_id` VARCHAR( 20 ) NOT NULL ,
	`course_id` INT( 10 ) NOT NULL ,
	`att_code` VARCHAR( 1 ) NOT NULL ,
	`last_modified` DATETIME NOT NULL ,
	PRIMARY KEY ( `date`, `day_section`, `person_id`, `course_id` ) ,
	INDEX ( `day_section` ) ,
	INDEX ( `person_id` ) ,
	INDEX ( `course_id` ) ,
	INDEX ( `att_code` ) ,
	FOREIGN KEY ( `day_section` ) REFERENCES #__apoth_tt_daydetails( `day_section` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `person_id` ) REFERENCES #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `course_id` ) REFERENCES #__apoth_cm_courses( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `att_code` ) REFERENCES #__apoth_att_codes( `code` ) ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_att_incidents` (
	`code`       CHAR( 1 )     NOT NULL ,
	`score`      TINYINT( 4 )  NOT NULL ,
	`meaning`    VARCHAR( 50 ) NOT NULL ,
	`type`       VARCHAR( 15 ) NOT NULL DEFAULT 'checkbox' ,
	`valid_from` DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00' ,
	`valid_to`   DATETIME      NULL ,
	PRIMARY KEY ( `code` ) ,
	INDEX       ( `valid_from` ) ,
	INDEX       ( `valid_to` ) 
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_att_dailyincidents` (
	`date` DATE ,
	`day_section` VARCHAR( 30 ) NOT NULL ,
	`person_id` VARCHAR( 20 ) NOT NULL ,
	`course_id` INT( 10 ) NOT NULL ,
	`incident` CHAR( 1 ) NOT NULL ,
	`last_modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
	PRIMARY KEY ( `date`, `day_section`, `person_id`, `course_id`, `incident` ) ,
	INDEX ( `date` ) ,
	INDEX ( `day_section` ) ,
	INDEX ( `person_id` ) ,
	INDEX ( `course_id` ) ,
	INDEX ( `incident` ) ,
	FOREIGN KEY ( `day_section` ) REFERENCES #__apoth_tt_daydetails( `day_section` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `person_id` ) REFERENCES #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `course_id` ) REFERENCES #__apoth_cm_courses( `id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
	FOREIGN KEY ( `incident` ) REFERENCES #__apoth_att_incidents( `code` ) ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_att_notes` (
	`id` BIGINT NOT NULL AUTO_INCREMENT ,
	`pupil_id` VARCHAR( 20 ) NOT NULL ,
	`message` TEXT NOT NULL ,
	`last_modified` DATETIME NOT NULL ,
	`delivered_on` DATETIME NULL ,
	PRIMARY KEY ( `id` ) ,
	INDEX ( `pupil_id` ) ,
	INDEX ( `delivered_on` ) ,
	FOREIGN KEY ( `pupil_id` ) REFERENCES #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `#__apoth_att_truants` (
	`pupil_id` VARCHAR( 20 ) NOT NULL ,
	`valid_from` DATETIME NOT NULL ,
	`valid_to` DATETIME NULL ,
	INDEX ( `pupil_id` ) ,
	INDEX ( `valid_from` ) ,
	INDEX ( `valid_to` ) ,
	FOREIGN KEY ( `pupil_id` ) REFERENCES #__apoth_ppl_people( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

INSERT INTO `#__apoth_att_statistical_meaning`
( `id`, `meaning` )
VALUES
( 1, 'Present' ),
( 2, 'Approved educational activity' ),
( 3, 'Authorised absence' ),
( 4, 'Unauthorised absence' ),
( 5, 'Attendance not required' ),
( 6, 'No mark' );

INSERT INTO `#__apoth_att_physical_meaning`
( `id`, `meaning` )
VALUES
( 1, 'In for whole session' ),
( 2, 'Late for session' ),
( 3, 'Out for whole session' ),
( 4, 'Left session early' ),
( 5, 'No mark' );

INSERT INTO `#__apoth_att_school_meaning`
( `id`, `meaning` )
VALUES
(  1, 'Present (AM)' ),
(  2, 'Present (PM)' ),
(  3, 'Do not use' ),
(  4, 'Educated off site (not Dual Reg.)' ),
(  5, 'Other authorised circumstances' ),
(  6, 'Dual Registration' ),
(  7, 'Excluded' ),
(  8, 'Extended family holiday (agreed)' ),
(  9, 'Family holiday (not agreed)' ),
( 10, 'Family Holiday (agreed)' ),
( 11, 'Illness' ),
( 12, 'Interview' ),
( 13, 'Late (before registers closed)' ),
( 14, 'Medical/Dentist appointment' ),
( 15, 'No reason yet provided for absence' ),
( 16, 'Unauthorised Abs' ),
( 17, 'Approved sporting activity' ),
( 18, 'Religious observance' ),
( 19, 'Study leave' ),
( 20, 'Traveller absence' ),
( 21, 'Late (after registers closed)' ),
( 22, 'Educational visit or trip' ),
( 23, 'Work Experience' ),
( 24, 'Dfes #: School closed to pupils' ),
( 25, 'Enforced closure' ),
( 26, 'Do not use' ),
( 27, 'Dfes X: Non-compulsory school age abs' ),
( 28, 'School closed to pupils & staff' ),
( 29, 'Dfes Z: Pupil not on roll' ),
( 30, 'All should attend / No mark recorded' );

INSERT INTO `#__apoth_att_codes`
( `code` , `school_meaning` , `statistical_meaning` , `physical_meaning`, `is_common`, `order_id`, `image_link` )
VALUES
( '/',  1, 1, 1, 1, 1, 'components/com_arc_attendance/images/present.png' ),
( CHAR(92), 2, 1, 1, 0, 2, NULL ),
( '@',  3, 4, 2, 0, 3 , NULL ),
( 'B',  4, 2, 3, 0, 4 , NULL ),
( 'C',  5, 3, 3, 0, 5 , NULL ),
( 'D',  6, 2, 3, 0, 6 , NULL ),
( 'E',  7, 3, 3, 0, 7 , NULL ),
( 'F',  8, 3, 3, 0, 8 , NULL ),
( 'G',  9, 4, 3, 0, 9 , NULL ),
( 'H', 10, 3, 3, 0, 10, NULL ),
( 'I', 11, 3, 3, 0, 11, NULL ),
( 'J', 12, 2, 3, 0, 12, NULL ),
( 'L', 13, 1, 2, 1, 13, 'components/com_arc_attendance/images/late.png' ),
( 'M', 14, 3, 3, 0, 14, NULL ),
( 'N', 15, 4, 3, 1, 15, 'components/com_arc_attendance/images/absent.png' ),
( 'O', 16, 4, 3, 0, 16, NULL ),
( 'P', 17, 2, 3, 0, 17, NULL ),
( 'R', 18, 3, 3, 0, 18, NULL ),
( 'S', 19, 3, 3, 0, 19, NULL ),
( 'T', 20, 3, 3, 0, 20, NULL ),
( 'U', 21, 4, 2, 0, 21, NULL ),
( 'V', 22, 2, 3, 0, 22, NULL ),
( 'W', 23, 2, 3, 0, 23, NULL ),
( 'X', 24, 5, 3, 0, 24, NULL ),
( 'Y', 25, 5, 3, 0, 25, NULL ),
( 'Z', 26, 3, 3, 0, 26, NULL ),
( '!', 27, 5, 3, 0, 27, NULL ),
( '#', 28, 5, 3, 0, 28, NULL ),
( '*', 29, 5, 3, 0, 29, NULL ),
( '-', 30, 6, 5, 0, 30, NULL );

INSERT INTO `#__apoth_att_incidents`
(`code`, `score`, `meaning`, `type`, `valid_from`, `valid_to`)
VALUES
( 'B', -1, 'Behaviour',                        'checkbox', '0000-00-00 00:00:00', NULL ),
( 'E', -1, 'Forgot / Inappropriate Equipment', 'checkbox', '0000-00-00 00:00:00', NULL ),
( 'H', -1, 'Forgot Homework',                  'checkbox', '0000-00-00 00:00:00', NULL ),
( 'M', -1, 'Music Lesson',                     'checkbox', '0000-00-00 00:00:00', NULL ),
( 'N', -1, 'Nurse',                            'checkbox', '0000-00-00 00:00:00', NULL ),
( 'P', -1, 'PE Note',                          'checkbox', '2008-02-10 08:26:17', NULL ),
( 'T', -1, 'Toilet Break',                     'checkbox', '0000-00-00 00:00:00', NULL ),
( 'U', -1, 'Uniform Infringement',             'checkbox', '2008-02-10 08:26:17', NULL );

# -- Sample attendance indicates the following
# GB-19980602-0001-7 late every morning
# GB-19980319-0001-8 \ bunk or attend together
# GB-19980113-0001-5 / often late though
# GB-19971023-0001-7 bunks pe physical (12, 13)
# GB-19971015-0001-3 misses wednesdays
# all others punctual

#INSERT INTO `#__apoth_att_dailyatt`
#( `date`, `day_section`, `person_id`, `course_id`, `att_code` )
#VALUES
## Monday
#("2007-01-01", "Period 1", "GB-19970517-0001-9", "18", "/"),
#("2007-01-01", "Period 1", "GB-19971015-0001-3", "18", "/"),
#("2007-01-01", "Period 1", "GB-19971023-0001-7", "18", "/"),
#("2007-01-01", "Period 1", "GB-19971130-0001-0", "18", "/"),
#("2007-01-01", "Period 1", "GB-19971205-0002-8", "18", "/"),
#("2007-01-01", "Period 1", "GB-19980101-0001-0", "18", "/"),
#("2007-01-01", "Period 1", "GB-19980202-0001-6", "18", "/"),
#("2007-01-01", "Period 1", "GB-19980319-0001-8", "18", "O"),
#("2007-01-01", "Period 1", "GB-19980521-0002-7", "18", "/"),
#("2007-01-01", "Period 1", "GB-19980602-0001-7", "18", "L"),
#("2007-01-01", "Period 1", "GB-19980624-0001-1", "18", "/"),
#("2007-01-01", "Period 1", "GB-19980801-0001-5", "18", "/"),
#
#("2007-01-01", "Period 1", "GB-19970923-0001-9", "19", "/"),
#("2007-01-01", "Period 1", "GB-19971004-0001-7", "19", "/"),
#("2007-01-01", "Period 1", "GB-19971112-0001-8", "19", "/"),
#("2007-01-01", "Period 1", "GB-19971126-0001-8", "19", "/"),
#("2007-01-01", "Period 1", "GB-19971205-0001-0", "19", "/"),
#("2007-01-01", "Period 1", "GB-19971214-0001-2", "19", "/"),
#("2007-01-01", "Period 1", "GB-19980113-0001-5", "19", "O"),
#("2007-01-01", "Period 1", "GB-19980214-0001-1", "19", "/"),
#("2007-01-01", "Period 1", "GB-19980430-0001-3", "19", "/"),
#("2007-01-01", "Period 1", "GB-19980430-0002-1", "19", "/"),
#("2007-01-01", "Period 1", "GB-19980521-0001-9", "19", "/"),
#("2007-01-01", "Period 1", "GB-19980603-0001-5", "19", "/"),
#("2007-01-01", "Period 1", "GB-19980724-0001-9", "19", "/"),
#
#("2007-01-01", "Period 2", "GB-19970517-0001-9", "12", "/"),
#("2007-01-01", "Period 2", "GB-19971004-0001-7", "12", "/"),
#("2007-01-01", "Period 2", "GB-19971112-0001-8", "12", "/"),
#("2007-01-01", "Period 2", "GB-19971126-0001-8", "12", "/"),
#("2007-01-01", "Period 2", "GB-19971205-0002-8", "12", "/"),
#("2007-01-01", "Period 2", "GB-19971214-0001-2", "12", "/"),
#("2007-01-01", "Period 2", "GB-19980202-0001-6", "12", "/"),
#("2007-01-01", "Period 2", "GB-19980214-0001-1", "12", "/"),
#("2007-01-01", "Period 2", "GB-19980430-0001-3", "12", "/"),
#("2007-01-01", "Period 2", "GB-19980430-0002-1", "12", "/"),
#("2007-01-01", "Period 2", "GB-19980521-0002-7", "12", "/"),
#("2007-01-01", "Period 2", "GB-19980602-0001-7", "12", "/"),
#("2007-01-01", "Period 2", "GB-19980603-0001-5", "12", "/"),
#
#("2007-01-01", "Period 2", "GB-19970923-0001-9", "13", "/"),
#("2007-01-01", "Period 2", "GB-19971015-0001-3", "13", "/"),
#("2007-01-01", "Period 2", "GB-19971023-0001-7", "13", "I"),
#("2007-01-01", "Period 2", "GB-19971130-0001-0", "13", "/"),
#("2007-01-01", "Period 2", "GB-19971205-0001-0", "13", "/"),
#("2007-01-01", "Period 2", "GB-19980101-0001-0", "13", "/"),
#("2007-01-01", "Period 2", "GB-19980113-0001-5", "13", "L"),
#("2007-01-01", "Period 2", "GB-19980319-0001-8", "13", "L"),
#("2007-01-01", "Period 2", "GB-19980521-0001-9", "13", "/"),
#("2007-01-01", "Period 2", "GB-19980624-0001-1", "13", "/"),
#("2007-01-01", "Period 2", "GB-19980724-0001-9", "13", "/"),
#("2007-01-01", "Period 2", "GB-19980801-0001-5", "13", "/"),
#
#("2007-01-01", "Period 3", "GB-19970517-0001-9", "20", "/"),
#("2007-01-01", "Period 3", "GB-19971004-0001-7", "20", "/"),
#("2007-01-01", "Period 3", "GB-19971023-0001-7", "20", "/"),
#("2007-01-01", "Period 3", "GB-19971130-0001-0", "20", "/"),
#("2007-01-01", "Period 3", "GB-19971205-0001-0", "20", "/"),
#("2007-01-01", "Period 3", "GB-19980101-0001-0", "20", "/"),
#("2007-01-01", "Period 3", "GB-19980202-0001-6", "20", "/"),
#("2007-01-01", "Period 3", "GB-19980214-0001-1", "20", "/"),
#("2007-01-01", "Period 3", "GB-19980430-0002-1", "20", "/"),
#("2007-01-01", "Period 3", "GB-19980521-0001-9", "20", "/"),
#("2007-01-01", "Period 3", "GB-19980624-0001-1", "20", "/"),
#("2007-01-01", "Period 3", "GB-19980801-0001-5", "20", "/"),
#
#("2007-01-01", "Period 3", "GB-19970923-0001-9", "21", "/"),
#("2007-01-01", "Period 3", "GB-19971015-0001-3", "21", "/"),
#("2007-01-01", "Period 3", "GB-19971112-0001-8", "21", "/"),
#("2007-01-01", "Period 3", "GB-19971126-0001-8", "21", "/"),
#("2007-01-01", "Period 3", "GB-19971205-0002-8", "21", "/"),
#("2007-01-01", "Period 3", "GB-19971214-0001-2", "21", "/"),
#("2007-01-01", "Period 3", "GB-19980113-0001-5", "21", "/"),
#("2007-01-01", "Period 3", "GB-19980319-0001-8", "21", "/"),
#("2007-01-01", "Period 3", "GB-19980430-0001-3", "21", "/"),
#("2007-01-01", "Period 3", "GB-19980521-0002-7", "21", "/"),
#("2007-01-01", "Period 3", "GB-19980602-0001-7", "21", "/"),
#("2007-01-01", "Period 3", "GB-19980603-0001-5", "21", "/"),
#("2007-01-01", "Period 3", "GB-19980724-0001-9", "21", "/"),
#
## Tuesday
#("2007-01-02", "Period 1", "GB-19970517-0001-9", "10", "/"),
#("2007-01-02", "Period 1", "GB-19971015-0001-3", "10", "/"),
#("2007-01-02", "Period 1", "GB-19971112-0001-8", "10", "/"),
#("2007-01-02", "Period 1", "GB-19971130-0001-0", "10", "/"),
#("2007-01-02", "Period 1", "GB-19971205-0001-0", "10", "/"),
#("2007-01-02", "Period 1", "GB-19971214-0001-2", "10", "/"),
#("2007-01-02", "Period 1", "GB-19980202-0001-6", "10", "/"),
#("2007-01-02", "Period 1", "GB-19980214-0001-1", "10", "/"),
#("2007-01-02", "Period 1", "GB-19980430-0001-3", "10", "/"),
#("2007-01-02", "Period 1", "GB-19980430-0002-1", "10", "/"),
#("2007-01-02", "Period 1", "GB-19980521-0001-9", "10", "/"),
#("2007-01-02", "Period 1", "GB-19980602-0001-7", "10", "L"),
#("2007-01-02", "Period 1", "GB-19980801-0001-5", "10", "/"),
#
#("2007-01-02", "Period 1", "GB-19970923-0001-9", "11", "/"),
#("2007-01-02", "Period 1", "GB-19971004-0001-7", "11", "/"),
#("2007-01-02", "Period 1", "GB-19971023-0001-7", "11", "/"),
#("2007-01-02", "Period 1", "GB-19971126-0001-8", "11", "/"),
#("2007-01-02", "Period 1", "GB-19971205-0002-8", "11", "/"),
#("2007-01-02", "Period 1", "GB-19980101-0001-0", "11", "/"),
#("2007-01-02", "Period 1", "GB-19980113-0001-5", "11", "L"),
#("2007-01-02", "Period 1", "GB-19980319-0001-8", "11", "L"),
#("2007-01-02", "Period 1", "GB-19980521-0002-7", "11", "/"),
#("2007-01-02", "Period 1", "GB-19980603-0001-5", "11", "/"),
#("2007-01-02", "Period 1", "GB-19980624-0001-1", "11", "/"),
#("2007-01-02", "Period 1", "GB-19980724-0001-9", "11", "/"),
#
#("2007-01-02", "Period 2", "GB-19970517-0001-9", "12", "/"),
#("2007-01-02", "Period 2", "GB-19971004-0001-7", "12", "/"),
#("2007-01-02", "Period 2", "GB-19971112-0001-8", "12", "/"),
#("2007-01-02", "Period 2", "GB-19971126-0001-8", "12", "/"),
#("2007-01-02", "Period 2", "GB-19971205-0002-8", "12", "/"),
#("2007-01-02", "Period 2", "GB-19971214-0001-2", "12", "/"),
#("2007-01-02", "Period 2", "GB-19980202-0001-6", "12", "/"),
#("2007-01-02", "Period 2", "GB-19980214-0001-1", "12", "/"),
#("2007-01-02", "Period 2", "GB-19980430-0001-3", "12", "/"),
#("2007-01-02", "Period 2", "GB-19980430-0002-1", "12", "/"),
#("2007-01-02", "Period 2", "GB-19980521-0002-7", "12", "/"),
#("2007-01-02", "Period 2", "GB-19980602-0001-7", "12", "/"),
#("2007-01-02", "Period 2", "GB-19980603-0001-5", "12", "/"),
#
#("2007-01-02", "Period 2", "GB-19970923-0001-9", "13", "/"),
#("2007-01-02", "Period 2", "GB-19971015-0001-3", "13", "/"),
#("2007-01-02", "Period 2", "GB-19971023-0001-7", "13", "I"),
#("2007-01-02", "Period 2", "GB-19971130-0001-0", "13", "/"),
#("2007-01-02", "Period 2", "GB-19971205-0001-0", "13", "/"),
#("2007-01-02", "Period 2", "GB-19980101-0001-0", "13", "/"),
#("2007-01-02", "Period 2", "GB-19980113-0001-5", "13", "/"),
#("2007-01-02", "Period 2", "GB-19980319-0001-8", "13", "/"),
#("2007-01-02", "Period 2", "GB-19980521-0001-9", "13", "/"),
#("2007-01-02", "Period 2", "GB-19980624-0001-1", "13", "/"),
#("2007-01-02", "Period 2", "GB-19980724-0001-9", "13", "/"),
#("2007-01-02", "Period 2", "GB-19980801-0001-5", "13", "/"),
#
#("2007-01-02", "Period 3", "GB-19970517-0001-9", "16", "/"),
#("2007-01-02", "Period 3", "GB-19971015-0001-3", "16", "/"),
#("2007-01-02", "Period 3", "GB-19971023-0001-7", "16", "/"),
#("2007-01-02", "Period 3", "GB-19971126-0001-8", "16", "/"),
#("2007-01-02", "Period 3", "GB-19971205-0002-8", "16", "/"),
#("2007-01-02", "Period 3", "GB-19971214-0001-2", "16", "/"),
#("2007-01-02", "Period 3", "GB-19980113-0001-5", "16", "I"),
#("2007-01-02", "Period 3", "GB-19980214-0001-1", "16", "/"),
#("2007-01-02", "Period 3", "GB-19980430-0002-1", "16", "/"),
#("2007-01-02", "Period 3", "GB-19980521-0001-9", "16", "/"),
#("2007-01-02", "Period 3", "GB-19980521-0002-7", "16", "/"),
#("2007-01-02", "Period 3", "GB-19980624-0001-1", "16", "/"),
#("2007-01-02", "Period 3", "GB-19980801-0001-5", "16", "/"),
#
#("2007-01-02", "Period 3", "GB-19970923-0001-9", "17", "/"),
#("2007-01-02", "Period 3", "GB-19971004-0001-7", "17", "/"),
#("2007-01-02", "Period 3", "GB-19971112-0001-8", "17", "/"),
#("2007-01-02", "Period 3", "GB-19971130-0001-0", "17", "/"),
#("2007-01-02", "Period 3", "GB-19971205-0001-0", "17", "/"),
#("2007-01-02", "Period 3", "GB-19980101-0001-0", "17", "/"),
#("2007-01-02", "Period 3", "GB-19980202-0001-6", "17", "/"),
#("2007-01-02", "Period 3", "GB-19980319-0001-8", "17", "I"),
#("2007-01-02", "Period 3", "GB-19980430-0001-3", "17", "/"),
#("2007-01-02", "Period 3", "GB-19980602-0001-7", "17", "/"),
#("2007-01-02", "Period 3", "GB-19980603-0001-5", "17", "/"),
#("2007-01-02", "Period 3", "GB-19980724-0001-9", "17", "/"),
#
## Wednesday
#("2007-01-03", "Period 1", "GB-19970517-0001-9", "14", "/"),
#("2007-01-03", "Period 1", "GB-19971004-0001-7", "14", "/"),
#("2007-01-03", "Period 1", "GB-19971023-0001-7", "14", "/"),
#("2007-01-03", "Period 1", "GB-19971126-0001-8", "14", "/"),
#("2007-01-03", "Period 1", "GB-19971205-0001-0", "14", "/"),
#("2007-01-03", "Period 1", "GB-19971214-0001-2", "14", "/"),
#("2007-01-03", "Period 1", "GB-19980113-0001-5", "14", "L"),
#("2007-01-03", "Period 1", "GB-19980214-0001-1", "14", "/"),
#("2007-01-03", "Period 1", "GB-19980521-0001-9", "14", "/"),
#("2007-01-03", "Period 1", "GB-19980603-0001-5", "14", "/"),
#("2007-01-03", "Period 1", "GB-19980724-0001-9", "14", "/"),
#("2007-01-03", "Period 1", "GB-19980801-0001-5", "14", "/"),
#
#("2007-01-03", "Period 1", "GB-19970923-0001-9", "15", "/"),
#("2007-01-03", "Period 1", "GB-19971015-0001-3", "15", "O"),
#("2007-01-03", "Period 1", "GB-19971112-0001-8", "15", "/"),
#("2007-01-03", "Period 1", "GB-19971130-0001-0", "15", "/"),
#("2007-01-03", "Period 1", "GB-19971205-0002-8", "15", "/"),
#("2007-01-03", "Period 1", "GB-19980101-0001-0", "15", "/"),
#("2007-01-03", "Period 1", "GB-19980202-0001-6", "15", "/"),
#("2007-01-03", "Period 1", "GB-19980319-0001-8", "15", "L"),
#("2007-01-03", "Period 1", "GB-19980430-0001-3", "15", "/"),
#("2007-01-03", "Period 1", "GB-19980430-0002-1", "15", "/"),
#("2007-01-03", "Period 1", "GB-19980521-0002-7", "15", "/"),
#("2007-01-03", "Period 1", "GB-19980602-0001-7", "15", "L"),
#("2007-01-03", "Period 1", "GB-19980624-0001-1", "15", "/"),
#
#("2007-01-03", "Period 2", "GB-19970517-0001-9", "10", "/"),
#("2007-01-03", "Period 2", "GB-19971015-0001-3", "10", "O"),
#("2007-01-03", "Period 2", "GB-19971112-0001-8", "10", "/"),
#("2007-01-03", "Period 2", "GB-19971130-0001-0", "10", "/"),
#("2007-01-03", "Period 2", "GB-19971205-0001-0", "10", "/"),
#("2007-01-03", "Period 2", "GB-19971214-0001-2", "10", "/"),
#("2007-01-03", "Period 2", "GB-19980202-0001-6", "10", "/"),
#("2007-01-03", "Period 2", "GB-19980214-0001-1", "10", "/"),
#("2007-01-03", "Period 2", "GB-19980430-0001-3", "10", "/"),
#("2007-01-03", "Period 2", "GB-19980430-0002-1", "10", "/"),
#("2007-01-03", "Period 2", "GB-19980521-0001-9", "10", "/"),
#("2007-01-03", "Period 2", "GB-19980602-0001-7", "10", "/"),
#("2007-01-03", "Period 2", "GB-19980801-0001-5", "10", "/"),
#
#("2007-01-03", "Period 2", "GB-19970923-0001-9", "11", "/"),
#("2007-01-03", "Period 2", "GB-19971004-0001-7", "11", "/"),
#("2007-01-03", "Period 2", "GB-19971023-0001-7", "11", "/"),
#("2007-01-03", "Period 2", "GB-19971126-0001-8", "11", "/"),
#("2007-01-03", "Period 2", "GB-19971205-0002-8", "11", "/"),
#("2007-01-03", "Period 2", "GB-19980101-0001-0", "11", "/"),
#("2007-01-03", "Period 2", "GB-19980113-0001-5", "11", "/"),
#("2007-01-03", "Period 2", "GB-19980319-0001-8", "11", "/"),
#("2007-01-03", "Period 2", "GB-19980521-0002-7", "11", "/"),
#("2007-01-03", "Period 2", "GB-19980603-0001-5", "11", "/"),
#("2007-01-03", "Period 2", "GB-19980624-0001-1", "11", "/"),
#("2007-01-03", "Period 2", "GB-19980724-0001-9", "11", "/"),
#
#("2007-01-03", "Period 3", "GB-19970517-0001-9", "16", "/"),
#("2007-01-03", "Period 3", "GB-19971015-0001-3", "16", "O"),
#("2007-01-03", "Period 3", "GB-19971023-0001-7", "16", "/"),
#("2007-01-03", "Period 3", "GB-19971126-0001-8", "16", "/"),
#("2007-01-03", "Period 3", "GB-19971205-0002-8", "16", "/"),
#("2007-01-03", "Period 3", "GB-19971214-0001-2", "16", "/"),
#("2007-01-03", "Period 3", "GB-19980113-0001-5", "16", "O"),
#("2007-01-03", "Period 3", "GB-19980214-0001-1", "16", "/"),
#("2007-01-03", "Period 3", "GB-19980430-0002-1", "16", "/"),
#("2007-01-03", "Period 3", "GB-19980521-0001-9", "16", "/"),
#("2007-01-03", "Period 3", "GB-19980521-0002-7", "16", "/"),
#("2007-01-03", "Period 3", "GB-19980624-0001-1", "16", "/"),
#("2007-01-03", "Period 3", "GB-19980801-0001-5", "16", "/"),
#
#("2007-01-03", "Period 3", "GB-19970923-0001-9", "17", "/"),
#("2007-01-03", "Period 3", "GB-19971004-0001-7", "17", "/"),
#("2007-01-03", "Period 3", "GB-19971112-0001-8", "17", "/"),
#("2007-01-03", "Period 3", "GB-19971130-0001-0", "17", "/"),
#("2007-01-03", "Period 3", "GB-19971205-0001-0", "17", "/"),
#("2007-01-03", "Period 3", "GB-19980101-0001-0", "17", "/"),
#("2007-01-03", "Period 3", "GB-19980202-0001-6", "17", "/"),
#("2007-01-03", "Period 3", "GB-19980319-0001-8", "17", "O"),
#("2007-01-03", "Period 3", "GB-19980430-0001-3", "17", "/"),
#("2007-01-03", "Period 3", "GB-19980602-0001-7", "17", "/"),
#("2007-01-03", "Period 3", "GB-19980603-0001-5", "17", "/"),
#("2007-01-03", "Period 3", "GB-19980724-0001-9", "17", "/");
