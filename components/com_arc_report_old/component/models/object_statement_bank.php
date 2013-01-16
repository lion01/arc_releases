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

/**
 * Models statement banks.
 * A statement bank must be associated with a field into which the statements are to be inserted
 */
class ApothStatementBank extends JObject
{
	/** @var The id of the group to which these statements apply */
	var $_groupId;
	
	/** @var The field in which these statements may be used */
	var $_field;
	
	/** @var The array of id-indexed statements */
	var $_statements;
	
	/** @var The fields that are used for setting the "range" properties of this statement bank 
	 *  These need to be kept track of for sleep and wakeup */
	var $_rangeFields;
	
	/** @var */
//	var $_;
	
	
	function &getBank( $cycle, $group, $field )
	{
		static $instances;
		$instances = array();
		
		$index = $group.'_'.$cycle.'_'.$field;
		if( !array_key_exists($index, $instances) ) {
			$instances[$index] = new ApothStatementBank( $cycle, $group, $field );
		}
		
		return $instances[$index];
	}
	
	
	function __construct( $cycle, $group, $field )
	{
		$this->_cycleId = $cycle;
		$this->_groupId = $group;
		$this->_field = $field;
		$this->_rangeFields = NULL;

		$db = &JFactory::getDBO();
		$qStr = 'SELECT s.id, s.range_min, s.range_max, s.range_of, s.keyword, s.color, s.text, sm.'.$db->nameQuote('order')
			."\n".' FROM #__apoth_rpt_statements AS s'
			."\n".' INNER JOIN #__apoth_rpt_statements_map AS sm ON sm.statement_id = s.id'
			."\n".' WHERE sm.'.$db->nameQuote( 'group_id' ).' = '.$db->Quote( $this->_groupId )
			."\n".'   AND sm.'.$db->nameQuote( 'cycle_id' ).' = '.$db->Quote( $this->_cycleId )
			."\n".'   AND s.'.$db->nameQuote( 'field' ).' = '.$db->Quote( $this->_field )
			."\n".' ORDER BY sm.'.$db->nameQuote( 'order' );
		$db->setQuery( $qStr );
		$this->_statements = $db->loadObjectList( 'id' );
		if ($this->_statements === false) { $this->_statements = array(); }
		$this->_fields['id']        = new ApothFieldFixed ( false, 'id',        'id',        10, 10, 10, 10, 0, 0, 0, 0,   '0%',   '0px', 'ID',                  '', '' );
		$this->_fields['text']      = new ApothFieldText  ( false, 'text',      'text',      10, 10, 10, 10, 0, 0, 0, 0, '100%',   '5em', 'Statement',           '', '' );
		$this->_fields['range_min'] = new ApothFieldWord  ( false, 'range_min', 'range_min', 10, 10, 10, 10, 0, 0, 0, 0,  '50%', '1.5em', 'Applies from',        '', '' );
		$this->_fields['range_max'] = new ApothFieldWord  ( false, 'range_max', 'range_max', 10, 10, 10, 10, 0, 0, 0, 0,  '50%', '1.5em', 'Applies to',          '', '' );
		$this->_fields['range_of']  = new ApothFieldWord  ( false, 'range_of',  'range_of',  10, 10, 10, 10, 0, 0, 0, 0,  '50%', '1.5em', 'Applies on field...', '', '' );
		$this->_fields['keyword']   = new ApothFieldWord  ( false, 'keyword',   'keyword',   10, 10, 10, 10, 0, 0, 0, 0, '50%', '1.5em', 'Keyword',             '', '' );
		$this->_fields['color']     = new ApothFieldWord  ( false, 'color',     'color',     10, 10, 10, 10, 0, 0, 0, 0, '50%', '1.5em', 'Colour',              '', '' );
		$this->_fields['order']     = new ApothFieldHidden( false, 'order',     'order',      0,  0,  0,  0, 0, 0, 0, 0, '0%',   '0px', 'Order',               '', '' );
	}
	
	function getCycle()
	{
		return $this->_cycleId;
	}
	function getGroup()
	{
		return $this->_groupId;
	}
	function getField()
	{
		return $this->_field;
	}
	function getRangeFields()
	{
		return $this->_rangeFields;
	}
	
