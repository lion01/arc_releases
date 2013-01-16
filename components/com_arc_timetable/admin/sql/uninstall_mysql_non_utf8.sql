-- package     Arc
-- subpackage  Timetable
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `#__apoth_tt_patterns`;
DROP TABLE IF EXISTS `#__apoth_tt_pattern_instances`;
DROP TABLE IF EXISTS `#__apoth_tt_daydetails`;
DROP TABLE IF EXISTS `#__apoth_tt_timetable`;
DROP TABLE IF EXISTS `#__apoth_tt_group_members`;

SET FOREIGN_KEY_CHECKS = 1;

DELETE FROM `#__apoth_sys_roles`
WHERE `id` IN (30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 59, 60, 61, 62, 63);