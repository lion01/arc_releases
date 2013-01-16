-- package     Arc
-- subpackage  Planner
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

DELETE FROM `#__apoth_ppl_profile_categories`
WHERE `component` = 'planner';

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE `#__apoth_plan_tasks`;
DROP TABLE `#__apoth_plan_tasks_ancestry`;
DROP TABLE `#__apoth_plan_tasks_requirements`;
DROP TABLE `#__apoth_plan_tasks_relations`;
DROP TABLE `#__apoth_plan_groups`;
DROP TABLE `#__apoth_plan_group_members`;
DROP TABLE `#__apoth_plan_updates`;
DROP TABLE `#__apoth_plan_update_evidence`;
DROP TABLE `#__apoth_plan_update_microtasks`;
DROP TABLE `#__apoth_plan_category_cols`;

SET FOREIGN_KEY_CHECKS = 1;