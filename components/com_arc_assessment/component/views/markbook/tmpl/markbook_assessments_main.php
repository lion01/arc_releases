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
	<tr class="assmnt_title">
		<?php
		foreach($this->ass as $aId) {
			$a = &$this->fAss->getInstance( $aId );
			$aspects = &$a->getAspects();
			$colCount = count($aspects);
			
			if( $a->getIsShown() ) {
				$editLink = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_ass_main_edit', array('assessment.assessments'=>$aId) );
				$adminLink = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_ass_admin_existing', array('assessment.assessments'=>$aId) );
				
				echo "\n".'<td colspan="'.($colCount == 0 ? '1' : $colCount).'" style="background-color: '.$a->getProperty( 'color' ).';">'
					.'<span class="classTip title" title="'.$a->getProperty( 'title' ).'::'.$a->getProperty( 'description' ).'">';
				
				if( $editLink !== false ) {
					if( $a->getEditsOn() ) {
						echo '<a href="'.$editLink.'">'.$a->getProperty( 'title' ).'</a></span>';
						echo '<input type="hidden" name="aId" value="'.$aId.'" />';
						echo '<input type="submit" name="task" value="Save" />';
					}
					else {
						echo '<a href="'.$editLink.'">'.$a->getProperty( 'short' ).'</a></span>';
					}
				}
				else {
					echo $a->getProperty( 'short' ).'</span>';
				}
				
				if( $adminLink !== false ) {
					echo JHTML::_( 'arc.adminLink', $adminLink, 'Administrate' );
				}
				echo '&nbsp;</td>';
			}
			else {
				$link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_ass_show_column', array('assessment.aspects'=>reset(array_keys($aspects))) );
				echo "\n".'<td colspan="'.($colCount == 0 ? '1' : $colCount).'" style="background-color: '.$a->getProperty( 'color' ).';" class="hidden">'
					.($link === false
						? '&nbsp;'
						: '<a href="'.$link.'" class="classTip" title="Show::'.$a->getProperty( 'title' ).'">...</a>'
					 )
					.'&nbsp;</td>';
			}
		}
		?>
	</tr>
