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
		<td colspan="2" rowspan="2">
			<input type="submit" name="task" value="hide" />
			<input type="submit" name="task" value="hideOthers" />
			<input type="submit" name="task" value="showAll" />
			<br />
			<input type="submit" name="task" value="ascending" />
			<input type="submit" name="task" value="decending" />
			<?php echo ( ($l = ApotheosisLibAcl::getUserLinkAllowed('apoth_ass_admin_add')) ? '<a href="'.$l.'">Add</a>' : '&nbsp;' ); ?> 
		</td>
	</tr>
	