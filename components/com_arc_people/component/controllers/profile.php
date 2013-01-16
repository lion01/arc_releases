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

require_once( JPATH_SITE.DS.'components'.DS.'com_arc_people'.DS.'helpers'.DS.'data_access.php' );

/**
 * People Manager People Controller
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage People Manager
 * @since      0.1
 */
class PeopleControllerProfile extends PeopleController
{
	/**
	 * Default action.
	 * Calls appropriate display function
	 */
	function display()
	{
		$task = JRequest::getVar( 'task' );
		
		$model = &$this->getModel( 'profile' );
		$view =  &$this->getView( 'profile', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		$link = $this->_getLink( array() );
		$view->link = $link;
		
		$scope = JRequest::getVar( 'scope', false );
		
		switch( $scope ) {
		case( 'panel' ):
			$view->panel();
			break;
		
		case( 'panel_edit' ):
			$view->panelEdit();
			break;
			
		default:
			$view->display();
			break;
		}
		$this->saveModel( 'profile' );
	}
	
	/**
	 * Uses the data submitted via the search form to set the model's collection of profile(s)
	 * Then calls display() to show the retrieved profile(s)
	 */
	function search()
	{
		$pId = JRequest::getVar( 'pId', false );
		if( $pId !== false ) {
			$model = &$this->getModel( 'profile' );
			$model->setProfiles( array('pId'=>$pId), true );
		}
		
		$this->display();
	}
	
	function edit()
	{
		$pId = JRequest::getVar( 'pId', false );
		
		$model = &$this->getModel( 'profile' );
		$model->setProfiles( array('pId'=>$pId), true );
		$view =  &$this->getView( 'profile', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		$link = $this->_getLink(array());
		$view->link = $link;
		
		$view->edit();
		$this->saveModel( 'profile' );
	}
	
	
	function saveBiography()
	{
		$this->_save( 'biography' );
	}
	
	function saveLinks()
	{
		$this->_save( 'links' );
	}
	
	function saveSen()
	{
		$this->_save( 'sen' );
	}
	
	function _save( $what )
	{
		ob_start();
		global $mainframe;
		$this->link = $this->_getLink(array());
		$link = $this->link.'&tmpl=component';
		
		$model = &$this->getModel( 'profile' );
		$profile = &$model->getProfile();
		
		$scope = JRequest::getVar('scope', '');
		$pId = $profile->getId();
		
		switch( $what ) {
		case( 'biography' ):
			$biog = JRequest::getVar( 'biography' );
			$profile->setBiography( $biog );
			break;
		
		case( 'links' ):
			if( (($link = JRequest::getVar('link', false)) != false)
			 && ($link != 'http://') ) {
				$text = JRequest::getVar( 'link_text' );
				$text = ( $text != '' ) ? $text : $link;
				$profile->addLink( trim($text), trim($link), $scope );
			}
			elseif( ($file = JRequest::getVar( 'file', false )) != false ) {
				$text = $file;
				$profile->addLink( $text, $file, $scope, true );
			}
			elseif( ($file = JRequest::getVar( 'new_file', false, 'files' )) != false ) {
				$text = $file['name'];
				$file = ApotheosisPeopleData::saveFile( $pId, 'new_file' );
				if( $file !== false ) {
					$profile->addLink( $text, $file, $scope, true );
				}
			}
			
			// Remove those that need to be removed
			$del = JRequest::getVar( 'del_link', array() );
			foreach( $del as $id=>$on ) {
				$profile->removeLink( $id );
			}
			
			$link = $this->link.'&task=edit&pId='.$pId.'&scope='.$scope.'&tmpl=component';
			break;
		
		case( 'sen' );
			$data = JRequest::get( 'post' );
			$profile->setSen( $data );
			$link = ApotheosisLib::getActionLinkByName( 'eportfolio_edit_sen', array('people.arc_people'=>JRequest::getVar('pId')) );
			echo 'updated SEN information';
		}
		
		$profile->commit();
		$this->saveModel( 'profile' );
		
		$msg = ob_get_clean();
		if( !empty($msg) ) {
			$mainframe->enqueueMessage($msg);
		}
		$mainframe->redirect( $link );
	}
	
	function panelSave()
	{
		global $mainframe;
		$model = &$this->getModel( 'profile' );
		$model->savePanelSettings();
		
		// the following is for when we have no javascript,
		// otherwise form does the submit by JS, refreshes parent and closes interstitial
		$link = ApotheosisLib::getActionLinkByName( 'homepage_customise_edit' );
		$mainframe->enqueueMessage( 'Changes successfully saved.', 'message' );
		$mainframe->redirect( $link );
	}
}
?>