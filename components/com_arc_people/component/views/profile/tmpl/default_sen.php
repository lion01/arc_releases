<?php
/**
 * @package     Arc
 * @subpackage  People
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<style>
.sen ul {
	margin: 0px;
	padding: 0px;
}
.sen ul li {
	margin-left: 15px;
	padding-left: 0px;
}
</style>
<div class="sen">

<p>SEN profile information includes notes on areas of concern and strengths</p>

<?php 
$list = &$this->people[ApotheosisLibAcl::getRoleId( 'pastoral_sen_mentor' )];
if( !empty($list) ) {
	echo 'My mentees:<br />'
		."\n".'<ul>';
	foreach( $list as $person ) {
		echo "\n".'<li><a class="modal" target="blank" rel="{handler: \'iframe\'}" href="'.ApotheosisLib::getActionLinkByName( 'eportfolio_edit_sen', array('people.arc_people'=>$person->getId()) ).'">'.$person->getDisplayName().'\'s profile</a></li>';
	}
	echo "\n".'</ul>';
}

$list = &$this->people[ApotheosisLibAcl::getRoleId( 'pastoral_sen_mentee' )];
if( !empty($list) ) {
	echo 'My mentors:<br />'
		."\n".'<ul>';
	foreach( $list as $person ) {
		echo "\n".'<li><a class="modal" target="blank" rel="{handler: \'iframe\'}" href="'.ApotheosisLib::getActionLinkByName( 'eportfolio_edit_sen', array('people.arc_people'=>$person->getId()) ).'">'.$person->getDisplayName().'\'s profile</a></li>';
	}
	echo "\n".'</ul>';
	}

$list = &$this->people[ApotheosisLibAcl::getRoleId( 'pastoral_sen_self' )];
if( !empty($list) ) {
	echo '<ul>';
	foreach( $list as $person ) {
		echo "\n".'<li><a class="modal" target="blank" rel="{handler: \'iframe\'}" href="'.ApotheosisLib::getActionLinkByName( 'eportfolio_edit_sen', array('people.arc_people'=>$person->getId()) ).'">My SEN profile</a></li>';
	}
	echo "\n".'</ul>';
	}

?>
</div>
