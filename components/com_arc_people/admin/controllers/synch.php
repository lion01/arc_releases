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
 * People Admin Synch Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage People
 * @since      1.6
 */
class PeopleAdminControllerSynch extends PeopleAdminController
{
	/**
	 * Default method
	 */
	function display()
	{
		$model = &$this->getModel( 'synch' );
		$view = &$this->getView( 'synch', 'html' );
		
		$view->setModel( $model, true );
		$view->display();
	}
	
	/**
	 * Queues up the calls needed to get the data to import
	 */
	function import()
	{
		$arcParams = &JComponentHelper::getParams( 'com_arc_core' );
		$src = $arcParams->get('ext_source');
		$db = &JFactory::getDBO();
		$db->setQuery( 'SELECT '.$db->nameQuote('name').' FROM #__apoth_sys_data_sources WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $src ) );
		$srcName = $db->loadResult();
		
		$params = JRequest::getVar( 'params' );
		$params['_subclass'] = $src;
		$complete = $params['complete'];
		
		switch( $srcName ) {
		case( 'MIStA - SIMS' ):
			if( $params['ppl_data'] == 1 ) {
				$id = ApotheosisData::_( 'core.addImportBatch', 'people', 'importPeople', $params );
				if( $id === false ) {
					$r = false;
					break;
				}
				$r1 = ApotheosisData::_( 'core.addToImportQueue', $id, $src, 'arc_people_contacts', array('complete'=>$complete) );
				$r2 = ApotheosisData::_( 'core.addToImportQueue', $id, $src, 'arc_people_staff', array('complete'=>$complete) );
				$r3 = ApotheosisData::_( 'core.addToImportQueue', $id, $src, 'arc_people_staff_future', array('complete'=>$complete) );
				$r4 = ApotheosisData::_( 'core.addToImportQueue', $id, $src, 'arc_people_pupils', array('complete'=>$complete) );
				$r = ($r1 && $r2 && $r3 && $r4);
				
				// pause to datestamp separate the import jobs
				sleep( 1 );
			}
			
			if( $params['ppl_relation'] == 1 ) {
				$id = ApotheosisData::_( 'core.addImportBatch', 'people', 'importRelations', $params );
				if( $id === false ) {
					$r = false;
					break;
				}
				$r1 = ApotheosisData::_( 'core.addToImportQueue', $id, $src, 'arc_people_relationships', array('complete'=>$complete) );
				$r = $r1;
				
				// pause to datestamp separate the import jobs
				sleep( 1 );
			}
			
			if( $params['ppl_photo'] == 1 ) {
				$id = ApotheosisData::_( 'core.addImportBatch', 'people', 'importPhotos', $params );
				if( $id === false ) {
					$r = false;
					break;
				}
				$r1 = ApotheosisData::_( 'core.addToImportQueue', $id, $src, 'arc_people_staff_photos', array('complete'=>$complete) );
				$r2 = ApotheosisData::_( 'core.addToImportQueue', $id, $src, 'arc_people_pupil_photos', array('complete'=>$complete) );
				$r = ( $r1 && $r2 );
			}
			break;
		}
		
		global $mainframe;
		$mainframe->redirect( 'index.php?option=com_arc_core&view=synch', 'Import jobs added' );
	}
}
?>