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

jimport('joomla.application.component.controller');
jimport('joomla.application.helper');

/**
 * Content Component Controller
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ApotheosisControllerAjax extends ApotheosisController
{
	function __construct( $config = array() )
	{
		parent::__construct( $config );
	}
	
	function display()
	{
		$model = &$this->getModel( 'ajax' );
		$view  = &$this->getView ( 'ajax' );
		
		$view->setModel( $model, true );
		$view->display();
	}
	
	function loadTreeNode()
	{
		$model = &$this->getModel( 'ajax' );
		$view  = &$this->getView ( 'ajax', 'xml' );
		
		$nodeId = JRequest::getVar( 'node', ApotheosisLibDb::getRootItem('#__apoth_cm_courses') );
		$actionId = JRequest::getVar( 'action', null );
		if( is_null($actionId) ) {
			// when finding restricted list we need to know what action we're doing this for
			// (remember this gets loaded by AJAX from anywhere in the system)
			$referer = JRequest::getString( 'HTTP_REFERER', '', 'server' );
			$actionId = ApotheosisLib::getActionId( $referer );
		}
		ApotheosisLib::setTmpAction( $actionId );
		
		$model->setTreeNodeData( $nodeId, $actionId );
		
		$view->setModel( $model, true );
		$view->renderTreeNode( $actionId );
	}
}
?>