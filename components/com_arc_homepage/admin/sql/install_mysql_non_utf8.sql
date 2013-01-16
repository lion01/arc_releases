-- package     Arc
-- subpackage  Homepage
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_home_panels` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`url` text NOT NULL,
	`alt` varchar(255) default NULL,
	`type` enum('internal','external','module') NOT NULL,
	`option` varchar(255) default NULL,
	`customisable` tinyint(1) unsigned NOT NULL default '1',
	`persistent` tinyint(1) unsigned NOT NULL default '0',
	`jscript` varchar(255) default NULL,
	`css` varchar(255) default NULL,
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB;

INSERT INTO `#__apoth_home_panels`
	(`id`, `url`, `alt`, `type`, `option`, `jscript`)
VALUES
	( 1, 'http://www.facebook.com/home.php?id=~FACEBOOK~',                                                'Facebook',             'external', NULL,                 NULL ),
	( 2, '&view=lists&scope=courses&Itemid=229',                                                          'Reports',              'internal', 'com_arc_report',     NULL ),
	( 3, '&view=reports&report=attsummary&Itemid=329&format=raw&scope=pupil&pId=~ARC~',                   'Attendance Summary',   'internal', 'com_arc_attendance', 'components/com_arc_attendance/views/reports/tmpl/att_panel_script.js' ),
	( 4, '&view=reports&report=today&Itemid=331&format=raw&scope=person&pId=~ARC~',                       'Today''s timetable',   'internal', 'com_arc_timetable',  NULL ),
	( 5, '&view=markbook&Itemid=218&format=raw&task=summary&assessments[]=1313&assessments[]=1315&ass[1313][1][]=2402&ass[1313][1][]=2412&ass[1313][2][]=2403&ass[1313][2][]=2413&ass[1313][3][]=2404&ass[1313][3][]=2414&ass[1313][4][]=2405&ass[1313][4][]=2415&pupil=~ARC~&no_others=1' , 'Assessment Summary',   'internal', 'com_arc_assessment', NULL ),
	( 6, '&view=profile&Itemid=332&task=search&pId=~ARC~&scope=panel&panel=links&format=raw',             'My Links',             'internal', 'com_arc_people',     NULL ),
	( 7, '&view=profile&Itemid=332&task=search&pId=~ARC~&scope=panel&panel=awards&format=raw',            'My Awards',            'internal', 'com_arc_people',     NULL ),
	( 8, 'http://newsrss.bbc.co.uk/rss/newsonline_uk_edition/front_page/rss.xml\r\n
	      http://rss.cnn.com/rss/cnn_topstories.rss\r\n
	      http://rss.slashdot.org/Slashdot/slashdot\r\n
	      http://www.telegraph.co.uk/sport/rss\r\n
	      year_group_news',                                                                               'My News',              'module',   'rss',                NULL ),
	( 9, '&view=reports&report=incSummary&Itemid=328&format=raw&scope=pupil&pId=~ARC~',                   'Merits and Sanctions', 'internal', 'com_arc_attendance', 'components/com_arc_attendance/views/reports/tmpl/inc_panel_script.js' );

CREATE TABLE `#__apoth_home_links` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`text` varchar(255) NOT NULL,
	`panel` varchar(255) NOT NULL,
	`url` text NOT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB;

INSERT INTO `#__apoth_home_links`
	(`id`, `text`, `panel`, `url`)
VALUES
	( NULL, 'ePortfolio',                    'links', 'index.php?option=com_arc_homepage&view=homepage&scope=eportfolio&Itemid=327'),
	( NULL, 'Attendance',                    'links', 'index.php?option=com_arc_attendance&view=reports&report=pupilDetails&Itemid=209'),
	( NULL, 'Markbook',                      'links', 'index.php?option=com_arc_assessment&view=markbook&Itemid=218'),
	( NULL, 'eReg - Current Class',          'links', 'index.php?option=com_arc_attendance&view=ereg&scope=current&Itemid=197'),
	( NULL, 'Reports',                       'links', 'index.php?option=com_arc_report&view=lists&Itemid=227'),
	( NULL, 'Behaviour Reports',             'links', 'index.php?option=com_arc_behaviour&view=reports&Itemid=347'),
	( NULL, 'Email',                         'links', 'http://mail.wildern.hants.sch.uk'),
	( NULL, 'DiDa Portfolio',                'links', 'http://dida.wildern.org/~USERNAME~'),
	( NULL, 'VLE',                           'links', 'http://vle.wildern.hants.sch.uk'),
	( NULL, 'Wildern TV',                    'links', 'http://www.wildern.tv'),
	( NULL, 'Report a Fault / Request Help', 'links', 'index.php?option=com_wrapper&view=wrapper&Itemid=175'),
	( NULL, 'Staff Skills Audit (2011-12)',  'links', 'https://docs.google.com/a/wildern.hants.sch.uk/spreadsheet/viewform?formkey=dHBEWkFjdjcyTTRPUHJWdXI1UUVGcmc6MQ'),
	( NULL, 'Behaviour / Messages',          'links', 'index.php?option=com_arc_message&view=hub&scope=summary&tags=&Itemid=345'),
	( NULL, 'ICT equipment booking',         'links', 'https://spreadsheets.google.com/a/wildern.hants.sch.uk/viewform?hl=en&formkey=dGIxQlc0VU1CbmJXVjBwX1JreVpfQmc6MQ&AuthEventSource=SSO'),
	( NULL, 'Letters',                       'links', 'http://www.wildern.hants.sch.uk/index.php?option=com_content&view=article&id=127&Itemid=171'),
	( NULL, 'Examinations',                  'links', 'https://www.wildern.hants.sch.uk/index.php?option=com_content&view=article&id=59&Itemid=64'),
	( NULL, 'Absence Form',                  'links', 'http://www.wildern.hants.sch.uk/media/documents/Leave_of_Absence.pdf');

INSERT INTO `#__apoth_ppl_profile_categories` (`id`, `name`, `component`) VALUES
(NULL, 'panels', 'homepage');
SELECT @panelsid := LAST_INSERT_ID();

INSERT INTO `#__apoth_ppl_profile_categories` (`id`, `name`, `component`) VALUES
(NULL, 'links', 'homepage');
SELECT @linksid := LAST_INSERT_ID();

INSERT INTO `#__apoth_ppl_profile_templates` (`person_type`, `category_id`, `property`, `value`) VALUES
('pupil', @panelsid, '100', 'id=6
col=1
shown=1'),
('pupil', @panelsid, '105', 'id=9
col=1
shown=0'),
('pupil', @panelsid, '110', 'id=7
col=1
shown=0'),
('pupil', @panelsid, '200', 'id=4
col=2
shown=1'),
('pupil', @panelsid, '210', 'id=8
col=2
shown=1'),
('pupil', @panelsid, '305', 'id=16
col=3
shown=1'),
('pupil', @panelsid, '310', 'id=3
col=3
shown=1'),
('pupil', @panelsid, '315', 'id=10
col=3
shown=1'),
('pupil', @panelsid,  '95', 'id=11
col=1
shown=1'),
('pupil', @linksid,    '0', '1'),
('pupil', @linksid,    '1', '7'),
('pupil', @linksid,   '10', '8'),
('pupil', @linksid,    '2', '9'),
('pupil', @linksid,    '3', '10'),
('pupil', @linksid,    '4', '2'),
('pupil', @linksid,    '5', '3'),
('pupil', @linksid,    '6', '11'),

('teacher', @panelsid, '100', 'id=6
col=1
shown=1'),
('teacher', @panelsid, '200', 'id=4
col=2
shown=1'),
('teacher', @panelsid, '210', 'id=17
col=2
shown=1'),
('teacher', @panelsid, '313', 'id=19
col=3
shown=1'),
('teacher', @panelsid, '315', 'id=10
col=3
shown=1'),
('teacher', @panelsid, '95', 'id=11
col=1
shown=1'),
('teacher', @linksid,   '0', '1'),
('teacher', @linksid,   '1', '7'),
('teacher', @linksid,   '2', '9'),
('teacher', @linksid,   '3', '10'),
('teacher', @linksid,   '4', '4'),
('teacher', @linksid,   '5', '3'),
('teacher', @linksid,  '50', '12'),
('teacher', @linksid,   '6', '5'),
('teacher', @linksid,   '7', '11'),
('teacher', @linksid,  '40', '13'),
('teacher', @linksid,  '55', '14'),
('teacher', @linksid,  '42', '6'),

('parent', @panelsid, '100', 'id=18
col=1
shown=1'),
('parent', @panelsid, '110', 'id=6
col=1
shown=1'),
('parent', @panelsid, '315', 'id=17
col=2'),
('parent', @panelsid,  '95', 'id=11
col=3
shown=1'),
('parent', @linksid,    '4', '2'),
('parent', @linksid,    '5', '3'),
('parent', @linksid,    '6', '11'),
('parent', @linksid,   '10', '15'),
('parent', @linksid,   '11', '16'),
('parent', @linksid,   '12', '17'),

('staff', @panelsid, '100', 'id=6
col=1
shown=1'),
('staff', @panelsid, '210', 'id=17
col=2
shown=1'),
('staff', @panelsid, '313', 'id=19
col=3
shown=1'),
('staff', @panelsid, '315', 'id=10
col=3
shown=1'),
('staff', @panelsid, '95', 'id=11
col=1
shown=1'),
('staff', @linksid,   '0', '1'),
('staff', @linksid,   '1', '7'),
('staff', @linksid,   '2', '9'),
('staff', @linksid,   '3', '10'),
('staff', @linksid,  '50', '12'),
('staff', @linksid,   '7', '11'),
('staff', @linksid,  '55', '14'),
('staff', @linksid,  '42', '6');
