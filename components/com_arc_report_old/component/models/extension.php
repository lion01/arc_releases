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

jimport( 'joomla.application.component.model' );

/**
 * Reports Model Extension
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsModel extends JModel
{
	/** @var state information */
	var $_state;
	
	var $_cycle;
	
	var $_mergeWords;
	
	var $_mergeStart;
	var $_mergeEnd;
	
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		
		$paramsObj = &JComponentHelper::getParams('com_arc_report');
		$c = ( (isset($config['cycle']) && !empty($config['cycle'])) ? $config['cycle'] : JRequest::getVar('report_cycle', $paramsObj->get( 'current_cycle', false )) );
		
		$this->setCycle( $c );
		$this->_mergeStart = $paramsObj->get( 'merge_start', '((' );
		$this->_mergeEnd   = $paramsObj->get( 'merge_end',   '))' );
		$this->_lastGId = false;
		
		$db = &JFactory::getDBO();
		$db->setQuery( 'SELECT *'
			."\n".' FROM #__apoth_rpt_merge_words' );
		$this->_mergeWords = $db->loadObjectList( 'word' );
	}
	
	/**
	 * Sets the cycle used for this model.
	 * Clears out all other class variables so we have a clean slate
	 */
	function setCycle( $id )
	{
		$vars = get_object_vars($this);
		unset($vars['_db']);         // don't want to destroy the db object as we kinda need that
		unset($vars['_state']);      // likewise the vital state information
		unset($vars['_mergeStart']); // ... and the merge info
		unset($vars['_mergeEnd']);
		unset($vars['_mergeWords']);
		unset($vars['_name']);       // keep the model name too
		if( is_array($vars) ) {
			foreach($vars as $k=>$v) {
				unset($this->$k);
			}
		}
		
		$this->_cycle = ApothReportLib::getCycle( $id );
	}
	
	function getCycleId()
	{
		return $this->_cycle->id;
	}
	
	function getCycleStart()
	{
		return $this->_cycle->valid_from;
	}
	
	function getCycleEnd()
	{
		return $this->_cycle->valid_to;
	}
	
	function getCycleAllowMultiple()
	{
		return (bool)$this->_cycle->allow_multiple;
	}

	function getCycleReChecker()
	{
		return $this->_cycle->rechecker;
	}
	
	function getCycleGroups()
	{
		return $this->_cycle->groups;
	}
	
	function getCycleGroupsList()
	{
		return $this->_cycle->groupsList;
	}
	
	/**
	 * Retrieves the year with which we are currently dealing
	 */
	function getYear()
	{
		return $this->_cycle->year_group;
	}
	
	/**
	 * Retrieves the string used to indicate the start of a merge word
	 */
	function getMergeStart()
	{
		return $this->_mergeStart;
	}
	
	/**
	 * Retrieves the string used to indicate the end of a merge word
	 */
	function getMergeEnd()
	{
		return $this->_mergeEnd;
	}
	
	/**
	 * Retrieves the id-indexed array of merge word data objects
	 */
	function getMergeWords()
	{
		return $this->_mergeWords;
	}
	
	/**
	 * Get a regex which will match a merge word
	 *
	 * @param $mWord string  The merge word to regex-ify
	 * @param $allowNongreedy boolean  Should we use the non-greedy "?" modifier if not presenting a list of options
	 */
	function mergeWordAsRegex( $mWord, $allowNongreedy = true )
	{
		$options = array();
		if( array_key_exists($mWord, $this->_mergeWords) ) {
			$m = $this->_mergeWords[$mWord];
			
			if( !is_null($m->male) ) {
				$options[] = preg_quote( $m->male );
			}
			if( !is_null($m->female) ) {
				$options[] = preg_quote( $m->female );
			}
			if( !is_null($m->neuter) ) {
				$options[] = preg_quote( $m->neuter );
			}
		}
		
		if( !empty($options) ) {
			$retVal = '('.implode('|', $options).')';
		}
		else {
			$retVal = '(.*'.($allowNongreedy ? '?' : '').')';
		}
		return $retVal;
	}
	
	/**
	 * Sets up the data which may need to be merged into a statement based on a pupil / group id pair
	 */
	function setMergeDetails( $pId, $gId )
	{
		require_once( JPATH_SITE.DS.'components'.DS.'com_arc_course'.DS.'helpers'.DS.'data_access.php' );
		$gId2 = ApotheosisCoursesData::getTwin( $gId );
		
		$db = &JFactory::getDBO();
		$qStr = 'SELECT p.*, c.*, p.id AS pupil_id, c.id AS course_id'
			."\n".' FROM #__apoth_ppl_people AS p'
			."\n".' INNER JOIN #__apoth_tt_group_members AS gm'
			."\n".'   ON gm.person_id = p.id'
			."\n".' INNER JOIN #__apoth_cm_courses AS c'
			."\n".'   ON c.id = gm.group_id'
			."\n".' WHERE p.id = '.$db->quote($pId)
			."\n".'   AND c.id = '.$db->quote($gId2);
		$db->setQuery( $qStr );
		$this->_mergeData = $db->loadObject();
		
		if( $gId !== $this->_lastGId ) {
			$this->_lastGId;
			// go through the ancestors of the current report's group to find the subject, course, etc
			$this->_heritage = ApotheosisLibDb::getAncestors($this->_mergeData->course_id, '#__apoth_cm_courses', 'id', 'parent', true);
		}
		
		end($this->_heritage);
		$cur = current($this->_heritage);
		while( $cur !== false ) {
			$t = $cur->ext_type;
			$T = ucfirst( $cur->ext_type );
			$this->_mergeData->$t = $cur->fullname;
			$this->_mergeData->$T = ucfirst($cur->fullname);
			$cur = prev($this->_heritage);
		}
		$this->_mergeData->Name = ApotheosisLib::nameCase( 'pupil', $this->_mergeData->title, $this->_mergeData->firstname );
	}
	
	/**
	 * Takes a merge field and fills in the relevant data
	 */
	function mergeWord( $mergeWord )
	{
//		echo 'merging '.$mergeWord.'<br />';
//		echo 'with mergewords: ';var_dump_pre($this->_mergeWords);
		if( isset($this->_mergeWords[$mergeWord]) ) {
			$mWord = $this->_mergeWords[$mergeWord];
		
			if( !is_null($mWord->property) && !is_null($this->_mergeData->{$mWord->property}) ) {
				$retVal = $this->_mergeData->{$mWord->property};
			}
			elseif( !is_null($mWord->male) && !is_null($mWord->female) && !is_null($this->_mergeData->gender) && ($this->_mergeData->gender != '') ) {
				$gender = ( ($this->_mergeData->gender == 'M') ? 'male' : 'female' );
				$retVal = $mWord->$gender;
			}
			elseif( !is_null($mWord->neuter) ) {
				$retVal = $mWord->neuter;
			}
		}
		elseif( ($tmp = JRequest::getVar( $mergeWord, false )) !== false ) {
			$retVal = $tmp;
		}
		else {
			$retVal = false;
		}
		
		return $retVal;
	}
	
	/**
	 * Takes a string, parses it for merge fields, and fills in the relevant data
	 *
	 * @param $text string  The text to parse
	 * @param $regex boolean optional  Is the text to be used in a regex search?
	 */
	function mergeText( $text, $regex = false )
	{
//		echo 'merging '.$text.'<br />';
//		echo 'with mergedata: ';var_dump_pre($this->_mergeData);
		if( !isset($this->_mergeData) ) {
			return $text;
		}
		$start = preg_quote( $this->_mergeStart, '/' );
		$end   = preg_quote( $this->_mergeEnd, '/' );
		$search = '/(?<='.$start.').+?(?='.$end.')/';
		$matches = array();
		preg_match_all( $search, $text, $matches );
		
		$terms = reset($matches);
		if( is_array( $terms ) ) {
			foreach( $terms as $term ) {
				$replace = $this->mergeWord( $term );
				if( $replace === false ) {
					$replace = ( $regex ? $this->mergeWordAsRegex( $term ) : $this->_mergeStart.$term.$this->_mergeEnd );
				}
				else {
					$replace = ( $regex ? preg_quote($replace) : $replace );
				}
				
				$text = str_replace($this->_mergeStart.$term.$this->_mergeEnd, $replace, $text);
			}
		}
		
		return $text;
	}
}

?>