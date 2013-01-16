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
class ApothFactory_Behaviour_ScoreAggregate extends ApothFactory
{
	/**
	 * Retrieves the identified score aggregate
	 * NB: does not create the object if it didn't already exist
	 *     aggregate scores can only be created via getInstances
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		return $r;
	}
	
	/**
	 * Retrieves the identified scores, creating the objects if they didn't already exist
	 * @param $id
	 */
	function &getInstances( $requirements, $init = true )
	{
		$sId = $this->_getSearchId( $requirements );
		$scoreAggIds = $this->_getInstances( $sId );
		
		if( is_null($scoreAggIds) ) {
//			var_dump_pre($requirements, 'requirements for aggregate instances');
			$u = &ApotheosisLib::getUser();
			$db = &JFactory::getDBO();
			
			$dbTblTmp = $db->nameQuote( 'tmp_'.$u->id.'_'.str_replace( array(' ', '.'), array('', ''), microtime() ) );
			$dbTblAgg = $db->nameQuote( 'agg_'.$u->id.'_'.str_replace( array(' ', '.'), array('', ''), microtime() ) );
			$dbTmp = $db->nameQuote( 't' );
			$dbAgg = $db->nameQuote( 'a' );
			
			$dbId       = $db->nameQuote( 'id' );
			$dbTotalFormula = 'IFNULL( SUM( IFNULL('.$db->nameQuote('score').', 0) ), 0 )';
			$dbTotal    = $db->nameQuote( 'total' );
			$dbCount    = $db->nameQuote( 'count' );
			$dbPersonId = $db->nameQuote( 'person_id' );
			$dbAuthor   = $db->nameQuote( 'author' );
			$dbGroupId  = $db->nameQuote( 'group_id' );
			$dbDateIssued = $db->nameQuote( 'date_issued' );
			
			$order = array( $dbDateIssued.' ASC' );
			$orderPost = array( $dbDateIssued.' ASC' );
			// work out the behaviour score conditionals
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
				
				case( 'academic_year' ):
					// do we mean pupil year or group year?
					if( isset($requirements['_aggregate']) ) {
						switch($requirements['_aggregate']) {
						case( 'group_id' ):
							$preWhere[] = $db->nameQuote( 'year' ).$assignPart;
							break;
						
						case( 'tutor_id' ):
						case( 'person_id' ):
							$preJoin['tutor'] = 'INNER JOIN jos_apoth_tt_group_members AS gm'
								."\n".'   ON gm.person_id = p.id'
								."\n".'  AND '.ApotheosisLibDb::dateCheckSql('gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
								."\n".'  AND gm.role = '.ApotheosisLibAcl::getRoleId( 'group_participant_student' )
								."\n".'INNER JOIN jos_apoth_cm_courses AS gmc'
								."\n".'   ON gmc.id = gm.group_id'
								."\n".'  AND gmc.type = '.$db->Quote('pastoral')
								."\n".'  AND gmc.deleted = '.$db->Quote('0');
							$preWhere[] = 'gmc.'.$db->nameQuote( 'year' ).$assignPart;
							break;
						}
					}
					break;
				
				case( 'groups' ):
					$where[] = 's.'.$db->nameQuote( 'group_id' ).' '.$assignPart;
					if( isset($requirements['_aggregate']) && ($requirements['_aggregate'] == 'group_id') ) {
						$preWhere[] = 'c.'.$dbId.$assignPart;
					}
					break;
				
				case( 'tutor' ):
					$columns[] = 'c_tut.id AS tutor_id';
					$join[] = 'INNER JOIN jos_apoth_tt_group_members AS gm_tut'
						."\n".'   ON gm_tut.'.$dbPersonId.' = s.'.$dbPersonId
						."\n".'  AND '.ApotheosisLibDb::dateCheckSql('gm_tut.valid_from', 'gm_tut.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
						."\n".'  AND gm_tut.role = '.ApotheosisLibAcl::getRoleId( 'group_participant_student' )
						."\n".'INNER JOIN jos_apoth_cm_courses AS c_tut'
						."\n".'   ON c_tut.id = gm_tut.group_id'
						."\n".'  AND c_tut.type = '.$db->Quote('pastoral')
						."\n".'  AND c_tut.deleted = '.$db->Quote('0');
					$where[] = 'c_tut.'.$dbId.' '.$assignPart;
					if( isset($requirements['_aggregate']) && ($requirements['_aggregate'] == 'tutor_id') ) {
						$preJoin['tutor'] = 'INNER JOIN jos_apoth_tt_group_members AS gm'
							."\n".'   ON gm.person_id = p.id'
							."\n".'  AND '.ApotheosisLibDb::dateCheckSql('gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') )
							."\n".'  AND gm.role = '.ApotheosisLibAcl::getRoleId( 'group_participant_student' )
							."\n".'INNER JOIN jos_apoth_cm_courses AS gmc'
							."\n".'   ON gmc.id = gm.group_id'
							."\n".'  AND gmc.type = '.$db->Quote('pastoral')
							."\n".'  AND gmc.deleted = '.$db->Quote('0');
						$preWhere['tutor'] = 'gmc.id '.$assignPart;
					}
					break;
				
				case( 'author' ):
					if( isset($requirements['_aggregate']) && ($requirements['_aggregate'] == $col) ) {
						$preWhere[] = 'p.'.$dbId.$assignPart;
					}
					$where[] = 'm.'.$db->nameQuote( $col ).' '.$assignPart;
					break;
				
				case( 'person_id' ):
					if( isset($requirements['_aggregate']) && ($requirements['_aggregate'] == $col) ) {
						$preWhere[] = 'p.'.$dbId.$assignPart;
					}
				case( 'id' ):
					$where[] = 's.'.$db->nameQuote( $col ).' '.$assignPart;
					break;
				
				case( 'msg_id' ):
					$where[] = $db->nameQuote( $col ).' '.$assignPart;
					break;
				
				case( 'order' ):
					if( isset($requirements['abs']) && $requirements['abs'] ) {
						array_unshift( $order, 'ABS('.$dbTotalFormula.') '.($val == -1 ? ' DESC' : ' ASC') );
						array_unshift( $orderPost, 'ABS(IFNULL('.$dbTotal.', 0)) '.($val == -1 ? ' DESC' : ' ASC') );
					}
					else {
						array_unshift( $order, $dbTotalFormula.' '.($val == -1 ? ' DESC' : ' ASC') );
						array_unshift( $orderPost, 'IFNULL('.$dbTotal.', 0) '.($val == -1 ? ' DESC' : ' ASC') );
					}
					break;
				
				case( 'limit' ):
					$limit = (int)$val;
					break;
				}
			}
			
			// Work out the pre-query and postQuery
			// These are used to ensure we can get series with no scores by using a left join 
			// (eg if we want the bottom 10 and all series are on 0 or more)
			if( isset($requirements['_aggregate']) ) {
				$val = $requirements['_aggregate'];
				
				$groupForId[] = $val;
				$group[$val] = $db->nameQuote( $val );
				
				switch( $val ) {
				case( 'group_id' ):
					$preWhere[] = 'c.deleted = 0';
					$preQuery = 'CREATE TEMPORARY TABLE '.$dbTblTmp.' AS'
						."\n".'SELECT '.$dbId
						."\n".'FROM '.$db->nameQuote( '#__apoth_cm_courses' ).' AS '.$db->nameQuote( 'c' )
						.( empty($preJoin) ? '' : "\n".implode("\n", $preJoin) )
						."\n".'WHERE '.implode("\n".' AND ', $preWhere);
					$postQuery = 'SELECT IFNULL('.$dbTotal.', 0) AS '.$dbTotal.', '.$dbCount.', '.$dbPersonId.', '.$dbTmp.'.'.$dbId.' AS '.$dbGroupId.', '.$dbAuthor
						."\n".'FROM '.$dbTblTmp.' AS '.$dbTmp
						."\n".'LEFT JOIN '.$dbTblAgg.' AS '.$dbAgg
						."\n".'  ON '.$dbAgg.'.'.$dbGroupId.' = '.$dbTmp.'.'.$dbId;
					break;
				
				case( 'tutor_id' ):
					$preQuery = 'CREATE TEMPORARY TABLE '.$dbTblTmp.' AS'
						."\n".'SELECT gmc.'.$dbId
						."\n".'FROM '.$db->nameQuote( '#__apoth_ppl_people' ).' AS '.$db->nameQuote( 'p' )
						.( empty($preJoin) ? '' : "\n".implode("\n", $preJoin) )
						.( empty($preWhere) ? '' : "\n".'WHERE '.implode("\n".'  AND ', $preWhere) )
						."\n".'GROUP BY gmc.'.$dbId;
					$postQuery = 'SELECT IFNULL('.$dbTotal.', 0) AS '.$dbTotal.', '.$dbCount.', '.$dbPersonId.', '.$dbTmp.'.'.$dbId.' AS tutor_id, '.$dbAuthor
						."\n".'FROM '.$dbTblTmp.' AS '.$dbTmp
						."\n".'LEFT JOIN '.$dbTblAgg.' AS '.$dbAgg
						."\n".'  ON '.$dbAgg.'.'.$db->nameQuote( $val ).' = '.$dbTmp.'.'.$dbId;
					
					$group[$val] = 'c_tut.id';
					break;
				
				case( 'person_id' ):
				case( 'author' ):
				default:
					$preQuery = 'CREATE TEMPORARY TABLE '.$dbTblTmp.' AS'
						."\n".'SELECT p.'.$dbId
						."\n".'FROM '.$db->nameQuote( '#__apoth_ppl_people' ).' AS '.$db->nameQuote( 'p' )
						.( empty($preJoin) ? '' : "\n".implode("\n", $preJoin) )
						.( empty($preWhere) ? '' : "\n".'WHERE '.implode("\n".'  AND ', $preWhere) );
					$postQuery = 'SELECT IFNULL('.$dbTotal.', 0) AS '.$dbTotal.', '.$dbCount.', '.$dbTmp.'.'.$dbId.' AS '.$dbPersonId.', '.$dbGroupId.', '.$dbAuthor
						."\n".'FROM '.$dbTblTmp.' AS '.$dbTmp
						."\n".'LEFT JOIN '.$dbTblAgg.' AS '.$dbAgg
						."\n".'  ON '.$dbAgg.'.'.$db->nameQuote( $val ).' = '.$dbTmp.'.'.$dbId;
					break;
				}
				
			}			
			else {
				$groupForId = array();
				$group[] = '1=1';
				$preQuery = '';
				$postQuery = 'SELECT '.$dbAgg.'.*'
					."\n".'FROM '.$dbTblAgg.' AS '.$dbAgg;
			}
			
			
			// Run whatever preQuery we need to set up list of possibilities
			$db->setQuery( $preQuery );
			$db->Query();
//			debugQuery($db);
			
			// Pull out scores based on requirements
			$query = 'CREATE TEMPORARY TABLE '.$dbTblAgg.' AS '
				."\n".'SELECT '.$dbTotalFormula.' AS '.$dbTotal
				."\n".', COUNT(*) AS '.$dbCount
				.', s.'.$dbDateIssued
				.', s.'.$dbPersonId
				.', s.'.$dbGroupId
				.', IFNULL( m.author, -1 ) AS '.$dbAuthor
				.( empty($columns)  ? '' : ','.implode(',', $columns) )
				."\n".'FROM '.$db->nameQuote( '#__apoth_bhv_scores' ).' AS '.$db->nameQuote( 's' )
				."\n".'LEFT JOIN '.$db->nameQuote( '#__apoth_msg_messages' ).' AS '.$db->nameQuote( 'm' )
				."\n".'  ON '.$db->nameQuote( 'm' ).'.'.$db->nameQuote( 'id' ).' = '.$db->nameQuote( 's' ).'.'.$db->nameQuote( 'msg_id' )
				.( empty($join)  ? '' : "\n".implode("\n", $join) )
				.( empty($where) ? '' : "\n".'WHERE '.implode("\n".'  AND ', $where) )
				.( empty($group) ? '' : "\n".'GROUP BY '.implode(',', $group) )
				.( empty($order) ? '' : "\n".'ORDER BY '.implode( ', ', $order ) )
				.( empty($limit) ? '' : "\n".'LIMIT '.$limit );
			$db->setQuery( $query );
			$db->Query();
//			debugQuery($db);
			
			// Run whatever postQuery we need to get available data for all listed possibilities
			$postQuery .=
				  (empty($order) ? '' : "\n".'ORDER BY '.implode( ', ', $orderPost ) )
				.( empty($limit) ? '' : "\n".'LIMIT '.$limit );
			
			$db->setQuery( $postQuery );
			$scoreAggObjsData = $db->loadAssocList();
//			debugQuery($db, $scoreAggObjsData);
//			var_dump_pre( $groupForId );
			if( !is_array($scoreAggObjsData) ) { $scoreAggObjsData = array(); }
			
			// work out a unique id for each row so we can get them back later
			// and since we're going through the data let's do any required initialisation
			$existing = $this->_getInstances();
			$scoreAggIds = array();
			foreach( $scoreAggObjsData as $row ) {
				if( empty($groupForId) ) {
					$rid = '';
				}
				else {
					$rId = array();
					foreach( $groupForId as $col ) {
						$rId[] = $row[$col];
					}
//					var_dump_pre( $rId );
					$rId = $this->_getSearchId( $rId );
				}
				$id = $sId.'~'.$rId;
				$scoreAggIds[] = $id;
				
				if( $init ) {
					if( !isset($existing[$id]) ) {
						$scoreAggObj = new ApothScoreAggregate( $row );
						$this->_addInstance( $id, $scoreAggObj );
						unset( $scoreAggObj );
					}
				}
			}
			
			$this->_addInstances( $sId, $scoreAggIds );
		}
		
		return $scoreAggIds;
	}
	
}


/**
 * Behaviour Score Object
 */
class ApothScoreAggregate extends JObject
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