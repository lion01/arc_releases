<?php
/**
 * @package     Arc
 * @subpackage  Message
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
 * @author     David Swain <d.swain@wildern.hants.sch.uk>
 * @package	   Apotheosis
 * @subpackage Message
 * @since 0.1
 */
class ApotheosisData_Message extends ApotheosisData
{
	function info()
	{
		return 'Course component installed';
	}
	
	function tagId( $category, $label )
	{
		$fTag = ApothFactory::_( 'message.Tag' );
		$incs = $fTag->getInstances( array('category'=>$category, 'label'=>$label) );
		return reset($incs);
	}
	
	function tag( $tagId )
	{
		$fTag = ApothFactory::_( 'message.Tag' );
		return $fTag->getInstance( $tagId ); 
	}
	
	/**
	 * Retrieve the color names for each of the given message ids
	 * 
	 * @param array $msgIds  The ids of the messages to look for
	 */
	function color( $msgs )
	{
		if( !is_array($msgs) || empty($msgs) ) {
			return array();
		}
		$db = &JFactory::getDBO();
		foreach( $msgs as $k=>$v ) {
			$msgs[$k] = $db->Quote( $v );
		}
		$query = 'SELECT *'
			."\n".'FROM `jos_apoth_msg_tag_map` AS m'
			."\n".'INNER JOIN `jos_apoth_msg_tags` AS t'
			."\n".'   ON t.id = m.tag_id'
			."\n".'  AND t.id IN (31, 32, 33, 34, 35, 39)'
			."\n".'WHERE m.msg_id IN ('.implode( ', ', $msgs ).')';
		$db->setQuery( $query );
		$r = $db->loadAssocList();
		if( !is_array($r) ) { $r = array(); }
		
		$retVal = array();
		foreach( $r as $row ) {
			$retVal[$row['msg_id']] = $row['label'];
		}
		return $retVal;
	}
	
	/**
	 * Retrieve the order value for each of the given message ids
	 * 
	 * @param array $msgIds  The ids of the messages to look for
	 */
	function order( $msgs )
	{
		if( !is_array($msgs) || empty($msgs) ) {
			return array();
		}
		$db = &JFactory::getDBO();
		foreach( $msgs as $k=>$v ) {
			$msgs[$k] = $db->Quote( $v );
		}
		$query = 'SELECT msg_id, `order`'
			."\n".'FROM `#__apoth_msg_threads` AS t'
			."\n".'WHERE msg_id IN ('.implode( ', ', $msgs ).')';
		$db->setQuery( $query );
		$r = $db->loadAssocList();
		if( !is_array($r) ) { $r = array(); }
		
		$retVal = array();
		foreach( $r as $row ) {
			$retVal[$row['msg_id']] = $row['order'];
		}
		return $retVal;
	}
	
	function miniInfo( $msgId )
	{
		$retVal['html']    = JHTML::_( 'arc_message.render', 'renderMessageMiniRow', 'message', $msgId );
		$retVal['tooltip'] = JHTML::_( 'arc_message.render', 'renderMessageTooltip', 'message', $msgId );
		
		return $retVal;
	}
	
	/**
	 * Retrieve the thread ids for each of the given message ids
	 * 
	 * @param array $msgIds  The ids of the messages to look for
	 */
	function threads( $msgIds )
	{
		if( !is_array($msgIds) || empty($msgIds) ) {
			return array();
		}
		$db = &JFactory::getDBO();
		foreach( $msgIds as $k=>$msgId ) {
			$msgIdsQ[$k] = $db->Quote($msgId);
		}
		$query = 'SELECT '.$db->nameQuote('thr').'.'.$db->nameQuote('id').', '.$db->nameQuote('thr').'.'.$db->nameQuote('msg_id')
			."\n".'FROM '.$db->nameQuote('#__apoth_msg_threads').' AS '.$db->nameQuote('thr')
			."\n".'WHERE '.$db->nameQuote('thr').'.'.$db->nameQuote('msg_id').' IN ('.implode( ', ', $msgIdsQ ).')';
		$db->setQuery( $query );
		$r = $db->loadAssocList('msg_id');
		if( !is_array($r) ) { $r = array(); }
		
		$retVal = array();
		foreach( $msgIds as $msgId ) {
			$retVal[$msgId] = $r[$msgId]['id'];
		}
		return $retVal;
	}
	
	function helperData( $method, $type, $id, $param = null )
	{
		// work out which component knows how to render the given thread
		switch( $type ) {
		case( 'message' ):
			$fMsg = ApothFactory::_( 'message.Message' );
			$item = &$fMsg->getInstance( $id );
			break;
		
		case( 'thread' ):
			$fThread = ApothFactory::_( 'message.Thread' );
			$item = &$fThread->getInstance( $id );
			break;
		
		default:
			$item = null;
		}
		
		$retVal = true;
		if( !is_null($item) ) {
			$c = self::_getMessageHelper( $item->getHandler() );
			
			if( $c !== false ) {
				if( method_exists($c, $method) ) {
					$retVal = $c->$method( $item, $param );
				}
			}
		}
		
		return $retVal;
	}
	
