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
class ApothFactory_Behaviour_Report extends ApothFactory
{
	function &getDummy()
	{
		$id = -1;
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothAction( array() );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified report, creating the object if it didn't already exist
	 * In order for the report to be of any use its "init" function must be called with appropriate params
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothReport();
			$this->_addInstance( $id, $r );
		}
		return $r;
	}

}


/**
 * Behaviour Action Object
 */
class ApothReport extends JObject
{
	/**
	 * All the data for this message (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_scores = array();
	
	function __construct()
	{
		$this->fSco = ApothFactory::_( 'behaviour.Score', $this->fSco );
		$this->fScoAgg = ApothFactory::_( 'behaviour.ScoreAggregate', $this->fScoAgg );
	}
	
	function init( $requirements, $aggregate )
	{
		// work out extra values determined by the aggregate type
		$this->_agg = $aggregate;
		switch( $this->_agg ) {
		case( 'groups' ):
			$aggDb = 'group_id';
			$labelFunc = 'course.name';
			$labelParam = null;
			break;
		
		case( 'tutor' ):
			$aggDb = 'tutor_id';
			$labelFunc = 'course.name';
			$labelParam = null;
			break;
		
		case( 'person_id' ):
			$aggDb = 'person_id';
			$labelFunc = 'people.displayname';
			$labelParam = 'pupil';
			break;
		
		case( 'author' ):
			$aggDb = 'author';
			$labelFunc = 'people.displayname';
			$labelParam = 'teacher';
			break;
		}
		
		$requirements = $this->_fillRequirements( $requirements );
		
		// work out what our requirements actually are in light of enrolments and limits
		if( isset($requirements['limits']) ) {
			$requirements[$this->_agg] = $this->_getLimitedSeries( $requirements, $aggDb );
			unset( $requirements['limits'] );
			unset( $requirements['limits_val'] );
			// since tutor group-related things rely on pupil lists, let's refine the pupil list
			if( $this->_agg == 'tutor' ) {
				$tmp = array();
				foreach( $requirements[$this->_agg] as $tg ) {
					$tgId = $tg->tutor_id;
					foreach( $this->_tutorGroups as $pId=>$gId ) {
						if( $gId == $tg ) {
							$tmp[] = $pId;
						}
					}
				}
				$requirements['person_id'] = $tmp;
			}
		}
		
		// set up the series ids based on the requirements
		$this->_setSeries( $requirements, $aggregate );
		
		// construct a date list to ensure consistent data points
		$this->_dates = array();
		$cur = strtotime( $requirements['start_date'] );
		$end = strtotime( $requirements['end_date'] );
		while( $cur <= $end ) {
			$this->_dates[] = date('Y-m-d', $cur);
			$cur = strtotime( '+1 day', $cur );
		}
		
		// get the raw score data
		$this->_scores = $this->fSco->getInstances( $requirements );
		
		// check to see if we should proceed based on whether or not we got a sensibly sized set of score objects
		if( $this->_scores === 0 ) {
			$this->_series = array();
		}
		elseif( $this->_scores ) {
			// if we didn't manage to work out series ids from requirements, let's try to derive them from the scores
			if( $this->_seriesIds == array('all') ) {
				$this->_setSeries( $requirements, $aggregate );
			}
			// still no luck working out series ids ? :-( Let's not bother then
			// *** all-in-one-series doesn't give good results, but shouldn't really happen
			if( $this->_seriesIds == array( 'all' ) ) {
				$this->_agg = null;
			}
			
			// get the starting score values
//			if( $params['from_0'] !== true ) {
				$r = $requirements;
				$r['end_date'] = $requirements['start_date'];
				$r['start_date'] = ApotheosisLib::getEarlyDate();
				$r['_aggregate'] = $aggDb;
				$this->_initialScores = $this->fScoAgg->getInstances( $r );
//			}
			
			// store starting values for each series
			$this->_series = array();
			foreach( $this->_seriesIds as $seriesId ) {
				$label = ApotheosisData::_( $labelFunc, $seriesId, $labelParam );
				$highlight = isset( $requirements['highlightDate'] ) ? $requirements['highlightDate'] : null;
				$this->_series[$seriesId] = array( '_meta'=>array('init'=>0, 'label'=>$label, 'highlight'=>$highlight) );
			}
			foreach( $this->_initialScores as $isId ) {
				$scoAgg = $this->fScoAgg->getInstance( $isId );
				$ser = $this->_getSeriesId( $scoAgg );
				$this->_series[$ser]['_meta']['init'] = $scoAgg->getDatum('total');
			}
			
			$this->_msgs = array();
			
			$this->_setSeriesData();
			$this->_colors  = ApotheosisData::_( 'message.color', $this->_msgs );
			$this->_orders  = ApotheosisData::_( 'message.order', $this->_msgs );
			$this->_threads = ApotheosisData::_( 'message.threads', $this->_msgs );
		}
		else {
			$this->_series = false;
		}
	}
	
	/**
	 * Fills in the person or group ids most needed for the current aggregation
	 * 
	 * @param array $requirements  The requirements to use as a basis
	 */
	function _fillRequirements( $requirements )
	{
		if( ($this->_agg == 'groups') && !isset($requirements['groups']) ) {
			$e = ApotheosisData::_( 'timetable.studentEnrolments', $requirements );
			$tmp = array();
			foreach( $e as $pair ) {
				$tmp[$pair['group_id']] = true;
			}
			
			if( empty($tmp) ) {
				$requirements['groups'] = array( false );
			}
			else {
				$requirements['groups'] = array_keys( $tmp );
			}
		}
		elseif( (($this->_agg == 'person_id') && !isset($requirements['person_id']))
		 || (($this->_agg == 'author') && !isset($requirements['person_id'])) ) {
			$e = ApotheosisData::_( 'timetable.studentEnrolments', $requirements );
			$tmp = array();
			foreach( $e as $pair ) {
				$tmp[$pair['person_id']] = true;
			}
			
			if( empty($tmp) ) {
				$requirements['person_id'] = array( false );
			}
			else {
				$requirements['person_id'] = array_keys( $tmp );
			}
		}
		if( ($this->_agg == 'tutor') && (!isset($requirements['person_id']) || !isset($requirements['tutor'])) ) {
			$requirements['group_type'] = 'pastoral';
			if( isset($requirements['groups']) ) {
				$tmpG = $requirements['groups'];
				unset( $requirements['groups'] );
			}
			$e = ApotheosisData::_( 'timetable.studentEnrolments', $requirements );
			unset( $requirements['group_type'] );
			if( isset($tmpG) ) {
				$requirements['groups'] = $tmpG;
			}
			
			$tmp1 = array();
			$tmp2 = array();
			$this->_tutorGroups = array();
			foreach( $e as $pair ) {
				$tmp1[$pair['person_id']] = true;
				$tmp2[$pair['group_id']] = true;
				$this->_tutorGroups[$pair['person_id']] = $pair['group_id'];
			}
			
			if( !isset($requirements['person_id']) ) {
				if( empty($tmp1) ) {
					$requirements['person_id'] = array( false );
				}
				else {
					$requirements['person_id'] = array_keys( $tmp1 );
				}
			}
			if( !isset($requirements['tutor']) ) {
				if( empty($tmp2) ) {
					$requirements['tutor'] = array( false );
				}
				else {
					$requirements['tutor'] = array_keys( $tmp2 );
				}
			}
		}
		
		return $requirements;
	}
	
