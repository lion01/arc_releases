<?php
/**
 * @package     Arc
 * @subpackage  Planner
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$loader = 'components'.DS.'com_arc_planner'.DS.'clips'.DS.'loader.swf';
//$loader = 'components'.DS.'com_arc_planner'.DS.'clips'.DS.'flash_animations.swf'; // Just to check we can load any swl
$dataFile = JURI::base().$this->link
	.'&task=search'
	.'&taskId='.JRequest::getVar( 'taskId', false )
	.'&personId='.JRequest::getVar( 'personId', false )
	.'&descendants=1'
	.'&format=xml';
$addLink = JURI::base().$this->link
	.'&task=editUpdate'
	.'&taskId=~TASKID~'
	.'&groupId=~GROUPID~';
$removeLink = JURI::base().$this->link
	.'&task=removeUpdate'
	.'&taskId=~TASKID~'
	.'&groupId=~GROUPID~'
	.'&updateId=~UPDATEID~';

/*
var_dump_pre($dataFile, 'datafile: ');
var_dump_pre($addLink, 'addLink: ');
var_dump_pre($removeLink, 'removeLink: ');
// */

$clip = $loader
	.'?xml='.urlencode($dataFile)
	.'&add='.urlencode($addLink)
	.'&remove='.urlencode($removeLink);
?>
<h1>Update your progress for this task</h1>
<object
	type="application/x-shockwave-flash"
	width="600" height="700"
	data="<?php echo $clip; ?>"
	>
	<param name="movie" value="<?php echo $clip; ?>" />
	Loading Task modification app.
</object>
