<?php
/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

echo $this->loadTemplate('headings')."\n";

foreach( $this->rows as $row ) {
	if( !isset($this->people[$row['person']]) ) {
		$this->people[$row['person']] = ApotheosisData::_( 'people.person', $row['person'] );
	}
	$this->person = $this->people[$row['person']];
	
	if( !isset($this->groups[$row['group']]) ) {
		$this->groups[$row['group']] = new stdClass();
		$this->groups[$row['group']]->id = $row['group'];
		$this->groups[$row['group']]->fullname  = ApotheosisData::_( 'course.name',  $row['group']);
		$this->groups[$row['group']]->subjfull  = ApotheosisData::_( 'course.name',  ApotheosisData::_( 'course.subject', $row['group']) );
		$this->groups[$row['group']]->subjshort = ApotheosisData::_( 'course.short', ApotheosisData::_( 'course.subject', $row['group']) );
	}
	$this->group = $this->groups[$row['group']];
	
	echo $this->loadTemplate('row')."\n";
}
?>