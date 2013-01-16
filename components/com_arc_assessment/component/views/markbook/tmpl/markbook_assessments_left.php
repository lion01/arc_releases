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

if( !isset($this->_ass_tmpl_once) ) :
$this->_ass_tmpl_once = true; ?>
	<tr class="assmnt_title">
	</tr>
<?php else: ?>
	<tr class="assmnt_title">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
<?php endif; ?>