-- package     Arc
-- subpackage  Behaviour
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_bhv_inc_types` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`parent` int(10) unsigned default NULL,
	`label` varchar(100) NOT NULL,
	`score` int(11) default NULL,
	`msg_tag` int(10) unsigned default NULL,
	`has_text` tinyint(1) NOT NULL default '0',
	PRIMARY KEY (`id`),
	INDEX (`parent`),
	FOREIGN KEY (`parent`) REFERENCES `#__apoth_bhv_inc_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_bhv_actions` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`label` varchar(50) NOT NULL,
	`score` varchar(20) default NULL,
	`has_text` tinyint(1) NOT NULL default '0',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_bhv_inc_actions` (
	`inc_id` int(10) unsigned NOT NULL,
	`act_id` int(10) unsigned NOT NULL,
	`order` int(10) unsigned default NULL,
	INDEX (`inc_id`),
	INDEX (`act_id`),
	FOREIGN KEY (`inc_id`) REFERENCES `#__apoth_bhv_inc_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`act_id`) REFERENCES `#__apoth_bhv_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_bhv_scores` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`person_id` varchar(20) default NULL,
	`group_id` int(10) default NULL,
	`score` int(11) NOT NULL,
	`date_issued` datetime NOT NULL,
	`msg_id` int(11) default NULL,
	PRIMARY KEY  (`id`),
	INDEX (`person_id`),
	INDEX (`group_id`),
	INDEX (`date_issued`),
	FOREIGN KEY (`person_id`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`group_id`) REFERENCES `#__apoth_cm_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO `#__apoth_msg_tags` (`id`, `parent`, `label`, `category`, `enabled`, `order`) VALUES
(20,  1, 'Behaviour', 'folder',    1, 2),
(21, 20, 'Inbox',     'folder',    1, 1),
(22, 20, 'Sent',      'folder',    1, 2),
(23, 20, 'Drafts',    'folder',    1, 3),
(24, 20, 'Archive',   'folder',    1, 4),
(25, 20, 'Bin',       'folder',    0, 5),
(31,  2, 'Gold',      'attribute', 1, NULL),
(32,  2, 'Green',     'attribute', 1, NULL),
(33,  2, 'Amber',     'attribute', 1, NULL),
(34,  2, 'Red',       'attribute', 1, NULL),
(35,  2, 'Purple',    'attribute', 1, NULL),
(36,  2, 'Tutor',     'attribute', 1, NULL),
(37,  2, 'Lesson',    'attribute', 1, NULL),
(38,  2, 'Untaught',  'attribute', 1, NULL),
(39,  2, 'Clear',     'attribute', 1, NULL);

INSERT INTO `#__apoth_bhv_inc_types` (`id`, `parent`, `label`, `score`, `msg_tag`, `has_text`) VALUES
( 1,   1, 'Gold',    4, 31, 0),
( 2,   2, 'Green',   2, 32, 0),
( 3,   3, 'Amber',  -1, 33, 0),
( 4,   4, 'Red',    -2, 34, 0),
( 5,   5, 'Purple', -4, 35, 0),
( 6,   6, 'Clear',   0, 39, 0),

(NULL, 1, 'Homework / Homestudy',                                                            NULL, NULL, 0),
(NULL, 1, 'Progress',                                                                        NULL, NULL, 0),
(NULL, 1, 'Contribution in class',                                                           NULL, NULL, 0),
(NULL, 1, 'Class work',                                                                      NULL, NULL, 0),
(NULL, 1, 'Sustained excellent active participator',                                         NULL, NULL, 0),
(NULL, 1, 'Demonstrating sustained excellent team work',                                     NULL, NULL, 0),
(NULL, 1, 'Demonstrating sustained excellent reflection and thinking about ways to improve', NULL, NULL, 0),
(NULL, 1, 'Being a sustained excellent creative thinker',                                    NULL, NULL, 0),
(NULL, 1, 'Demonstrating sustained excellent skills of self management',                     NULL, NULL, 0),
(NULL, 1, 'Demonstrating sustained excellent skills of independent enquiry',                 NULL, NULL, 0),
(NULL, 1, 'Community involvement',                                                           NULL, NULL, 0),
(NULL, 1, 'Other',                                                                           NULL, NULL, 1),

(NULL, 2, 'Homework / Homestudy',                                                  NULL, NULL, 0),
(NULL, 2, 'Progress',                                                              NULL, NULL, 0),
(NULL, 2, 'Contribution in class',                                                 NULL, NULL, 0),
(NULL, 2, 'Class work',                                                            NULL, NULL, 0),
(NULL, 2, 'Excellent active participator',                                         NULL, NULL, 0),
(NULL, 2, 'Demonstrating excellent team work',                                     NULL, NULL, 0),
(NULL, 2, 'Demonstrating excellent reflection and thinking about ways to improve', NULL, NULL, 0),
(NULL, 2, 'Being an excellent creative thinker',                                   NULL, NULL, 0),
(NULL, 2, 'Demonstrating excellent skills of self management',                     NULL, NULL, 0),
(NULL, 2, 'Demonstrating excellent skills of independent enquiry',                 NULL, NULL, 0),
(NULL, 2, 'Community involvement',                                                 NULL, NULL, 0),
(NULL, 2, 'Extra curricular',                                                      NULL, NULL, 0),
(NULL, 2, 'Clinics',                                                               NULL, NULL, 0),
(NULL, 2, 'Other',                                                                 NULL, NULL, 1),