	function _getLimitedSeries( $requirements, $aggDb )
	{
		$r = $requirements;
		switch( $requirements['limits'] ) {
		case( 'top' ):
			$r['order'] = -1;
			$r['start_date'] = ApotheosisLib::getEarlyDate();
			break;
		
		case( 'climb' ):
			$r['order'] = -1;
			break;
		
		case( 'steady' ):
			$r['order'] = 1;
			$r['abs'] = true;
			// if we don't give a list of pupils, only those with some score change are considered
			if( !isset($requirements['person_id']) ) { // **** should be [$this->_agg] ??
				$r['person_id'] = ApotheosisData::_( 'timetable.students' );
			}
			break;
		
		case( 'decline' ):
			$r['order'] = 1;
			break;
		
		case( 'bottom' ):
			$r['order'] = 1;
			$r['start_date'] = ApotheosisLib::getEarlyDate();
			break;
		}
		$r['limit'] = $requirements['limits_val'];
		$r['_aggregate'] = $aggDb;
		unset( $r['limits'] );
		unset( $r['limits_val'] );
		$lim = $this->fScoAgg->getInstances( $r );
//		var_dump_pre( $lim, 'limited series aggregate ids' );
		if( empty($lim) ) {
			$retVal = array( false );
		}
		foreach( $lim as $lId ) {
			$scoAgg = $this->fScoAgg->getInstance( $lId );
			$retVal[] = $this->_getSeriesId( $scoAgg );
		}
//		var_dump_pre( $retVal );
		return $retVal;
	}
	
