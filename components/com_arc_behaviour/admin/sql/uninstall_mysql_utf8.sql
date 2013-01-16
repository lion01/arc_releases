-- package     Arc
-- subpackage  Behaviour
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

DELETE FROM `#__apoth_msg_tags`
WHERE id IN ( 20, 21, 22, 23, 24, 25, 31, 32, 33, 34, 35, 36, 37, 38, 39 );

DELETE FROM `#__apoth_ppl_profile_categories`
WHERE `component` = 'behaviour';

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `#__apoth_bhv_inc_types`;
DROP TABLE IF EXISTS `#__apoth_bhv_actions`;
DROP TABLE IF EXISTS `#__apoth_bhv_inc_actions`;
DROP TABLE IF EXISTS `#__apoth_bhv_scores`;


SET FOREIGN_KEY_CHECKS = 1;