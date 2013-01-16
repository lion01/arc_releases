<?php
/**
 * @package     Arc
 * @subpackage  People
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * People Admin People Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage People
 * @since      1.6
 */
class PeopleAdminControllerPeople extends PeopleAdminController
{
	/**
	 * Provides the people controller class
	 */
	function __construct()
	{
		parent::__construct();
		$this->registerTask( 'save_details', 'saveDetails' );
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		global $mainframe, $option;
		jimport('joomla.html.pagination');
		$model = &$this->getModel( 'people' );
		$view = &$this->getView( 'people', 'html' );
		$view->setModel( $model, true );
		
		$searchTerms = $mainframe->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
		$searchTerms = explode( ' ', $searchTerms );
		$model->setSearchTerms( $searchTerms );
		
		$limitStart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$model->setPagination( $limitStart, $limit );
		
		$model->setPagedPeople();
		
		switch( JRequest::getVar('task') ) {
		// view a person's details
		case( 'revert_details' ):
			$mainframe->enqueueMessage( JText::_('Details successfully reverted.'), 'message' );
		case( 'details' ):
		case( 'save_details' ):
			$personIndex = reset( array_keys(JRequest::getVar('eid', array())) );
			if( $personIndex === false ) {
				$personIndex = JRequest::getVar( 'personIndex' );
			}
			
			$model->setPersonIndex( $personIndex );
			$model->setPerson( $personIndex, true );
			$view->details();
			break;
		
		// hand over to the profiles MVC supplying relevant info
		case( 'profiles' ):
			$indexData = array_keys( JRequest::getVar('eid') );
			$model->setPersonIds( $indexData );
			$personIds = $model->getPersonIds();
			
			$session = &JFactory::getSession();
			$session->set( 'personIds', $personIds );
			$session->set( 'origin', 'index.php?option=com_arc_people&view=people' );
			
			$mainframe->redirect( 'index.php?option=com_arc_people&view=profiles&task=edit_profile' );
			break;
		
		// Show a paginated list of all the courses
		default:
			$view->display();
			break;
		}
	}
	
	function saveDetails()
	{
		global $mainframe, $option;
		jimport('joomla.html.pagination');
		$model = &$this->getModel( 'people' );
		
		$searchTerms = $mainframe->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
		$searchTerms = explode( ' ', $searchTerms );
		$model->setSearchTerms( $searchTerms );
		
		$limitStart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$model->setPagination( $limitStart, $limit );
		
		$model->setPagedPeople();
		
		// collect form data
		$data = array();
		$data['roles'] = JRequest::getVar( 'roles', array() );
		
		// save the data
		$model->setPerson( JRequest::getVar('personIndex'), true );
		$save = $model->saveDetails( $data );
		
		// check the success of the save operation
		if( $save[0] === true ) {
			$mainframe->enqueueMessage( JText::_('Details changes successfully saved.') );
		}
		else {
			$mainframe->enqueueMessage( JText::_('Details changes failed to save correctly.'), 'error' );
			
			// collect up any error messages...
			foreach( $save[1] as $errMsg ) {
				if( $errMsg != '' ) {
					$saveErrMsgs[] = $errMsg;
				}
			}
			
			// ...an report accordingly
			if( !empty($saveErrMsgs) ) {
				foreach( $saveErrMsgs as $errMsg ) {
					$mainframe->enqueueMessage( $errMsg, 'error' );
				}
			}
		}
		
		$this->display();
	}
}
?>