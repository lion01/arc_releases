<?php
/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Give us access to the joomla view class
jimport('joomla.application.component.view');

/**
 * Core Admin Ajax View
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Core
 * @since      1.6
 */
class CoreAdminViewAjax extends JView
{
	function renderTreeNode()
	{
		$treeNodeData = $this->get( 'treeNodeData' );
		$retVal = '<?xml version="1.0"?>'
			."\n".'<nodes>';
			foreach( $treeNodeData as $group ) {
				$classChk = $group->is_teacher; // *** titikaka
				$node = "\n".'<node id="%1$s" text="%2$s" %3$s />';
				
				if( $group->children > 0 ) {
					$retVal .= sprintf( $node, $group->id, htmlspecialchars($group->fullname.' ('.$group->children.')'), 'load="'.htmlspecialchars(JHTML::_('admin_groups.nodelink', $group->id)).'"' );
				}
				else {
					$retVal .= sprintf( $node, $group->id, htmlspecialchars($group->fullname), '' );
				}
			}
		$retVal .= "\n".'</nodes>';
		
		echo $retVal;
	}
}
?>