	function setRangeFields( $fields )
	{
		$this->_rangeFields = array();
		if( !is_array($fields)
		 || empty($fields)
		 || !is_object(($first = reset($fields))) ) {
			return false;
		}
		
		$minMaxChanged = false;
		foreach( $fields as $k=>$v ) {
			$this->_rangeFields[] = $v->getName(); // for sleep / wakeup
			if( !$minMaxChanged
			 && strtolower(get_class($first)) == 'apothfieldlist' ) {
				// set the min / max fields to be lists the same as the first of the lists that we allow selection between
				$style = $v->getStyle();
				$this->_fields['range_min'] = new ApothFieldList( false, 'range_min', 'range_min', 10, 10, 10, 10,  '50%', '1.5em', 'Applies from', '', '', $style);
				$this->_fields['range_max'] = new ApothFieldList( false, 'range_max', 'range_max', 10, 10, 10, 10,  '50%', '1.5em', 'Applies to',   '', '', $style);
				$minMaxChanged = true;
			}
			$tmp = new stdClass();
			$tmp->value = $v->getName();
			$tmp->text = $v->getTitle();
			$fields[$k] = $tmp;
		}
		$this->_fields['range_of'] = new ApothFieldList( false, 'range_of',  'range_of',  10, 10, 10, 10,  '50%', '1.5em', 'Applies on field...', '', '', $fields);
	}
	
	function getStatements( $incParents = false )
	{
		if( $incParents ) {
			$db = JFactory::getDBO();
			
			$this->_statements = array();
			
			$ancestors = ApotheosisLibDb::getAncestors( $this->_groupId, '#__apoth_cm_courses' );
			$visited = array(); // list of visited groups. use to avoid reference loops
			$useParent = true;
			while ( !is_null($group = array_pop($ancestors)) ) {
				$query = 'SELECT *'
					."\n".' FROM #__apoth_rpt_style'
					."\n".' WHERE '.$db->nameQuote('group').' = '.$db->Quote($group->id)
					."\n".'   AND '.ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
					."\n".'   AND '.$db->nameQuote('cycle').' = '.$db->Quote($this->_cycleId);
				$db->setQuery( $query );
				$r = $db->loadAssocList();
				$res = ( empty($r) ? NULL : reset($r) );
				
				// check we've not been here before
				if( !array_key_exists($group->id, $visited) ) {
					$visited[$group->id] = $group->id;
					
					if( is_null($res['twin']) ) {
						// no twin means use this group's statements (if any)
						if( $useParent ) {
							$tmp = &ApothStatementBank::getBank( $this->_cycleId, $group->id, $this->_field );
							$this->_statements = $this->_statements + $tmp->getStatements();
							unset( $tmp );
						}
					}
					else {
						// twin exists so use its settins instead of ours
						$ancestors = ApotheosisLibDb::getAncestors( $res['twin'], '#__apoth_cm_courses' );
					}
				}
				
				$useParent = ( is_null($res['use_parent_statements']) || (bool)$res['use_parent_statements'] );
			}
			
		}
		
		if( empty($this->_statements) ) {
			return array();
		}
		else {
			return $this->_statements;
		}
	}
	
	function getStatementText( $statementId )
	{
		if( array_key_exists( $statementId, $this->_statements ) ) {
			return $this->_statements[$statementId]->text;
		}
		else {
			return '';
		}
	}
	
	/**
	 * Removes a statement from this statement banks list, both in this statement bank instance
	 * and in the mapping table in the database.
	 *
	 * @param $statementId int  The id of the statement to be removed
	 * @param mixed  true if operation successful, error message otherwise
	 */
	function deleteStatement( $statementId )
	{
		if( array_key_exists( $statementId, $this->_statements ) ) {
			unset($this->_statements[$statementId]);
			$db = JFactory::getDBO();
			$query = 'DELETE FROM #__apoth_rpt_statements_map'
				."\n".' WHERE '.$db->nameQuote('statement_id').' = '.$db->Quote($statementId)
				."\n".'   AND '.$db->nameQuote('group_id').' = '.$db->Quote($this->_groupId)
				."\n".'   AND '.$db->nameQuote('cycle_id').' = '.$db->Quote($this->_cycleId).';';
			$db->setQuery($query);
			$r = $db->query();
			return ($r ? $r : 'Could not remove statement due to a database error');
		}
		else {
			return 'statement id: '.$statementId.' not found in statement bank';
		}
	}
	
