<?php
/**
 * @package     Arc
 * @subpackage  Message
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<table>
<?php
if( $this->empty ) {
	echo '<tr><td style="text-align: center; padding-top: 1em;">'.$this->emptyMessage.'</td></tr>';
}
else {
	while( $this->thread = $this->get('Thread') ) {
		echo JHTML::_( 'arc_message.render', 'renderThreadListRow', 'thread', $this->thread->getId() );
	}
}
?>
</table>