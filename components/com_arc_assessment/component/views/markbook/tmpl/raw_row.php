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

echo  '"'.addcslashes($this->person->surname,   '\"').'"';
echo ',"'.addcslashes($this->person->firstname, '\"').'"';
echo ',"'.addcslashes($this->person->gender,    '\"').'"';
echo ',"'.addcslashes($this->group->subjshort,  '\"').'"';
echo ',"'.addcslashes($this->group->subjfull,   '\"').'"';
echo ',"'.addcslashes($this->group->fullname,   '\"').'"';

foreach($this->ass as $aId) {
	$a = &$this->fAss->getInstance( $aId );
	$aspects = &$a->getAspects();
	foreach($aspects as $aspId=>$asp) {
		if( $asp->getIsShown() ) {
			$m = JHTML::_( 'arc_assessment.mark', $aspId, $this->person->id, $this->group->id, 'display');
			echo ',"'.addcslashes($m['raw'], '\"').'"';
		}
	}
}
?>