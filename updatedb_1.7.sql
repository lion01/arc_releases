-- ---------------- --
-- 1.6.max to 1.7.0 --
-- ---------------- --

-- #####  dev_203_cor_pagination  #####

# -- Add "specific page" action for hub
INSERT INTO `jos_apoth_sys_actions`
SELECT 
  NULL AS `id`,
  `menu_id`,
  `option`,
  `task`,
  CONCAT( `params`, "\npage=~core.page~") AS `params`,
  "apoth_msg_hub_paged" AS `name`,
  `menu_text`,
  CONCAT( `description`, " - specific page" )
FROM `jos_apoth_sys_actions`
WHERE `name` = "apoth_msg_hub";

# -- keep the id of new action
SELECT @newId := LAST_INSERT_ID();

# -- setup the acl for new action
INSERT INTO `jos_apoth_sys_acl` (`action`, `role`, `sees`, `allowed`)
SELECT @newId, role, sees, allowed
FROM jos_apoth_sys_acl AS acl
INNER JOIN jos_apoth_sys_actions AS a
   ON a.id = acl.action
WHERE a.name = 'apoth_msg_hub';


# -- Add "specific page" action for hub via ajax
INSERT INTO `jos_apoth_sys_actions`
SELECT 
  NULL AS `id`,
  `menu_id`,
  `option`,
  `task`,
  CONCAT( `params`, "\npage=~core.page~") AS `params`,
  "apoth_msg_hub_ajax_paged" AS `name`,
  `menu_text`,
  CONCAT( `description`, " - specific page" )
FROM `jos_apoth_sys_actions`
WHERE `name` = "apoth_msg_hub_ajax";

# -- keep the id of new action
SELECT @newId := LAST_INSERT_ID();

# -- setup the acl for new action
INSERT INTO `jos_apoth_sys_acl` (`action`, `role`, `sees`, `allowed`)
SELECT @newId, role, sees, allowed
FROM jos_apoth_sys_acl AS acl
INNER JOIN jos_apoth_sys_actions AS a
   ON a.id = acl.action
WHERE a.name = 'apoth_msg_hub_ajax';


-- #####  dev_546_tv_genesis  #####

UPDATE `jos_components`
SET `params` = CONCAT( `params` , "\r\nsite_id=1" )
WHERE `link` = "option=com_arc_core"
LIMIT 1;

-- ---------------- --
-- 1.7.0-rc.2 to 1.7.0-rc.3 --
-- ---------------- --

-- #####  dev_583_tv_panel  #####

# -- Homepage panel definition
INSERT INTO `jos_apoth_home_panels`
(`id`, `url`, `alt`, `type`, `option`, `customisable`, `persistent`, `jscript`, `css`)
VALUES
(NULL, '&view=video&task=panel&format=raw', 'Video of the Week', 'internal', 'com_arc_tv', 1, 0, NULL, 'templates/full_screen_2/css/arc/tv.css');

# -- keep the id of new action
SELECT @newId := LAST_INSERT_ID();

# -- Allocate new panel to all teachers and staff
INSERT IGNORE INTO `jos_apoth_ppl_profiles`
SELECT `person_id`, '3', '230', CONCAT( 'id=', @newId, '\r\ncol=2\r\nshown=1' )
FROM `jos_apoth_ppl_profiles`
WHERE `property` = 'FACEBOOK'
  AND (`value` = '*' OR `value` = '?');

# -- Allocate new panel to all pupils
INSERT IGNORE INTO `jos_apoth_ppl_profiles`
SELECT DISTINCT `prof`.`person_id`, '3', '230', CONCAT( 'id=', @newId, '\r\ncol=2\r\nshown=1' )
FROM `jos_apoth_ppl_profiles` AS `prof`
INNER JOIN `jos_apoth_tt_group_members` AS `tt`
   ON `tt`.`person_id` = `prof`.`person_id`
WHERE `prof`.`property` = 'FACEBOOK'
  AND (`prof`.`value` != '*' OR `prof`.`value` != '?');

# -- Quick Links
UPDATE `jos_apoth_ppl_profiles`
SET `property` = '210'
WHERE `category_id` = '3'
  AND `value` LIKE '%col=2%'
  AND `value` LIKE '%id=22%';

# -- Timetable
UPDATE `jos_apoth_ppl_profiles`
SET `property` = '220'
WHERE `category_id` = '3'
  AND `value` LIKE '%col=2%'
  AND `value` LIKE '%id=4%';

# -- My News
UPDATE `jos_apoth_ppl_profiles`
SET `property` = '240'
WHERE `category_id` = '3'
  AND `value` LIKE '%col=2%'
  AND `value` LIKE '%id=17%';