(NULL, 3, 'Not handing homework in on time',  NULL, NULL, 0),
(NULL, 3, 'Disruptive calling out',           NULL, NULL, 0),
(NULL, 3, 'Poor attendance',                  NULL, NULL, 0),
(NULL, 3, 'Punctuality',                      NULL, NULL, 0),
(NULL, 3, 'Missing equipment',                NULL, NULL, 0),
(NULL, 3, 'Incorrect uniform',                NULL, NULL, 0),
(NULL, 3, 'Eating in class',                  NULL, NULL, 0),
(NULL, 3, 'Dropping litter',                  NULL, NULL, 0),
(NULL, 3, 'Missed subject detention',         NULL, NULL, 0),
(NULL, 3, 'Missed tutor detention',           NULL, NULL, 0),
(NULL, 3, 'Late for tutor',                   NULL, NULL, 0),
(NULL, 3, 'Failed to get report card signed', NULL, NULL, 0),
(NULL, 3, 'Lack of work in lessons',          NULL, NULL, 0),
(NULL, 3, 'Other',                            NULL, NULL, 1),

(NULL, 4, 'Refusal to follow staff instructions', NULL, NULL, 0),
(NULL, 4, 'Out of bounds',                        NULL, NULL, 0),
(NULL, 4, 'Smoking',                              NULL, NULL, 0),
(NULL, 4, 'Bullying',                             NULL, NULL, 0),
(NULL, 4, 'Failure to hand in coursework',        NULL, NULL, 0),
(NULL, 4, 'Missed tutor detentions',              NULL, NULL, 0),
(NULL, 4, 'Missed school detentions',             NULL, NULL, 0),
(NULL, 4, 'Repeated amber incidents (3 times)',   NULL, NULL, 0),
(NULL, 4, 'Truanting',                            NULL, NULL, 0),
(NULL, 4, 'Minor physical',                       NULL, NULL, 0),
(NULL, 4, 'Missed subject detentions',            NULL, NULL, 0),
(NULL, 4, 'Other',                                NULL, NULL, 1),

(NULL, 5, 'Swearing at staff',                             NULL, NULL, 0),
(NULL, 5, 'Physical assault on student',                   NULL, NULL, 0),
(NULL, 5, 'Physical assault on staff',                     NULL, NULL, 0),
(NULL, 5, 'Vandalism',                                     NULL, NULL, 0),
(NULL, 5, 'Refusal to follow instructions after warnings', NULL, NULL, 0),
(NULL, 5, 'Racism',                                        NULL, NULL, 0),
(NULL, 5, 'Other',                                         NULL, NULL, 1),

(NULL, 6, 'Music Lesson', NULL, NULL, 0),
(NULL, 6, 'Nurse',        NULL, NULL, 0),
(NULL, 6, 'P.E. Note',    NULL, NULL, 0),
(NULL, 6, 'Toilet Break', NULL, NULL, 0);

INSERT INTO `#__apoth_bhv_actions` (`id`, `label`, `score`, `has_text`) VALUES
( 1, 'Other',                               NULL,            1),
( 2, 'Positive discussion with pupil',      NULL,            0),
( 3, 'Ask student to add to ePortfolio',    NULL,            0),
( 4, 'Subject detention',                   NULL,            1),
( 5, 'Tutor detention',                     NULL,            0),
( 6, 'Break/lunchtime detention',           NULL,            1),
( 7, 'Verbal warning',                      NULL,            0),
( 8, 'Moved seats',                         NULL,            0),
( 9, 'Sent for cool off',                   NULL,            0),
(10, 'Spoke to SLT link',                   NULL,            1),
(11, 'Letter home',                         NULL,            1),
(12, 'Phone home',                          NULL,            1),
(13, 'Parental Interview',                  NULL,            1),
(14, 'Year detention',                      NULL,            1),
(15, 'School detention (DoL/SL/DoPA only)', '-3',            1),
(16, 'Head detention',                      '-5',            1),
(17, 'Inclusion (DoPA only)',               '-2 * n',        1),
(18, 'Exclusion',                           '-6 + (-2 * n)', 1),
(19, 'Social detention',                    NULL,            0);

INSERT INTO `#__apoth_bhv_inc_actions` (`inc_id`, `act_id`, `order`) VALUES
(1,  2, 1),
(1,  3, 2),
(1,  1, 3),

(2,  2, 1),
(2,  3, 2),
(2,  1, 3),

(3,  4, 1),
(3,  5, 2),
(3,  6, 3),
(3,  7, 5),
(3,  8, 7),
(3,  9, 6),
(3,  1, 8),
(3, 19, 4),
(3, 12, 9),

(4, 14, 1),
(4,  4, 2),
(4,  6, 3),
(4, 11, 4),
(4, 12, 5),
(4, 13, 6),
(4, 10, 7),
(4, 17, 8),
(4, 15, 9),

(5, 15, 1),
(5, 16, 2),
(5, 17, 3),
(5, 18, 4);

INSERT INTO `#__apoth_ppl_profile_categories` (`id`, `name`, `component`) VALUES
(NULL, 'awards', 'behaviour');