	/**
	 * Calculate to which channels and therefore which users this message should be delivered
	 * 
	 * @param object $message  The message whose recipients are sought
	 * @param string $method  The action ('save' or 'send') being carried out
	 */
	function recipients( &$message, $method )
	{
		// get all rule check definitions
		$db = &JFactory::getDBO();
		$query = 'SELECT p.*, r.handler, r.check'
			."\n".'FROM `#__apoth_msg_rule_param_sets` AS p'
			."\n".'INNER JOIN `#__apoth_msg_rules` AS r'
			."\n".'   ON r.id = p.rule_id'
			."\n".'ORDER BY p.id, p.order';
		$db->setQuery( $query );
		$checks = $db->loadAssocList();
		
		// check each set of params to see if they contain a match for the message
		$setMatches = array();
		$check = reset($checks);
		$preId = $curId = $check['id'];
		$negate = false;
		while( $check !== false ) {
			if( !isset($setMatches[$curId])
			 || ($setMatches[$curId] == '?') ) {
				// work out the message's relevant value if we don't already have it cached
				$msgVal = self::_getMessageVal( $message, $check, $method );
				
				// do we need to negate the answer?
				$negate = $negate || (bool)$check['negate'];
				
				// work out what that means if we can
				switch( $check['type'] ) {
				case( 'value' ):
					$uVal = $check['data'];
					if( !is_null($msgVal) ) {
						if( is_array($msgVal) ) {
							if( is_array($uVal) ) {
								$tmp = array_intersect($msgVal, $uVal);
								$match = !empty($tmp);
							}
							else {
								$match = ( array_search( $uVal, $msgVal ) !== false );
							}
						}
						else {
							if( is_array($uVal) ) { 
								$match =( array_search( $msgVal, $uVal ) !== false );
							}
							else {
								$match = ( $uVal == $msgVal );
							}
						}
						
						// don't just set ...=$match as want it unset if no match found
						if( $match ) {
							$setMatches[$curId] = true;
						}
					}
					break;
				
				case( 'variable' ):
					$setMatches[$curId] = '?'; // Match-ness is yet to be determined, but is possible
					break;
				
				default:
					continue; // don't process if there's no valid type
				}

				
				$check = next($checks);
				$preId = $curId;
				$curId = $check['id'];
				if( ($preId != $curId) && !is_null($preId) ) {
					if( !isset($setMatches[$preId]) ) {
						// if we got all the way through the previous set without a match
						// then mark as unmatched
						$setMatches[$preId] = false;
					}
					if( $negate ) {
						// once we finish a set we look at if it should be negated
						$setMatches[$preId] = !$setMatches[$preId];
					}
					$negate = false;
				}
				
			}
		}

		// get all channel definitions
		$query = 'SELECT r.*, c.exclusive'
			."\n".'FROM #__apoth_msg_channel_rules AS r'
			."\n".'INNER JOIN #__apoth_msg_channels AS c'
			."\n".'   ON c.id = r.channel_id'
			."\n".'ORDER BY channel_id';
		$db->setQuery($query);
		$channelRules = $db->loadAssocList();
		
		// check for channels with matches in the rule list
		$channels = array();
		$varChannels = array();
		$rule = reset( $channelRules );
		$preId = $curId = $rule['channel_id'];
		$match = true; // channel matches all rules so far
		$var = false;  // channel depends on a variable to match
		while( $rule != false ) {
			if( $match ) {
				$match = $match && (bool)$setMatches[$rule['param_set_id']];
				$var = $var || ( $setMatches[$rule['param_set_id']] === '?' );
			}
			
			$exclusive = $rule['exclusive'];
			$rule = next( $channelRules );
			$preId = $curId;
			$curId = $rule['channel_id'];
			if( $preId != $curId ) {
				if( $match ) {
					if( $exclusive ) {
						$channels = array( $preId );
						break;
					}
					else {
						$channels[] = $preId;
					}
					if( $var ) {
						$varChannels[] = $preId;
					}
				}
				$match = true;
				$var = false;
			}
			
		}
		
		$recipients = array();
		if( !empty($channels) ) {
			// find subscribers to matched channels
			$d = date( 'Y-m-d H:i:s' );
			$query = 'SELECT s.*, COALESCE( c.default_folder, s.folder ) AS use_folder'
				."\n".'FROM #__apoth_msg_channel_subscribers AS s'
				."\n".'INNER JOIN #__apoth_msg_channels AS c'
				."\n".'   ON c.id = s.channel_id'
				."\n".'WHERE channel_id IN ('.implode(', ', $channels).')'
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 's.valid_from', 's.valid_to', $d, $d)
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'c.valid_from', 'c.valid_to', $d, $d);
			$db->setQuery( $query );
			$subs = $db->loadAssocList();
			if( !is_array($subs) ) { $subs = array(); }
			
