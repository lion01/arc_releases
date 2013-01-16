<?php
/**
 * @package     Arc
 * @subpackage  Mista
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */
$config['timezone'] = 'Europe/London';

// ArcHub is Punnet's central list of Arc apps and customers.
// It provides an api to retrieve the customer api urls amongst other things
$config['ArcHub'] = 'https://archub.pun.net';
$config['app_id'] = 2;

// ArcHub-registered app consumer key / secret and callback
$config['cons_key'] = '4580b8ec0a331';
$config['cons_secret'] = 'a5b8ec85c4580b8ec0a331ddf63e90b3af5cbb01';
$config['url_callback'] = 'oob'; // setup is not web-based, so use out-of-band verification

// Installation-specific variables
$config['instance'] = '';
$config['proxy_host'] = '';
$config['proxy_port'] = '';
$config['ssl_verify'] = false;
$config['url_request'] = 'http://example.com/index.php?option=com_arc_api&view=oauth&format=raw&task=request_token';
$config['url_auth'] = 'http://example.com/index.php?option=com_arc_api&view=oauth&task=authorise';
$config['url_access'] = 'http://example.com/index.php?option=com_arc_api&view=oauth&format=raw&task=access_token';
$config['url_read'] = 'http://example.com/index.php?option=com_arc_api&view=data&task=read&call=~api.call~&format=~api.format~';
$config['url_write'] = 'http://example.com/index.php?option=com_arc_api&view=data&task=write&call=~api.call~&format=~api.format~';
$config['oauth_access_token'] = '';
$config['oauth_access_secret'] = '';

// Report generation parameters
$config['rpt_user'] = '';
$config['rpt_pwd'] = '';
$config['rpt_clr'] = 'C:\Program Files\SIMS\SIMS .net\CommandReporter.exe';
$config['rpt_outdir'] = 'C:\arc_exports';
// how long should we give the report to run? this may seem like a very long time, but SIMS can be slow
$config['rpt_tMax'] = 2400;
$config['rpt_sizeMax'] = 50000;
$config['rpt_sizeAccuracy'] = 200;

// This list contains all the reports that we know exist and are safe to run.
$config['rpt_trusted'] = array( 'arc_course_curriculum', 'arc_course_pastoral', 'arc_people_contacts', 'arc_people_pupils', 'arc_people_relationships', 'arc_people_staff', 'arc_people_staff_future', 'arc_student_attendance', 'arc_timetable_classes', 'arc_timetable_instances', 'arc_timetable_patterns', 'arc_timetable_members', 'test');
?>