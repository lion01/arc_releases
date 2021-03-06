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
	<tr class="controls">
	<?php
		foreach($this->ass as $aId) {
			$a = &$this->fAss->getInstance( $aId );
			$aspects = &$a->getAspects();
			if(count($aspects) == 0) {
				echo "\n".'<td>&nbsp;</td>';
			}
			else {
				foreach($aspects as $aspect) {
					if( $aspect->getIsShown() ) {
						echo "\n".'<td><input name="aspects['.$aspect->getProperty( 'id' ).']" value="'.$aspect->getProperty( 'id' ).'" type="checkbox" /></td>';
					}
					else {
						$link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_ass_show_column', array('assessment.aspects'=>$aspect->getProperty('id') ) );
						echo "\n".'<td class="hidden">'
							.($link === false
								? '&nbsp;'
								: '<a href="'.$link.'" class="classTip" title="'.$aspect->getProperty( 'title' ).'">&nbsp;</a>'
							 )
							.'</td>';
					}
				}
			}
		}

	?>
	</tr>
