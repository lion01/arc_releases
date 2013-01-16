-- ---------------- --
-- 1.8.max to 1.9.0 --
-- ---------------- --

-- #####  dev_566_rpt_reports  #####

SELECT @menu_id := id
FROM jos_menu
WHERE `link` LIKE '%index.php?option=com_arc_report%';

-- new action for nav element data
INSERT INTO jos_apoth_sys_actions
(`id`, `menu_id`, `option`, `task`, `params`, `name`, `menu_text`, `description`)
VALUES
(NULL, @menu_id, NULL, NULL, 'view=nav\r\nnavElement=~report.navelement~\r\nparams=~report.params~\r\nformat=~report.format~', 'apoth_report_nav_elements',  'Report nav elements', 'The snippets of navigation data either as raw html or json data'),
(NULL, @menu_id, NULL, NULL, 'view=nav\r\ntask=setFilters\r\nformat=~report.format~', 'apoth_report_nav_setfilters',  'Report nav set filters', 'Set the filter values to use for the subreport search'),
-- and break down the saving action into separates
(NULL, @menu_id, NULL, NULL, "view=subreport\r\nformat=raw\r\nsubreport=~report.subreport~\r\ntask=save\r\ncommit=~report.commit~\r\nstatus=3", 'apoth_report_ajax_submit', 'Report subreport - submit', 'The Reports subreport - submit a subreport'),
(NULL, @menu_id, NULL, NULL, "view=subreport\r\nformat=raw\r\nsubreport=~report.subreport~\r\ntask=save\r\ncommit=~report.commit~\r\nstatus=5", 'apoth_report_ajax_approve', 'Report subreport - approve', 'The Reports subreport - approve a subreport');

# -- keep the id of new action
SELECT @newId := LAST_INSERT_ID();

# -- setup the acl for new actions
SELECT @self := r1.id
FROM jos_apoth_sys_roles AS r1
INNER JOIN jos_apoth_sys_roles AS r2
   ON r2.id = r1.parent
WHERE r2.`role` = 'sys'
  AND r1.`role` = 'user';

INSERT INTO jos_apoth_sys_acl
VALUES
(@newId    , @self, NULL, 1 ),
(@newId+1  , @self, NULL, 1 ),
(@newId+2  , @self, NULL, 1 ),
(@newId+3  , @self, NULL, 1 );

-- adjust original save action in light of new actions
UPDATE jos_apoth_sys_actions
SET `params` = "view=subreport\r\nformat=raw\r\nsubreport=~report.subreport~\r\ntask=save\r\ncommit=~report.commit~\r\nstatus=2"
WHERE `name` = 'apoth_report_ajax_save';

-- fix foreign key check
ALTER TABLE `jos_apoth_rpt_field_config` DROP FOREIGN KEY `jos_apoth_rpt_field_config_ibfk_5` ;
ALTER TABLE `jos_apoth_rpt_field_config` ADD FOREIGN KEY ( `rpt_group_id` ) REFERENCES `jos_apoth_cm_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;


-- #####  dev_584_tv_recentsearches  #####

-- new action for ajaxing a sidebar div
INSERT INTO `jos_apoth_sys_actions`
(`id`, `menu_id`, `option`, `task`, `params`, `name`, `menu_text`, `description`)
VALUES
(NULL, 399, NULL, NULL, 'view=video\ntask=sidebar\nformat=raw', 'arc_tv_sidebar_ajax', 'Sidebar - via Ajax', 'Arc TV sidebar div via Ajax');

# -- keep the id of new action
SELECT @newId := LAST_INSERT_ID();

# -- setup the acl for new action
INSERT INTO `jos_apoth_sys_acl` (`action`, `role`, `sees`, `allowed`)
SELECT @newId, role, sees, allowed
FROM jos_apoth_sys_acl AS acl
INNER JOIN jos_apoth_sys_actions AS a
   ON a.id = acl.action
WHERE a.name = 'arc_tv_tag';

-- extra param for Ajax status div action
UPDATE `jos_apoth_sys_actions`
SET `params` = 'view=video\ntask=updateStatus\nvidId=~tv.videoId~\nidCheck=~js.idcheck.replace~\nformat=raw'
WHERE `jos_apoth_sys_actions`.`id` = 287 LIMIT 1 ;

-- #####  hot_634_tv_vidsbyidname  #####

# -- somehow the userid bit got added to metadata.xml but didn't go into an updatedb (till now)
UPDATE `jos_apoth_sys_actions`
SET `params` = 'view=video\ntask=idssearch\nuserid=~tv.userid~'
WHERE `jos_apoth_sys_actions`.`id` = 311
LIMIT 1 ;