	/**
	 * work out what our series are
	 */
	function _setSeries( $requirements )
	{
//		var_dump_pre( $this->_agg );
//		var_dump_pre( $requirements );
		if( isset( $requirements[$this->_agg] ) ) {
			$this->_seriesIds = ( is_array($requirements[$this->_agg]) ? $requirements[$this->_agg] : array($requirements[$this->_agg]) );
		}
		else {
			foreach( $this->_scores as $sId ) {
				$score = &$this->fSco->getInstance( $sId );
				$id = $this->_getSeriesId( $score );
				$tmp[$id] = true;
			}
			if( empty($tmp) ) {
				$this->_seriesIds = array( 'all' );
			}
			else {
				$this->_seriesIds = array_keys( $tmp );
			}
		}
//		var_dump_pre( $this->_seriesIds, 'series Ids' );
	}
	
	/**
	 * fill each series with values of scores received on each date
	 * while we're at it let's note all the message ids
	 */
	function _setSeriesData()
	{
		foreach( $this->_scores as $sId) {
			$score = &$this->fSco->getInstance( $sId );
			$ser  = $this->_getSeriesId( $score );
			if( is_null($ser) ) {
				continue;
			}
			$date = $score->getDatum('date_issued');
			$date = reset( explode(' ', $date) );
			$val  = $score->getDatum('score');
			$msg  = $score->getDatum('msg_id');
			if( !isset($this->_series[$ser][$date]) ) {
				$this->_series[$ser][$date] = array( 'total'=>0, 'messages'=>array() );
			}
			$this->_series[$ser][$date]['total'] += $val;
			$this->_series[$ser][$date]['messages'][$sId] = $msg;
			$this->_msgs[] = $msg;
			$this->fSco->freeInstance( $sId );
		}
		foreach( $this->_series as $sk=>$series ) {
			if( count( $series ) == 1 ) {
				unset( $this->_series[$sk] );
				if( ($k = array_search( $sk, $this->_seriesIds )) !== false ) {
					unset( $this->_seriesIds[$k] );
				}
			}
		}
	}
	
	function _getSeriesId( $score )
	{
		switch( $this->_agg ) {
		case( 'groups' ):
			$id = $score->getDatum( 'group_id' );
			break;
		
		case( 'tutor' ):
			$id = $score->getDatum( 'tutor_id' );
			if( is_null($id) ) {
				$id = $this->_tutorGroups[$score->getDatum( 'person_id' )];
			}
			break;
		
		case( 'person_id' ):
			$id = $score->getDatum( 'person_id' );
			break;
		
		case( 'author' ):
			$id = $score->getDatum( 'author' );
			break;
		}
		return $id;
	}
	
	function getSeriesStatus()
	{
		if( is_array($this->_series) ) {
			$retVal = count( $this->_series );
		}
		else {
			$retVal = false;
		}
		
		return $retVal;
	}
	
