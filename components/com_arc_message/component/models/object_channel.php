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
 * Messaging Tag Factory
 */
class ApothFactory_Message_Channel extends ApothFactory
{
	function &getDummy( $id )
	{
		if( $id >= 0 ) {
			$r = null;
			return $r;
		}
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothChannel( array('id'=>$id,
				'name'=>'New Channel',
				'description'=>'description here') );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified channel, creating the object if it didn't already exist
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT c.*'
				."\n".'FROM '.$db->nameQuote( '#__apoth_msg_channels' ).' AS c'
				."\n".'WHERE c.id = '.$db->Quote( $id );
			$db->setQuery($query);
			$data = $db->loadAssoc();
			
			$r = new ApothChannel( $data );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	function &getInstances( $requirements )
	{
		$sId = $this->_getSearchId( $requirements );
		$ids = $this->_getInstances($sId);
		$restrict = $this->getParam( 'restrict' );
		
		if( is_null($ids) ) {
			$db = &JFactory::getDBO();
			
			$where = array();
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
				case( 'valid_from' ):
				case( 'valid_to' ):
					if( !isset($where['date']) ) {
						$where['date'] = ApotheosisLibDb::dateCheckSql('c.valid_from', 'c.valid_to', $requirements['valid_from'], $requirements['valid_to']);
					}
					break;
				
				case( 'sub_from' ):
				case( 'sub_to' ):
					if( !isset($where['sub_date']) &&
					   ( isset($requirements['subscriber']) && !empty($requirements['subscriber'])
					  || isset($requirements['nonsubscriber']) && !empty($requirements['nonsubscriber']) )
					   ) {
						$where['sub_date'] = ApotheosisLibDb::dateCheckSql('s.valid_from', 's.valid_to', $requirements['sub_from'], $requirements['sub_to']);
					}
					break;
				
				case( 'privacy' ):
					$where[] = 'c.privacy'.$assignPart;
					if( $val == 2 ) {
						$u = ApotheosisLib::getUser();
						$where[] = 'c.'.$db->nameQuote( 'created_by' ).' = '.$db->Quote( $u->person_id );
					}
					break;
				
				case( 'subscriber' ):
					$joins['sub'] = 'INNER JOIN #__apoth_msg_channel_subscribers AS s'
						."\n".'  ON s.channel_id = c.id'
						."\n".' AND s.person_id'.$assignPart;
					break;
				
				case( 'nonsubscriber' ):
					$u = ApotheosisLib::getUser();
					$tmpTbl = 'tmp_subscribers_'.$u->id;
					$preQuery = 'CREATE TEMPORARY TABLE '.$tmpTbl.' ('
						."\n".'  `id` VARCHAR(20) PRIMARY KEY'
						."\n".');'
						."\n"
						."\n".'INSERT INTO '.$tmpTbl
						."\n".'VALUES'
						."\n".'( '.implode( '), (',$val ).' );';
					$postQuery = 'DROP TABLE IF EXISTS '.$tmpTbl;
					$joins['sub'] = 'INNER JOIN '.$tmpTbl.' AS tmp'
						."\n".'LEFT JOIN #__apoth_msg_channel_subscribers AS s'
						."\n".'  ON s.channel_id = c.id'
						."\n".' AND (s.person_id = tmp.id OR s.person_id IS NULL)';
					$where[] = 's.channel_id IS NULL';
					break;
				
				case( 'id' ):
					$where[] = 'c.id'.$assignPart;
					break;
				}
			}
			
			if( isset($joins['sub']) && isset($where['sub_date']) ) {
				$joins['sub'] .= "\n AND ".$where['sub_date'];
				unset( $where['sub_date'] );
			}
			
			if( isset($preQuery) ) {
				$db->setQuery( $preQuery );
				$db->QueryBatch();
			}
			
			$query = 'SELECT DISTINCT c.*'
				."\n".'FROM '.$db->nameQuote( '#__apoth_msg_channels' ).' AS c'
				.( $restrict ? "\n".'~LIMITINGJOIN~' : '' )
				.( empty($joins) ? '' : "\n".implode("\n", $joins) )
				.( empty($where) ? '' : "\nWHERE ".implode("\n AND ", $where) )
				."\n".'ORDER BY c.name';
			
 			$db->setQuery( $restrict ? ApotheosisLibAcl::limitQuery($query, 'message.channels') : $query );
			$data = $db->loadAssocList( 'id' );
			
			if( isset($postQuery) ) {
				$db->setQuery( $postQuery );
				$db->Query();
			}
			
			$ids = array_keys( $data );
			$this->_addInstances( $sId, $ids );
			
			foreach( $data as $id=>$d ) {
				$r = new ApothChannel( $d );
				$this->_addInstance( $id, $r );
				unset( $r );
			}
		}
		