	/**
	 * Adds a new statement to the statement bank and to the database
	 *
	 * @param $data object  The statement object with all appropriate properties to be added to the db
	 * @return boolean  True on success, false on failure
	 */
	function addStatement( $data )
	{
		$orderValue = $data->order;
		unset( $data->order );
		
		$db = &JFactory::getDBO();
		$retVal = $db->insertObject( '#__apoth_rpt_statements', $data, 'id' );
		
		$data->order = $orderValue;
		
		if( $retVal ) {
			// add the corresponding row to the map table
			$mapQStr = 'INSERT INTO #__apoth_rpt_statements_map ('.$db->nameQuote('group_id').', '.$db->nameQuote('cycle_id').', '.$db->nameQuote('statement_id').', '.$db->nameQuote('order').')'
				."\n".' VALUES ('
				."\n".' '.$db->Quote($this->_groupId).','
				."\n".' '.$db->Quote($this->_cycleId).','
				."\n".' '.$db->Quote($data->id).','
				."\n".' '.$db->Quote($data->order).')';
			$db->setQuery($mapQStr);
			$retVal = $db->query();
		}
		
		if( $retVal ) {
			$this->_statements[$data->id] = $data;
		}
		
		return $retVal;
	}
	
	/**
	 * Updates the statement with the given data. (the id of the statement to be updated is in the data)
	 *
	 * @param $data object  The statement object with all appropriate properties to be added to the db
	 * @return boolean  True on success, false on failure
	 */
	function updateStatement( $data )
	{
		$orderValue = $data->order;
		unset( $data->order );
		
		$db = &JFactory::getDBO();
		$retVal = $db->updateObject( '#__apoth_rpt_statements', $data, 'id', true );
		
		$data->order = $orderValue;
		
		if( $retVal ) {
			$this->_statements[$data->id] = $data;
		}
		
		return $retVal;
	}
	
	/**
	 * Re-orders the statement bank
	 *
	 * @param $data array  The sorted array of statement id's to be written to the db
	 * @return string  Number of updated rows or boolean false on failure
	 */
	function orderStatements( $data )
	{
		$db = &JFactory::getDBO();
		
		foreach( $data as $k=>$v ) {
			$tmp = new stdClass();
			$tmp->statement_id = $v;
			$tmp->group_id = $this->_groupId;
			$tmp->cycle_id = $this->_cycleId;
			$tmp->order = $k;
			$data[$k] = $tmp;
		}
		
		$retVal = ApotheosisLibDb::updateList( '#__apoth_rpt_statements_map', $data, array('statement_id', 'group_id', 'cycle_id') );
		
		return $retVal;
	}
	
	/**
	 * Finds the highest order value set for the current statement bank
	 *
	 * @return int  Highest order value found + 1
	 */
	function getNextOrder()
	{
		$tmp = $this->_statements;
		$lastStatement = end( $tmp );
		
		return $lastStatement->order + 1;
	}
	
	/**
	 * Accessor to pull out the fields used by the statement bank
	 * Gives fields with the values of the identified statement if an id is given,
	 * otherwise gives fields with no values
	 */
	function &getFields( $statementId = false )
	{
		if( ($statementId !== false) && (array_key_exists( $statementId, $this->_statements )) ) {
			$statement = $this->_statements[$statementId];
			$props = get_object_vars($statement);
			foreach( $props as $k=>$v ) {
				$this->_fields[$k]->setValue( $v );
			}
		}
		else {
			foreach( $this->_fields as $k=>$v ) {
				$this->_fields[$k]->setValue( NULL );
			}
		}
		return $this->_fields;
	}
}
?>