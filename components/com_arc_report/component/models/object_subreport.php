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
 * Report Subreport Factory
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothFactory_Report_Subreport extends ApothFactory
{
	function initialise()
	{
		$this->setParam( 'restrict', true );
	}
	
	function &getDummy( $id )
	{
		if( $id >= 0 ) {
			$r = null;
			return $r;
		}
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothReportSubreport( array('id'=>$id,
				'cycle_id'=>null,
				'rpt_group_id'=>null,
				'person_id'=>null,
				'author_id'=>null,
				'status_id'=>ARC_REPORT_STATUS_NASCENT) );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified subreport, creating the object if it didn't already exist
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT s.*'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_subreports' ).' AS s'
				."\n".'WHERE s.id = '.$db->Quote( $id );
			$db->setQuery($query);
			$data = $db->loadAssoc();
			
			$r = new ApothReportSubreport( $data );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	function &getInstances( $requirements, $init = true, $orders = null )
	{
		$restrict = $this->getParam( 'restrict' );
		$requirements['_restrict'] = $restrict;
		$sId = $this->_getSearchId( $requirements );
		$ids = $this->_getInstances($sId);
		$now = date( 'Y-m-d H:i:s' );
		
		if( is_null($ids) ) {
			$where = $join = $orderBy = array();
			$this->requirementsToClauses( $requirements, $where, $join );
			$this->ordersToClauses( $orders, $orderBy, $join );
			
			$db = &JFactory::getDBO();
			$query = 'SELECT DISTINCT s.'.( $init ? '*' : 'id' )
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_subreports' ).' AS s'
				.( empty($join)    ? '' : "\n".implode("\n", $join) )
				.( empty($where)   ? '' : "\nWHERE ".implode("\n AND ", $where) )
				.( empty($orderBy) ? '' : "\nORDER BY ".implode(',', $orderBy) );
			if( $restrict ) {
				$query = ApotheosisLibAcl::limitQuery( $query, 'report.subreports' ); 
			}
			
			$db->setQuery( $query );
			$data = $db->loadAssocList( 'id' );
//			dumpQuery( $db, $data );
			
			$ids = array_keys( $data );
			$this->_addInstances( $sId, $ids );
			
			if( $init ) {
				$existing = $this->_getInstances();
				$newIds = array_diff( $ids, $existing );
				
				if( !empty($newIds) ) {
					// initialise and cache
					foreach( $newIds as $id ) {
						$objData = $data[$id];
						$obj = new ApothReportSubreport( $objData );
						$this->_addInstance( $id, $obj );
						unset( $obj );
					}
				}
			}
		}
		
		return $ids;
	}
	
	/**
	 * Generate sql clauses suitable for use in getInstances.
	 * Generated clauses are added to $where and $join parameters (passed by reference).
	 * 
	 * @param array $requirements  Associative array of column=>value(s) by which to restrict the results
	 * @param array $where  Array to populate with clauses. Passed by reference,
	 * @param array $join   Array to populate with clauses. Passed by reference,
	 */
	function requirementsToClauses( $requirements, &$where, &$join )
	{
		if( !is_array( $where ) ) { $where = array(); }
		if( !is_array( $join )  ) { $join  = array(); }
		if( !is_array( $requirements ) || empty( $requirements ) ) { return; }
		
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
			case( 'id' ):
				$where[] = 's.id'.$assignPart;
				break;
			
			case( 'cycle' ):
				$where[] = 's.cycle_id'.$assignPart;
				break;
			
			case( 'person' ):
				$where[] = '(s.author_id'.$assignPart.' OR s.reportee_id'.$assignPart.')';
				break;
			
			case( 'author' ):
				$where[] = 's.author_id'.$assignPart;
				break;
			
			case( 'reportee' ):
				$where[] = 's.reportee_id'.$assignPart;
				break;
			
			case( 'subject' ):
				$dbCA = $db->nameQuote( 'ca' );
				$join[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_cm_courses_ancestry' ).' AS '.$dbCA
					."\n".'   ON '.$dbCA.'.id = s.rpt_group_id';
				$where[] = $dbCA.'.'.$db->nameQuote( 'ancestor' ).$assignPart;
				break;
			
			case( 'group' ):
				$where[] = 's.rpt_group_id'.$assignPart;
				break;
			
			case( 'status' ):
				$where[] = 's.status_id'.$assignPart;
				break;
			
			case( 'role' ):
				if( isset( $requirements['role_person'] ) ) {
					$personId = $requirements['role_person'];
				}
				else {
					$u = ApotheosisLib::getUser();
					$personId = $u->person_id;
				}
				
				$rptTable = ApotheosisLibAcl::getUserTable( 'report.subreports' );
				$join['role'] = 'INNER JOIN '.$db->nameQUote( $rptTable ).' AS r'
					."\n".'   ON r.id = s.id';
				$where['role'] = 'r.role'.$assignPart;
				break;
			
			case( 'restrict' );
			case( '_restrict' );
				$join['restrict'] = '~LIMITINGJOIN~';
				break;
			}
		}
	}
	
	/**
	 * Generate sql clauses suitable for use in getInstances.
	 * Generated clauses are added to $orderBy parameter (passed by reference).
	 * 
	 * @param array $orders  Associative array of column=>direction by which to order the results
	 * @param array $orderBy  Array to populate with clauses. Passed by reference,
	 */
	function ordersToClauses( $orders, &$orderBy, &$join )
	{
		if( !is_array( $orderBy ) ) { $orderBy = array(); }
		if( !is_array( $join )  ) { $join  = array(); }
		if( !is_array( $orders ) || empty( $orders ) ) { return; }
		
		$db = &JFactory::getDBO();
		foreach( $orders as $orderOn=>$orderDir ) {
			if( $orderDir == 'a' ) {
				$orderDir = 'ASC';
			}
			elseif( $orderDir == 'd' ) {
				$orderDir = 'DESC';
			}
			
			switch( $orderOn ) {
			case( 'group_name' ):
				$dbC = $db->nameQuote( 'c' );
				$dbP = $db->nameQuote( 'p' );
				$join['groups'] = 'INNER JOIN '.$db->nameQuote( '#__apoth_cm_courses' ).' AS '.$dbC
					."\n".'    ON '.$dbC.'.id = s.rpt_group_id';
				$orderBy[] = $dbC.'.fullname '.$orderDir;
				
			case( 'reportee_name' ):
				$dbP = $db->nameQuote( 'p' );
				$join['reportees'] = 'INNER JOIN '.$db->nameQuote( '#__apoth_ppl_people' ).' AS '.$dbP
					."\n".'    ON '.$dbP.'.id = s.reportee_id';
				$orderBy[] = $dbP.'.surname '.$orderDir;
				$orderBy[] = $dbP.'.firstname '.$orderDir;
				break;
			
			case( 'reportee_tutorgroup' ):
				$join[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_tt_group_members' ).' AS gm'
					."\n".'   ON gm.person_id = s.reportee_id'
					."\n".'  AND '.ApotheosisLibDb::dateCheckSQL( 'gm.valid_from', 'gm.valid_to', $this->getParam( 'date' ), $this->getParam( 'date' ) )
					."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_cm_courses' ).' AS c'
					."\n".'   ON c.id = gm.group_id'
					."\n".'  AND c.type = '.$db->Quote( 'pastoral' );
				$orderBy[] = 'c.fullname '.$orderDir;
				break;
			}
		}
	}
	
	
	function commitInstance( $id )
	{
		$r = $this->_getInstance( $id );
		if( is_null( $r ) ) {
			return false;
		}
		$db = &JFactory::getDBO();
		$id = $r->getId();
		$dbId = $db->Quote( $id );
		
		// update core data
		$query = 'UPDATE '.$db->nameQuote( '#__apoth_rpt_subreports' )
			."\n".'SET '
			."\n  ".$db->nameQuote('cycle_id').' = '.$db->Quote( $r->getDatum( 'cycle_id' ) )
			."\n, ".$db->nameQuote('rpt_group_id').' = '.$db->Quote( $r->getDatum( 'rpt_group_id' ) )
			."\n, ".$db->nameQuote('reportee_id').' = '.$db->Quote( $r->getDatum( 'reportee_id' ) )
			."\n, ".$db->nameQuote('author_id').' = '.$db->Quote( $r->getDatum( 'author_id' ) )
			."\n, ".$db->nameQuote('status_id').' = '.$db->Quote( $r->getDatum( 'status_id' ) )
			."\n, ".$db->nameQuote('last_modified_by').' = '.$db->Quote( $r->getDatum( 'last_modified_by' ) )
			."\n".'WHERE'.$db->nameQuote( 'id' ).' = '.$dbId;
		$db->setQuery( $query );
		$db->Query();
		
		// early abort if we couldn't even put in the core data
		if( $db->getErrorMsg() != '' ) {
			return false;
		}
		
		// store any status comment
		$c = $r->getAndClearStatusComment();
		if( !is_null( $c ) ) {
			// This performs an update to the row which the trigger on rpt_subreports has created
			$query = 'CREATE TEMPORARY TABLE tmp_rt AS'
				."\n".'SELECT `subreport_id`, `person_id`, MAX( `time` ) AS `time`'
				."\n".'FROM `jos_apoth_rpt_subreport_status_log`'
				."\n".'WHERE `subreport_id` = '.$dbId
				."\n".'  AND `person_id` = '.$db->Quote( $r->getDatum( 'last_modified_by' ) )
				."\n".'GROUP BY `subreport_id`;'
				."\n"
				."\n".'UPDATE `jos_apoth_rpt_subreport_status_log` AS l'
				."\n".'INNER JOIN tmp_rt AS rt'
				."\n".'   ON rt.`subreport_id` = l.`subreport_id`'
				."\n".'  AND rt.`person_id` = l.`person_id`'
				."\n".'  AND rt.`time` = l.`time`'
				."\n".'SET l.`comment` = '.$db->Quote( $c ).';';
			$db->setQuery( $query );
			$db->QueryBatch();
		}
		
		// now the field data
		$d = $r->getFieldData();
		foreach( $d as $k=>$v ) {
			$deletes[] = '( subreport_id = '.$dbId.' AND field_id = '.$db->Quote( $k ).' )';
			$inserts[] = '( '.$dbId.', '.$db->Quote( $k ).', '.$db->Quote( $v ).' )';
		}
		
		if( !empty( $d ) ) {
			$query = 'START TRANSACTION;'
				."\n"
				."\n".'DELETE FROM '.$db->nameQuote( '#__apoth_rpt_subreport_data' )
				."\n".'WHERE '.implode( "\n  OR ", $deletes ).';'
				."\n"
				."\n".'INSERT INTO '.$db->nameQuote( '#__apoth_rpt_subreport_data' )
				."\n".'VALUES '.implode( "\n, ", $inserts ).';'
				."\n"
				."\n".'COMMIT;';
			$db->setQuery( $query );
			$db->QueryBatch();
		}
		
		return $db->getErrorMsg() == '';
	}
	
	function getFieldData( $id )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT d.*'
			."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_subreport_data' ).' AS d'
			."\n".'WHERE d.subreport_id = '.$db->Quote( $id );
		$db->setQuery( $query );
		$data = $db->loadAssocList();
		
		$retVal = array();
		foreach( $data as $row ) {
			$retVal[$row['field_id']] = $row['value'];
		}
		return $retVal;
	}
	
	function getStatusLog( $id, $withComment = false )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_subreport_status_log' )
			."\n".'WHERE '.$db->nameQuote( 'subreport_id' ).' = '.$db->Quote( $id )
			.($withComment ? '' : "\n".'  AND '.$db->nameQuote( 'comment' ).' IS NOT NULL' )
			."\n".'ORDER BY '.$db->nameQuote( 'time' ).' DESC';
		$db->setQuery( $query );
		$r = $db->loadAssocList();
		return $db->loadAssocList();
	}
	
}


