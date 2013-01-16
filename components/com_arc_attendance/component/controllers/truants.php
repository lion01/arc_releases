<?php
/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Attendance Manager Controller Truants
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class AttendanceControllerTruants extends AttendanceController
{
	/**
	 * Create a new attendance truants controller
	 */
	function __construct()
	{
		parent::__construct();
		
		// Register Extra tasks
		$this->registerTask( 'Add', 'truantAdd');
		$this->registerTask( 'Remove', 'truantRemove' );
	}
	
	/**
	 * Display the truant editing list
	 */
	function display()
	{
		$model = &$this->getModel( 'truants' );
		$view  = &$this->getView ( 'truants', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		$scope = JRequest::getVar( 'scope' );
		switch( $scope ) {
		case( 'edit' ):
			$view->editTruants();
			break;
		}
		
		$this->saveModel();
	}
	
	function truantAdd()
	{
		$model = &$this->getModel( 'truants' );
		global $mainframe;
		
		$res = $model->addTruants( JRequest::getVar('non_truants', array()) );
		if( $res['errors'] == 0 ) {
			$mainframe->enqueueMessage( $res['added'].' new truants added', 'message' );
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem saving the new truants list', 'error' );
		}
		
		$this->saveModel( 'truants' );
		$mainframe->redirect( ApotheosisLib::getActionLinkByName() );
	}
	
	function truantRemove()
	{
		$model = &$this->getModel( 'truants' );
		global $mainframe;
		
		$res = $model->removeTruants( JRequest::getVar('truants', array()) );
		if( $res['errors'] == 0 ) {
			$mainframe->enqueueMessage( $res['deleted'].' existing truants removed', 'message' );
		}
		else {
			$mainframe->enqueueMessage( 'There was a problem removing the truants from the list', 'error' );
		}
		
		$this->saveModel( 'truants' );
		$mainframe->redirect( ApotheosisLib::getActionLinkByName() );
	}
}
?>
