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
?>
<div id="task_panel">
<?php
$actionId = ApotheosisLib::getActionIdByName( 'apoth_eportfolio_questions' );
$pId = JRequest::getVar( 'pId', null );
$JUserId = ApotheosisLib::getJUserId( $pId );

foreach( $this->tasks as $id ) {
	$this->task = &$this->model->getTask( $id );
	$g = &$this->task->getGroups();
	
	$links = array();
	foreach( $g as $group ) {
		$a = reset( $group->getPeopleInRole('assignee') );
		if( empty($a) ) {
			$a = reset( $group->getPeopleInRole('leader') );
		}
		$r = $group->roles( $pId );
		if( ($r['assignee'] == true) || $r['leader'] == true ) {
			$links['me'][$group->getId()] = $a;
		}
		else {
			$links['staff'][$group->getId()] = $a;
		}
	}
	
	if( !empty($links) ) {
		echo '<div class="task">'."\n";
		$link = '<a class="panel_modal" target="blank" rel="{handler: \'iframe\', size: {x: 650, y: 600}}" href="%1$s">%2$s</a>';

		if( isset($links['me']) ) {
			$me = reset($links['me']);
			printf( $link, $this->task->getUrl( $actionId, $JUserId, $me->id ), $this->task->getTitle() );
		}
		else {
			echo $this->task->getTitle().'<br />';
		}
		
		if( is_array($links['staff']) ) {
			echo '<ul>'."\n";
			foreach( $links['staff'] as $gId=>$person ) {
				echo '<li>';
				printf( $link, $this->task->getUrl( $actionId, $JUserId, $person->id ), $person->displayname );
				echo '</li>'."\n";
			}
			echo '</ul>';
		}
		echo '</div>';
	}
	continue;
}
?>
</div>