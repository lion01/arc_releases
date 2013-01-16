-- package     Arc
-- subpackage  Message
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

DELETE FROM `#__apoth_sys_roles`
WHERE `id` IN (50, 51, 52, 53);

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `#__apoth_msg_messages`;
DROP TABLE IF EXISTS `#__apoth_msg_data`;
DROP TABLE IF EXISTS `#__apoth_msg_tags`;
DROP TABLE IF EXISTS `#__apoth_msg_tag_map`;
DROP TABLE IF EXISTS `#__apoth_msg_threads`;
DROP TABLE IF EXISTS `#__apoth_msg_rules`;
DROP TABLE IF EXISTS `#__apoth_msg_rule_param_sets`;
DROP TABLE IF EXISTS `#__apoth_msg_channels`;
DROP TABLE IF EXISTS `#__apoth_msg_channel_rules`;
DROP TABLE IF EXISTS `#__apoth_msg_channel_subscribers`;

SET FOREIGN_KEY_CHECKS = 1;