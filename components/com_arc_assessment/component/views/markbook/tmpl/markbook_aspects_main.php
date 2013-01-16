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
	<tr class="aspect_title">
		<?php
		foreach($this->ass as $aId) {
			$a = &$this->fAss->getInstance( $aId );
			$aspects = &$a->getAspects();
			if(count($aspects) == 0) {
				echo'<td style="background-color: lightgrey;">&nbsp;</td>';
			}
			else {
				foreach($aspects as $aspId=>$asp) {
					if( $asp->getIsShown() ) {
						echo "\n".'<td class="aspect" style="background-color: '.$a->getProperty( 'color' ).';">'.( $a->getEditsOn() ? $asp->getProperty( 'title' ) : $asp->getProperty( 'short' ) ).'</td>';
					}
					elseif( $a->getIsShown() ) {
						$link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_ass_show_column', array('assessment.aspects'=>$aspId) );
						echo "\n".'<td class="hidden" style="background-color: '.$a->getProperty( 'color' ).';">'
							.($link === false
								? '&nbsp;'
								: '<a href="'.$link.'" class="classTip" title="'.$asp->getProperty( 'title' ).'::">...</a>'
							 )
							 .'</td>';
					}
					else {
						echo "\n".'<td class="hidden" style="background-color: '.$a->getProperty( 'color' ).';">&nbsp;</td>';
					}
				}
			}
		}
		?>
	</tr>