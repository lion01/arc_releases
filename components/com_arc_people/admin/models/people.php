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

// Give us access to the joomla model class
jimport( 'joomla.application.component.model' );

/**
 * People Admin People Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage People
 * @since      1.6
 */
class PeopleAdminModelPeople extends JModel
{
	/**
	 * Set the search term
	 * 
	 * @param array $searchTerms  The search terms to set
	 */
	function setSearchTerms( $searchTerms )
	{
		$this->_searchTerms = $searchTerms;
	}
	
	/**
	 * Retrieve the search term
	 * 
	 * @return array $this->_searchTerms  The search terms
	 */
	function getSearchTerms()
	{
		return $this->_searchTerms;
	}
	
	/**
	 * Set the currently valid pagination object
	 * 
	 * @param int $limitStart Where to start paging from
	 * @param int $limit  The total number of items to page
	 */
	function setPagination( $limitStart, $limit )
	{
		$total = $this->_loadPagedPeople( true );
		$this->_pagination = new JPagination( $total, $limitStart, $limit );
	}
	
	/**
	 * Retrieve the currently valid pagination object
	 * 
	 * @return object $this->_pagination  The pagination object
	 */
	function &getPagination()
	{
		return $this->_pagination;
	}
	
	/**
	 * Set a paginated array of people objects
	 */
	function setPagedPeople()
	{
		$peopleInfo = $this->_loadPagedPeople();
		foreach( $peopleInfo as $personData ) {
			$this->_pagedPeople[] = new AdminPerson( $personData );
		}
	}
	
	/**
	 * Fetch a paginated list of people
	 * 
	 * @return array $this->_pagedPeople  Array of people objects
	 */
	function &getPagedPeople()
	{
		return $this->_pagedPeople;
	}
	
	/**
	 * Retrieve people or a count of people from the db
	 * 
	 * @param bool $numOnly  Whether we only want a count of people, defaults to false
	 * @return int|array $result  The count of people or array of people info
	 */
	function _loadPagedPeople( $numOnly = false )
	{
		$db = &JFactory::getDBO();
		$searchTerms = $this->_searchTerms;
		
		// create the select
		$select = ( $numOnly ? 'SELECT COUNT(*)' : 'SELECT *' );
		
		// preset the where clause
		$where = array();
		
		// create the search term where clause
		if( !empty($searchTerms) ) {
			foreach( $searchTerms as $searchTerm) {
				$searchEscaped = $db->Quote( '%'.$db->getEscaped( $searchTerm, true ).'%', false );
				$where[] = $db->nameQuote('id').' LIKE '.$searchEscaped
					.' OR '.$db->nameQuote('ext_person_id').' LIKE '.$searchEscaped
					.' OR '.$db->nameQuote('juserid').' LIKE '.$searchEscaped
					.' OR '.$db->nameQuote('firstname').' LIKE '.$searchEscaped
					.' OR '.$db->nameQuote('preferred_firstname').' LIKE '.$searchEscaped
					.' OR '.$db->nameQuote('surname').' LIKE '.$searchEscaped
					.' OR '.$db->nameQuote('preferred_surname').' LIKE '.$searchEscaped;
			}
		}
		
		// create the combined where clause
		$where = !empty($where) ? "\n".'WHERE (' . implode( ') AND (', $where ) . ')' : '';
		
		// create the order clause
		$order = "\n".'ORDER BY '.$db->nameQuote('id');
		
		// create the query
		$query = $select
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_people')
			.$where;
		
		// get the results
		if( $numOnly ) {
			$db->setQuery( $query );
			$result = $db->loadResult();
		}
		else {
			$pagination = $this->_pagination;
			$db->setQuery( $query.$order, $pagination->limitstart, $pagination->limit );
			$result = $db->loadAssocList();
		}
		
		return $result;
	}
	
	/**
	 * Set the current person's pagination index
	 * 
	 * @param array $personIndex  Current person's pagination index
	 */
	function setPersonIndex( $personIndex )
	{
		$this->_personIndex = $personIndex;
	}
	
	/**
	 * Retrieve the current person's pagination index
	 * 
	 * @return array $this->_personIndex  Current person's pagination index
	 */
	function &getPersonIndex()
	{
		return $this->_personIndex;
	}
	
	/**
	 * Set a person object
	 * 
	 * @param int|array $personInfo  Optional current pagination index of the person we want or array of data
	 * @param bool $details  Do we want a detailed person object
	 */
	function setPerson( $personInfo = null, $details = false )
	{
		if( is_array($personInfo) ) {
			$this->_person = new AdminPerson( $personInfo ); // *** for future use saving a new person
		}
		elseif( !is_null($personInfo) && ($personInfo !== false) ) {
			$this->_person = $this->_pagedPeople[$personInfo];
			if( $details ) {
				$this->_loadDetails();
			}
		}
		else {
			$this->_person = new AdminPerson(); // *** for future use creating a new person
		}
	}
	