		return $ids;
	}
	
	function loadRules( $cId )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT ps.id AS params_id, ps.rule_id, ps.type, ps.data, ps.negate, ps.order, r.handler, r.check'
			."\n".'FROM #__apoth_msg_channels AS c'
			."\n".'INNER JOIN #__apoth_msg_channel_rules AS cr'
			."\n".'   ON cr.channel_id = c.id'
			."\n".'INNER JOIN #__apoth_msg_rule_param_sets AS ps'
			."\n".'   ON cr.param_set_id = ps.id'
			."\n".'INNER JOIN #__apoth_msg_rules AS r'
			."\n".'   ON r.id = ps.rule_id'
			."\n".'WHERE c.id = '.$db->Quote( $cId );
		$db->setQuery( $query );
		$r = $db->loadAssocList();
		if( !is_array($r) ) { $r = array(); }
		
		$retVal = array();
		foreach( $r as $rule ) {
			$param = array();
			$param['type']   = $rule['type'];
			$param['data']   = $rule['data'];
			$param['negate'] = $rule['negate'];
			unset( $rule['type']   );
			unset( $rule['data']   );
			unset( $rule['negate'] );
			
			$order = $rule['order'];
			unset( $rule['order']);
			
			$id = $rule['params_id'];
			unset($rule['params_id']);
			
			$check = $rule['handler'].$rule['check'];
			if( !isset( $retVal[$id] ) ) {
				$retVal[$id] = $rule;
				$retVal[$id]['_params'] = array();
			}
			$retVal[$id]['_params'][$order] = $param;
		}
		
		return $retVal;
	}
	
	function getRuleId( $handler, $check )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT id'
			."\n".'FROM #__apoth_msg_rules'
			."\n".'WHERE '.$db->nameQuote( 'handler' ).' = '.$db->Quote( $handler )
			."\n".'  AND '.$db->nameQuote( 'check'   ).' = '.$db->Quote( $check );
		$db->setQuery( $query );
		$r = $db->loadResult();
		return ( is_null( $r ) ? -1 : $r );
	}
	
	/**
	 * Searches for existing param sets which match the params we're using for some rule
	 * 
	 * @param unknown_type $ruleId
	 * @param unknown_type $params
	 * @return mixed  The int param set id if found, null otherwise
	 */
	function getParamSetId( $ruleId, $params )
	{
		if( empty($params) ) {
			return null;
		}
		$db = &JFactory::getDBO();
		
		$paramChecks = array();
		$pCount = 0;
		foreach( $params as $param ) {
			$paramChecks[] = '('
				.$db->nameQuote('type')  .' = '.$db->Quote($param['type']).' AND '
				.$db->nameQuote('data')  .' = '.$db->Quote($param['data']).' AND '
				.$db->nameQuote('negate').' = '.$db->Quote((int)$param['negate']).')';
			$pCount++;
		}
		$u = ApotheosisLib::getUser();
		$tblName1 = '#__apoth_tmptmp_'.$u->id.'_'.time();
		$tblName2 = '#__apoth_tmpsets_'.$u->id.'_'.time();
		
		$query = 'CREATE TEMPORARY TABLE '.$tblName1.' AS'
			."\n".'SELECT id, COUNT(*) AS num'
			."\n".'FROM `#__apoth_msg_rule_param_sets`'
			."\n".'WHERE rule_id = '.$ruleId.''
			."\n".'GROUP BY id'
			."\n".'HAVING num = '.$pCount.';'
			."\n"
			."\n".'CREATE TABLE '.$tblName2.' AS'
			."\n".'SELECT tmp.*, COUNT( * ) AS num2'
			."\n".'FROM '.$tblName1.' AS tmp'
			."\n".'INNER JOIN `#__apoth_msg_rule_param_sets` AS s'
			."\n".'   ON s.id = tmp.id'
			."\n".'WHERE '.implode( "\n OR ", $paramChecks ).''
			."\n".'GROUP BY s.id'
			."\n".'HAVING num = num2;';
		$db->setQuery( $query );
		$db->queryBatch();
		
		$query = 'SELECT id FROM '.$tblName2;
		$db->setQuery( $query );
		$r = $db->loadResult();
		
		$query =  'DROP TABLE IF EXISTS '.$tblName1.';'
			."\n".'DROP TABLE IF EXISTS '.$tblName2.';';
		$db->setQuery( $query );
		$db->queryBatch();
		
		return $r;
	}
	
	function commitInstance( $id )
	{
		$channel = &$this->getInstance( $id );
		
		if( is_null($channel) ) {
			return null;
		}
		$db = &JFactory::getDBO();
		$data = $channel->getData();
		$oldId = $id = $data['id'];
		$isNew = ( $id < 0 );
		
		// Write core data (create channel)
		if( $isNew ) {
			unset( $data['id'] );
			$u = ApotheosisLib::getUser();
			$data['created_by'] = $channel->_core['created_by'] = $u->person_id;
			$data['valid_from'] = $channel->_core['valid_from'] = date( 'Y-m-d H:i:s');
			$queryStart = 'INSERT INTO '.$db->nameQuote('#__apoth_msg_channels');
			$queryEnd = '';
		}
		else {
			$queryStart = 'UPDATE '.$db->nameQuote('#__apoth_msg_channels');
			$queryEnd = 'WHERE '.$db->nameQuote('id').' = '.$db->Quote($id);
		}
		
		foreach( $data as $col=>$val ) {
			if( is_null($val) || $val === '' ) {
				$values[] = $db->nameQuote($col).' = NULL';
			}
			else {
				$values[] = $db->nameQuote($col).' = '.$db->Quote($val);
			}
		}
		$query = $queryStart
			."\n".'SET '
			."\n".implode( "\n, ", $values )
			."\n".$queryEnd;
		
		$db->setQuery( $query );
		$db->Query();
		
		if( $isNew ) {
			// Now it's inserted, what's the id for this newly made channel?
			$query = 'SELECT LAST_INSERT_ID()';
			$db->setQuery( $query );
			$id = $db->loadResult();
			$channel->setId( $id );
		}
		else {
			$id = $channel->getId();
		}
		
		
		// Sort out any new param sets
		$r = $channel->getRules();
		$psIds = array();
		foreach( $r as $psId=>$rule ) {
			if( $psId < 0 ) {
				// param sets below 0 are new and to be created
				if( !isset($newPsId) ) {
					$query = 'SELECT MAX(id) FROM #__apoth_msg_rule_param_sets';
					$db->setQuery( $query );
					$newPsId = $db->loadResult();
				}
				$newPsId++;
				$psIds[] = $newPsId;
				$order = 0;
				foreach( $rule['_params'] as $param ) {
					$paramVals[] = '('
						.$db->Quote( $newPsId ).', '
						.$db->Quote( $rule['rule_id'] ).', '
						.$db->Quote( $param['type'] ).', '
						.$db->Quote( $param['data'] ).', '
						.$db->Quote( (int)$param['negate'] ).', '
						.$db->Quote( $order++ ).')';
				}
			}
			else {
				$psIds[] = $psId;
			}
		}
		if( !empty($paramVals) ) {
			$query = 'INSERT INTO #__apoth_msg_rule_param_sets'
				."\n".'VALUES '.implode( ', ', $paramVals );
			$db->setQuery( $query );
			$db->Query();
		}
		
		
		// Sort out linking the channel and its param sets
		// ... first find out what's already there (to avoid duplicates / leaving stale data)
		$query = 'SELECT param_set_id'
			."\n".'FROM #__apoth_msg_channel_rules'
			."\n".'WHERE channel_id = '.$id;
		$db->setQuery( $query );
		$existing = $db->loadResultArray();
		if( !is_array($existing) ) { $existing = array(); }
		
		$deletables = array();
		foreach( $existing as $k=>$v ) {
			// do we need to remove a stale param ?
			if( ($psK = array_search( $v, $psIds )) === false ) {
				$deletables[] = $db->Quote( $v );
			}
			// already existing ones don't need to be re-added
			else {
				unset( $psIds[$psK] );
			}
		}
		// ... so remove the obsolete ones
		if( !empty($deletables) ) {
			$query = 'DELETE FROM #__apoth_msg_channel_rules'
				."\n".'WHERE channel_id = '.$id
				."\n".'  AND param_set_id IN ('.implode(', ', $deletables).')';
			$db->setQuery( $query );
			$db->query();
		}
		
		// ... and add the newbies
		foreach( $psIds as $psId ) {
			$channelVals[] = '('
				.$db->Quote( $id ).', '
				.$db->Quote( $psId ).')';
		}
		if( !empty($channelVals) ) {
			$query = 'INSERT INTO #__apoth_msg_channel_rules'
				."\n".'VALUES '.implode( ', ', $channelVals );
			$db->setQuery( $query );
			$db->Query();
		}
		
		// refresh the permissions tables
		ApotheosisLibAcl::getUserTable( 'message.channels', null, true );
		
		// Update the factory
		$this->_clearCachedInstances( $oldId );
		$this->_clearCachedSearches();
		$tmp = &$this->getInstance( $id );
		$tmp->getRules();
	}
	
	
	function getSubscriptions( $pIds, $cIds )
	{
		$db = &JFactory::getDBO();
		
		foreach( $pIds as $k=>$v ) {
			$pIds[$k] = $db->Quote( $v );
		}
		foreach( $cIds as $k=>$v ) {
			$cIds[$k] = $db->Quote( $v );
		}
		
		$query = 'SELECT person_id, channel_id'
			."\n".'FROM #__apoth_msg_channel_subscribers AS s'
			."\n".'WHERE (person_id IN ('.implode( ', ', $pIds ).') OR s.person_id IS NULL )'
			."\n".'  AND channel_id IN ('.implode( ', ', $cIds ).')'
			."\n".'  AND ( s.valid_from <= NOW() AND (s.valid_to >= NOW() OR s.valid_to IS NULL) )';
		$db->setQuery( $query );
		$tmp = $db->loadAssocList();
		if( !is_array($tmp) ) { $tmp = array(); }
		$retVal = array();
		foreach( $tmp as $row ) {
			$retVal[$row['person_id']][$row['channel_id']] = true;
		}
		return $retVal;
		
	}
	
	function getSubscriptionLevels( $pIds, $cIds, $details = false )
	{
		$db = &JFactory::getDBO();
		
		foreach( $pIds as $k=>$v ) {
			$pIds[$k] = $db->Quote( $v );
		}
		foreach( $cIds as $k=>$v ) {
			$cIds[$k] = $db->Quote( $v );
		}
		
		if( $details ) {
			$select = 'SELECT c.id, s.person_id';
			$groupBy = '';
			$index = '';
		}
		else {
			$select = 'SELECT c.id, COUNT(s.person_id) AS sub_count';
			$groupBy = "\n".'GROUP BY c.id';
			$index = 'id';
		}
		
		$query = "\n".'FROM #__apoth_msg_channels AS c'
			."\n".'INNER JOIN #__apoth_msg_channel_subscribers AS s'
			."\n".'   ON s.channel_id = c.id'
			."\n".'  AND ( s.person_id IN ('.implode( ', ', $pIds ).') OR s.person_id IS NULL )'
			."\n".'  AND ( s.valid_from <= NOW() AND (s.valid_to >= NOW() OR s.valid_to IS NULL) )'
			."\n".'WHERE c.id IN ('.implode( ', ', $cIds ).')';
		$orderBy = "\n".'ORDER BY c.name';
		$db->setQuery( $select.$query.$groupBy.$orderBy );
		$tmp = $db->loadAssocList( $index );
		
		if( $details ) {
			foreach( $tmp as $sub ) {
				$retVal[$sub['id']]['person_id'][] = $sub['person_id'];
				$retVal[$sub['id']]['sub_count'] = count( $retVal[$sub['id']]['person_id'] );
			}
		}
		else {
			$retVal = $tmp;
		}
		
		return $retVal;
	}
	
	function addSubsciptions( $pIds, $cIds )
	{
		$db = &JFactory::getDBO();
		
		$exists = $this->getSubscriptions($pIds, $cIds);
		foreach( $cIds as $cId ) {
			$dbCId = $db->Quote( $cId );
			foreach( $pIds as $pId ) {
				if( !isset($exists[$pId][$cId]) ) {
					$dbPId = $db->Quote( $pId );
					$vals[] = '( NULL, '.$dbPId.', '.$dbCId.', NULL , NOW(), NULL )';
				}
			} 
		}
		
		if( isset($vals) ) {
			$query = 'INSERT INTO #__apoth_msg_channel_subscribers'
				."\n".'VALUES'
				."\n".implode( ',', $vals );
			$db->setQuery( $query );
			$db->Query();
		}
	}
	
	function delSubsciptions( $pIds, $cIds )
	{
		$db = &JFactory::getDBO();
		
		$exists = $this->getSubscriptions($pIds, $cIds);
		$dbPIdCol = $db->nameQuote( 'person_id' );
		$dbChanCol = $db->nameQuote( 'channel_id' );
		
		$vals = array();
		foreach( $cIds as $cId ) {
			$dbCId = $db->Quote( $cId );
			foreach( $pIds as $pId ) {
				if( isset($exists[$pId][$cId]) ) {
					$dbPId = $db->Quote( $pId );
					$vals[] = '( '.$dbPIdCol.' = '.$dbPId.' AND '.$dbChanCol.' = '.$dbCId.' )';
				} 
			} 
		}
		
		if( !empty( $vals ) ) {
			$query = 'UPDATE #__apoth_msg_channel_subscribers'
				."\n".'SET '.$db->nameQuote( 'valid_to' ).' = '.$db->Quote( date( 'Y-m-d H:i:s', time()-1 ) )
				."\n".'WHERE'
				."\n".implode( "\n OR ", $vals );
			$db->setQuery( $query );
			$db->Query();
		}
	}
}


