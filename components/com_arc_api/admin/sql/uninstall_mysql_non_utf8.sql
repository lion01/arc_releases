-- package     Arc
-- subpackage  API
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `#__apoth_api_nonce`;
DROP TABLE IF EXISTS `#__apoth_api_request_tokens`;
DROP TABLE IF EXISTS `#__apoth_api_access_tokens`;
DROP TABLE IF EXISTS `#__apoth_api_consumers`;

SET FOREIGN_KEY_CHECKS = 1;