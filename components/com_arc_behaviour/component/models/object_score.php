<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Behaviour Analysis Sheet Factory
 */
class ApothFactory_Behaviour_Score extends ApothFactory
{
	function &getDummy()
	{
		$id = -1;
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothScore( array() );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified score, creating the object if it didn't already exist
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT *'
				."\n".'FROM '.$db->nameQuote( '#__apoth_bhv_msg_scores' )
				."\n".'WHERE '.$db->nameQuote( 'id' ).' = '.$db->Quote( $id );
			$r = new ApothScore( $data );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified scores, creating the objects if they didn't already exist
	 * @param $id
	 */
	function &getInstances( $requirements, $init = true )
	{
		$sId = $this->_getSearchId( $requirements );
		$scoreIds = $this->_getInstances( $sId );
		
		if( is_null($scoreIds) ) {
			$db = &JFactory::getDBO();
			
			foreach( $requirements as $col=>$val ) {
				if( is_array($val) ) {
					if( empty($val) ) {
						continue;
					}
					foreach( $val as $k=>$v ) {
						$val[$k] = $db->Quote( $v );
					}
					$assignPart = ' IN ('.implode( ', ',$val ).')';
				}
				else {
					$assignPart = ' = '.$db->Quote( $val );
				}
				switch( $col ) {
				case( 'start_date' ):
				case( 'end_date' ):
					if( !isset($where['date']) ) {
						$where['date'] = $db->nameQuote('s').'.'.$db->nameQuote('date_issued').' BETWEEN '.$db->Quote($requirements['start_date']).' AND '.$db->Quote($requirements['end_date']);
					}
					
					break;
				
				case( 'day_section' ):
					$join[] = 'INNER JOIN jos_apoth_tt_patterns AS p'
						."\n".'   ON (s.date_issued > p.valid_from AND ( s.date_issued < p.valid_to OR p.valid_to IS NULL ) )'
						."\n".'INNER JOIN `jos_apoth_tt_daydetails` AS dd'
						."\n".'   ON dd.day_type = SUBSTRING( p.format, arc_dateToCycleDay( `date_issued` ) + 1, 1 )'
						."\n".'  AND dd.pattern = p.id'
						."\n".'  AND (s.date_issued > dd.valid_from AND ( s.date_issued < dd.valid_to OR dd.valid_to IS NULL ) )'
						."\n".'  AND TIME( s.date_issued ) BETWEEN dd.start_time AND dd.end_time';
					$where[] = 'dd.day_section '.$assignPart;
					break;
				
				case( 'groups' ):
					$where[] = $db->nameQuote( 'group_id' ).' '.$assignPart;
					break;
				
				case( 'incident' ):
				case( 'incidents' ):
					$join[] = 'INNER JOIN jos_apoth_msg_data AS md'
						."\n".'   ON md.msg_id = s.msg_id'
						."\n".'  AND md.col_id = '.$db->Quote( 'incident' );
					$where[] = $db->nameQuote( 'md' ).'.'.$db->nameQuote( 'data' ).' '.$assignPart;
					break;
				
				case( 'id' ):
				case( 'person_id' ):
				case( 'msg_id' ):
				case( 'author' ):
					$where[] = $db->nameQuote( $col ).' '.$assignPart;
					break;
				}
			}
			
			// First check how many score objects we are attempting to retrieve
			$query = 'SELECT COUNT(*)'
				."\n".'FROM '.$db->nameQuote( '#__apoth_bhv_scores' ).' AS '.$db->nameQuote( 's' )
				."\n".'LEFT JOIN '.$db->nameQuote( '#__apoth_msg_messages' ).' AS '.$db->nameQuote( 'm' )
				."\n".'  ON '.$db->nameQuote( 'm' ).'.'.$db->nameQuote( 'id' ).' = '.$db->nameQuote( 's' ).'.'.$db->nameQuote( 'msg_id' )
				.( empty($join)  ? '' : "\n".implode("\n", $join) )
				.( empty($where) ? '' : "\n".'WHERE '.implode("\n".'  AND ', $where) )
				."\n".'ORDER BY '.$db->nameQuote( 'date_issued' );
			$db->setQuery( $query );
			$scoreObjsCount = $db->loadResult();
			
			$limit = 4000; // somewhat arbitrary but tested in an appropriate environment
			if( $scoreObjsCount == 0 ) {
				// Return 0 so we can warn the user to widen the search as we have no results
				$scoreIds = 0;
			}
			elseif( $scoreObjsCount <= $limit ) {
				// Pull out scores based on requirements
				$query = 'SELECT s.*, IFNULL( m.author, -1 ) AS author'
					."\n".'FROM '.$db->nameQuote( '#__apoth_bhv_scores' ).' AS '.$db->nameQuote( 's' )
					."\n".'LEFT JOIN '.$db->nameQuote( '#__apoth_msg_messages' ).' AS '.$db->nameQuote( 'm' )
					."\n".'  ON '.$db->nameQuote( 'm' ).'.'.$db->nameQuote( 'id' ).' = '.$db->nameQuote( 's' ).'.'.$db->nameQuote( 'msg_id' )
					.( empty($join)  ? '' : "\n".implode("\n", $join) )
					.( empty($where) ? '' : "\n".'WHERE '.implode("\n".'  AND ', $where) )
					."\n".'ORDER BY '.$db->nameQuote( 'date_issued' );
				$db->setQuery( $query );
				$scoreObjsData = $db->loadAssocList( 'id' );
				
				$scoreIds = array_keys( $scoreObjsData );
				$this->_addInstances( $sId, $scoreIds );
				
				if( $init ) {
					$existing = $this->_getInstances();
					$newIds = array_diff( $scoreIds, $existing );
					
					// initialise and cache
					foreach( $newIds as $id ) {
						$data = $scoreObjsData[$id];
						$scoreObj = new ApothScore( $data );
						$this->_addInstance( $id, $scoreObj );
						unset( $scoreObj );
					}
				}
			}
			else {
				// Return false so we can warn the user to narrow the search as we have too many results
				$scoreIds = false;
			}
		}
		
		return $scoreIds;
	}
}


/**
 * Behaviour Score Object
 */
class ApothScore extends JObject
{
	/**
	 * All the data for this score (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_core = array();
	
	function __construct( $data )
	{
		$this->_core = $data;
	}
	
	function getDatum( $field )
	{
		return $this->_core[$field];
	}
	
}
?>