/**
 * Messaging Channel Object
 */
class ApothChannel extends JObject
{
	function __construct( $data )
	{
		$this->_id     = $data['id'];
		$this->_core   = $data;
	}
	
	// #####  accessors  #####
	function getData()        { return $this->_core; }
	function getId()          { return $this->_id; }
	function getName()        { return ( isset($this->_core['name']           ) ? $this->_core['name']           : null ); }
	function getDescription() { return ( isset($this->_core['description']    ) ? $this->_core['description']    : null ); }
	function getPrivacy()     { return ( isset($this->_core['privacy']        ) ? $this->_core['privacy']        : null ); }
	function getFolder()      { return ( isset($this->_core['default_folder'] ) ? $this->_core['default_folder'] : null ); }
	function getExclusive()   { return ( isset($this->_core['exclusive']      ) ? $this->_core['exclusive']      : null ); }
	function getCreator()     { return ( isset($this->_core['created_by']     ) ? $this->_core['created_by']     : null ); }
	
	function setId( $v )
	{
		$this->_id = (int)$v;
		$this->_core['id'] = (int)$v;
	}
	
	function setName( $v )
	{
		$this->_core['name'] = (string)$v;
	}
	
	function setDescription( $v )
	{
		$this->_core['description'] = $v;
	}
	
