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

jimport( 'joomla.application.component.model' );
 /*
 * Extension Manager Summary Model
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class ApotheosisModelAjax extends JModel
{
	/** @var array  holds array of node data */
	var $_treeNodeData;
	
	function setTreeNodeData( $id, $actionId )
	{
		$this->_treeNodeData = JHTML::_( 'groups.grouplist', $id, $actionId );
	}
	
	function getTreeNodeData()
	{
		return $this->_treeNodeData;
	}
}
?>