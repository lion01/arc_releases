<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );
jimport( 'joomla.application.helper' );
require_once( JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_arc_core'.DS.'helpers'.DS.'lib_sync.php' ); 
require_once( JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'sync.php' ); 

/**
 * Reports Controller Report
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsControllerReport extends ReportsController
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		// Register Extra tasks
		$this->registerTask( 'Savecomplete', 'submit');
		$this->registerTask( 'Saveasincompletable', 'incomplete');
		$this->registerTask( 'Savedraft', 'save');
		$this->registerTask( 'SavePreview', 'preview');
		$this->registerTask( 'Accept', 'approve');
		$this->registerTask( 'Reject', 'feedback');
		$this->registerTask( 'Sendfeedback', 'reject');
		$this->registerTask( 'Archive', 'finalise');
		$this->registerTask( 'Preview', 'preview');
		
		// un-tuple courseid
		if( !is_null($cycleGroupTuple = JRequest::getVar('courseid')) ) {
			$cgArray = explode( '_', $cycleGroupTuple );
			$courseId = array_pop( $cgArray );
			JRequest::setVar( 'courseid', $courseId );
		}
		
		// un-tuple ogroup
		if( !is_null($cycleGroupTuple = JRequest::getVar('ogroup')) ) {
			$cgArray = explode( '_', $cycleGroupTuple );
			$oGroup = array_pop( $cgArray );
			JRequest::setVar( 'ogroup', $oGroup );
		}
		
		// un-tuple ogroup
		if( !is_null($cyclePupilTuple = JRequest::getVar('pupilid')) ) {
			$cpArray = explode( '_', $cyclePupilTuple );
			$pupil = array_pop( $cpArray );
			JRequest::setVar( 'pupilid', $pupil );
		}
	}
	
	function display()
	{
		$model = &$this->getModel( 'report' );
		$view  = &$this->getView ( 'report', 'html' );
		
		// set up a list view to display the previous and next kids
		$lister   = &$this->getModel( 'lists' );
		$listView = &$this->getView ( 'lists', 'html' );
		
		// initialise variables
//		$report = &$model->getReport();
		$report = $model->getReport();
		$cycleId = $model->getCycleId();
		$rptId = JRequest::getVar( 'reportid', false );
		$student = JRequest::getVar( 'pupilid', false );
		$group = JRequest::getVar( 'courseid', false );
		
		// initialise a fresh report if we don't have one or if we're looking at a different one than before
		if ( ($report == false)
			|| !(($report->getStudent() == $student) && ($report->getGroup() == $group) && ($report->getCycle() == $cycleId))
			|| !($report->getId() == $rptId) ) {
			if ($rptId !== false) {
				$model->setReportExisting( $rptId );
			}
			else if (($student !== false) && ($group !== false)) {
				$model->setReportNew( $student, $group, $cycleId );
			}
			else {
				$model->setReportNew( 0, 0, $cycleId );
			}
//			$report = &$model->getReport();
			$report = $model->getReport();
		}
		
		// check the user is allowed here
		if( is_object($report) ) {
			if( !ApotheosisLibAcl::checkDependancy('report.groups', $cycleId.'_'.$report->getGroup()) ) {
				$report->disable();
			}
		}
		
		$model->setState( 'search', false);
		$lister->setState( 'search', false);
		
		// assign both models to both views, and display
		$listView->setModel( $lister, true );
		$listView->setModel( $model, false);
		
		$view->setModel( $model, true );
		$view->setModel( $lister, false );
		
		$view->listView = &$listView;
		$view->display();
		$this->saveModel( 'report' );
		$this->saveModel( 'lists' );
	}
	
	function preview()
	{
		$this->_save( 'draft', true );
	}
	
	function save()
	{
		$this->_save( 'draft' );
	}
	
	function submit()
	{
		$this->_save( 'submitted' );
	}
	
	function incomplete()
	{
		$this->_save( 'incomplete' );
	}
	
	function approve()
	{
		$this->_save( 'approved' );
	}
	
	function feedback()
	{
		$model = &$this->getModel( 'report' );
		$report = &$model->getReport();
		$view  = &$this->getView ( 'report', 'html' );
		$view->setModel( $model, true );
		
		$view->feedback();
	}
	
	function reject()
	{
		$this->_save( 'rejected' );
	}
	
	function finalise()
	{
		$this->_save( 'final' );
	}
	
	/**
	 * Update the status, checker, checked on and feedback of a report
	 *
	 * @return boolean  True on success, false on failure
	 */
	function _updateReport( &$report, $status, $by, $on, $feedback = NULL )
	{
		$db = &JFactory::getDBO();
		$report->setStatus( $status );
		$report->setFeedback( $feedback );
		$report->setCheckedBy( $by );
		$report->setCheckedOn( $on );
		$query = 'UPDATE #__apoth_rpt_reports'
			."\n".' SET'
			."\n".' '.$db->nameQuote('status').' = '.$db->Quote($status).','
			."\n".' '.$db->nameQuote('feedback').' = '.$db->Quote($feedback).','
			."\n".' '.$db->nameQuote('checked_by').' = '.$db->Quote($by).','
			."\n".' '.$db->nameQuote('checked_on').' = '.$db->Quote($on)
			."\n".' WHERE '.$db->nameQuote('id').' = '.$db->Quote($report->getId());
		$db->setQuery($query);
		$result = $db->query();
		return $result;
	}
	
	/**
	 * Saves the report with the given status.
	 * Status-only saves use the _updateReport function, others use $report->save()
	 * After save, notification emails may be sent
	 * After emails, on-screen messages are set
	 * A submitted report which encounters errors is set to be draft
	 * Checks that the user has editting rights to this report
	 *
	 * @param $status string  The status indicator string. One of: ('draft', 'submitted', 'rejected', 'approved', 'final')
	 * @param $status boolean Do we require the report to be previewed on reload
	 */
	function _save( $status, $preview = false )
	{
		ob_start();
		global $mainframe;
		$model = &$this->getModel( 'report' );
		$report = &$model->getReport();
		$allok = true;
		
		// check for the report object
		if ( $report == false ) {
			$result = false;
			$mainframe->enqueueMessage( JText::_('Lost the report object, probably due to a login time-out. Please go around again'), 'error' );
		}
		// if we're good to go, then go save
		else {
			// get the person id of the current user
			$db = &JFactory::getDBO();
			$user = &ApotheosisLib::getUser();
			$personId = $user->person_id;
			$now = date('Y-m-d H:i:s');
			
			switch( $status ) {
			case( 'rejected' ):
				// check the user is allowed to perform this action
				if(  !ApotheosisLibAcl::checkDependancy('report.groups', $model->getCycleId().'_'.$report->getGroup())
					&& !ApotheosisLibAcl::checkDependancy('report.people', $model->getCycleId().'_'.$report->getStudent()) ) {
					$allok = false;
					break;
				}
				$feedback = JRequest::getVar('feedback', '');
				$allok = $this->_updateReport( $report, 'rejected', $personId, $now, $feedback );
				if( $allok == false ) {
					$mainframe->enqueueMessage( JText::_('Failed to save feedback due to a database error. Please try again'), 'error' );
				}
				else {
					$mainframe->enqueueMessage( JText::_('Saved feedback'), 'message' );
				}
				break;
			
			case( 'approved' ):
				// check the user is allowed to perform this action
				if(  !ApotheosisLibAcl::checkDependancy('report.groups', $model->getCycleId().'_'.$report->getGroup())
					&& !ApotheosisLibAcl::checkDependancy('report.people', $model->getCycleId().'_'.$report->getStudent()) ) {
					$allok = false;
					break;
				}
				$allok = $this->_updateReport( $report, 'approved', $personId, $now );
				if( $allok == false ) {
					$mainframe->enqueueMessage( JText::_('Failed to save acceptance due to a database error. Please try again'), 'error' );
				}
				else {
					$mainframe->enqueueMessage( JText::_('Saved acceptance'), 'message' );
				}
				break;
			
			case( 'final' ):
				// check the user is allowed to perform this action
				if( !ApotheosisLibAcl::checkDependancy('report.groups', $model->getCycleId().'_'.$report->getGroup()) ) {
					$allok = false;
					break;
				}
				$allok = $this->_updateReport( $report, 'final', $personId, $now );
				if( $allok == false ) {
					$mainframe->enqueueMessage( JText::_('Failed to archive report due to a database error. Please try again'), 'error' );
				}
				else {
					$mainframe->enqueueMessage( JText::_('Report archived'), 'message' );
				}
				break;
			
			case( 'incomplete' ):
				// check the user is allowed to perform this action
				if( !ApotheosisLibAcl::checkDependancy('report.groups', $model->getCycleId().'_'.$report->getGroup()) ) {
					$allok = false;
					break;
				}
				$report->setStatus( 'submitted' );
				$results = $report->save( 'POST' );
				$mainframe->enqueueMessage( JText::_('Report saved as incomplete, and submitted for peer review'), 'message' );
				break;
			
			default:
				// check the user is allowed to perform this action
				if( !ApotheosisLibAcl::checkDependancy('report.groups', $model->getCycleId().'_'.$report->getGroup()) ) {
					$allok = false;
					break;
				}
				$report->setStatus( $status );
				$results = $report->save( 'POST' );
				if( array_key_exists('errors', $results) ) {
					foreach($results['errors'] as $v) {
						$mainframe->enqueueMessage( JText::_($v), 'error' );
					}
					$allok = false;
				}
				else {
					$mainframe->enqueueMessage( JText::_('Report saved'), 'message' );
				}
				if( array_key_exists('warnings', $results) ) {
					foreach($results['warnings'] as $v) {
						$mainframe->enqueueMessage( JText::_($v), 'warning' );
					}
					$allok = false;
				}
			}
			
			// post-save actions for if it went well
			if( $allok ) {
				// Now that we've saved the report, send any email required
				$target   = false;
				$target2  = false;
				$setField = $report->getField('setname');
				$setText  = ( is_null($setField) ? '' : ' in class '.htmlspecialchars($setField->getValue()) );
				
				switch( $status ) {
				case( 'rejected' ) :
					$target = $report->getAuthor();
					$subject = 'Report rejected';
					$body = 'Your report for '.$report->getStudentFirstName().' '.$report->getStudentSurname().$setText.' was rejected with the following message:'
						."\n".$feedback
						."\n".'Please go back to this report and correct the problems, then re-submit it for peer review.';
					
					if( ($model->getCycleReChecker() == 'first') && ($report->getCheckedBy() != $report->getCheckedByFirst()) ) {
						$target2 = $report->getCheckedByFirst();
						$body2 = 'The report for '.$report->getStudentFirstName().' '.$report->getStudentSurname().$setText.' which was first checked by you was rejected with the following message:'
						."\n".$feedback
						."\n".'Please be aware that this report will be re-submitted for peer review and you will then need to re-check it.';
					}
					break;
				
				case( 'incomplete' ):
				case( 'submitted' ) :
					$target = ( ($model->getCycleReChecker() == 'first') ?  $report->getCheckedByFirst() : $report->getCheckedBy() );
					$subject = 'Report re-submitted';
					$body = 'A report for '.$report->getStudentFirstName().' '.$report->getStudentSurname().$setText.' was re-submitted for peer checking.'
						."\n".'You checked this report '.(($model->getCycleReChecker() == 'first') ? 'first' : 'last').' time, so are probably responsible for re-checking it now.'
						."\n".'Please go back to this report and peer review it.';
					break;
				
				}
				
				// send primary email
				if( $target != false ) {
					$query = 'SELECT u.'.$db->nameQuote('email').', COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
						."\n".' FROM #__apoth_ppl_people AS p'
						."\n".' INNER JOIN #__users AS u'
						."\n".'   ON u.id = p.juserid'
						."\n".' WHERE p.'.$db->nameQuote('id').' = '.$db->Quote($target);
					$db->setQuery($query);
					$row = $db->loadAssoc();
					
					if( empty($row['email']) ) {
						$sent = false;
					}
					else {
						$to = $row['email'];
						$mail = &JFactory::getMailer();
						$mail->addRecipient( $to );
						$mail->setSender( $user->email, $user->name );
						$mail->setSubject( $subject );
						$mail->setBody( $body );
						$sent = $mail->Send();
					}
				}
				// send secondary email
				if( $target2 != false ) {
					$query = 'SELECT u.'.$db->nameQuote('email').', COALESCE( p.preferred_firstname, p.firstname ) AS firstname, p.middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
						."\n".' FROM #__apoth_ppl_people AS p'
						."\n".' INNER JOIN #__users AS u'
						."\n".'   ON u.id = p.juserid'
						."\n".' WHERE p.'.$db->nameQuote('id').' = '.$db->Quote($target2);
					$db->setQuery($query);
					$row = $db->loadAssoc();
					
					if( empty($row['email']) ) {
						$sent = false;
					}
					else {
						$to2 = $row['email'];
						$mail = &JFactory::getMailer();
						$mail->addRecipient( $to2 );
						$mail->setSender( $user->email, $user->name );
						$mail->setSubject( $subject );
						$mail->setBody( $body2 );
						$sent2 = $mail->Send();
					}
				}
				
				// non-email / post-email actions
				switch( $status ) {
				case( 'rejected' ):
					if( $sent ) {
						$mainframe->enqueueMessage( JText::_('Sent email to '.$to.' with message:<br /><br />'.nl2br(htmlspecialchars($body))), 'message' );
					}
					if( $sent2 ) {
						$mainframe->enqueueMessage( JText::_('Sent email to '.$to2.' with message:<br /><br />'.nl2br(htmlspecialchars($body2))), 'message' );
					}
					break;
				
				case( 'incomplete' ):
				case( 'submitted' ):
					if( $sent ) {
						$mainframe->enqueueMessage( JText::_('Message sent to peer checker'), 'message' );
					}
					else {
						$mainframe->enqueueMessage( JText::_('Please notify your peer checker'), 'message' );
					}
					break;
				
				case( 'draft' ):
					$mainframe->enqueueMessage( JText::_('This report is complete but not submitted for peer review.'), 'warning' );
					$mainframe->enqueueMessage( JText::_('You will need to "Save Complete" before your report can be checked.'), 'warning' );
				
				default:
					break;
				
				}
			}
			// post-save actions for if it didn't go well (tidy things up)
			else {
				switch( $status ) {
				case( 'submitted' ) :
					$report->setStatus( 'draft' );
					$results = $report->save( 'POST' );
					$mainframe->enqueueMessage( JText::_('Could not submit for peer review due to these problems'), 'warning' );
					break;
				
				default:
					break;
				}
			}
		}
		
		$this->saveModel();
		if( ($msg = ob_get_clean()) !== '' ) {
			$mainframe->enqueueMessage( $msg, 'message' );
		}
		if( $preview ) {
			$mainframe->redirect( ApotheosisLib::getActionLinkByName('apoth_report_preview', array('report.reports'=>$report->getId(), 'report.scope'=>JRequest::getVar('repscope', 'group'))) );
		}
		else {
			$mainframe->redirect( ApotheosisLib::getActionLinkByName('apoth_report_edit', array('report.reports'=>$report->getId(), 'report.scope'=>JRequest::getVar('repscope', 'group'))) );
		}
		
	}
	
	function pickStatement()
	{
		$this->_pickStatement('pick');
	}
	
	function clarifyStatement()
	{
		$this->_pickStatement('clarify');
	}
	
	/**
	 * Sets things up for the statement picker window, used to select statements
	 * for insertion into the current report, and to fill in custom fields in
	 * those statements prior to insertion
	 * Checks that the user has editting rights to this report
	 *
	 * @param $pickOrClarify string  Either 'pick' or 'clarify' to determine which stage of statement picking should be shown
	 */
	function _pickStatement($pickOrClarify)
	{
		$model = &$this->getModel( 'report' );
		$view  = &$this->getView ( 'report', 'html' );
		$report = &$model->getReport();
		$model->setMergeDetails( $report->getStudent(), $report->getGroup() );
		$view->setModel( $model, true );
		
		global $mainframe;
		$tmpl = $mainframe->getTemplate();
		$doc = &JFactory::getDocument();
//		$doc->addStyleSheet( 'templates'.DS.$tmpl.DS.'css'.DS.'nomenu.css' );
		$doc->addScript( JURI::base().'administrator/components/com_arc_core/libraries/js/core.js' );
		
		// check the user is allowed to perform this action
		if( !ApotheosisLibAcl::checkDependancy('report.groups', $model->getCycleId().'_'.$report->getGroup()) ) {
			break;
		}
		
		switch($pickOrClarify) {
		case('pick'):
			$view->statementPicker();
			break;
			
		case('clarify'):
			$view->statementFinisher();
			break;
		}
	}
}
?>