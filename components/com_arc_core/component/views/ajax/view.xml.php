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

jimport('joomla.application.component.view');

/**
 * Extension Manager Install View
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class ApotheosisViewAjax extends JView
{
	function display( $tpl = NULL )
	{
		echo 'You shouldn\'t here - something moy be wrang ';
	}
	
	function renderTreeNode( $actionId )
	{
		$treeNodeData = $this->get( 'treeNodeData' );
		
		$retVal = '<?xml version="1.0"?>'
			."\n".'<nodes>';
		foreach( $treeNodeData as $group ) {
			$classChk = $group->is_teacher; // *** titikaka
			$node = "\n".'<node id="%1$s" text="'.(($classChk) ? '* ' : '').'%2$s"'.(($classChk) ? ' color="fuchsia"' : '').'%3$s />';
			
			if( $group->children > 0 ) {
				$retVal .= sprintf($node, $group->id, htmlspecialchars($group->fullname.' ('.$group->children.')'), ' load="'.htmlspecialchars(JHTML::_('groups.nodelink', $group->id, $actionId)).'"');
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