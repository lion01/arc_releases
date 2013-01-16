-- package     Arc
-- subpackage  People
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_ppl_addresses` (
	`id` varchar(40) NOT NULL default '',
	`src` TINYINT(3) UNSIGNED NULL DEFAULT 1,
	`ext_id` int(10) default NULL,
	`address_type` varchar(20) default NULL,
	`number` int(11) default NULL,
	`number_range` int(11) default NULL,
	`number_suffix` varchar(2) default NULL,
	`apartment` int(5) default NULL,
	`name` varchar(30) default NULL,
	`street` varchar(50) default NULL,
	`district` varchar(50) default NULL,
	`town` varchar(50) default NULL,
	`county` varchar(50) default NULL,
	`administrative_area` varchar(50) default NULL,
	`postcode` varchar(10) NOT NULL,
	`country` char(3) NOT NULL default 'GB',
	`landline_number` varchar(13) default NULL,
	INDEX (`ext_id`),
	INDEX (`id`),
	INDEX (`src`),
	FOREIGN KEY (`src`) REFERENCES `#__apoth_sys_data_sources` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_ppl_people` (
	`id` varchar(20) NOT NULL,
	`src` TINYINT(3) UNSIGNED NULL DEFAULT 1,
	`ext_person_id` varchar(20) default NULL,
	`juserid` int(11) default NULL,
	`upn` char(13) default NULL,
	`title` varchar(10) default NULL,
	`firstname` varchar(30) NOT NULL,
	`surname` varchar(35) NOT NULL,
	`middlenames` varchar(50) default NULL,
	`dob` date default NULL,
	`gender` varchar(1) NOT NULL,
	`former_surname` varchar(35) default NULL,
	`name_order_indicator` varchar(1) NOT NULL default '',
	`preferred_firstname` varchar(30) default NULL,
	`preferred_surname` varchar(35) default NULL,
	`ethnic_code` varchar(4) NOT NULL,
	`country_of_birth` varchar(2) default NULL,
	`nationality` varchar(15) NOT NULL,
	`first_language` varchar(20) default NULL,
	`language_code` varchar(3) default NULL,
	`email` varchar(75) default NULL,
	`photograph` varchar(50) default NULL,
	`mobile_number` varchar(12) default NULL,
	`address_id` varchar(40) default NULL,
	PRIMARY KEY (`id`),
	INDEX (`juserid`),
	INDEX (`src`),
	INDEX (`address_id`),
	FOREIGN KEY (`address_id`) REFERENCES `#__apoth_ppl_addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`src`) REFERENCES `#__apoth_sys_data_sources` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_ppl_address_history` (
	`person_id` varchar(20) NOT NULL,
	`address_id` varchar(13) NOT NULL,
	`start_date` date NOT NULL default '0000-00-00',
	`end_date` date default NULL,
	`start_time` time default NULL,
	`end_time` time default NULL,
	PRIMARY KEY (`person_id`,`address_id`,`start_date`),
	INDEX (`person_id`),
	INDEX (`address_id`),
	FOREIGN KEY (`person_id`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`address_id`) REFERENCES `#__apoth_ppl_addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_ppl_medical_conditions` (
	`conditions` varchar(50) NOT NULL,
	PRIMARY KEY (`conditions`)
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_ppl_medical` (
	`person_id` varchar(20) NOT NULL,
	`condition` varchar(50) NOT NULL,
	`start_date` date NOT NULL,
	`end_date` date default NULL,
	`notes` varchar(255) default NULL,
	PRIMARY KEY (`person_id`,`condition`,`start_date`),
	INDEX (`person_id`),
	INDEX (`condition`),
	FOREIGN KEY (`person_id`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`condition`) REFERENCES `#__apoth_ppl_medical_conditions` (`conditions`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_ppl_date_series` (
	`date` date NOT NULL,
	`number` int(10) NOT NULL auto_increment,
	PRIMARY KEY (`date`),
	INDEX (`number`)
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_ppl_contacts` (
	`id` int(5) NOT NULL auto_increment,
	`person` varchar(20) NOT NULL,
	`contact` varchar(20) NOT NULL,
	`priority` varchar(2) NOT NULL,
	`relationship` varchar(25) NOT NULL,
	`responsibility` varchar(1) NOT NULL,
	`day_location` varchar(13) NOT NULL,
	`written_communication` varchar(1) NOT NULL default 'M',
	`armed_forces_indicator` varchar(1) NOT NULL,
	`translator_indicator` varchar(1) NOT NULL,
	`priority_source` varchar(20) NOT NULL,
	`lives_with_contact` varchar(1) NOT NULL,
	`private_address` tinyint(1) NOT NULL default '0',
	PRIMARY KEY (`id`),
	INDEX (`person`),
	INDEX (`contact`),
	INDEX (`day_location`),
	FOREIGN KEY (`person`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`contact`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`day_location`) REFERENCES `#__apoth_ppl_addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_ppl_relation_tree` (
	`id` int(3) NOT NULL auto_increment,
	`parent` int(3) default NULL,
	`description` varchar(50) NOT NULL,
	`src` TINYINT(3) UNSIGNED NULL DEFAULT 1,
	`ext_id` int(3) default NULL,
	`ext_type` varchar(50) default NULL,
	`role` int(10) unsigned default NULL,
	PRIMARY KEY (`id`),
	INDEX (`parent`),
	INDEX (`src`),
	INDEX (`description`),
	FOREIGN KEY (`parent`) REFERENCES `#__apoth_ppl_relation_tree` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	FOREIGN KEY (`src`) REFERENCES `#__apoth_sys_data_sources` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO `#__apoth_ppl_relation_tree` (`id`, `parent`, `description`, `ext_id`, `ext_type`, `role`) VALUES
(1, 1, 'Root', NULL, NULL, NULL),
(2, 1, 'Carer', 1, 'category', NULL),
(3, 1, 'Childminder', 2, 'category', NULL),
(4, 1, 'Doctor', 3, 'category', NULL),
(5, 1, 'Other Family Member', 4, 'category', NULL),
(6, 1, 'Other Contact', 5, 'category', NULL),
(7, 1, 'Other Relative', 8, 'category', NULL),
(8, 1, 'Religious/Spiritual Contact', 9, 'category', NULL),
(9, 1, 'Self', 10, 'category', NULL),
(10, 1, 'Step Parent', 11, 'category', NULL),
(11, 1, 'Social Worker', 12, 'category', NULL),
(12, 1, 'Foster Parent', 13, 'category', NULL),
(13, 1, 'Parent', NULL, 'category', NULL),
(14, 1, 'Aunt', 2, 'type', NULL),
(15, 2, 'Carer', 3, 'type', NULL),
(16, 3, 'Childminder', 4, 'type', NULL),
(17, 6, 'Contact Person', 5, 'type', NULL),
(18, 4, 'Doctor', 1, 'type', NULL),
(19, 5, 'Other Family Member', 6, 'type', NULL),
(20, 12, 'Foster Parent', 7, 'type', 9),
(21, 6, 'Guardian', 8, 'type', 10),
(22, 6, 'Grandparent', 9, 'type', NULL),
(23, 6, 'LEA Nominee', 10, 'type', NULL),
(24, 6, 'Neighbour', 11, 'type', NULL),
(25, 6, 'Other Contact', 12, 'type', NULL),
(26, 13, 'Father', 13, 'type', 11),
(27, 13, 'Mother', 14, 'type', 11),
(28, 6, 'Probation Service', 15, 'type', NULL),
(29, 7, 'Other Relative', 16, 'type', NULL),
(30, 8, 'Religious/Spiritual Contact', 17, 'type', NULL),
(31, 6, 'Self', 18, 'type', NULL),
(32, 10, 'Step Parent', 19, 'type', 12),
(33, 11, 'Social Worker', 20, 'type', NULL),
(34, 1, 'Uncle', 21, 'type', NULL),
(35, 1, 'Foster Father', 14, 'category', NULL),
(36, 1, 'Foster Mother', 15, 'category', NULL),
(37, 1, 'Head Teacher', 16, 'category', NULL),
(38, 1, 'Step Father', 17, 'category', NULL),
(39, 1, 'Step Mother', 18, 'category', NULL),
(40, 1, 'Teacher', 19, 'category', NULL),
(41, 35, 'Foster Father', 22, 'type', 9),
(42, 36, 'Foster Mother', 23, 'type', 9),
(43, 37, 'Head Teacher', 24, 'type', NULL),
(44, 38, 'Step Father', 25, 'type', 12),
(45, 39, 'Step Mother', 26, 'type', 12),
(46, 40, 'Teacher', 27, 'type', NULL);

CREATE TABLE `#__apoth_ppl_relations` (
	`pupil_id` varchar(20) NOT NULL,
	`relation_id` varchar(20) NOT NULL,
	`relation_type_id` int(4) NOT NULL,
	`src` tinyint(3) unsigned default '1',
	`parental` tinyint(1) NOT NULL default '0',
	`legal_order` tinyint(1) NOT NULL default '0',
	`correspondence` tinyint(1) NOT NULL default '0',
	`reports` tinyint(1) NOT NULL default '0',
	`valid_from` datetime NOT NULL,
	`valid_to` datetime default NULL,
	PRIMARY KEY (`pupil_id`,`relation_id`,`relation_type_id`),
	INDEX (`pupil_id`),
	INDEX (`relation_id`),
	INDEX (`relation_type_id`),
	INDEX (`src`),
	FOREIGN KEY (`src`) REFERENCES `#__apoth_sys_data_sources` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	FOREIGN KEY (`pupil_id`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`relation_id`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`relation_type_id`) REFERENCES `#__apoth_ppl_relation_tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_ppl_profile_awards` (
	`id` tinyint(3) unsigned NOT NULL default '0',
	`name` varchar(50) NOT NULL,
	`image` varchar(50) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `#__apoth_ppl_profile_awards` (`id`, `name`, `image`) VALUES
(1, 'Gold Cup', 'goldcup.png'),
(2, 'Silver Cup', 'silvercup.jpg'),
(3, 'Bronze Cup', 'bronzecup.jpg'),
(4, 'Gold Medal', 'goldmedal.png'),
(5, 'Silver Medal', 'silvermedal.png'),
(6, 'Bronze Medal', 'bronzemedal.png');

CREATE TABLE `#__apoth_ppl_profile_categories` (
	`id` tinyint(3) unsigned NOT NULL auto_increment,
	`name` varchar(50) NOT NULL,
	`component` varchar(50) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `#__apoth_ppl_profile_categories` (`id`, `name`, `component`) VALUES
(NULL, 'ids', 'people');
SELECT @idsid := LAST_INSERT_ID();

INSERT INTO `#__apoth_ppl_profile_categories` (`id`, `name`, `component`) VALUES
(NULL, 'personal', 'people');
SELECT @peopleid := LAST_INSERT_ID();

CREATE TABLE `#__apoth_ppl_profiles` (
	`person_id` varchar(20) NOT NULL,
	`category_id` tinyint(3) unsigned NOT NULL,
	`property` varchar(50) NOT NULL,
	`value` text NOT NULL,
	PRIMARY KEY (`person_id`,`category_id`,`property`),
	INDEX (`category_id`),
	INDEX (`property`),
	FOREIGN KEY (`person_id`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`category_id`) REFERENCES `#__apoth_ppl_profile_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_ppl_profile_templates` (
	`person_type` varchar(20) NOT NULL,
	`category_id` tinyint(3) unsigned NOT NULL,
	`property` varchar(50) NOT NULL,
	`value` text NOT NULL,
	INDEX (`category_id`),
	FOREIGN KEY (`category_id`) REFERENCES `#__apoth_ppl_profile_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO `#__apoth_ppl_profile_templates` (`person_type`, `category_id`, `property`, `value`) VALUES
('pupil',   @idsid,    'ARC', '--'),
('pupil',   @idsid,    'FACEBOOK', ''),
('pupil',   @peopleid, 'biography', 'Write a description of yourself here.'),
('pupil',   @peopleid, 'year', '--'),

('teacher', @idsid,    'ARC', '--'),
('teacher', @idsid,    'FACEBOOK', '?'),
('teacher', @peopleid, 'biography', 'Write a description of yourself here.'),
('teacher', @peopleid, 'year', '--'),

('parent',  @idsid,    'ARC', '--'),
('parent',  @idsid,    'FACEBOOK', ''),
('parent',  @peopleid, 'biography', 'Write a description of yourself here.'),
('parent',  @peopleid, 'year', '-'),

('staff',   @idsid,    'ARC', '--'),
('staff',   @idsid,    'FACEBOOK', '*'),
('staff',   @peopleid, 'biography', 'Write a description of yourself here.'),
('staff',   @peopleid, 'year', '--');

ALTER TABLE `#__apoth_sys_com_roles`
ADD CONSTRAINT `ppl_sys_com_roles_people_id` FOREIGN KEY (`person_id`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO `#__apoth_sys_roles` (`id`, `parent`, `role`) VALUES
(10,  1, 'rel'),
(11, 10, 'parental'),
(12, 11, 'foster parent'),
(13, 11, 'guardian'),
(14, 11, 'parent'),
(15, 11, 'step parent'),
(16, 10, 'self'),
(17,  1, 'pastoral'),
(18, 17, 'sen_mentor'),
(19, 17, 'sen_mentee'),
(20, 17, 'sen_self');