	function setPrivacy( $v )
	{
		$this->_core['privacy'] = $v;
	}
	function setFolder( $v )
	{
		$this->_core['default_folder'] = $v;
	}
	function setExclusive( $v )
	{
		$this->_core['exclusive'] = $v;
	}
	
	
	function getRules()
	{
		if( !isset($this->_rules) ) {
			$fChan = ApothFactory::_( 'message.Channel' );
			$this->_rules = $fChan->loadRules( $this->_id );
		}
		return $this->_rules;
	}
	
	function resetRules()
	{
		$this->_rules = array();
	}
	
	function setRule( $handler, $check, $params )
	{
		if( !isset($this->newPsId) ) {
			$this->newPsId = 0;
		}
		
		$fChan = ApothFactory::_( 'message.Channel' );
		$rId = $fChan->getRuleId( $handler, $check );
		$psId = $fChan->getParamSetId( $rId, $params );
		if( is_null($psId) ) {
			$psId = --$this->newPsId;
		}
		
		$this->_rules[$psId] = array(
			'rule_id'=>$rId,
			'handler'=>$handler,
			'check'=>$check,
			'_params'=>$params );
	}
	
	function delete()
	{
		$this->_core['valid_to'] = date( 'Y-m-d H:i:s', (time()-1) );
		$this->commit();
	}
	
	/**
	 * Commit the channel definition to the database
	 */
	function commit()
	{
		$fChan = ApothFactory::_( 'message.Channel' );
		$fChan->commitInstance( $this->_id );
		return $this->_id;
	}
}
?>