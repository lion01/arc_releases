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

	$new = false;
	$gId = $this->row['group'];
	$pId = $this->row['person'];
	
	echo "\n".'<tr class="'.( $this->edits ? 'pupil_entry' : 'pupil' ).(($this->oddrow) ? ' oddrow' : '').'">';
	foreach($this->ass as $aId) {
		$a = &$this->fAss->getInstance( $aId );
		$aspects = &$a->getAspects();
		if(count($aspects) == 0) {
			echo'<td style="background-color: lightgrey;">&nbsp;</td>';
		}
		else {
			foreach($aspects as $aspId=>$asp) {
				if( $asp->getIsShown() ) {
					if( $a->getEditsOn() ) {
						$usage = 'edit';
					}
					else {
						$usage = 'display'; // *** switch this to "mark" to show the entered mark
					}
					
					// *** if you're here looking to optimise for speed, this line calls the slow bit.
					// it's not that it's especially slow per-se, but it gets called a _lot_
					$m = JHTML::_( 'arc_assessment.mark', $aspId, $pId, $gId, $usage, $this->rowCount.'_'.$this->colCount );
					
					if( $m['hasMark'] ) {
						if( is_null($m['color']) ) {
							$s = '';
						}
						else {
							$s = 'style="background-color: '.htmlspecialchars($m['color']).';"';
						}
						echo "\n".'<td class="cell_on'.( (is_null($m['group']) || $m['group'] == $gId) ? '' : ' old' ).'"'.$s.'>';
					}
					else {
						echo "\n".'<td class="cell_off">';
					}
					
					echo $m['html'];
					
					echo '</td>';
				}
				else {
					echo "\n".'<td class="hidden" style="background-color: '.$a->getProperty( 'color' ).';">'
						.'<a href="'.ApotheosisLibAcl::getUserLinkAllowed( 'apoth_ass_show_column', array('assessment.aspects'=>$aspId) ).'" class="classTip" title="'.$asp->getProperty( 'title' ).'::">&nbsp;</a></td>';
				}
			}
		}
	}
	$this->oddrow = !$this->oddrow;
?>