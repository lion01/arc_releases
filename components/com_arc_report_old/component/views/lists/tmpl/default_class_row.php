<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$actionLinkName = ($this->row->childcount > 0 ? 'apoth_report_list_classes' : 'apoth_report_list_pupils' );
 if( ($link = ApotheosisLibAcl::getUserLinkAllowed($actionLinkName, array('report.groups'=>$this->get('CycleId').'_'.$this->row->id))) !== false ):
?>
<tr>
	<td><?php echo ( ($adminLink = ApotheosisLibAcl::getUserLinkAllowed('apoth_rpt_admin_course', array('report.groups'=>$this->get('CycleId').'_'.$this->row->id))) !== false )
		? '<a href="'.$adminLink.'"><img src=".'.DS.'components'.DS.'com_arc_report'.DS.'images'.DS.'spanner.gif" alt="administrate" title="Administrate" /></a>'
		: '&nbsp'; ?></td>
	<td><a href="<?php echo $link; ?>"><?php echo $this->row->fullname; ?></a></td>
	<td><?php echo ( is_array($this->row->teachers) ? htmlspecialchars(implode(', ', $this->row->teachers)) : JText::_('No teacher set') ); ?></td>
</tr>
<?php endif; ?>