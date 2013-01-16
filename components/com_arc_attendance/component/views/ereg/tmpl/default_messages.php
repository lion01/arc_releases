<?php
/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
	foreach( $this->incidents as $id=>$v ) {
		$miniInfo = ApotheosisData::_( 'message.miniInfo', $id );
		$incsHtml[] = '<div class="mini_dot_div">'.$miniInfo['html'].'</div>';
		$incsTooltip[] = '<div>'.htmlspecialchars($miniInfo['tooltip']).'</div>';
	}
	$incsHtml = implode( "\n", $incsHtml );
	$incsTooltip = implode( "\n", $incsTooltip );
	
	echo '<div class="arcTip_inc_tip" title="Incident(s) :: '.$incsTooltip.'">'.$incsHtml.'</div>';
?>