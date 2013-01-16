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

/**
 * Behaviour Admin Actions Controller
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Behaviour
 * @since 0.1
 */
class BehaviourAdminControllerActions extends BehaviourAdminController
{
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->registerTask(   'publish', 'toggleHasText' );
		$this->registerTask( 'unpublish', 'toggleHasText' );
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		global $mainframe, $option;
		jimport('joomla.html.pagination');
		$model = &$this->getModel( 'actions' );
		$view = &$this->getView( 'actions', 'html' );
		$view->setModel( $model, true );
		
		$limitStart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$model->setPagination( $limitStart, $limit );
		$model->setPagedActions();
		
		switch( JRequest::getVar('task') ) {
		// Create an action
		case( 'add' ):
			$model->setAction( -1 );
			$view->edit();
			break;
			
		// Edit an action
		case( 'edit' ):
			$checked = reset( array_keys( JRequest::getVar( 'eid') ) );
			$acts = $model->getPagedActions();
			$act = $acts[$checked];
			$model->setAction( $act->getId() );
			$view->edit();
			break;
			
		// Remove action(s)
		case( 'remove' ):
			$actIds = array();
			$checked = JRequest::getVar( 'eid' );
			$acts = $model->getPagedActions();
			if( is_array($checked) ) {
				foreach( $checked as $index=>$on ) {
					$actIds[] = $acts[$index]->getId();
				}
			}
			
			if( is_array($actIds) && !empty($actIds) ) {
				$r = $model->delete( $actIds );
				if( $r ) {
		 			$mainframe->enqueueMessage( 'Action types removed' );
				}
				else {
		 			$mainframe->enqueueMessage( 'There was a problem removing the selected Action types', 'error' );
				}
			}
			else {
	 			$mainframe->enqueueMessage( 'No action types selected', 'notice' );
			}
			
			$view->display();
			break;
		
		// Show a paginated list of all the actions
		default:
			$view->display();
			break;
		}
	}
	
	function toggleHasText()
	{
		global $mainframe, $option;
		jimport('joomla.html.pagination');
		$model = &$this->getModel( 'actions' );
		$limitStart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$model->setPagination( $limitStart, $limit );
		$model->setPagedActions();
		
		$checked = reset( array_keys( JRequest::getVar( 'eid') ) );
		$acts = $model->getPagedActions();
		$act = $acts[$checked];
		$model->setAction( $act->getId() );
		$model->toggleHasText();
		$this->display();
	}
	
	/**
	 * Save changes to a message and return to the list
	 */
	function save()
	{
		global $mainframe;
		$actId = JRequest::getVar( 'id' );
		$model = &$this->getModel( 'actions' );
		
		$success = $this->_save( $actId );
		
		if( $success ) {
			$mainframe->enqueueMessage( 'Action was successfully saved' );
			$this->display();
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem saving the action, please try again', 'error' );
			$model->setAction( $actId );
			$view = &$this->getView( 'actions', 'html' );
			$view->setModel( $model, true );
			$view->edit();
		}
	}
	
	/**
	 * Save changes to a message and stay on the edit page
	 */
	function apply()
	{
		global $mainframe;
		$actId = JRequest::getVar( 'id' );
		$model = &$this->getModel( 'actions' );
		
		$success = $this->_save( $actId );
		
		if( $success ) {
			$mainframe->enqueueMessage( 'Action was successfully saved' );
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem saving the action, please try again', 'error' );
		}
		
		$model->setAction( $actId );
		$view = &$this->getView( 'actions', 'html' );
		$view->setModel( $model, true );
		$view->edit();
	}
	
	function _save( $actId )
	{
		$model = &$this->getModel( 'actions' );
		$model->setAction( $actId );
		
		// common message data
		$data['id'] = $actId;
		$data['label'] = JRequest::getVar( 'label' );
		$data['score'] = JRequest::getVar( 'score' );
		$data['has_text'] = (bool)JRequest::getVar( 'has_text', false );
		$data['incidents'] = JRequest::getVar( 'incidents' );
		
		// Save the message data
		return $model->save( $data );
	}
}
?>