/**
 * Report Subreport Object
 */
class ApothReportSubreport extends JObject
{
	function __construct( $data )
	{
		$this->_id     = $data['id'];
		$this->_core   = $data;
	}
	
	// #####  accessors  #####
	function getId()          { return $this->_id; }
	
	function getDatum( $key ) {
		return ( isset($this->_core[$key]) ? $this->_core[$key] : null );
	}
	
	function getFieldData()
	{
		if( !isset( $this->_fieldData ) ) {
			$fSub = ApothFactory::_( 'report.subreport' );
			$this->_fieldData = $fSub->getFieldData( $this->_id );
		}
		return $this->_fieldData;
	}
	
	function getFieldDatum( $fieldId )
	{
		if( !isset( $this->_fieldData ) ) {
			$fSub = ApothFactory::_( 'report.subreport' );
			$this->_fieldData = $fSub->getFieldData( $this->_id );
		}
		return ( isset($this->_fieldData[$fieldId]) ? $this->_fieldData[$fieldId] : null );
	}
	
	function setFieldDatum( $fieldId, $val )
	{
		if( !isset( $this->_fieldData ) ) {
			$fSub = ApothFactory::_( 'report.subreport' );
			$this->_fieldData = $fSub->getFieldData( $this->_id );
		}
		$this->_fieldData[$fieldId] = $val;
	}
	
