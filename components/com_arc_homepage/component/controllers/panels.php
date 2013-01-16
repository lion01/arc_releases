<?php
/**
 * @package     Arc
 * @subpackage  Homepage
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Homepage Panels Controller
 * 
 * @author     p.walker@wildern.hants.sch.uk
 * @package    Arc
 * @subpackage Homepage
 * @since      0.1
 */
class HomepageControllerPanels extends HomepageController
{
	/**
	 * Displays native homepage panels
	 */
	function display()
	{
		$scope = $viewFunc = JRequest::getWord('scope');
		$model = &$this->getModel( 'panels' );
		$model->link = $this->_getLink();
		$view  = &$this->getView ( 'panels', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		$view->link = $this->_getLink();
		
		switch( $scope ) {
		case( 'people' ):
			$pId = JRequest::getVar( 'pId', false );
			if( $pId == false ) {
				$u = ApotheosisLib::getUser();
				$pId = $u->id;
			}
			else {
				$pId = ApotheosisLib::getJUserId($pId);
			}
			
			$model->setPeopleList( $pId );
			break;
		
		case( 'clock' ):
		case( 'lotd' ):
		default:
			if( !method_exists( $view, $viewFunc ) ) {
				$viewFunc = 'clock';
			} 
			break;
		}
		$view->$viewFunc();
	}
}
?>