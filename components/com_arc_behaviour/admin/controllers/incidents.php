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
 * Behaviour Admin Incidents Controller
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Behaviour
 * @since 0.1
 */
class BehaviourAdminControllerIncidents extends BehaviourAdminController
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
		$model = &$this->getModel( 'incidents' );
		$view = &$this->getView( 'incidents', 'html' );
		$view->setModel( $model, true );
		
		$limitStart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$model->setPagination( $limitStart, $limit );
		$model->setPagedIncidents();
		
		switch( JRequest::getVar('task') ) {
		// Create an incident
		case( 'add' ):
			$model->setIncident( -1 );
			$view->edit();
			break;
			
		// Edit an incident
		case( 'edit' ):
			$checked = reset( array_keys( JRequest::getVar( 'eid') ) );
			$incs = $model->getPagedIncidents();
			$inc = $incs[$checked];
			$model->setIncident( $inc['id'] );
			$view->edit();
			break;
			
		// Remove incident(s)
		case( 'remove' ):
			$incIds = array();
			$checked = JRequest::getVar( 'eid' );
			$incs = $model->getPagedIncidents();
			if( is_array($checked) ) {
				foreach( $checked as $index=>$on ) {
					$incIds[] = $incs[$index]['obj']->getId();
				}
			}
			
			if( is_array($incIds) && !empty($incIds) ) {
				$r = $model->delete( $incIds );
				if( $r ) {
		 			$mainframe->enqueueMessage( 'Incident types removed' );
				}
				else {
		 			$mainframe->enqueueMessage( 'There was a problem removing the selected Incident types', 'error' );
				}
			}
			else {
	 			$mainframe->enqueueMessage( 'No incident types selected', 'notice' );
			}
			
			$view->display();
			break;
		
		// Show a paginated list of all the incidents
		default:
			$view->display();
			break;
		}
	}
	
	function toggleHasText()
	{
		global $mainframe, $option;
		jimport('joomla.html.pagination');
		$model = &$this->getModel( 'incidents' );
		$limitStart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$model->setPagination( $limitStart, $limit );
		$model->setPagedIncidents();
		
		$checked = reset( array_keys( JRequest::getVar( 'eid') ) );
		$incs = $model->getPagedIncidents();
		$inc = $incs[$checked];
		$model->setIncident( $inc['id'] );
		$model->toggleHasText();
		$this->display();
	}
	
	/**
	 * Save changes to a message and return to the list
	 */
	function save()
	{
		global $mainframe;
		$incId = JRequest::getVar( 'id' );
		$model = &$this->getModel( 'incidents' );
		
		$success = $this->_save( $incId );
		
		if( $success ) {
			$mainframe->enqueueMessage( 'Incident was successfully saved' );
			$this->display();
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem saving the incident, please try again', 'error' );
			$model->setIncident( $incId );
			$view = &$this->getView( 'incidents', 'html' );
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
		$incId = JRequest::getVar( 'id' );
		$model = &$this->getModel( 'incidents' );
		
		$success = $this->_save( $incId );
		
		if( $success ) {
			$mainframe->enqueueMessage( 'Incident was successfully saved' );
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem saving the incident, please try again', 'error' );
		}
		
		$model->setIncident( $incId );
		$view = &$this->getView( 'incidents', 'html' );
		$view->setModel( $model, true );
		$view->edit();
	}
	
	function _save( $incId )
	{
		$model = &$this->getModel( 'incidents' );
		$model->setIncident( $incId );
		
		// common message data
		$data['id'] = $incId;
		$data['label'] = JRequest::getVar( 'label' );
		$data['score'] = JRequest::getVar( 'score' );
		$data['has_text'] = (bool)JRequest::getVar( 'has_text', false );
		$data['tag'] = JRequest::getVar( 'tag' );
		$data['parent'] = JRequest::getVar( 'parent' );
		
		// Save the message data
		return $model->save( $data );
	}
	
}
?>