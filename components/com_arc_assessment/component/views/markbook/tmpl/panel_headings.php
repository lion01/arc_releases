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

	$oddCol = true;
	
	$assmntTitleRow = "\n".'<tr class="assmnt_title">';
	$assmntTitleRow .= "\n".'<td>&nbsp;</td>';
	
	$aspTitleRow = "\n".'<tr class="aspect_title">';
	$aspTitleRow .= "\n".'<th class="oddcol">Subject</th>';
	
	foreach( $this->aspectCols as $aId=>$cols ) {
		if( array_search($aId, $this->ass) === false ) {
			continue; // if we've not got an object, don't try and render it.
		}
		$a = &$this->fAss->getInstance( $aId );
		$colCount = 0;
		$aspTitles = '';
		$aspects = $a->getAspects();
		
		foreach( $cols as $colId=>$aspIds ) {
			$curAspId = false;
			foreach( $aspIds as $aspId ) {
				if( isset($aspects[$aspId]) ) {
					$curAspId = $aspId;
					break;
				}
			}
			if( $curAspId === false
			 || !$aspects[$curAspId]->getIsShown() ) {
				// Have no aspect to get a title from, or just aren't showing this column
				// so don't include it in the list of ones to show
				// *** adjust this logic to improve flebibility (eg allow full union of 2 overlapping assessments)
				unset( $this->aspectCols[$aId][$colId] );
				continue;
			}
			$colCount++;
			$aspTitles .= "\n".'<th id="'.$aspects[$curAspId]->getProperty( 'id' ).'" class="'.( $oddCol ? 'oddcol' : 'evencol' ).'">'.$aspects[$curAspId]->getProperty( 'short' ).'</th>';
		}
		
		$aspTitleRow .= ( ($colCount == 0) ? "\n".'<th>&nbsp;</th>' : $aspTitles );
		
		$assmntTitleRow .= "\n".'<th id="'.$aId.'" colspan="'.( ($colCount == 0) ? '1' : $colCount ).'" class="'.( ($oddCol = !$oddCol) ? 'oddcol' : 'evencol' ).'">';
		$assmntTitleRow .= $a->getProperty( 'short' );
		$assmntTitleRow .= '</th>';
	}
	$assmntTitleRow .= "\n".'</tr>';
	$aspTitleRow .= "\n".'</tr>';
	
	echo $assmntTitleRow;
	echo $aspTitleRow;
?>