	function setStatus( $status, $comment = null )
	{
		if( !is_null( $comment ) && ( $this->_core['status_id'] != $status ) ) {
			$this->_statusComment = $comment;
		}
		
		$u = &ApotheosisLib::getUser();
		$this->_core['status_id'] = $status;
		$this->_core['last_modified_by'] = $u->person_id;
		
		if( $status == ARC_REPORT_STATUS_INCOMPLETE || $status == ARC_REPORT_STATUS_SUBMITTED ) {
			$this->_core['author_id'] = $u->person_id;
		}
	}
	
	function getAndClearStatusComment()
	{
		$retVal = ( isset( $this->_statusComment ) ? $this->_statusComment : null );
		unset( $this->_statusComment );
		return $retVal;
	}
	
	/**
	 * Get the feedback given when this report was rejected
	 *
	 * return string  The (unescaped) feedback string
	 */
	function getFeedback( $count = 1 )
	{
		if( $this->getDatum( 'status_id' ) == ARC_REPORT_STATUS_REJECTED ) {
			if( !isset( $this->_statusCommentLog ) ) {
				$fSub = ApothFactory::_( 'report.subreport' );
				$this->_statusCommentLog = $fSub->getStatusLog( $this->_id, true );
			}
			return array_slice( $this->_statusCommentLog, 0, $count );
		}
		else {
			return null;
		}
	}
	
