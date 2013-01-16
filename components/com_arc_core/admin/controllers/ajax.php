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

/**
 * Core Admin Ajax Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Timetable
 * @since      1.6
 */
class CoreAdminControllerAjax extends CoreAdminController
{
	function loadTreeNode()
	{
		$model = &$this->getModel( 'ajax' );
		$view  = &$this->getView ( 'ajax', 'xml' );
		$nodeId = JRequest::getVar( 'node', ApotheosisLibDb::getRootItem('#__apoth_cm_courses') );
		$model->setTreeNodeData( $nodeId );
		
		$view->setModel( $model, true );
		$view->renderTreeNode();
	}
}
?>