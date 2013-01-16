-- package     Arc
-- subpackage  Homepage
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

DELETE FROM `#__apoth_ppl_profile_categories`
WHERE `component` = 'homepage';

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE `#__apoth_home_panels`;
DROP TABLE `#__apoth_home_links`;

SET FOREIGN_KEY_CHECKS = 1;