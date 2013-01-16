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
 * People Sync Helper
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage People
 * @since      1.6.5
 */
class ArcSync_People extends ArcSync
{
	/**
	 * Import all data about people, including addresses
	 *
	 * @param array $params  Values from the form used to originally add the job
	 * @param array $jobs  Array of jobs. Each job is an array with all that job's settings
	 */
	function importPeople( $params, $jobs )
	{
		$tablesArray = array( '#__apoth_ppl_addresses', '#__apoth_ppl_people', '#__users', '#__core_acl_acro' );
		ApotheosisLibDb::disableDBChecks( $tablesArray );
		
		timer( 'importing people' );
		$this->_loadDateSeries();
		
		// People and Address data for:
		
		// - contacts
		$j = $this->jobSearch( array('call'=>'arc_people_contacts'), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->_srcId = $jobs[$j]['src'];
		$this->_importUsers( $xml, 'arc_people_contacts' );
		$xml->free();
		
		// - staff
		$j = $this->jobSearch( array('call'=>'arc_people_staff'), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->_srcId = $jobs[$j]['src'];
		$this->_importUsers( $xml, 'arc_people_staff' );
		$xml->free();
		
		// - staff future
		$j = $this->jobSearch( array('call'=>'arc_people_staff_future'), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->_srcId = $jobs[$j]['src'];
		$this->_importUsers( $xml, 'arc_people_staff_future' );
		$xml->free();
		
		// - pupils
		$j = $this->jobSearch( array('call'=>'arc_people_pupils'), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->_srcId = $jobs[$j]['src'];
		$this->_importUsers( $xml, 'arc_people_pupils' );
		$xml->free();
		
		$this->_commitDateSeries();
		ApotheosisLibDb::enableDBChecks( $tablesArray );
		timer( 'imported people' );
		
		return true;
	}
	
	/**
	 * Import all relationship definitions to link people together
	 */
	function importRelations( $params, $jobs )
	{
		$tablesArray = array( '#__apoth_ppl_relations', '#__apoth_ppl_relation_types' );
		ApotheosisLibDb::disableDBChecks( $tablesArray );
		
		timer( 'importing relations' );
		$this->_complete = (bool)$params['complete'];
		
		$j = $this->jobSearch( array('call'=>'arc_people_relationships'), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->_srcId = $jobs[$j]['src'];
		$this->_importRelations( $xml );
		$xml->free();
		
		ApotheosisLibDb::enableDBChecks( $tablesArray );
		timer( 'imported relations' );
		
		return true;
	}
	
	function importPhotos( $params, $jobs )
	{
		$tablesArray = array( '#__apoth_ppl_addresses', '#__apoth_ppl_people', '#__users', '#__core_acl_acro' );
		ApotheosisLibDb::disableDBChecks( $tablesArray );
		
		timer( 'importing photos' );
		
		// Photo data for:
		
		// - staff
		$j = $this->jobSearch( array('call'=>'arc_people_staff_photos'), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->_srcId = $jobs[$j]['src'];
		$this->_importPhotos( $xml );
		$xml->free();
		
		// - pupils
		$j = $this->jobSearch( array('call'=>'arc_people_pupil_photos'), $jobs );
		$xml = $this->_loadReport( $jobs[$j], 'progressive' );
		$this->_srcId = $jobs[$j]['src'];
		$this->_importPhotos( $xml );
		$xml->free();
		
		ApotheosisLibDb::enableDBChecks( $tablesArray );
		timer( 'imported photos' );
		
		return true;
	}
	
	function _rawToObjects( $rpt, $r )
	{
		if( empty($r) ) {
			return null;
		}
		
		switch($rpt) {
		case( 'arc_people_contacts' ):
		case( 'arc_people_staff' ):
		case( 'arc_people_staff_future' ):
		case( 'arc_people_pupils' ):
			$this->_setExistingPeople();
			$arcId = $r->childData( 'arc_person_id' );
			if( !is_null($arcId) && !isset($this->existingPeople[$arcId]) ) {
				// the Arc ID is not from this source so set a flag to ignore this person
				$retVal = false;
			}
			else {
				$obj = new stdClass();
				$obj2 = new stdClass();
				
				$obj->id                   = $arcId;
				$obj->src                  = $this->_srcId;
				$obj->ext_person_id        = $r->childData( 'primary_id' );
				$obj->dob                  = $r->childData( 'date_of_birth' );  
				$obj->upn                  = $r->childData( 'upn' );
				$obj->title                = $r->childData( 'title' );
				$obj->firstname            = $r->childData( 'legal_forename' );
				$obj->middlenames          = $r->childData( 'middle_names' );
				$obj->surname              = $r->childData( 'legal_surname' );
				$obj->gender               = $r->childData( 'gender' );
				$obj->preferred_firstname  = $r->childData( 'preferred_forename' );
				$obj->preferred_surname    = $r->childData( 'preferred_surname' );
				$obj->_newId               = false;
				
				$obj2->number              = $r->childData( 'housenumber' );
				$obj2->src                 = $this->_srcId;
				$obj2->name                = $r->childData( 'housename' );
				$obj2->apartment           = $r->childData( 'apartment' );
				$obj2->street              = $r->childData( 'street' );
				$obj2->district            = $r->childData( 'district' );
				$obj2->town                = $r->childData( 'town' );
				$obj2->county              = $r->childData( 'county' );
				$obj2->administrative_area = null;
				$obj2->postcode            = $r->childData( 'postcode' );
				
				$this->_setExternalPeople();
				$this->_setExistingAddresses();
				
				// clean up the data
				$user = $this->_cleanUser( $obj );
				$address = $this->_cleanAddress( $obj2 );
				
				// Determine user ID
				if( is_null($user->id) ) {
					if( is_null($user->ext_person_id) || !isset($this->externalPeople[$user->ext_person_id]) ) {
						// we have been given no ext id or one we have not seen before
						// so this is a new person
						$user->id = $this->_getUserId( $user );
						$user->_newId = true;
					}
					// fetch the existing Arc ID based on the provided ext id
					else {
						$user->id = $this->externalPeople[$user->ext_person_id];
					}
				}
				
				// add address id to both objects
				$user->address_id = $address->id = $this->_getAddressId( $address );
				
				$retVal['user'] = $user;
				$retVal['address'] = $address;
			}
			break;
		
		case( 'arc_people_relationships' ):
			$arcId = $r->childData( 'arc_person_id' );
			$rawIds = $r->childData( 'multiple_id' );
			$ids = explode( ',', $rawIds );
			
			if( !is_null($arcId) || (!is_null($rawIds) && !empty($ids[0]) && ($ids[0] != ' ') && ($ids[1] != 0)) ) {
				$this->_setRelationTypes();
				$this->_setExternalPeople();
				$this->_setExistingPeopleComposite();
				$this->_setExistingRelations();
				
				$pData = array();
				$pData['surname']    = $r->childData( 'p_surname' );
				$pData['firstname']  = $r->childData( 'p_forename' );
				$pData['dob']        = $this->_cleanDate( $r->childData('dob') );
				$pData['upn']        = $r->childData( 'upn' );
				$pData['postcode']   = $r->childData( 'postcode' );
				$compId = strtolower( implode( '~', $pData ) );
				
				$obj = new stdClass();
				$obj->pupil_id         = ( isset($this->existingPeopleComp[$compId]) ? $this->existingPeopleComp[$compId] : null );
				$obj->relation_id      = !is_null( $arcId ) ? $arcId : ( isset( $this->externalPeople[$ids[0]] ) ? $this->externalPeople[$ids[0]] : null );
				$obj->relation_type_id = intVal( $this->_rTypes[strtolower($r->childData('relationship'))]->id );
				$obj->src              = $this->_srcId;
				$obj->parental         = ( $r->childData('parental')       == 'T' );
				$obj->legal_order      = ( $r->childData('court_order')    == 'T' );
				$obj->correspondence   = ( $r->childData('correspondence') == 'T' );
				$obj->reports          = ( $r->childData('pupil_report')   == 'T' );
				$obj->valid_from       = date( 'Y-m-d H:i:s' );
				$obj->valid_to         = null;
				$obj->_id              = $obj->pupil_id.','.$obj->relation_id.','.$obj->relation_type_id;
				$obj->_newId           = false;
				
				if( !isset($this->existingRelations[$obj->_id]) ) {
					$obj->_newId = true;
				}
			}
			
			// check it all went ok before setting return value
			if( !isset( $obj ) || is_null( $obj->pupil_id ) || is_null( $obj->relation_id ) || ( $obj->relation_type_id == 0 ) ) {
				$retVal = null;
			}
			else {
				$retVal = $obj;
			}
			break;
		
		case( 'arc_people_any_photos' ):
			$arcId = $r->childData( 'arc_person_id' );
			$extId = $r->childData( 'primary_id' );
			
			if( !is_null($arcId) || !is_null($extId) ) {
				$obj = new stdClass();
				$obj->person_id = $arcId;
				$obj->photo = $r->childData( 'photo' );
				$obj->_hasPhoto = ( $r->childData('photo_available') == 'T' );
				$obj->_newId = false;
				
				// Determine user ID
				if( is_null($obj->person_id) && isset($this->externalPeople[$extId]) ) {
					$obj->person_id = $this->externalPeople[$extId];
				}
				
				// Determine if this is a new photo
				if( !isset($this->existingPhotos[$obj->person_id]) ) {
					$obj->_newId = true;
				}
			}
			
			// check it all went ok before setting return value
			if( !isset( $obj ) || is_null( $obj->person_id ) ) {
				$retVal = null;
			}
			else {
				$retVal = $obj;
			}
		}
		
		return $retVal;
	}
	
	function _setExternalPeople( $refresh = false, $onlyOurs = true )
	{
		if( $refresh ) {
			unset( $this->externalPeople );
		}
		
		if( !isset($this->externalPeople) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('ext_person_id')
				."\n".'FROM '.$db->nameQuote('#__apoth_ppl_people');
			
			if( $onlyOurs ) {
				$query .= "\n".'WHERE '.$db->nameQuote('src').' = '.$db->Quote($this->_srcId);
			}
			
			$db->setQuery( $query );
			$this->externalPeople = $db->loadAssocList( 'ext_person_id' );
			
			if( is_null($this->externalPeople) ) { $this->externalPeople = array(); } // to avoid errors
			foreach( $this->externalPeople as $k=>$v ) {
				$this->externalPeople[$k] = $v['id'];
			}
		}
	}
	
	function _setExistingPeople( $refresh = false, $onlyOurs = true )
	{
		if( $refresh ) {
			unset( $this->existingPeople );
		}
		
		if( !isset($this->existingPeople) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('ext_person_id')
				."\n".'FROM '.$db->nameQuote('#__apoth_ppl_people');
			
			if( $onlyOurs ) {
				$query .= "\n".'WHERE '.$db->nameQuote('src').' = '.$db->Quote($this->_srcId);
			}
			
			$db->setQuery( $query );
			$this->existingPeople = $db->loadAssocList( 'id' );
			
			if( is_null($this->existingPeople) ) { $this->existingPeople = array(); } // to avoid errors
			foreach( $this->existingPeople as $k=>$v ) {
				$this->existingPeople[$k] = $v['ext_person_id'];
			}
		}
	}
	
	function _setExistingPeopleComposite()
	{
		if( !isset($this->existingPeopleComp) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT '.$db->nameQuote('p').'.'.$db->nameQuote('id')
				.', CONCAT_WS( '.$db->Quote('~').', '
				.'IFNULL( '.$db->nameQuote('p').'.'.$db->nameQuote('surname').'   , "" ),'
				.'IFNULL( '.$db->nameQuote('p').'.'.$db->nameQuote('firstname').' , "" ),'
				.'IFNULL( '.$db->nameQuote('p').'.'.$db->nameQuote('dob').'       , "" ),'
				.'IFNULL( '.$db->nameQuote('p').'.'.$db->nameQuote('upn').'       , "" ),'
				.'IFNULL( '.$db->nameQuote('a').'.'.$db->nameQuote('postcode').'  , "" )'
				.' ) AS '.$db->nameQuote('comp_id')
				."\n".'FROM '.$db->nameQuote('#__apoth_ppl_people').' AS '.$db->nameQuote('p')
				."\n".'LEFT JOIN '.$db->nameQuote('#__apoth_ppl_addresses').' AS '.$db->nameQuote('a')
				."\n".'  ON '.$db->nameQuote('a').'.'.$db->nameQuote('id').' = '.$db->nameQuote('p').'.'.$db->nameQuote('address_id');
			$db->setQuery( $query );
			$this->existingPeopleComp = $db->loadAssocList( 'comp_id' );
			
			if( is_null($this->existingPeopleComp) ) { $this->existingPeopleComp = array(); } // to avoid errors
			foreach( $this->existingPeopleComp as $k=>$v ) {
				$this->existingPeopleComp[strtolower( $k )] = $v['id'];
			}
		}
	}
	
	function _setExistingAddresses()
	{
		if( !isset($this->existingAddresses) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT '.$db->nameQuote('id')
				."\n".'FROM '.$db->nameQuote('#__apoth_ppl_addresses')
				."\n".'WHERE '.$db->nameQuote('src').' = '.$db->Quote($this->_srcId);
			$db->setQuery( $query );
			$tmp = $db->loadResultArray();
			
			foreach( $tmp as $id ) {
				$this->existingAddresses[$id] = $id;
			}
			
			if( !isset($this->existingAddresses) ) { $this->existingAddresses = array(); } // to avoid errors
		}
	}
	
	function _setRelationTypes()
	{
		if( !isset( $this->_rTypes ) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT '.$db->nameQuote('id').', LCASE('.$db->nameQuote('description').') AS '.$db->nameQuote('description')
				."\n".'FROM '.$db->nameQuote('#__apoth_ppl_relation_tree')
				."\n".'WHERE '.$db->nameQuote('ext_type').' = '.$db->Quote('type');
			$db->setQuery( $query );
			
			$this->_rTypes = $db->loadObjectList( 'description' );
		}
	}
	
	function _setExistingRelations()
	{
		if( !isset($this->existingRelations) ) {
			$db = &JFactory::getDBO();
			
			$dbR = $db->nameQuote('r');
			$dbP = $db->nameQuote('p');
			$dbPR = $db->nameQuote('pr');
			$dbId = $db->nameQuote('id');
			$query = 'SELECT '.$dbR.'.'.$db->nameQuote('pupil_id')
				.', '.$dbR.'.'.$db->nameQuote('relation_id')
				.', '.$dbR.'.'.$db->nameQuote('relation_type_id')
				.', CONCAT('.$dbP.'.'.$dbId
					.', '.$db->Quote(',')
					.', '.$dbPR.'.'.$dbId
					.', '.$db->Quote(',')
					.', '.$dbR.'.'.$db->nameQuote('relation_type_id')
				.') AS '.$db->nameQuote('id')
				."\n".'FROM '.$db->nameQuote('#__apoth_ppl_relations').' AS '.$dbR
				."\n".'INNER JOIN '.$db->nameQuote('#__apoth_ppl_people').' AS '.$dbP
				."\n".'   ON '.$dbP.'.'.$dbId.' = '.$dbR.'.'.$db->nameQuote('pupil_id')
				."\n".'INNER JOIN '.$db->nameQuote('#__apoth_ppl_people').' AS '.$dbPR
				."\n".'   ON '.$dbPR.'.'.$dbId.' = '.$dbR.'.'.$db->nameQuote('relation_id')
				."\n".'WHERE '.ApotheosisLibDb::dateCheckSql( $dbR.'.'.$db->nameQuote('valid_from'), $dbR.'.'.$db->nameQuote('valid_to'), date('Y-m-d H:i:s'), date('Y-m-d H:i:s') );
			$db->setQuery( $query );
			$this->existingRelations = $db->loadAssocList( 'id' );
			
			if( is_null($this->existingRelations) ) { $this->existingRelations = array(); } // to avoid errors
		}
	}
	
	
	function _setExistingPhotos()
	{
		if( !isset($this->existingPhotos) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT '.$db->nameQuote('person_id')
				."\n".'FROM '.$db->nameQuote('#__apoth_ppl_photos');
			$db->setQuery( $query );
			$tmp = $db->loadResultArray();
			
			foreach( $tmp as $id ) {
				$this->existingPhotos[$id] = $id;
			}
			
			if( !isset($this->existingPhotos) ) { $this->existingPhotos = array(); } // to avoid errors
		}
	}
	
	/**
	 * Columns required for SIMS XML report creation from an uploaded CSV
	 * 
	 * @param string $report  The report name
	 * @return array $columns  Array of column names as keys with description as value
	 */
	function CSVcolumns( $report )
	{
		$columns = array();
		
		switch( $report ) {
		case( 'arc_people_contacts' ):
			$columns['Arc Person ID'] = 'Arc Person ID or blank for a new user';
			$columns['Unique ID'] = 'User generated unique person ID if available';
			$columns['Title'] = 'Mr, Mrs, Dr etc';
			$columns['Legal Surname'] = 'Legal surname';
			$columns['Legal Forename'] = 'Legal forename';
			$columns['Gender'] = 'M or F';
// 			$columns['HouseNumber'] = 'House number';
// 			$columns['HouseName'] = 'House name';
// 			$columns['Apartment'] = 'Apartment';
// 			$columns['Street'] = 'Street';
// 			$columns['District'] = 'District';
// 			$columns['Town'] = 'Town';
// 			$columns['County'] = 'County';
// 			$columns['Postcode'] = 'Postcode';
			break;
		
		case( 'arc_people_staff' ):
		case( 'arc_people_staff_future' ):
			$columns['Arc Person ID'] = 'Arc Person ID or blank for a new user';
			$columns['Unique ID'] = 'User generated unique person ID if available';
			$columns['Date of Birth'] = 'Date of birth as yyyy-mm-dd';
			$columns['Title'] = 'Mr, Mrs, Dr etc';
			$columns['Legal Forename'] = 'Legal forename';
			$columns['Middle Name(s)'] = 'Middle name(s)';
			$columns['Legal Surname'] = 'Legal surname';
			$columns['Gender'] = 'M or F';
			$columns['Preferred Forename'] = 'Preferred forename';
			$columns['Preferred Surname'] = 'Preferred surname';
// 			$columns['HouseNumber'] = 'House number';
// 			$columns['HouseName'] = 'House name';
// 			$columns['Apartment'] = 'Apartment';
// 			$columns['Street'] = 'Street';
// 			$columns['District'] = 'District';
// 			$columns['Town'] = 'Town';
// 			$columns['County'] = 'County';
			$columns['Postcode'] = 'Postcode';
			break;
		
		case( 'arc_people_pupils' ):
			$columns['Arc Person ID'] = 'Arc Person ID or blank for a new user';
			$columns['Unique ID'] = 'User generated unique person ID if available';
			$columns['Date of birth'] = 'Date of birth as yyyy-mm-dd';
			$columns['Legal Forename'] = 'Legal forename';
			$columns['Middle name(s)'] = 'Middle name(s)';
			$columns['Legal Surname'] = 'Legal surname';
			$columns['Gender'] = 'M or F';
			$columns['Preferred Forename'] = 'Preferred forename';
			$columns['Preferred Surname'] = 'Preferred surname';
			$columns['UPN'] = 'Unique/Universal Pupil Number';
// 			$columns['HouseNumber'] = 'House number';
// 			$columns['HouseName'] = 'House name';
// 			$columns['Apartment'] = 'Apartment';
// 			$columns['Street'] = 'Street';
// 			$columns['District'] = 'District';
// 			$columns['Town'] = 'Town';
// 			$columns['County'] = 'County';
			$columns['Postcode'] = 'Postcode';
			break;
		
		case( 'arc_people_relationships' ):
			$columns['Arc Person ID'] = 'Arc Person ID';
			$columns['Unique ID'] = 'User generated unique person ID if available';
			$columns['Relationship'] = 'Mother, Father etc';
			$columns['Parental'] = 'Parental rights (T for true, F for false)';
			$columns['Correspondence'] = 'Correspondence rights (T for true, F for false)';
			$columns['Pupil report'] = 'Pupil report rights (T for true, F for false)';
			$columns['Court order'] = 'Court order restricting child access (T for true, F for false)';
			$columns['P_Surname'] = 'Legal surname of the student';
			$columns['P_Forename'] = 'Preferred forename of the student';
			$columns['DOB'] = 'Date of birth of the student as yyyy-mm-dd';
			$columns['UPN'] = 'Unique/Universal Pupil Number';
			$columns['Postcode'] = 'Postcode of the student';
			break;
		}
		
		return $columns;
	}
	
	
	// #####  Private functions to achieve the primary goals (the public functions above)  #####
	
	function _importUsers( $xml, $report )
	{
		timer( 'importing users and addresses' );
		$insertAddressVals = array();
		$insertUserVals = array();
		$updateUserVals = array();
		
		while( ($data = $xml->next('record')) !== false ) {
			$data = $this->_rawToObjects( $report, $data );
			if( $data ) {
				$user = $data['user'];
				$address = $data['address'];
				
				// address details first
				if( !isset($this->existingAddresses[$user->address_id]) && !array_key_exists($user->address_id, $insertAddressVals) ) {
					$insertAddressVals[$user->address_id] = $address;
				}
				
				// determine if user is new and unset temp _newId property
				// then add the user to the relevant array (insertUserVals or updateUserVals)
				$isNew = $user->_newId;
				unset( $user->_newId );
				
				if( $isNew ) {
					$insertUserVals[] = $user;
				}
				else {
					unset( $user->ext_person_id );
					$updateUserVals[] = $user;
				}
			}
		}
		
		// store address details in db
		timer( 'ready to write addresses to db' );
		ApotheosisLibDb::insertList( '#__apoth_ppl_addresses', $insertAddressVals );
		timer( 'imported addresses ('.count($insertAddressVals).' inserts)' );
		
		// store user details in db
		timer( 'ready to write users to db' );
		ApotheosisLibDb::insertList( '#__apoth_ppl_people', $insertUserVals );
		ApotheosisLibDb::updateList( '#__apoth_ppl_people', $updateUserVals, array('id') );
		timer( 'imported users ('.count($insertUserVals).' inserts, '.count($updateUserVals).' updates)' );
	}
	
	function _importRelations( $xml )
	{
		timer('importing relations data');
		
		$insertVals = array();
		$insertValsCount = 0;
		$updateVals = array();
		$updateValsCount = 0;
		$updateVals2 = array();
		$updateVals2Count = 0;
		
		$this->_setExternalPeople( true, false );
		while( ($relation = $xml->next('record')) !== false ) {
			$relation = $this->_rawToObjects( 'arc_people_relationships', $relation );
			
			if( !is_null($relation) ) {
				// determine if relationship is new and unset temp _newId property
				// then add the relationship to the relevant array (insertVals or updateVals)
				$isNew = $relation->_newId;
				unset( $relation->_newId );
				$id = $relation->_id;
				unset( $relation->_id );
				
				if( $isNew ) {
					$insertVals[] = $relation;
				}
				else {
					$this->existingRelations[$id]['used'] = true;
					unset( $relation->valid_from ); // leave the existing start date intact rather than resetting to now
					$updateVals[] = $relation;
				}
			}
			
			// if the amount of data to put into db is getting big then enter this chunk now
			if( ($insertNum = count($insertVals)) >= 1000 ) {
				ApotheosisLibDb::insertList( '#__apoth_ppl_relations', $insertVals );
				$insertVals = array();
				$insertValsCount = $insertValsCount + $insertNum;
			}
			if( ($updateNum = count($updateVals)) >= 1000 ) {
				ApotheosisLibDb::updateList( '#__apoth_ppl_relations', $updateVals,  array('pupil_id', 'relation_id', 'relation_type_id') );
				$updateVals = array();
				$updateValsCount = $updateValsCount + $updateNum;
			}
		}
		
		// Mark as no-longer valid any existing entries that weren't updated if we have a complete report
		if( $this->_complete ) {
			$now = date( 'Y-m-d H:i:s' );
			foreach( $this->existingRelations as $k=>$v ) {
				if( !isset($v['used']) ) {
					$t = new stdClass();
					$t->pupil_id = $v['pupil_id'];
					$t->relation_id = $v['relation_id'];
					$t->relation_type_id = $v['relation_type_id'];
					$t->src = $this->_srcId;
					$t->valid_to = $now;
					$updateVals2[] = $t;
				}
				if( ($update2Num = count($updateVals2)) >= 1000 ) {
					ApotheosisLibDb::updateList( '#__apoth_ppl_relations', $updateVals2, array('pupil_id', 'relation_id', 'relation_type_id') );
					$updateVals2 = array();
					$updateVals2Count = $updateVals2Count + $update2Num;
				}
			}
		}
		
		// complete any remaining db operations
		ApotheosisLibDb::insertList( '#__apoth_ppl_relations', $insertVals );
		ApotheosisLibDb::updateList( '#__apoth_ppl_relations', $updateVals,  array('pupil_id', 'relation_id', 'relation_type_id') );
		ApotheosisLibDb::updateList( '#__apoth_ppl_relations', $updateVals2, array('pupil_id', 'relation_id', 'relation_type_id') );
		timer('imported relations data ('.($insertValsCount + count($insertVals)).' inserts, '.($updateValsCount + count($updateVals)).' updates, '.($updateVals2Count + count($updateVals2)).' terminations)');
		
		return true;
	}
	
	function _importPhotos( $xml )
	{
		$insertVals = array();
		$updateVals = array();
		$deleteVals = array();
		
		$insertValsCount = $updateValsCount = $deleteValsCount = 0;
		
		$this->_setExternalPeople( true, false );
		$this->_setExistingPhotos();
		while( ($data = $xml->next('record')) !== false ) {
			$photo = $this->_rawToObjects( 'arc_people_any_photos', $data );
			if( $photo ) {
				// determine if photo is new and unset temp _newId property
				// then add the photo to the relevant array
				$isNew = $photo->_newId;
				unset( $photo->_newId );
				$hasPhoto = $photo->_hasPhoto;
				unset( $photo->_hasPhoto );
				
				// convert the given image to a base64-encoded png for storage
				if( $hasPhoto ) {
					$imgData = base64_decode( $photo->photo );
					$im = @imagecreatefromstring( $imgData );
					
					// maybe it's a bmp image?
					if( $im == false ) {
						echo $photo->person_id.' trying as a bmp<br />';
						$config = &JFactory::getConfig();
						$dirName = $config->getValue('config.tmp_path');
						$tmpName = tempnam( $dirName, 'photo_'.time().'_' );
						file_put_contents( $tmpName, $imgData );
						$im = imagecreatefrombmp( $tmpName );
						unlink( $tmpName );
					}
					else {
						echo $photo->person_id.' was fine<br />';
					}
					
					// if the image could not be interpreted, don't let it get into the db
					if( $im == false ) {
						echo '<b>'.$photo->person_id.' was not even a bmp</b><br />';
						$photo->photo = null;
						$hasPhoto = false;
					}
					else {
						ob_start();
						imagepng( $im );
						$photo->photo = base64_encode( ob_get_clean() );
					}
				}
				
				if( $isNew ) {
					if( $hasPhoto ) {
						$insertVals[] = $photo;
					}
				}
				else {
					if( $hasPhoto ) {
						$updateVals[] = $photo;
					}
					else {
						unset( $photo->photo );
						$deleteVals[] = $photo;
					}
				}
				
			}
			
			// send data to db if the list is getting a bit long
			if( ($insertNum = count($insertVals)) >= 10 ) {
				ApotheosisLibDb::insertList( '#__apoth_ppl_photos', $insertVals );
				$insertVals = array();
				$insertValsCount += $insertNum;
			}
			if( ($updateNum = count($updateVals)) >= 10 ) {
				ApotheosisLibDb::updateList( '#__apoth_ppl_photos', $updateVals, array( 'person_id' ) );
				$updateVals = array();
				$updateValsCount += $updateNum;
			}
			if( ($deleteNum = count($deleteVals)) >= 1000 ) {
				ApotheosisLibDb::deleteList( '#__apoth_ppl_photos', $deleteVals );
				$deleteVals = array();
				$deleteValsCount += $deleteNum;
			}
		}
		
		// store / update / delete photos in db
		ApotheosisLibDb::insertList( '#__apoth_ppl_photos', $insertVals );
		ApotheosisLibDb::updateList( '#__apoth_ppl_photos', $updateVals, array( 'person_id' ) );
		ApotheosisLibDb::deleteList( '#__apoth_ppl_photos', $deleteVals );
		
		timer( 'imported photos ('.$insertValsCount.' inserts, '.$updateValsCount.' updates, '.$deleteValsCount.' deletes)' );
	}
	
	/**
	 * Cleans up an address to make it safe and valid enough to include in our database
	 */
	function _cleanAddress( $address )
	{
		// clean up the data
		// ... put numbers that have been put in street description into house number if we don't already have a house number
		if( empty($address->number) ) {
			$tmp = array();
			preg_match( '/^(\\d+\\-?\\d*)\\s*([^\\d].*)$/s', $address->street, $tmp );
			if( isset($tmp[1]) && (strlen($tmp[1]) > 0) ) { // there's a number at the start, so pick it out
				$address->number = $tmp[1];
				$address->street = $tmp[2];
			}
		}
		
		// ... apartment numbers first
		if( !is_numeric($address->apartment) ) {
			$apt = trim( str_replace(array('flat', 'apartment', 'apt'), '', strtolower($address->apartment)) );
			if( is_numeric($apt) ) {
				$address->apartment = $apt;
			}
			else {
				$address->name = ( empty($address->name) ? '' : $address->name.', ' ).$address->apartment;
				$address->apartment = NULL;
			}
		}
		
		// ... then house numbers (and suffixes / ranges)
		$tmp = array();
		preg_match( '/(\\d*)(\\-\\d+)?\\s*([^\\d]*)/', $address->number, $tmp);
		if( strlen($tmp[0]) == 0 ) { // no house number
			$address->number = NULL;
		}
		elseif( strlen($tmp[1]) == 0 ) { // non-numeric, so put it in the name
			$address->name = $address->number.( empty($address->name) ? '' : ' '.$address->name );
			$address->number = NULL;
		}
		else { // numeric part goes in number
			$address->number = $tmp[1];
			// deal with any non-numeric part
			// 2 chars is the maximum length of a number suffix
			if( strlen($tmp[2]) > 0 ) { // we have a range then
				$address->number_range = substr($tmp[2], 1);
			}
			elseif( strlen($tmp[3]) > 2 ) {
				$address->name = $tmp[2].( empty($address->name) ? '' : ' '.$address->name );
			}
			elseif( strlen($tmp[3]) > 0 ) {
				$address->number_suffix = $tmp[2];
			}
		}
		
		// make blanks be null
		if( empty($address->number)        ) { $address->number        = NULL; }
		if( empty($address->number_range)  ) { $address->number_range  = NULL; }
		if( empty($address->number_suffix) ) { $address->number_suffix = NULL; }
		if( empty($address->apartment)     ) { $address->apartment     = NULL; }
		if( empty($address->name)          ) { $address->name          = NULL; }
		// and fill in default country
		if( empty($address->country)       ) { $address->country       = 'GB'; }
		
		return $address;
	}
	
	/**
	 * Generates an Arc id for the given address
	 */
	function _getAddressid( $address )
	{
		// make the unique id by md5-ing all the address data and appending the postcode
		$slug = $address->number
		       .$address->number_range
		       .$address->number_suffix
		       .$address->apartment
		       .$address->name
		       .$address->street
		       .$address->district
		       .$address->town
		       .$address->postcode
		       .$address->country;
		return md5($slug).$address->postcode;
	}
	
	/**
	 * Cleans up a person's data to make it safe and valid enough to include in our database
	 */
	function _cleanUser( $user )
	{
		// fill in missing data and format date fields
		$user->firstname  = ucfirst( strtolower($user->firstname) );
		$user->middlenames = ucfirst( strtolower($user->middlenames) );
		$user->surname = ucfirst( strtolower($user->surname) );
		$user->dob = $this->_cleanDate( $user->dob );
		
		return $user;
	}
	
	
	function _loadDateSeries()
	{
		if( !isset( $this->_dates ) ) {
			// get the dob series out from the database
			$db = &JFactory::getDBO();
			$db->setQuery( 'LOCK TABLES '.$db->nameQuote('#__apoth_ppl_date_series') );
			$db->query();
			$db->setQuery( 'SELECT * FROM '.$db->nameQuote('#__apoth_ppl_date_series') );
			$this->_dates = $db->loadObjectList('date');
			$this->_newDates = array();
		}
	}
	
	function _commitDateSeries()
	{
		if( !isset( $this->_dates ) ) {
			return false;
		}
		ApotheosisLibDb::insertList( '#__apoth_ppl_date_series', $this->_newDates);
		ApotheosisLibDb::updateList( '#__apoth_ppl_date_series', $this->_dates, array('date') );
		
		$db = &JFactory::getDBO();
		$db->setQuery( 'UNLOCK TABLES' );
		$db->query();
	}
	
	/**
	 * Generates an Arc id for the given person who is to be inserted
	 * MUST be preceeded by a call to $this->_loadDateSeries(),
	 * and followed by a call to $this->_commitDateSeries()
	 */
	function _getUserId( $user )
	{
		if( !is_object( $user ) ) {
			return false;
		}
		$noDob = ( is_null($user->dob) || ($user->dob == '0000-00-00') );
		$dob = ( $noDob ? '0000-00-00' : $user->dob );
		
		if( isset($this->_dates[$dob]) ) {
			$num = ++$this->_dates[$dob]->number;
		}
		else {
			if( !isset($this->_newDates[$dob]) ) {
				$tmp = new stdClass();
				$tmp->date = $dob;
				$tmp->number = 0;
				$this->_newDates[$dob] = &$tmp;
				unset($tmp);
			}
			$num = ++$this->_newDates[$dob]->number;
		}
		
		// create the string for the Luhn Checknumber
		$serialLen = ( $noDob ? 8 : 4 );
		$serialNum = str_pad( $num, $serialLen, '0', STR_PAD_LEFT );
		
		$string = 'GB-'
			.( $noDob ? '0000' : str_replace( '-', '', $dob) ).'-'
			.$serialNum.'-';
		
		// add the check number and return
		return $string.ApotheosisLib::generateLuhn( $string, '-' );
	}
}


/**
 * Converts a .bmp (bitmap image) into a gd image for further processing
 * http://bytes.com/topic/php/answers/3033-there-bmp-support-gd
 * 
 * @param $src
 * @param $dest
 */
function ConvertBMP2GD($src, $dest = false) {
	if(!($src_f = fopen($src, "rb"))) {
		return false;
	}
	if(!($dest_f = fopen($dest, "wb"))) {
		return false;
	}
	$header = unpack("vtype/Vsize/v2reserved/Voffset", fread($src_f,14));
	$info = unpack("Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant",fread($src_f, 40));
	
	extract($info);
	extract($header);
	
	if($type != 0x4D42) { // signature "BM"
		return false;
	}
	
	$palette_size = $offset - 54;
	$ncolor = $palette_size / 4;
	$gd_header = "";
	// true-color vs. palette
	$gd_header .= ($palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF";
	$gd_header .= pack("n2", $width, $height);
	$gd_header .= ($palette_size == 0) ? "\x01" : "\x00";
	if($palette_size) {
	$gd_header .= pack("n", $ncolor);
	}
	// no transparency
	$gd_header .= "\xFF\xFF\xFF\xFF";
	
	fwrite($dest_f, $gd_header);
	
	if($palette_size) {
	$palette = fread($src_f, $palette_size);
	$gd_palette = "";
	$j = 0;
	while($j < $palette_size) {
	$b = $palette{$j++};
	$g = $palette{$j++};
	$r = $palette{$j++};
	$a = $palette{$j++};
	$gd_palette .= "$r$g$b$a";
	}
	$gd_palette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);
	fwrite($dest_f, $gd_palette);
	}
	
	$scan_line_size = (($bits * $width) + 7) >> 3;
	$scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size &
	0x03) : 0;
	
	for($i = 0, $l = $height - 1; $i < $height; $i++, $l--) {
	// BMP stores scan lines starting from bottom
	fseek($src_f, $offset + (($scan_line_size + $scan_line_align) *
	$l));
	$scan_line = fread($src_f, $scan_line_size);
	if($bits == 24) {
	$gd_scan_line = "";
	$j = 0;
	while($j < $scan_line_size) {
	$b = $scan_line{$j++};
	$g = $scan_line{$j++};
	$r = $scan_line{$j++};
	$gd_scan_line .= "\x00$r$g$b";
	}
	}
	else if($bits == 8) {
	$gd_scan_line = $scan_line;
	}
	else if($bits == 4) {
	$gd_scan_line = "";
	$j = 0;
	while($j < $scan_line_size) {
	$byte = ord($scan_line{$j++});
	$p1 = chr($byte >> 4);
	$p2 = chr($byte & 0x0F);
	$gd_scan_line .= "$p1$p2";
	}
	$gd_scan_line = substr($gd_scan_line, 0, $width);
	}
	else if($bits == 1) {
	$gd_scan_line = "";
	$j = 0;
	while($j < $scan_line_size) {
	$byte = ord($scan_line{$j++});
	$p1 = chr((int) (($byte & 0x80) != 0));
	$p2 = chr((int) (($byte & 0x40) != 0));
	$p3 = chr((int) (($byte & 0x20) != 0));
	$p4 = chr((int) (($byte & 0x10) != 0));
	$p5 = chr((int) (($byte & 0x08) != 0));
	$p6 = chr((int) (($byte & 0x04) != 0));
	$p7 = chr((int) (($byte & 0x02) != 0));
	$p8 = chr((int) (($byte & 0x01) != 0));
	$gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";
	}
	$gd_scan_line = substr($gd_scan_line, 0, $width);
	}
	
	fwrite($dest_f, $gd_scan_line);
	}
	fclose($src_f);
	fclose($dest_f);
	return true;
}

/**
 * Opens a .bmp file and gives back the gd image resource created from it
 * http://bytes.com/topic/php/answers/3033-there-bmp-support-gd
 * 
 * @param $filename
 */
function imagecreatefrombmp($filename) {
	$tmp_name = tempnam("/tmp", "GD");
	if(ConvertBMP2GD($filename, $tmp_name)) {
		$img = imagecreatefromgd($tmp_name);
		unlink($tmp_name);
		return $img;
	}
	return false;
}

?>