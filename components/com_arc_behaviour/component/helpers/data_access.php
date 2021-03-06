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
 * Data Access Helper
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package	   Arc
 * @subpackage Behaviour
 * @since 0.1
 */
class ApotheosisData_Behaviour extends ApotheosisData
{
	function info()
	{
		return 'Behaviour component installed';
	}
	
	function colorIncident( $colorName )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT id FROM '.$db->nameQuote( '#__apoth_bhv_inc_types' )
			."\n".'WHERE label = '.$db->Quote( $colorName );
		$db->setQuery( $query );
		return $db->loadResult();
	}
	
	function incidentScore( $incId )
	{
		$db = &JFactory::getDBO();
		$score = null;
		$r['parent'] = $incId;
		do {
			$incId = $r['parent'];
			$query = 'SELECT score, parent FROM '.$db->nameQuote( '#__apoth_bhv_inc_types' )
				."\n".'WHERE id = '.$db->Quote( $incId );
			$db->setQuery( $query );
			$r = $db->loadAssoc();
//			debugQuery($db, $r);
			$score = $r['score'];
		} while( is_null($score) && !is_null($r['parent']) );
			
		return $score;
	}
	
	function actionScore( $actId, $multiple )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT score FROM '.$db->nameQuote( '#__apoth_bhv_actions' )
			."\n".'WHERE id = '.$db->Quote( $actId );
		$db->setQuery( $query );
		$score = $db->loadResult();
//		debugQuery($db, $score);
		
		if( stristr($score, 'n') !== false ) {
			$score = str_replace( 'n', $multiple, $score );
			eval( '$score = '.$score.';' );
		}
		return $score;
	}
	
	function addScore( $pId, $gId, $score, $msgId )
	{
		$db = &JFactory::getDBO();
		$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_bhv_scores')
			."\n".'SET '.$db->nameQuote( 'person_id' )  .' = '.$db->Quote( $pId )
			."\n".'  , '.$db->nameQuote( 'group_id' )   .' = '.( empty($gId) ? 'NULL' : $db->Quote( $gId ) )
			."\n".'  , '.$db->nameQuote( 'score' )      .' = '.$db->Quote( $score )
			."\n".'  , '.$db->nameQuote( 'date_issued' ).' = NOW()'
			."\n".'  , '.$db->nameQuote( 'msg_id' )     .' = '.$db->Quote( $msgId );
		$db->setQuery( $query );
		$db->Query();
		return $db->getErrorMsg() == '';
	}
	
	function removeScore( $pId, $gId, $msgId )
	{
		$db = &JFactory::getDBO();
		$query = 'DELETE FROM '.$db->nameQuote( '#__apoth_bhv_scores')
			."\n".'WHERE '.$db->nameQuote( 'person_id' )  .' = '.$db->Quote( $pId )
			."\n".'  AND '.$db->nameQuote( 'group_id' )   .' = '.( empty($gId) ? 'NULL' : $db->Quote( $gId ) )
			."\n".'  AND '.$db->nameQuote( 'msg_id' )     .' = '.$db->Quote( $msgId );
		$db->setQuery( $query );
		$db->Query();
		return $db->getErrorMsg() == '';
	}
	
	function personScore( $pId, $from, $to )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT SUM( '.$db->nameQuote( 'score' ).' )'
			."\n".' FROM '.$db->nameQuote( '#__apoth_bhv_scores')
			."\n".'WHERE '.$db->nameQuote( 'date_issued' ).' BETWEEN '.$db->Quote( $from ).' AND '.$db->Quote( $to )
			.( is_null( $pId ) ? '' : "\n".'  AND '.$db->nameQuote( 'person_id' )  .' = '.$db->Quote( $pId ) )
			."\n".'GROUP BY person_id';
		$db->setQuery( $query );
		$r = $db->loadResultArray();
		
		if( is_null( $pId ) ) {
			// work out the average
			$retVal = array_sum( $r ) / count( $r );
		}
		else {
			$retVal = reset( $r );
		}
		
		if( empty( $retVal ) ) { $retVal = 0; }
		
		return $retVal;
	}
	
	function personTally( $pId, $color, $from, $to )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT COUNT( s.msg_id )'
			."\n".'FROM '.$db->nameQuote( '#__apoth_bhv_scores').' AS s'
			."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_msg_tag_map' ).' AS tm'
			."\n".'   ON tm.msg_id = s.msg_id'
			."\n".'  AND tm.person_id IS NULL'
			."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_bhv_inc_types' ).' AS it'
			."\n".'   ON it.msg_tag = tm.tag_id'
			.( is_null( $color ) ? '' : "\n".'  AND it.label = '.$db->Quote( $color ) )
			."\n".'INNER JOIN '.$db->nameQuote( '#__apoth_msg_threads' ).' AS th'
			."\n".'   ON th.msg_id = s.msg_id'
			."\n".'  AND th.'.$db->nameQuote( 'order' ).' = 1'
			."\n".'WHERE '.$db->nameQuote( 'date_issued' ).' BETWEEN '.$db->Quote( $from ).' AND '.$db->Quote( $to )
			.( is_null( $pId ) ? '' : "\n".'  AND s.'.$db->nameQuote( 'person_id' )  .' = '.$db->Quote( $pId ) )
			."\n".'GROUP BY s.person_id';
			
		$db->setQuery( $query );
		$r = $db->loadResultArray();
		
		if( is_null( $pId ) ) {
			// work out the average
			$retVal = array_sum( $r ) / count( $r );
		}
		else {
			$retVal = reset( $r );
		}
		
		if( empty( $retVal ) ) { $retVal = 0; }
		
		return $retVal;
	}
	
}
?>