	/**
	 * Retrieve the current person object
	 * 
	 * @return obj $this->_person  The current person object
	 */
	function &getPerson()
	{
		return $this->_person;
	}
	
	/**
	 * Load full details for the current person object
	 */
	function _loadDetails()
	{
		$db = &JFactory::getDBO();
		
		// add address details
		$addressQuery = 'SELECT *'
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_addresses')
			."\n".'WHERE '.$db->nameQuote('id').' = '.$db->Quote($this->_person->_data['address_id']);
		$db->setQuery( $addressQuery );
		$this->_person->_details['address'] = $db->loadAssoc();
		
		// add relations details
		$relQuery = 'SELECT '.$db->nameQuote('tree').'.'.$db->nameQuote('description')
			 .', '.$db->nameQuote('ppl').'.'.$db->nameQuote('firstname')
			 .', '.$db->nameQuote('ppl').'.'.$db->nameQuote('surname')
			 .', '.$db->nameQuote('ppl').'.'.$db->nameQuote('preferred_firstname')
			 .', '.$db->nameQuote('ppl').'.'.$db->nameQuote('preferred_surname')
			 .', '.$db->nameQuote('rel').'.'.$db->nameQuote('parental')
			 .', '.$db->nameQuote('rel').'.'.$db->nameQuote('legal_order')
			 .', '.$db->nameQuote('rel').'.'.$db->nameQuote('correspondence')
			 .', '.$db->nameQuote('rel').'.'.$db->nameQuote('reports')
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_relations').' AS '.$db->nameQuote('rel')
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_ppl_relation_tree').' AS '.$db->nameQuote('tree')
			."\n".'   ON '.$db->nameQuote('tree').'.'.$db->nameQuote('id').' = '.$db->nameQuote('rel').'.'.$db->nameQuote('relation_type_id')
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_ppl_people').' AS '.$db->nameQuote('ppl')
			."\n".'   ON '.$db->nameQuote('ppl').'.'.$db->nameQuote('id').' = '.$db->nameQuote('rel').'.'.$db->nameQuote('relation_id')
			."\n".'WHERE '.$db->nameQuote('rel').'.'.$db->nameQuote('pupil_id').' = '.$db->Quote($this->_person->_data['id'])
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'rel.valid_from', 'rel.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') );
		$db->setQuery( $relQuery );
		$this->_person->_details['relations'] = $db->loadAssocList();
		
		// add global roles
		$roles = ApotheosisLibAcl::getPeoplesGlobalRoles( array($this->_person->_data['id']) );
		if( !empty($roles) ) {
			$roles = reset( $roles );
		}
		$this->_person->_details['roles'] = $roles;
	}
	
	/**
	 * Retrieve the current list of person ARC IDs
	 */
	function getPersonIds()
	{
		return $this->_personIds;
	}
	
	/**
	 * Set an array of person ARC IDs
	 * 
	 * @param array $indexData  An array of index ID's from the current paginated list
	 */
	function setPersonIds( $indexData )
	{
		foreach( $indexData as $pagedId ) {
			$this->_personIds[] = $this->_pagedPeople[$pagedId]->getDatum( 'id' );
		}
	}
	
	/**
	 * Save the details form data
	 * 
	 * @param array $data  Array of form data to be saved
	 * @return array  An array containing success indicator and err msgs for the db operations
	 */
	function saveDetails( $data )
	{
		// *** For now we are just saving the roles
		$db = &JFactory::getDBO();
		
		// roles
		$deleteRolesQuery = 'DELETE FROM '.$db->nameQuote('#__apoth_sys_com_roles')
			."\n".'WHERE '.$db->nameQuote('person_id').' = '.$db->Quote($this->_person->_data['id']);
		$db->setQuery( $deleteRolesQuery );
		$db->Query();
		$delRolesErrMsg = $db->getErrorMsg();
		
		$insRolesErrMsg = '';
		if( !empty($data['roles']) ) {
			foreach( $data['roles'] as $role ) {
				$roleValue[] = '( '.$db->Quote($this->_person->_data['id']).', '.$db->Quote($role).' )';
			}
			$roleValues = implode( ','."\n", $roleValue );
			$insertRolesQuery = 'INSERT INTO '.$db->nameQuote('#__apoth_sys_com_roles')
				."\n".'VALUES '.$roleValues;
			$db->setQuery( $insertRolesQuery );
			$db->Query();
			$insRolesErrMsg = $db->getErrorMsg();
		}
		
		// prepare save outcomes
		$success = ( ($delRolesErrMsg == '') && ($insRolesErrMsg == '') );
		$errMsgs = array();
		if( !$success ) {
			$errMsgs = array( $delRolesErrMsg, $insRolesErrMsg );
		}
		
		return array( $success, $errMsgs );
	}
}
?>