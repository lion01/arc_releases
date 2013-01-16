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
 * People Manager List Controller
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage People Manager
 * @since      0.1
 */
class PeopleControllerList extends PeopleController
{
	/**
	 * Default method
	 */
	function display()
	{
		$model = &$this->getModel( 'list' );
		$view =  &$this->getView( 'list', JRequest::getVar('format', 'html') );
		$view->setModel( $model, true );
		
		switch( JRequest::getVar('task') ) {
		default:
			$model->setPeople();
			break;
		}
		
		$view->display();
		$this->saveModel();
	}
	
	/**
	 * Method to accept search form input
	 */
	function search()
	{
		$model = &$this->getModel( 'list' );
		$view =  &$this->getView( 'list', 'html' );
		$view->setModel( $model, true );
		
		// collect the form inputs
		$requirements = array();
		$requirements['firstname'] = JRequest::getVar( 'firstname', false );
		$requirements['surname'] = JRequest::getVar( 'surname', false );
		$requirements['relOf'] = JRequest::getVar( 'rel_of', array() );
		
		// sanitise the requirements
		foreach( $requirements as $prop=>$val ) {
			if( is_array($val) ) {
				foreach( $val as $k=>$val2 ) {
					if( $val2 == '' ) {
						unset( $requirements[$prop][$k] );
					}
				}
				if( empty($requirements[$prop]) ) {
					unset( $requirements[$prop] );
				}
			}
			else {
				if( $val == '' ) {
					unset( $requirements[$prop] );
				}
			}
		}
		
		// set up the people objects
		$model->setPeople( $requirements );
		
		// display the results
		$view->display();
		$this->saveModel();
	}
}
?>