	function getDates()
	{
		return $this->_dates;
	}
	
	function getSeriesIds()
	{
		return $this->_seriesIds;
	}
	
	function getSeries( $id )
	{
		return $this->_series[$id];
	}
	
	function getParsedSeries( $id )
	{
		$series = $this->_series[$id];
		$retVal = array();
		$retVal['_meta'] = $series['_meta'];
		$cur = $min = $max = $series['_meta']['init'];
		$tallyMax = $tallyAll = 0;
		$tallyThreads = array();
		foreach( $this->_dates as $date ) {
			if( $date == '_meta' || !isset($series[$date]) ) {
				continue;
			}
			
			$data = ( isset($series[$date]) ? $series[$date] : array( 'total'=>0, 'messages'=>null) );
			if( $data['total'] != 0 ) {
				$cur += $data['total'];
				if( $cur > $max ) { $max = $cur; }
				if( $cur < $min ) { $min = $cur; }
			}
			
			$tally = 0;
			$retVal[$date]['total'] = $cur;
			$retVal[$date]['tallies'] = array();
			if( is_array($data['messages']) ) {
				$retVal[$date]['messages'] = $data['messages'];
				foreach( $data['messages'] as $msgId ) {
					$c = $this->getColor( $msgId );
					if( !isset($retVal[$date]['tallies'][$c]) ) { $retVal[$date]['tallies'][$c] = 0; }
					$retVal[$date]['tallies'][$c]++;
					$tally++;
					$tallyAll++;
					$tallyThreads[$c][$this->getThread( $msgId )] = 1;
				}
				if( $tally > $tallyMax ) { $tallyMax = $tally; }
			}
		}
		$retVal['_meta']['end'] = $cur;
		$retVal['_meta']['min'] = $min;
		$retVal['_meta']['max'] = $max;
		$retVal['_meta']['tallyMax'] = $tallyMax;
		$retVal['_meta']['tallyAll'] = $tallyAll;
		$retVal['_meta']['tallyThreads'] = array();
		foreach( $tallyThreads as $k=>$v ) {
			$retVal['_meta']['tallyThreads'][$k] = count($v);
		}
		
		return $retVal;
	}
	
	function getAllSeries()
	{
		return $this->_series;
	}
	
	function getColor( $msgId )
	{
		return ( isset($this->_colors[$msgId]) ? $this->_colors[$msgId] : null );
	}
	
	function getOrder( $msgId )
	{
		return ( isset($this->_orders[$msgId]) ? $this->_orders[$msgId] : null );
	}
	
	function getThread( $msgId )
	{
		return ( isset($this->_threads[$msgId]) ? $this->_threads[$msgId] : null );
	}
	
	function getDataFile( $sIds )
	{
		$config = &JFactory::getConfig();
		$tmpName = tempnam( $config->getValue('config.tmp_path'), 'behav_'.time().'_' );
		$handle = fopen($tmpName, "w");
		fwrite( $handle, 'dates:' );
		fwrite( $handle, serialize($this->_dates) );
		fwrite( $handle, "\n" );
		foreach( $sIds as $sId ) {
			fwrite($handle, $sId.':');
			fwrite($handle, serialize($this->getParsedSeries($sId)));
			fwrite($handle, "\n");
		}
		fclose($handle);
		return $tmpName;
	}
	
	function removeDataFiles()
	{
		$config = &JFactory::getConfig();
		$dirName = $config->getValue('config.tmp_path');
		$dir = opendir( $dirName );
		do {
			$fName = readdir( $dir );
			$matches = array();
			preg_match( '/^behav_([\\d]+)_\\w+$/', $fName, $matches );
			if( is_file($dirName.DS.$fName) && isset($matches[1]) && ($matches[1] < (time()-60)) ) {
				unlink( $dirName.DS.$fName );
			}
		} while( $fName !== false );
	}
}
?>