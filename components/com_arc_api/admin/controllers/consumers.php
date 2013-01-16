<?php
/**
 * @package     Arc
 * @subpackage  API
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * API Admin Consumers Controller
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage API
 * @since      1.6.1
 */
class ApiAdminControllerConsumers extends ApiAdminController
{
	function __construct()
	{
		parent::__construct();
		$this->registerTask( 'add', 'display' );
		$this->registerTask( 'edit', 'display' );
		$this->registerTask( 'remove', 'delete' );
		$this->registerTask( 'cancel', 'display' );
	}
	
	function display()
	{
		global $mainframe, $option;
		jimport('joomla.html.pagination');
		$model = &$this->getModel( 'consumers' );
		$view = &$this->getView( 'consumers', 'html' );
		$view->setModel( $model, true );
		
		$searchTerm = $mainframe->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
		$model->setSearchTerm( $searchTerm );
		
		$limitStart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$model->setPagination( $limitStart, $limit );
		
		switch( JRequest::getVar('task') ) {
		// Add a new consumer
		case( 'add' ):
			$model->setConsumers();
			$view->add();
			break;
		
		// Edit a consumer
		case( 'edit' ):
			$consumerIndex = reset( array_keys(JRequest::getVar('eid')) );
			$model->setConsumers( $consumerIndex );
			$view->edit();
			break;
		
		default:
			$view->display();
			break;
		}
	}
	
	
	
	/**
	 * Save changes to a consumer
	 */
	function save()
	{
		global $mainframe;
		$model = &$this->getModel( 'consumers' );
		$view = &$this->getView( 'consumers', 'html' );
		$view->setModel( $model, true );
		
		// Retrieve data for updated consumer 
		$data = array();
		$data['id']          = JRequest::getVar( 'id' ); 
		$data['name']        = JRequest::getVar( 'name' );
		$data['description'] = JRequest::getVar( 'description' );
		$data['key']         = JRequest::getVar( 'key' );
		
		// Save the course data
		$save = $model->save( $data );
		
		if( $save ) {
			$mainframe->enqueueMessage( 'Consumer was successfully saved.' );
			$view->edit();
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem saving the consumer, please try again.', 'error' );
			$view->edit();
		}
	}
	
	/**
	 * Mark the consumers as deleted
	 */
	function delete()
	{
		global $mainframe, $option;
		jimport('joomla.html.pagination');
		$model = &$this->getModel( 'consumers' );
		$view = &$this->getView( 'consumers', 'html' );
		$view->setModel( $model, true );
		
		$searchTerm = $mainframe->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
		$model->setSearchTerm( $searchTerm );
		
		$limitStart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$model->setPagination( $limitStart, $limit );
		
		$consumerIndices = array_keys( JRequest::getVar('eid') );
		$model->setConsumers( $consumerIndices );
		
		// Mark consumers as deleted
		$delete = $model->delete();
		
		if( $delete ) {
			$message = ( count($consumerIndices) > 1 ) ? 'The consumers were successfully deleted.' : 'The consumer was successfully deleted.';
			$mainframe->enqueueMessage( $message );
		}
		else {
			$message = ( count($consumerIndices) > 1 ) ? 'There was a problem deleting the consumers, please try again.' : 'There was a problem deleting the consumer, please try again.';
			$mainframe->enqueueMessage( $message, 'error' );
		}
		
		$view->display();
	}
}
?>