<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

ob_start();
?>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td><small>NB: Responses to incidents are also shown in the following bar graphs as in some cases they have a score associated with them.</small></td>
	</tr>
</table>
<?php
$mainGraph = ob_get_clean();
$this->pdf->writeHtml( $mainGraph );