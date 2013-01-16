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
 * People Admin Profiles Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage People
 * @since      1.6
 */
class PeopleAdminControllerProfiles extends PeopleAdminController
{
	/**
	 * Provides the profile controller class
	 */
	function __construct()
	{
		parent::__construct();
		$this->registerTask( 'save_template', 'saveProfile' );
		$this->registerTask( 'save_profile', 'saveProfile' );
		$this->registerTask( 'save_template_types', 'saveTemplateTypes' );
		$this->registerTask( 'year_groups_apply', 'applyTask' );
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		$model = &$this->getModel( 'profiles' );
		$view = &$this->getView( 'profiles', 'html' );
		$view->setModel( $model, true );
		
		global $mainframe;
		
		switch( JRequest::getVar('task', false) ) {
		// picking a profiles management task
		case( 'revert_template_types' ):
		$mainframe->enqueueMessage( JText::_('Template types successfully reverted.'), 'message' );
		case( 'save_template_types' ):
		case( 'year_groups_apply' ):
		case( 'select_task' ):
			switch( JRequest::getVar('select_task') ) {
				
			// showing the list of templates
			case( 'template' ):
				$model->setCurType( 'template' );
				$model->setTemplateIds();
				
				$view->selectTemplate();
				break;
			
			// showing the year group management page
			case( 'year_groups' ):
				$view->updateYearGroups();
				break;
			
			// go back to the relevant page on the originating MVC
			case( 'profile' ):
				$session = &JFactory::getSession();
				$origin = $session->clear( 'origin' );
				
				$mainframe->redirect( $origin );
				break;
			
			default:
				$mainframe->enqueueMessage( JText::_('Please select a profile management task.'), 'notice' );
				
				$view->display();
				break;
			}
			break;
		
		// show the profile editing form
		case( 'revert_template' ):
			$mainframe->enqueueMessage( JText::_('Template successfully reverted.'), 'message' );
		case( 'edit_template' ):
		case( 'save_template' ):
			$ids = JRequest::getVar( 'ids' );
			$model->setCurType( 'template' );
				$model->setTemplateIds();
			if( is_null($ids) ) {
				$mainframe->enqueueMessage( JText::_('Please select a template type to view or edit.'), 'notice' );
				
				$view->selectTemplate();
			}
			else {
				// determine which profile to set (are we using a using an existing template?)
				$templateToApply = JRequest::getVar( 'template_to_apply' );
				$profileIds = ( $templateToApply != '' ) ? array( $templateToApply ) : $ids;
				
				$model->setCategoryMap();
				$model->setCurIds( $ids );
				$model->setProfiles( $profileIds );
				
				$view->profile();
			}
			break;
			
		case( 'revert_profile' ):
			$mainframe->enqueueMessage( JText::_('Profile(s) successfully reverted.'), 'message' );
		case( 'edit_profile' ):
		case( 'save_profile' ):
			$personIds = JRequest::getVar( 'ids', false );
			if( $personIds == false ) {
				$session = &JFactory::getSession();
				$personIds = $session->clear( 'personIds' );
			}
			
			// if we are setting profile(s) to match a template
			$templateToApply = JRequest::getVar( 'template_to_apply' );
			if( $templateToApply != '' ) {
				$model->setCurType( 'template' );
				$model->setProfiles( array($templateToApply) );
			}
			// if not then base profiles on people IDs
			else {
				$model->setCurType( 'profile' );
				$model->setProfiles( $personIds );
			}
			
			$model->setCategoryMap();
			$model->setCurType( 'profile' );
			$model->setCurIds( $personIds );
			$model->setTemplateIds();
			
			$view->profile();
			break;
		
		case( 'profile_tasks'):
		default:
			// Show a list of profile tasks
			$view->display();
			break;
		}
	}
	
	/**
	 * Save the profile form data
	 */
	function saveProfile()
	{
		$model = &$this->getModel( 'profiles' );
		
		global $mainframe;
		
		// collect form data
		$type = JRequest::getVar( 'type' );
		$ids = JRequest::getVar( 'ids' );
		$rawData = JRequest::getVar( 'cats', array() );
		$partials = JRequest::getVar( 'partials' );
		
		switch( $type ) {
		case( 'template' ):
			$model->setCurType( $type );
			$model->setProfiles( $ids );
			$dbColumn = 'person_type';
			break;
			
		case( 'profile' ):
			$model->setCurType( $type );
			$model->setProfiles( $ids );
			$dbColumn = 'person_id';
			break;
		}
		
		// process the form data
		$data = array();
		foreach( $rawData as $cat=>$catArray ) {
			foreach( $catArray as $prop=>$value ) {
				$value = str_replace( array("\r\n", "\r"), "\n", $value );
				foreach( $ids as $id ) {
					$data[$id][] = array( $dbColumn=>$id, 'category_id'=>$cat, 'property'=>$prop, 'value'=>$value );
				}
			}
		}
		
		// save the data
		$model->setCategoryMap();
		$save = $model->saveProfile( $data, $partials );
		
		// check the success of the save operation
		if( $save[0] === true ) {
			$mainframe->enqueueMessage( JText::_(ucfirst($type).' changes successfully saved.') );
		}
		else {
			$mainframe->enqueueMessage( JText::_(ucfirst($type).' changes failed to save correctly.'), 'error' );
			
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
	
	/**
	 * Save the list of template types
	 */
	function saveTemplateTypes()
	{
		$model = &$this->getModel( 'profiles' );
		
		global $mainframe;
		
		// collect form data
		$templateTypes = JRequest::getVar( 'template_types' );
		
		// save the data
		$model->setTemplateIds();
		$save = $model->saveTemplateTypes( $templateTypes );
		
		// check the success of the save operation
		if( $save[0] === true ) {
			$mainframe->enqueueMessage( JText::_('Template type changes successfully saved.') );
		}
		else {
			$mainframe->enqueueMessage( JText::_('Template type changes failed to save correctly.'), 'error' );
			
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
	
	/**
	 * Action the currently requested task
	 */
	function applyTask()
	{
		$model = &$this->getModel( 'profiles' );
		
		global $mainframe;
		$taskDone = false;
		
		switch( JRequest::getVar('apply_task', false) ) {
		case( 'match' ):
			$taskDone = true;
			$apply = $model->matchYearToTutor();
			$task = JText::_( 'Year group matching to tutor year' );
			break;
		
		default:
			$mainframe->enqueueMessage( JText::_('Please select a year groups management task.'), 'notice' );
			break;
		}
		
		// check we performed a task
		if( $taskDone ) {
			// check the success of the save operation
			if( $apply[0] === true ) {
				$mainframe->enqueueMessage( $task.' '.JText::_('changes successfully applied.') );
			}
			else {
				$mainframe->enqueueMessage( $task.' '.JText::_('changes failed to apply.'), 'error' );
				
				// collect up any error messages...
				foreach( $apply[1] as $errMsg ) {
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
		}
		
		$this->display();
	}
}
?>