-- package     Arc
-- subpackage  API
-- copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
-- license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt

CREATE TABLE `#__apoth_api_consumers` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`enabled` tinyint(1) unsigned NOT NULL default '0',
	`name` varchar(50) NOT NULL,
	`description` text,
	`cons_key` varchar(50) NOT NULL,
	`cons_secret` varchar(200) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_api_access_tokens` (
	`person_id` varchar(20) NOT NULL,
	`cons_id` int(10) unsigned NOT NULL,
	`token` char(20) default NULL,
	`secret` char(40) default NULL,
	`valid_from` datetime NOT NULL,
	`valid_to` datetime default NULL,
	PRIMARY KEY (`person_id`,`cons_id`,`valid_from`),
	UNIQUE (`token`),
	INDEX (`cons_id`),
	FOREIGN KEY (`person_id`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`cons_id`) REFERENCES `#__apoth_api_consumers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_api_request_tokens` (
	`cons_id` int(10) unsigned NOT NULL,
	`token` char(8) NOT NULL,
	`secret` char(24) NOT NULL,
	`callback` text,
	`verification` char(7) default NULL,
	`person_id` varchar(20) default NULL,
	`created` datetime NOT NULL,
	PRIMARY KEY (`cons_id`,`token`),
	UNIQUE (`token`),
	INDEX (`person_id`),
	FOREIGN KEY (`person_id`) REFERENCES `#__apoth_ppl_people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`cons_id`) REFERENCES `#__apoth_api_consumers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `#__apoth_api_nonce` (
	`nonce` varchar(50) NOT NULL,
	PRIMARY KEY (`nonce`)
) ENGINE=MyISAM;