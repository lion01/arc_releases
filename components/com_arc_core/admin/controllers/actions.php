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
 * Core Admin Actions Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Core
 * @since      1.6
 */
class CoreAdminControllerActions extends CoreAdminController
{
	function add()
	{
		$model	= &$this->getModel( 'actions' );
		$view	= &$this->getView( 'actions', 'html' );
		
		$model->setAction( -1 );
		
		$view->setModel( $model, true );
		$view->add();
	}
	
	function edit()
	{
		$aId = JRequest::getVar( 'aId', false );
		if( $aId === false || !is_array($aId) ) {
			global $mainframe;
			$mainframe->enqueueMessage( 'Please make a selection from the list' );
			$this->display();
			return;
		}
		$aId = reset( array_keys($aId) );
		
		$model	= &$this->getModel( 'actions' );
		$view	= &$this->getView( 'actions', 'html' );
		
		$model->setAction( $aId );
		
		$view->setModel( $model, true );
		$view->edit();
	}
	
	function apply()
	{
		$model	= &$this->getModel( 'actions' );
		$this->_save( $model );
		
		$view	= &$this->getView( 'actions', 'html' );
		$view->setModel( $model, true );
		$view->edit();
	}
	
	function save()
	{
		$model	= &$this->getModel( 'actions' );
		$this->_save( $model );
		
		global $mainframe;
		$mainframe->enqueueMessage( 'Successfully Saved Actions' );
		$this->display();
	}
	
	function _save( $model )
	{
		$data = JRequest::getVar( 'act' );
		if( !isset($data['favourite']) ) { $data['favourite'] = array(); }
		
		$model->setAction( $data['id'] );
		$model->setData( $data );
		$model->save();
	}
	
	function remove()
	{
		$aId = JRequest::getVar( 'aId', false );
		if( $aId === false || !is_array($aId) ) {
			global $mainframe;
			$mainframe->enqueueMessage( 'Please make a selection from the list' );
			$this->display();
			return;
		}
		$aId = array_keys($aId);
		
		$model	= &$this->getModel( 'actions' );
		$view	= &$this->getView( 'actions', 'html' );
		
		$model->delete( $aId );
		
		global $mainframe;
		$mainframe->enqueueMessage( 'Successfully deleted actions' );
		$view->setModel( $model, true );
		$this->display();
	}
}
?>