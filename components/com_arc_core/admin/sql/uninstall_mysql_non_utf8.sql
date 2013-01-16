-- package     Arc
-- subpackage  Core
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `#__apoth_sys_tmp_deletables`;
DROP TABLE IF EXISTS `#__apoth_sys_tmp_tables`;
DROP TABLE IF EXISTS `#__apoth_sys_actions`;
DROP TABLE IF EXISTS `#__apoth_sys_acl`;
DROP TABLE IF EXISTS `#__apoth_sys_com_roles`;
DROP TABLE IF EXISTS `#__apoth_sys_roles`;
DROP TABLE IF EXISTS `#__apoth_sys_roles_ancestry`;
DROP TABLE IF EXISTS `#__apoth_sys_favourites`;
DROP TABLE IF EXISTS `#__apoth_sys_markstyles`;
DROP TABLE IF EXISTS `#__apoth_sys_markstyles_info`;
DROP TABLE IF EXISTS `#__apoth_sys_log`;
DROP TABLE IF EXISTS `#__apoth_sys_import_batches`;
DROP TABLE IF EXISTS `#__apoth_sys_import_queue`;

SET FOREIGN_KEY_CHECKS = 1;