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
 * Report Field Factory
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothFactory_Report_Field extends ApothFactory
{
	var $configIds;
	
	/**
	 * To comply with automated saving of factories
	 * we must explicitly sleep any class vars in child factories
	 */
	function __sleep()
	{
		$parentVars = parent::__sleep();
		$myVars = array( 'configIds' );
		$allVars = array_merge( $parentVars, $myVars );
	
		return $allVars;
	}
	
	function &getDummy( $id )
	{
		if( $id >= 0 ) {
			$r = null;
			return $r;
		}
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = ApothReportField::_( 'report.text', array( 'id'=>$id ) );
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
			$query = 'SELECT f.*, ft.type, ft.lookup_source'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_fields' ).' AS f'
				."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_rpt_field_types' ).' AS ft'
				."\n".'   ON ft.id = f.type_id'
				."\n".'WHERE f.id = '.$db->Quote( $id );
			$db->setQuery($query);
			$data = $db->loadAssoc();
			
			$r = ApothReportField::_( $data['type'], $data );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	function &getInstances( $requirements, $init = true, $orders )
	{
		$sId = $this->_getSearchId( $requirements );
		$ids = $this->_getInstances($sId);
		
		if( is_null($ids) ) {
			$db = &JFactory::getDBO();
			
			$where = array();
			$join = array();
			$orderBy = array();
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
				
				case( 'section' ):
					$join['section'] = 'INNER JOIN '.$db->nameQuote( '#__apoth_rpt_section_fields' ).' AS sf'
						."\n".'   ON sf.field_id = f.id';
					$where[] = 'sf.section_id'.$assignPart;
					break;
				
				case( 'part' ):
					$where[] = 'f.web_part'.$assignPart;
					break;
				
				case( 'format' ):
					switch( strtolower( $val ) ) {
					case( 'html' ):
						$where[] = 'f.web_displayed = 1';
						break;
					
					case( 'pdf' ):
						$where[] = 'f.print_displayed = 1';
						break;
					}
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
					case( 'section_order' ):
						$join['section'] = 'INNER JOIN '.$db->nameQuote( '#__apoth_rpt_section_fields' ).' AS sf'
							."\n".'   ON sf.field_id = f.id';
						$orderBy[] = 'sf.order '.$orderDir;
					}
				}
			}
			
			// run the query
			$query = 'SELECT f.*, ft.type, ft.lookup_source'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_fields' ).' AS f'
				."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_rpt_field_types' ).' AS ft'
				."\n".'   ON ft.id = f.type_id'
				.( empty($join)  ? '' : "\n".implode("\n", $join) )
				.( empty($where) ? '' : "\nWHERE ".implode("\n AND ", $where) )
				.( empty($orderBy) ? '' : "\n ORDER BY ".implode(', ', $orderBy) );
			$db->setQuery( $query );
			$data = $db->loadAssocList( 'id' );
//			debugQuery( $db, $data );
			
			$ids = array_keys( $data );
			$this->_addInstances( $sId, $ids );
			
			if( $init ) {
				$existing = $this->_getInstances();
				$newIds = array_diff( $ids, $existing );
				
				if( !empty($newIds) ) {
					// initialise and cache
					foreach( $newIds as $id ) {
						$objData = $data[$id];
						$obj = ApothReportField::_( $objData['type'], $objData );
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
	
	
	function loadConfig( $cycleId, $layoutId, $sectionId, $fieldId, $groupId )
	{
		$db = &JFactory::getDBO();
		$dbGroupId = $db->Quote( $groupId );
		
		$where = array();
		$join = array();
		$orderBy = array();
		
		$where[] = '('.$db->nameQuote( 'cycle_id' )   .' IS NULL ' .( !is_null( $cycleId )   ? ' OR '.$db->nameQuote( 'cycle_id' )  .'='.$db->Quote( $cycleId )   : '' ).')';
		$where[] = '('.$db->nameQuote( 'layout_id' )  .' IS NULL ' .( !is_null( $layoutId )  ? ' OR '.$db->nameQuote( 'layout_id' ) .'='.$db->Quote( $layoutId )  : '' ).')';
		$where[] = '('.$db->nameQuote( 'section_id' ) .' IS NULL ' .( !is_null( $sectionId ) ? ' OR '.$db->nameQuote( 'section_id' ).'='.$db->Quote( $sectionId ) : '' ).')';
		$where[] = '('.$db->nameQuote( 'field_id' )   .' IS NULL ' .( !is_null( $fieldId )   ? ' OR '.$db->nameQuote( 'field_id' )  .'='.$db->Quote( $fieldId )   : '' ).')';
		
		$orderBy[] = 'IF( fc.cycle_id IS NULL, 0, 1 ) DESC';
		$orderBy[] = 'IF( fc.layout_id IS NULL, 0, 1 ) DESC';
		$orderBy[] = 'IF( fc.section_id IS NULL, 0, 1 ) DESC';
		$orderBy[] = 'IF( fc.field_id IS NULL, 0, 1 ) DESC';
		
		if( !is_null( $groupId ) ) {
			$dbAnc = $db->nameQuote( 'tmp_course_ancestry' );
			$preQuery = 'CREATE TEMPORARY TABLE '.$dbAnc.' AS '
				."\n".'SELECT a1.*, COUNT( a2.id ) AS `level`'
				."\n".'FROM `jos_apoth_cm_courses_ancestry` AS a1'
				."\n".'INNER JOIN `jos_apoth_cm_courses_ancestry` AS a2'
				."\n".'   ON a2.id = a1.ancestor'
				."\n".'WHERE a1.id = '.$dbGroupId
				."\n".'GROUP BY a1.id, a1.ancestor'
				."\n".'ORDER BY a1.id, COUNT( a2.id ) ASC;'
				."\n"
				."\n".'ALTER TABLE '.$dbAnc
				."\n".'CHANGE `ancestor` `ancestor` INT( 10 ) NULL;'
				."\n"
				."\n".'INSERT INTO '.$dbAnc
				."\n".'VALUES ( '.$dbGroupId.', NULL, 0 )';
			$db->setQuery( $preQuery );
			$db->queryBatch();
//			debugQuery( $db );
			
			$join[] = 'INNER JOIN '.$dbAnc.' AS ta'
				."\n".'   ON ta.ancestor <=> fc.rpt_group_id';
			$where[] = 'ta.'.$db->nameQuote( 'id' ).' = '.$db->Quote( $groupId );
			$orderBy[] = 'ta.'.$db->nameQuote( 'level' ).' DESC';
		}
		else {
			$where[] = '('.$db->nameQuote( 'rpt_group_id' ).' IS NULL '.( !is_null( $groupId )   ? ' OR '.$db->nameQuote( 'rpt_group_id' ).'='.$db->Quote( $groupId ) : '' ).')';
			$orderBy[] = 'IF( fc.rpt_group_id IS NULL, 0, 1 ) DESC';
		}
		
		$query = 'SELECT fc.id, fc.data'
			."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_field_config' ).' AS fc'
			.( empty($join)  ? '' : "\n".implode("\n", $join) )
			.( empty($where) ? '' : "\nWHERE ".implode("\n AND ", $where) )
			.( empty($orderBy) ? '' : "\n ORDER BY ".implode(', ', $orderBy) );
		$db->setQuery( $query );
		$raw = $db->loadAssocList();
//		dumpQuery( $db, $raw );
		
		if( !is_null( $groupId ) ) {
			$db->setQuery( 'DROP TABLE '.$dbAnc );
			$db->query();
		}
		
		// Handle any config lookup ids
		foreach( $raw as $i=>$row ) {
			if( substr( $row['data'], 0, 7 ) == 'lookup:' ) {
				$lookups[$i] = substr( $row['data'], 7 );
			}
		}
		if( !empty( $lookups ) ) {
			$query = 'SELECT fc.id, fc.data'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_field_config' ).' AS fc'
				."\n".'WHERE id IN ('.implode( ', ', $lookups ).')';
			$db->setQuery( $query );
			$lookupData = $db->loadAssocList( 'id' );
			
			foreach( $lookups as $i=>$l ) {
				$raw[$i]['data'] = $lookupData[$l]['data'];
			}
		}
		
		$this->configIds = array();
		$config = array();
		foreach( $raw as $row ) {
			$this->configIds[] = $row['id'];
			$d = json_decode( $row['data'], true );
			if( is_array( $d ) ) {
				foreach( $d as $k=>$v ) {
					if( !isset( $config[$k] ) ) {
						$config[$k] = $v;
					}
				}
			}
		}
		
		return $config;
	}
	
	function loadStatements( $cycleId, $layoutId, $sectionId, $fieldId, $groupId )
	{
		$this->loadConfig( $cycleId, $layoutId, $sectionId, $fieldId, $groupId );
		
		$db = &JFactory::getDBO();
		$ids = array();
		foreach( $this->configIds as $cId ) {
			$ids[] = $db->Quote( $cId );
		}
		
		if( empty( $ids ) ) {
			$retVal = array();
		}
		else {
			$query = 'SELECT *'
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_statements' ).' AS s'
				."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_rpt_statement_config' ).' AS c'
				."\n".'   ON c.statement_id = s.id'
				."\n".'WHERE c.field_config_id IN ('.implode( ', ', $ids ).')'
				."\n".'ORDER BY c.order';
			$db->setQuery( $query );
			$retVal = $db->loadAssocList();
		}
		return $retVal;
	}
	
	function loadMergeWords()
	{
		$db = &JFactory::getDBO();
		$retVal = array();
		
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote( '#__apoth_rpt_merge_words' ).' AS w'
			."\n".'LEFT JOIN '.$db->nameQuote( '#__apoth_rpt_merge_word_opts' ).' AS o'
			."\n".'  ON o.word_id = w.id'
			."\n".'ORDER BY w.id, o.opt_id';
		$db->setQuery( $query );
		$words = $db->loadAssocList();
		if( empty( $words ) ) { $words = array(); }
		$retVal = array();
		foreach( $words as $row ) {
			if( !isset( $retVal[$row['word']] ) ) {
				$retVal[$row['word']] = array(
					'word'=>$row['word'],
					'handler'=>$row['handler'],
					'datum'=>$row['datum'],
					'options'=>array()
				);
				
			}
			$retVal[$row['word']]['options'][$row['opt_id']] = $row['option'];
		}
		
		return $retVal;
	}
	
}


/**
 * Report Field Object
 */
class ApothReportField extends JObject
{
	static $_loadedTypes = array();
	static $_incFiles = array();
	static $_mergeWords = array();
	
	function _( $ident, $data )
	{
		$t = &self::$_loadedTypes;
		
		$ident = strtolower( $ident );
		$parts = explode( '.', $ident );
		if( count( $parts ) != 2 ) {
			$parts = array( 'report', 'text' );
		}
		$cName = $parts[0]; // component name
		$fName = $parts[1]; // field name
		
		if( !isset( $t[$cName] ) ) {
			$t[$cName] = true;
			// include the definition file
			$fileName = JPATH_SITE.DS.'components'.DS.'com_arc_'.$cName.DS.'helpers'.DS.'report.php';
			if( file_exists($fileName) ) {
				self::$_incFiles[] = $fileName;
				require_once($fileName);
			}
		}
		
		$cNameFull = 'ApothReportField_'.ucfirst($cName).'_'.ucfirst($fName);
		if( class_exists( $cNameFull ) ) { 
			return new $cNameFull( $data );
		}
		else {
			if( !isset( $t['report'] ) ) {
				$t['report'] = true;
				$fileName = JPATH_SITE.DS.'components'.DS.'com_arc_report'.DS.'helpers'.DS.'report.php';
				self::$_incFiles[] = $fileName;
				require_once($fileName);
			}
			echo 'Unsupported field type: '.$ident.' for field '.$data['id'].'<br />';
			return new ApothReportField_Report__Undefined( $data );
		}
	}
	
	function _mergeWord( $ident, $datum, $options )
	{
		$t = &self::$_loadedTypes;
		$mw = &self::$_mergeWords;
		
		$ident = strtolower( $ident );
		$parts = explode( '.', $ident );
		if( count( $parts ) != 2 ) {
			$parts = array( 'report', 'substitute' );
		}
		$cName = $parts[0]; // component name
		$fName = $parts[1]; // handler function name
		
		if( !isset( $t[$cName] ) ) {
			// include the definition file
			$fileName = JPATH_SITE.DS.'components'.DS.'com_arc_'.$cName.DS.'helpers'.DS.'report.php';
			if( file_exists($fileName) ) {
				self::$_incFiles[] = $fileName;
				require_once($fileName);
				
			}
		}
		
		$cNameFull = 'ApothReportMergeWords_'.ucfirst($cName);
		if( !isset( $mw[$cName] ) && class_exists( $cNameFull ) ) {
			$mw[$cName] = new $cNameFull();
		}
		if( method_exists( $mw[$cName], $fName ) ) { 
			return $mw[$cName]->$fName( $datum, $options );
		}
		else {
			echo 'could not find '.$cNameFull.'-&gt;'.$fName.'<br />';
			return null;
		}
	}
	
	
	
	
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
	
	
	function setContext( $cycleId = null, $layoutId = null, $sectionId = null, $groupId = null )
	{
		$this->_context['cycleId'] = $cycleId;
		$this->_context['layoutId'] = $layoutId;
		$this->_context['sectionId'] = $sectionId;
		$this->_context['groupId'] = $groupId;
	}
	
	function setConfig( $overrides = array() )
	{
		$fField = ApothFactory::_( 'report.field' );
		$this->_config = $fField->loadConfig( $this->_context['cycleId'], $this->_context['layoutId'], $this->_context['sectionId'], $this->_id, $this->_context['groupId'] );
		foreach( $overrides as $k=>$v ) {
			$this->_config[$k] = $v;
		}
//		dump( $this->_config, 'field config' );
	}
	
	function getConfig( $key )
	{
		return ( isset($this->_config[$key]) ? $this->_config[$key] : null );
	}
	
	/**
	 * Sets an array of the key data to use from the given report
	 * 
	 * @param obkect $data  The report object from which to extract key data
	 */
	function setReportData( $report )
	{
		$this->_rptData = array(
			'id'=>$report->getId(),
			'rpt_group_id'=>$report->getDatum( 'rpt_group_id' ),
			'reportee_id'=>$report->getDatum( 'reportee_id' ),
			'author_id'=>$report->getDatum( 'author_id' ),
		);
	}
	
	function renderHTML( $html, $absolute = false )
	{
		$v = ( is_null( $html ) ? '--'.$this->_config['default'] : $html );
		$s = 'style="top: '.$this->_core['web_t'].'px; left: '.$this->_core['web_l'].'px; width: '.$this->_core['web_width'].'px; height: '.$this->_core['web_height'].'px;"';
//		return '<div class="field f_'.$this->_id.'" '.$s.'>('.$this->_core['name'].' - '.get_class( $this ).')'.$v.'</div>';
		return '<div class="field f_'.$this->_id.($absolute ? ' absolute' : '').'" '.$s.'>'.$v.'</div>';
	}
	
	function getHTMLBottom()
	{
		return $this->_core['web_t'] + $this->_core['web_height'];
	}
	
	function getScripts()
	{
		return null;
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

/**
 * Just a parent for all the merge word classes
 */
class ApothReportMergeWords
{
	
}

?>