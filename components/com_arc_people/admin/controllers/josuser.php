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
 * People Admin Josuser Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage People
 * @since      1.6
 */
class PeopleAdminControllerJosuser extends PeopleAdminController
{
	/**
	 * Provides the people controller class
	 */
	function __construct()
	{
		parent::__construct();
		$this->registerTask( 'apply_task', 'applyTask' );
		$this->registerTask( 'save_format', 'saveFormat' );
	}
	
	/**
	 * Default method
	 */
	function display()
	{
		global $mainframe;
		$model = &$this->getModel( 'josuser' );
		$view = &$this->getView( 'josuser', 'html' );
		$view->setModel( $model, true );
		
		switch( JRequest::getVar('task', false) ) {
		// picking a joomla user management task
		case( 'revert_format' ):
			$mainframe->enqueueMessage( JText::_('Joomla! user variables format successfully reverted.'), 'message' );
		case( 'save_format' ):
		case( 'select_task' ):
			switch( JRequest::getVar('select_task') ) {
			case( 'create' ):
				$model->setPotentialJosUsers();
				
				// if there are lot of accounts to create, notify it will take a while
				$potUsers = $model->getPotentialJosUsers();
				if( $potUsers > 100 ) {
					$mainframe->enqueueMessage( JText::_('The creation of '.$potUsers.' Joomla! user accounts may take some time. Please click the \'Create Joomla! Users\' button and wait for the confirmation message to appear.'), 'notice' );
				}
				
				$view->josCreate();
				break;
			
			case( 'pword' ):
				$view->pword();
				break;
				
			case( 'format' ):
				$model->setDomain();
				
				$view->josFormat();
				break;
			
			default:
				$mainframe->enqueueMessage( JText::_('Please select a Joomla! user management task.'), 'notice' );
				$model->setPotentialJosUsers();
				
				$view->display();
				break;
			}
			break;
		
		case( 'apply_task' ):
		default:
			// Show a list of joomla user tasks
			$model->setPotentialJosUsers();
			
			$view->display();
			break;
		}
	}
	
	/**
	 * Save the Joomla user variable format
	 */
	function saveFormat()
	{
		global $mainframe;
		$model = &$this->getModel( 'josuser' );
		$view = &$this->getView( 'josuser', 'html' );
		$view->setModel( $model, true );
		
		// Retrieve data for Joomla user variable format 
		$data = JRequest::getVar( 'params' );
		
		// Save the format data
		$save = $model->saveParams( $data );
		
		if( $save ) {
			$mainframe->enqueueMessage( 'Joomla! user variable format was successfully saved.' );
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem saving the Joomla! user variable format, please try again.', 'error' );
		}
		
		$this->display();
	}
	
	/**
	 * Action the currently requested task
	 */
	function applyTask()
	{
		$model = &$this->getModel( 'josuser' );
		
		global $mainframe;
		$taskDone = false;
		$task = JRequest::getVar( 'apply_task', false );
		
		switch( $task ) {
		case( 'create' ):
			$taskDone = true;
			$taskTxt = JText::_( 'Joomla! user account creation' );
			
			$apply = $model->createJosUser();
			break;
		
		case( 'pword_upload' ):
			// if file was found then proceed
			if( ($_FILES['filename']['error'] == 0) && ($_FILES['filename']['size'] > 0) ) {
				// get the uploaded file
				$rawCSV = $_FILES['filename']['tmp_name'];
				$rawContents = ApotheosisLib::file_get_contents_utf8( $rawCSV );
				$cleaned = str_replace( array("\r\n", "\r"), "\n", $rawContents );
				$cleanCSV = tmpfile();
				fwrite( $cleanCSV, $cleaned );
				rewind( $cleanCSV );
				
				// put file contents into a useful array
				$pwords = array();
				while( ($data = fgetcsv($cleanCSV, 2048)) !== false ) {
					$pwords[trim($data[0])] = trim( $data[1] );
				} 
				
				// test the generated array
				if( !empty($pwords) ) {
					$taskDone = true;
					$taskTxt = JText::_( 'Joomla! user password setting' );
					
					$apply = $model->setJosPasswords( $pwords );
				}
				else {
					$mainframe->enqueueMessage( JText::_('No information could be read from the uploaded file. '), 'error' );
				}
			}
			elseif( $_FILES['filename']['error'] > 0 ) {
				$mainframe->enqueueMessage( JText::_('File upload failed with error: ').$_FILES['filename']['error'], 'error' );
			}
			elseif( $_FILES['filename']['size'] == 0 ) {
				$mainframe->enqueueMessage( JText::_('The uploaded file "'.$_FILES['filename']['name'].'" was empty.'), 'error' );
			}
			break;
		}
		
		// check we performed a task
		if( $taskDone ) {
				// check the success of the save operation
				if( $apply[0] === true ) {
					$mainframe->enqueueMessage( $taskTxt.' '.JText::_('was successfully applied.') );
				}
				else {
					$mainframe->enqueueMessage( $taskTxt.' '.JText::_('failed to apply in some cases.'), 'error' );
					
					switch( $task ) {
					case( 'create' ):
						if( array_key_exists('arc', $apply[1]) ) {
							$namesList = implode( ',&nbsp;&nbsp;', $apply[1]['arc'] );
							$saveErrMsgs[] = 'The Joomla! user account was created but the Arc record could not be updated for: '.$namesList;
						}
						if( array_key_exists('blank', $apply[1]) ) {
							$namesList = implode( ',&nbsp;&nbsp;', $apply[1]['blank'] );
							$saveErrMsgs[] = 'Joomla! user account creation failed with blank keyword substitutions for: '.$namesList;
						}
						if( array_key_exists('jos', $apply[1]) ) {
							$namesList = implode( ',&nbsp;&nbsp;', $apply[1]['jos'] );
							$saveErrMsgs[] = 'The potential Joomla! user account could not be saved for: '.$namesList;
						}
						break;
					
					case( 'pword_upload' ):
						// noArcId
						if( array_key_exists('noArcId', $apply[1]) ) {
							$arcIdList = implode( ',&nbsp;&nbsp;', $apply[1]['noArcId'] );
							$saveErrMsgs[] = 'The following Arc IDs were present in the uploaded file but were not found in the database: '.$arcIdList;
						}
						
						// noJUserId
						if( array_key_exists('noJUserId', $apply[1]) ) {
							$arcIdList = implode( ',&nbsp;&nbsp;', $apply[1]['noJUserId'] );
							$saveErrMsgs[] = 'The following Arc IDs have no Joomla! user ID associated with them: '.$arcIdList;
						}
						
						// noJUser
						if( array_key_exists('noJUser', $apply[1]) ) {
							$arcIdList = implode( ',&nbsp;&nbsp;', $apply[1]['noJUser'] );
							$saveErrMsgs[] = 'The following Joomla! IDs were associated with valid Arc IDs present in the uploaded file but were not found in the database: '.$arcIdList;
						}
						
						// noSave
						if( array_key_exists('noSave', $apply[1]) ) {
							$arcIdList = implode( ',&nbsp;&nbsp;', $apply[1]['noSave'] );
							$saveErrMsgs[] = 'The following Joomla! accounts failed to save after the addition of the password: '.$arcIdList;
						}
						break;
					
					default:
					// collect up any error messages...
					foreach( $apply[1] as $errMsg ) {
						if( $errMsg != '' ) {
							$saveErrMsgs[] = $errMsg;
						}
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