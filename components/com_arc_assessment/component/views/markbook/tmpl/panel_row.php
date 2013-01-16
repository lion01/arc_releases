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
<tr>
<td class ="oddcol"><?php echo ApotheosisData::_( 'course.name', ApotheosisData::_( 'course.subject', $this->group ) ); ?></td>
<?php
	$oddCol = false;
	
	foreach( $this->aspectCols as $aId=>$cols ) {
		if( array_search($aId, $this->ass) === false ) {
			continue; // if we've not got an object, don't try and render it.
		}
		$a = &$this->fAss->getInstance( $aId );
		$aspects = $a->getAspects();
		
		foreach( $cols as $colId=>$aspIds ) {
			echo '<td class="'.( $oddCol ? 'oddcol' : 'evencol' ).'">';
			$m = JHTML::_( 'arc_assessment.markCoalesce', $aspIds, $this->person, $this->group );
			echo $m['html'];
			echo '</td>';
		}
		$oddCol = !$oddCol;
	}
?>
</tr>