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
 * Report Section Factory
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothFactory_Report_Section extends ApothFactory
{
	function &getDummy( $id )
	{
		if( $id >= 0 ) {
			$r = null;
			return $r;
		}
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothReportSection( array( 'id'=>$id ) );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified section, creating the object if it didn't already exist
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT s.*'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_sections' ).' AS s'
				."\n".'WHERE s.id = '.$db->Quote( $id );
			$db->setQuery($query);
			$data = $db->loadAssoc();
			
			$r = new ApothReportSection( $data );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	function &getInstances( $requirements, $init = true, $orders = null )
	{
		$sId = $this->_getSearchId( $requirements );
		$ids = $this->_getInstances($sId);
		$restrict = $this->getParam( 'restrict' );
		$now = date( 'Y-m-d H:i:s' );
		
		if( is_null($ids) ) {
			$db = &JFactory::getDBO();
			
			$select = array();
			$where = array();
			$join = array();
			$orderBy = array();
			$preQuery = $postQuery = '';
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
				
				case( 'subreport' ):
					$where[] = 's.subreport = '.(int)(bool)$val;
					break;
				
				case( 'group' ):
					$dbAnc = $db->nameQuote( 'tmp_course_ancestry' );
					$preQuery = 'CREATE TEMPORARY TABLE '.$dbAnc.' AS '
						."\n".'SELECT a1.*, COUNT( a2.id ) AS `level`'
						."\n".'FROM `jos_apoth_cm_courses_ancestry` AS a1'
						."\n".'INNER JOIN `jos_apoth_cm_courses_ancestry` AS a2'
						."\n".'   ON a2.id = a1.ancestor'
						."\n".'WHERE a1.id'.$assignPart
						."\n".'GROUP BY a1.id, a1.ancestor'
						."\n".'ORDER BY a1.id, COUNT( a2.id ) ASC;';
					
					$postQuery = 'DROP TABLE '.$dbAnc.';';
					
					$col = $db->nameQuote( 'rpt_group_id' );
					$join['to_config1'] = 'INNER JOIN '.$db->nameQuote( '#__apoth_rpt_section_config' ).' AS sc'
						."\n".' ON ( sc.section_id = s.id OR sc.section_id IS NULL )';
					$join['to_config2'] = 'CROSS JOIN '.$dbAnc.' AS ta';
					$where[] = 'ta.id'.$assignPart;
					$where[] = '(ta.ancestor = sc.rpt_group_id OR sc.rpt_group_id IS NULL)';
					break;
					
				case( 'cycle' ):
					$join['to_config1'] = 'INNER JOIN '.$db->nameQuote( '#__apoth_rpt_section_config' ).' AS sc'
						."\n".' ON ( sc.section_id = s.id OR sc.section_id IS NULL )';
					
					$col = ( $col == 'cycle' ? $db->nameQuote( 'cycle_id' ) : $col );
					$where[] = 'sc.'.$col.$assignPart;
					break;
				}
			}
			
			// now set up any 'ORDER BY' clause
			if( !is_null( $orders ) ) {
				foreach( $orders as $orderOn=>$orderDir ) {
					if( $orderDir == 'a' ) {
						$orderDir = 'ASC';
					}
					elseif( $orderDir == 'd' ) {
						$orderDir = 'DESC';
					}
					
					switch( $orderOn ) {
					case( 'specificity' ):
						if( isset($requirements['cycle']) && isset($requirements['group'] ) ) {
							$spec = '( IF( sc.cycle_id IS NULL , 1, 2 ) * IF( sc.rpt_group_id IS NULL , 0, ta.level ) )';
						}
						elseif( isset($requirements['cycle'] ) ) {
							$spec = '( IF( sc.cycle_id IS NULL , 1, 2 ) )';
						}
						elseif( isset($requirements['group'] ) ) {
							$spec = '( IF( sc.rpt_group_id IS NULL , 0, ta.level ) )';
						}
						elseif( isset($requirements['group'] ) ) {
							$spec = '0';
						}
						$select[] = $spec.' AS spec';
						$orderBy[] = 'spec '.$orderDir;
					}
				}
			}
			
			if( !empty( $preQuery ) ) {
				$db->setQuery( $preQuery );
				$db->queryBatch();
			}
			
			// run the query
			$query = 'SELECT DISTINCT s.*'
				.( empty($select) ? '' : ', '.implode(', ', $select) )
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_sections' ).' AS s'
				.( empty($join)  ? '' : "\n".implode("\n", $join) )
				.( empty($where) ? '' : "\nWHERE ".implode("\n AND ", $where) )
				.( empty($orderBy) ? '' : "\n ORDER BY ".implode(', ', $orderBy) );
			if( $restrict ) {
				$query = ApotheosisLibAcl::limitQuery($query, 'report.cycles'); 
			}
			
			$db->setQuery( $query );
			$data = $db->loadAssocList();
//			debugQuery( $db, $data );
			
			if( !empty( $postQuery ) ) {
				$db->setQuery( $postQuery );
				$db->queryBatch();
			}
			
			// possibility of a section appearing twice in the list means arary_keys is not appropriate
			foreach( $data as $datum ) {
				$ids[] = $datum['id'];
			}
			$this->_addInstances( $sId, $ids );
			
			if( $init ) {
				$existing = $this->_getInstances();
				$newIds = array_diff( $ids, $existing );
				
				if( !empty($newIds) ) {
					// initialise and cache
					foreach( $newIds as $id ) {
						$objData = $data[$id];
						$obj = new ApothReportSection( $objData );
						$this->_addInstance( $id, $obj );
						unset( $obj );
					}
				}
			}
		}
		
		return $ids;
	}
	
	
	function commitInstance( $id )
	{
	}
	
}