			// convert list names into person ids
			foreach( $subs as $k=>$v ) {
				$matches = array();
				if( preg_match( '/^~(.*)~$/', $v['person_id'], $matches ) ) {
					unset( $subs[$k] );
					$people = ApotheosisData::_( 'people.people', $matches[1] );
					foreach( $people as $p ) {
						$v['person_id'] = $p->id;
						array_unshift( $subs, $v );
					}
				}
			}
			
			// establish if any of the channels depends on subscriber properties
			// add subscribers to result array where they pass
			// and remove subscribers once considered (to allow simple parsing later)
			foreach( $varChannels as $channelId ) {
				// find the subscribers to this channel
				$candidates = array();
				foreach( $subs as $sub ) {
					$candidates[] = $sub['person_id'];
				}
				// find the variable rules for this channel
				foreach( $channelRules as $rule ) {
					if( ($setMatches[$rule['param_set_id']] === '?')
					 && ($rule['channel_id'] == $channelId) ) {
						// this param set has variable options in it
						// go through the variable options and see which (if any)
						// subscribers fulfil any of them
					 	
					 	$survivors = array();
						foreach( $checks as $k=>$check ) {
							if( ($check['id'] == $rule['param_set_id'])
							 && ($check['type'] == 'variable') ) {
								$survivors += self::_getVariableMatches( $message, $check, $method, $candidates );
							}
						}
						
						$candidates = array_intersect( $candidates, $survivors );
					}
				}
				
				$candidates = array_flip( $candidates );
				foreach( $subs as $sk=>$sub ) {
					//$recipients[$pId][$channelId] = $sub['use_folder'];
					if( $sub['channel_id'] == $channelId ) {
						if( isset($candidates[$sub['person_id']]) ) {
							$recipients[$sub['person_id']][$sub['channel_id']] = $sub['use_folder'];
						}
						unset($subs[$sk]);
					}
				}
				
			}
			
