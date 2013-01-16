-- package     Arc
-- subpackage  People
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

ALTER TABLE `#__apoth_sys_com_roles`
DROP FOREIGN KEY `ppl_sys_com_roles_people_id`;

DELETE FROM `#__apoth_sys_roles`
WHERE `id` IN (10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20);

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `#__apoth_ppl_addresses`;
DROP TABLE IF EXISTS `#__apoth_ppl_people`;
DROP TABLE IF EXISTS `#__apoth_ppl_address_history`;
DROP TABLE IF EXISTS `#__apoth_ppl_medical_conditions`;
DROP TABLE IF EXISTS `#__apoth_ppl_medical`;
DROP TABLE IF EXISTS `#__apoth_ppl_date_series`;
DROP TABLE IF EXISTS `#__apoth_ppl_contacts`;
DROP TABLE IF EXISTS `#__apoth_ppl_relation_tree`;
DROP TABLE IF EXISTS `#__apoth_ppl_relations`;
DROP TABLE IF EXISTS `#__apoth_ppl_profile_awards`;
DROP TABLE IF EXISTS `#__apoth_ppl_profile_categories`;
DROP TABLE IF EXISTS `#__apoth_ppl_profiles`;
DROP TABLE IF EXISTS `#__apoth_ppl_profile_templates`;

SET FOREIGN_KEY_CHECKS = 1;