# -- My Yeargroup News
UPDATE `jos_apoth_ppl_profiles`
SET `property` = '250'
WHERE `category_id` = '3'
  AND `value` LIKE '%col=2%'
  AND `value` LIKE '%id=8%';

# -- SIG Blog
UPDATE `jos_apoth_ppl_profiles`
SET `property` = '260'
WHERE `category_id` = '3'
  AND `value` LIKE '%col=2%'
  AND `value` LIKE '%id=23%';

# -- Customise my page ( column 3 )
UPDATE `jos_apoth_ppl_profiles`
SET `property` = '399'
WHERE `category_id` = '3'
  AND `value` LIKE '%col=3%'
  AND `value` LIKE '%id=10%';

# -- Change name of year group news panel
UPDATE `jos_apoth_home_panels`
SET `alt` = 'My Yeargroup News'
WHERE `jos_apoth_home_panels`.`id` = 8 LIMIT 1;

# -- Add video of the week action for homepage panel
INSERT INTO `jos_apoth_sys_actions`
SELECT 
  NULL AS `id`,
  `menu_id`,
  `option`,
  `task`,
  "view=video\ntask=panel\nformat=raw" AS `params`,
  "arc_tv_homepage_panel" AS `name`,
  "TV Homepage Panel" AS `menu_text`,
  "Show video of the week in a homepage panel" AS `description`
FROM `jos_apoth_sys_actions`
WHERE `name` = "arc_tv";

# -- keep the id of new action
SELECT @newId := LAST_INSERT_ID();

# -- setup the acl for new action
INSERT INTO `jos_apoth_sys_acl` (`action`, `role`, `sees`, `allowed`)
SELECT @newId, role, sees, allowed
FROM jos_apoth_sys_acl AS acl
INNER JOIN jos_apoth_sys_actions AS a
   ON a.id = acl.action
WHERE a.name = 'arc_tv_video';


-- ------------------- --
-- 1.7.0-rc.4 to 1.7.1 --
-- ------------------- --

-- #####  dev_590_tv_ajaxbuttons  #####

# -- Accept action
INSERT INTO `jos_apoth_sys_actions`
(`id`, `menu_id`, `option`, `task`, `params`, `name`, `menu_text`, `description`)
VALUES
(NULL, 399, NULL, NULL, 'view=video\ntask=accept\nvidId=~tv.videoId~\nformat=raw', 'arc_tv_manage_accept', 'Accept a video', 'Video management - accept a video');

# -- keep the id of new action
SELECT @newId := LAST_INSERT_ID();

# -- setup the acl for new action
INSERT INTO `jos_apoth_sys_acl` (`action`, `role`, `sees`, `allowed`)
SELECT @newId, role, sees, allowed
FROM jos_apoth_sys_acl AS acl
INNER JOIN jos_apoth_sys_actions AS a
   ON a.id = acl.action
WHERE a.name = 'arc_tv_moderate';

# -- Reject action
INSERT INTO `jos_apoth_sys_actions`
(`id`, `menu_id`, `option`, `task`, `params`, `name`, `menu_text`, `description`)
VALUES
(NULL, 399, NULL, NULL, 'view=video\ntask=reject\nvidId=~tv.videoId~\nformat=raw', 'arc_tv_manage_reject', 'Reject a video', 'Video management - reject a video');

# -- keep the id of new action
SELECT @newId := LAST_INSERT_ID();

# -- setup the acl for new action
INSERT INTO `jos_apoth_sys_acl` (`action`, `role`, `sees`, `allowed`)
SELECT @newId, role, sees, allowed
FROM jos_apoth_sys_acl AS acl
INNER JOIN jos_apoth_sys_actions AS a
   ON a.id = acl.action
WHERE a.name = 'arc_tv_moderate';

-- #####  dev_591_tv_ajaxstatuspane  #####

# -- Update status action
INSERT INTO `jos_apoth_sys_actions`
(`id`, `menu_id`, `option`, `task`, `params`, `name`, `menu_text`, `description`)
VALUES
(NULL, 399, NULL, NULL, 'view=video\ntask=updateStatus\nvidId=~tv.videoId~\nformat=raw', 'arc_tv_manage_status', 'Video status panel', 'Video management - view status panel');

# -- keep the id of new action
SELECT @newId := LAST_INSERT_ID();

# -- setup the acl for new action
INSERT INTO `jos_apoth_sys_acl` (`action`, `role`, `sees`, `allowed`)
SELECT @newId, role, sees, allowed
FROM jos_apoth_sys_acl AS acl
INNER JOIN jos_apoth_sys_actions AS a
   ON a.id = acl.action
WHERE a.name = 'arc_tv_manage';

-- #####  dev_602_tv_player  #####
UPDATE `jos_apoth_home_panels`
SET jscript='components/com_arc_tv/views/video/tmpl/default_video_player.js'
WHERE `alt` = "Video of the Week";