			// set up array of tags to put message in appopriate folder for each recepient
			foreach( $subs as $sub ) {
				$recipients[$sub['person_id']][$sub['channel_id']] = $sub['use_folder'];
			}
			
		}

		return $recipients;
	}
	
	function _getMessageVal( $message, $check, $method )
	{
		static $cache = array();
		$cacheKey = md5( serialize( $message ) ).'.'.$check['handler'].'.'.$check['check'];
		$fullCheck = $check['handler'].'.'.$check['check'];
		
		if( !array_key_exists($cacheKey, $cache) ) {
			switch( $fullCheck ) {
				case( 'message.first' ):
					// see if this message is the first in a thread
					$prev = $message->getPreviousMessage();
					$retVal = is_null($prev);
					break;
				
				case( 'message.time' ):
					// get the message attributes and check 'em
					$attribs = $message->getTagLabels( 'attribute' );
					if(     array_search('Tutor',    $attribs) !== false ) { $retVal = 'tutor';    }
					elseif( array_search('Lesson',   $attribs) !== false ) { $retVal = 'lesson';   }
					elseif( array_search('Untaught', $attribs) !== false ) { $retVal = 'untaught'; }
					break;
				
				case( 'message.method' ):
					$retVal = $method;
					break;
				
				case( 'behaviour.color' ):
					// get the color value
					$c = ApotheosisData_Message::_getMessageHelper( $check['handler'] );
					$color = $c->getColor( $message );
					$retVal = ApotheosisData::_( 'behaviour.colorIncident', $color );
					break;
				
				case( 'behaviour.studentYear' ):
					// find the student and then their year group
					$student = $message->getDatum( 'student_id' );
					$retVal = ApotheosisData::_( 'people.year', $student );
					break;
				
				case( 'behaviour.studentTutor' ):
					// find the student and then their tutor group
					$student = $message->getDatum( 'student_id' );
					$retVal = ApotheosisData::_( 'timetable.tutorgroup', $student );
					break;
				
				case( 'behaviour.group' ):
					// find the group's ancestry
					$tmp = $message->getDatum( 'group_id' );
					$tmp = ApotheosisLibDb::getAncestors( $tmp, '#__apoth_cm_courses' );
					$retVal = array();
					foreach( $tmp as $t ) {
						$retVal[] = $t->id;
					}
					break;
				
				case( 'behaviour.action' ):
					// find the action id
					$retVal = $message->getDatum( 'action' );
					break;
				
				case( 'people.person' ):
					// find the student and then their year group
					$retVal = $message->getDatum( 'student_id' );
					break;
				
				default:
					// no testable rule so no affirmative match
					$retVal = null;
					break;
			}
			$cache[$cacheKey] = $retVal;
		}
			
		return $cache[$cacheKey];
	}
	
	function _getVariableMatches( $message, $check, $method, $users )
	{
		if( !is_array($users) || empty($users) ) {
			return array();
		}
		$retVal = array();
		$msgVal = self::_getMessageVal( $message, $check, $method );
		switch( $check['data'] ) {
		case( 'user.tutorgroup' ):
			foreach( $users as $pId ) {
				$data[$pId] = ApotheosisData::_( 'timetable.tutorgroup', $pId );
			}
			break;
		
		case( 'user.year' ):
			$data = ApotheosisData::_( 'people.years', $users );
			break;
		
		case( 'user.group' ):
			foreach( $users as $pId ) {
				$data[$pId] = ApotheosisData::_( 'timetable.group', array( 'person'=>$pId ) );
			}
			break;
		
		case( 'user.teaches' ):
			foreach( $users as $pId ) {
				$enrolments = ApotheosisData::_( 'timetable.studentEnrolments', array( 'teacher'=>$pId ) );
				$data[$pId] = array();
				foreach( $enrolments as $enrolment ) {
					$data[$pId][$enrolment['person_id']] = $enrolment['person_id'];
				}
			}
			break;
		
		case( 'user.related' ):
			foreach( $users as $pId ) {
				$relations = ApotheosisData::_( 'people.relatedPupils', $pId, true );
				$data[$pId] = array();
				foreach( $relations as $relation ) {
					$data[$pId][$relation->pupil_id] = $relation->pupil_id;
				}
			}
			break;
		
		case( 'user.person_id'):
		default:
			$data = array();
			foreach( $users as $pId ) {
				$data[$pId] = $pId;
			}
			break;
		}
		
		// compare the retrieved values (per user) with the target value in the message
		foreach( $data as $pId=>$uVal ) {
			if( is_array($msgVal) ) {
				if( is_array($uVal) ) {
					$tmp = array_intersect($msgVal, $uVal);
					$match = !empty($tmp);
				}
				else {
					$match = ( array_search( $uVal, $msgVal ) !== false );
				}
			}
			else {
				if( is_array($uVal) ) { 
					$match =( array_search( $msgVal, $uVal ) !== false );
				}
				else {
					$match = ( $uVal == $msgVal );
				}
			}
			
			if( $match !== (bool)$check['negate'] ) {
				$retVal[] = $pId;
			}
		}
		
		return $retVal;
	}
	
	function &_getMessageHelper( $component )
	{
		static $cache = array();
		if( !isset($cache[$component]) ) {
			$comName = strtolower( $component );
			$claName = 'ApothMessage_'.ucfirst($comName);
			$fileName = JPATH_SITE.DS.'components'.DS.'com_arc_'.$comName.DS.'helpers'.DS.'message.php';
			
			if( file_exists($fileName) ) {
				require_once($fileName);
				$cache[$component] = new $claName();
			}
			else {
				$cache[$component] = false;
			}
		}
		return $cache[$component];
	}
	
	
	function tweetEnabled()
	{
		$params = JComponentHelper::getParams( 'com_arc_message' );
		$p1 = $params->get( 'consKey' );
		$p2 = $params->get( 'consSecret' );
		$p3 = $params->get( 'token' );
		$p4 = $params->get( 'tokenSecret' );
		return ( !empty( $p1 )
		      && !empty( $p2 )
		      && !empty( $p3 )
		      && !empty( $p4 )
		      );
	}
	
	function tweet( $msg )
	{
		$ok = true;
		try {
			$params = JComponentHelper::getParams( 'com_arc_message' );
			$oauth = new OAuth( $params->get( 'consKey' ), $params->get( 'consSecret' ), OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_FORM );
			$oauth->setToken( $params->get( 'token' ), $params->get( 'tokenSecret' ) );
			$args = array( 'status'=>$msg );
			$oauth->fetch( $params->get( 'urlTwitTweet' ), $args, 'POST' );
			
			$json = json_decode( $oauth->getLastResponse(), true );
			if( isset( $json['id'] ) ) {
			  // Success
			  echo 'Tweet sent';
			}
			else {
			  // Failure
			  echo 'Failed to send tweet with no error';
			}
		}
		catch( OAuthException $E ) {
			echo 'There was a problem tweeting this message. Please contact IT support and tell them what is written below.<br />';
			echo $E->getMessage();
			$ok = false;
		}
		return $ok;
	}
	
}
?>