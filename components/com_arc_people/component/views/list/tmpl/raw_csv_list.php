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

echo JText::_( 'Arc ID' ).',';
echo JText::_( 'First Name' ).',';
echo JText::_( 'Last Name' ).',';
echo JText::_( 'Date of Birth' ).',';
echo JText::_( 'Sex' );
if( $this->rels ) {
	echo ',';
	echo JText::_( 'Relationship' ).',';
	echo JText::_( 'Full Contact Rights' );
}
echo "\n";
foreach( $this->people as $pObj ) {
	echo $pObj->getDatum( 'id' ).',';
	echo $pObj->getDatum( 'firstname' ).',';
	echo $pObj->getDatum( 'surname' ).',';
	echo $pObj->getDatum( 'dob' ).',';
	echo $pObj->getDatum( 'gender' );
	if( $this->rels ) {
		if( is_null($pObj->isFullContact()) ) {
			$fullContact = '?';
		}
		elseif( !$pObj->isFullContact() ) {
			$fullContact = 'No';
		}
		else {
			$fullContact = 'Yes';
		}
		echo ',';
		echo $pObj->getDatum( 'description' ).',';
		echo $fullContact;
	}
	echo "\n";
}
?>