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
?>
Arc id,UPN,Forename,Surname,Gender,Subject Code,Subject,Class<?php
foreach($this->ass as $aId) {
	$a = &$this->fAss->getInstance( $aId );
	$aspects = &$a->getAspects();
	foreach($aspects as $aspId=>$asp) {
		if( $asp->getIsShown() ) {
			echo ',"'.addcslashes( $a->getProperty( 'title' ).' - '.$asp->getProperty( 'short' ), '\"' ).'"';
		}
	}
}
?>