/**
 * Report Section Object
 */
class ApothReportSection extends JObject
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
	
	
	function renderHTML( $report, $part, $disabled = false )
	{
		// find out what fields this section has for the given $part
		$fCyc = ApothFactory::_( 'report.cycle' );
		$fField = ApothFactory::_( 'report.field' );
		
		$cycleId = $report->getDatum( 'cycle_id' );
		$cycle = $fCyc->getInstance( $cycleId );
		$layoutId = $cycle->getDatum( 'layout_id' );
		$sectionId = $this->_id;
		$groupId = $report->getDatum( 'rpt_group_id' );
		
		$p = ( $part == 'brief' ? 'brief_1' : $part );
		$fields = $fField->getInstances( array( 'section'=>$this->_id, 'part'=>$p, 'format'=>'html' ), true, array( 'section_order'=>'ASC' ) );
		
		if( $part == 'brief' ) {
			$fields2 = $fField->getInstances( array( 'section'=>$this->_id, 'part'=>'brief_2', 'format'=>'html' ), true, array( 'section_order'=>'ASC' ) );
			if( !empty( $fields2 ) ) {
				$fields = array_merge( $fields, array( 'changePart' ), $fields2 );
			}
		}
		
		$out = '<div class="part p_'.$p.'" style="height: ~BOTTOMHOLDER~px">'; // this is where all the html will be buffered
		$maxBottom = 0; // track the input which requires most height
		
		foreach( $fields as $fId ) {
			plgSystemArc_log::startTimer( 'report object_section renderHTML fieldloop' );
			$rptData = $report->getFieldDatum( $fId );
			$field = &$fField->getInstance( $fId );
			if( $fId == 'changePart' ) {
				$out = str_replace( '~BOTTOMHOLDER~', $maxBottom, $out ).'</div>';
				
				$moreFields = $fField->getInstances( array( 'section'=>$this->_id, 'part'=>'more', 'format'=>'html' ), true, array( 'section_order'=>'ASC' ) );
				if( !empty( $moreFields ) ) {
					$out .= '<div class="part p_more_wrapper"><div class="more_loader">'.JHTML::_( 'arc.loading' ).'</div></div>';
				}
				
				$out .= '<div class="part p_brief_2" style="height: ~BOTTOMHOLDER~px">';
				$maxBottom = 0;
			}
			elseif( !is_null( $field ) ) {
				$field->setContext( $cycleId, $layoutId, $sectionId, $groupId );
				if( $disabled ) {
					$field->setConfig( array( 'disabled'=>true ) );
				}
				else {
					$field->setConfig();
				}
				$field->setReportData( $report );
				
				$wd = $field->getConfig( 'web_displayed' );
				if( is_null( $wd ) == ($wd == 0) ) {
					$maxBottom = max( $maxBottom, $field->getHTMLBottom() );
					plgSystemArc_log::startTimer( 'report object_section renderHTML fieldRender' );
					$out .= $field->renderHTML( $rptData );
					plgSystemArc_log::stopTimer( 'report object_section renderHTML fieldRender' );
				}
			}
			plgSystemArc_log::stopTimer( 'report object_section renderHTML fieldloop' );
		}
		
		$out = str_replace( '~BOTTOMHOLDER~', $maxBottom, $out )
			.'</div>';
		
		return $out;
	}
	
	function getJavascript( $report, $part )
	{
		$fCyc = ApothFactory::_( 'report.cycle' );
		$fField = ApothFactory::_( 'report.field' );
		
		$p = ( $part == 'brief' ? 'brief_1' : $part );
		$fields = $fField->getInstances( array( 'section'=>$this->_id, 'part'=>$p, 'format'=>'html' ), true, array( 'section_order'=>'ASC' ) );
		if( $part == 'brief' ) {
			$p = 'brief_2';
			$fields2 = $fField->getInstances( array( 'section'=>$this->_id, 'part'=>$p, 'format'=>'html' ), true, array( 'section_order'=>'ASC' ) );
			$fields = array_merge( $fields, array( 'changePart' ), $fields2 );
		}
		
		$retVal = array();
		foreach( $fields as $fId ) {
			$field = &$fField->getInstance( $fId );
			$s = $field->getScripts();
			if( !empty( $s ) ) {
				$retVal = array_merge( $retVal, $s );
			}
		}
		return $retVal;
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