	function render( $part, $format, $pdfObj = null, $disabled = false )
	{
		// get the section to use for this subreport
		if( !isset( $this->_sectionId ) ) {
			$this->_loadSection();
		}
		$fSec = ApothFactory::_( 'report.section' );
		$section = $fSec->getInstance( $this->_sectionId );
		
		// get and return that section's rendering of this report
		switch( strtolower( $format ) ) {
		case( 'html' ):
			return $section->renderHTML( $this, $part, $disabled );
			break;
		
		case( 'pdf' ):
			// *** this case doesn't currently get used.
			// *** Rendering subreports in on printouts is done by directly rendering the section
			return  $section->renderPDF( $this, $part, $pdfObj );
			break;
		}
	}
	
	function getJavascript( $part )
	{
		if( !isset( $this->_sectionId ) ) {
			$this->_loadSection();
		}
		$fSec = ApothFactory::_( 'report.section' );
		$section = $fSec->getInstance( $this->_sectionId );
		
		return $section->getJavascript( $this, $part );
	}
	
	function getSectionId()
	{
		if( !isset( $this->_sectionId ) ) {
			$this->_loadSection();
		}
		return $this->_sectionId;
	}
	
	/**
	 * Get all the section candidate ids in preference order for this report
	 * (accounting for cycle and group) set best as sectionId
	 * 
	 * Sets $this->_sectionId
	 */
	function _loadSection()
	{
		$fSec = ApothFactory::_( 'report.section' );
		$req = array( 'cycle'=>$this->_core['cycle_id'], 'group'=>$this->_core['rpt_group_id'], 'subreport'=>true );
		$order = array( 'specificity'=>'DESC' );
		$candidates = $fSec->getInstances( $req, false, $order );
		$this->_sectionId = ( is_array( $candidates ) ? reset( $candidates ) : null );
	}
	
	/**
	 * Commit the subreport to the database
	 */
	function commit()
	{
		$fSub = ApothFactory::_( 'report.subreport' );
		$fSub->commitInstance( $this->_id );
		return $this->_id;
	}
}
?>