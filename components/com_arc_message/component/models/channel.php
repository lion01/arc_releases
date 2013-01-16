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

jimport( 'joomla.application.component.model' );

/**
 * Message Hub Controller
 */
class MessageModelChannel extends JModel
{
	function __construct()
	{
		parent::__construct();
		$this->fChan = ApothFactory::_( 'message.Channel', $this->fChan );
		
		$u = ApotheosisLib::getUser();
		$this->setSubscribers( $u->person_id );
	}
	
	function __wakeup()
	{
		$this->fChan = ApothFactory::_( 'message.Channel', $this->fChan );
		if( isset($this->_curChannel) ) {
			$this->_curChannel = &$this->fChan->getInstance( $this->_curChannel->getId() );
		}
	}
	
	function setChannel( $id )
	{
		$this->fChan->setParam( 'restrict', false );
		if( $id < 0 ) {
			$this->_curChannel = &$this->fChan->getDummy( $id );
		}
		else {
			$this->_curChannel = &$this->fChan->getInstance( $id );
		}
	}
	
	function &getChannel()
	{
		if( !isset($this->_curChannel) ) {
			$this->_curChannel = &$this->fChan->getDummy(-1);
		}
		return $this->_curChannel;
	}
	
	function deleteChannel()
	{
		if( isset($this->_curChannel) && ($this->_curChannel->getId() > 0) ) {
			$this->_curChannel->delete();
			$retVal = true;
		}
		else {
			$retVal = false;
		}
		return $retVal;
	}
	
	function resetChannelLists()
	{
		unset( $this->_subscribed );
		unset( $this->_channels );
	}
	
	function setSubscribers( $pIds )
	{
		// deal with incoming user ids
		if( !is_array($pIds) ) {
			$pIds = array( $pIds );
		}
		foreach( $pIds as $k=>$v ) {
			if( empty($v) ) {
				unset( $pIds[$k] );
			}
		}
		
		// deal with any lists the users might be in
		$lists = ApotheosisData::_( 'people.peopleListMemberships', $pIds );
		$derivedLists = array_diff( $lists, $pIds );
		
		$this->resetChannelLists();
		$this->_subscribers = $pIds;
		$this->_derSubscribers = $derivedLists;
	}
	
	/**
	 * retrieves the channels to which the current user is subscribed
	 * 
	 * @return array  An array of 2 arrays containing stripped-down channel info objects to be used in rendering select lists of selected and derived channels
	 */
	function getSubscribed()
	{
		if( empty($this->_subscribers) ) {
			$this->_subscribed = array();
		}
		if( !isset($this->_subscribed) ) {
			$date = date( 'Y-m-d H:i:s' );
			$this->fChan->setParam( 'restrict', true );
			$channels = array();
			
			// explicitly selected people / lists
			$cList = $this->fChan->getInstances( array('subscriber'=>$this->_subscribers, 'sub_from'=>$date, 'sub_to'=>$date, 'valid_from'=>$date, 'valid_to'=>$date) );
			if( !empty($cList) ) {
				$cCount = $this->fChan->getSubscriptionLevels( $this->_subscribers, $cList );
				$subCount = count( $this->_subscribers );
				foreach( $cList as $cId ) {
					$chan = $this->fChan->getInstance( $cId );
					$o = new stdClass();
					$o->id = $chan->getId();
					if( $cCount[$cId]['sub_count'] != $subCount ) {
						$o->name = '('.$chan->getName().')';
					}
					else {
						$o->name = $chan->getName();
					}
					$channels[] = $o;
				}
			}
			
			// lists derived from people memberships and not explicitly searched for if any
			$derived = array();
			if( !empty($this->_derSubscribers) ) {
				$derCList = $this->fChan->getInstances( array('subscriber'=>$this->_derSubscribers, 'sub_from'=>$date, 'sub_to'=>$date, 'valid_from'=>$date, 'valid_to'=>$date) );
				$derCList = array_diff( $derCList, $cList );
				if( !empty($derCList) ) {
					$derCCount = $this->fChan->getSubscriptionLevels( $this->_derSubscribers, $derCList, true );
					$derSubCount = count( $this->_derSubscribers );
					foreach( $derCList as $cId ) {
						$chan = $this->fChan->getInstance( $cId );
						$o = new stdClass();
						$o->id = $chan->getId();
						if( $derCCount[$cId]['sub_count'] != $derSubCount ) {
							$o->name = '('.$chan->getName().') *derived*';
						}
						else {
							$o->name = $chan->getName().' *derived*';
						}
						$derived[$o->name] = $derCCount[$cId]['person_id'];
						$channels[] = $o;
					}
					$derived = json_encode( $derived );
				}
			}
			
			$this->_subscribed = array( $channels, $derived );
		}
		return $this->_subscribed;
	}
	
	/**
	 * retrieves the global channels to which the current user may subscribe
	 * 
	 * @return array  An array of stripped-down channel info objects to be used in rendering select lists
	 */
	function getGlobal()
	{
		if( !isset($this->_channels[0]) ) {
			$this->_loadChannels( 0 );
		}
		return $this->_channels[0];
	}
	
	/**
	 * retrieves the shared channels to which the current user may subscribe
	 * 
	 * @return array  An array of stripped-down channel info objects to be used in rendering select lists
	 */
	function getShared()
	{
		if( !isset($this->_channels[1]) ) {
			$this->_loadChannels( 1 );
			
			$creators = array();
			foreach( $this->_channels[1] as $channelOpt ) {
				$chan = $this->fChan->getInstance( $channelOpt->id );
				$creator = $chan->getCreator();
				if( !isset($creators[$creator]) ) {
					$creators[$creator] = ApotheosisData::_( 'people.displayName', $creator, 'teacher' );
				}
				$channelOpt->name = '- '.$channelOpt->name;
				$restructured[$creator][] = $channelOpt;
			}
			
			$this->_channels[1] = array();
			foreach( $creators as $creatorId=>$creator ) {
				$tmp = new stdClass();
				$tmp->id = -1;
				$tmp->name = $creator;
				$this->_channels[1][] = $tmp;
				foreach( $restructured[$creatorId] as $channelOpt ) {
					$this->_channels[1][] = $channelOpt;
				}
			}
		}
		return $this->_channels[1];
	}
	
	/**
	 * retrieves the shared channels to which the current user may subscribe
	 * 
	 * @return array  An array of stripped-down channel info objects to be used in rendering select lists
	 */
	function getPrivate()
	{
		if( !isset($this->_channels[2]) ) {
			$this->_loadChannels( 2 );
		}
		return $this->_channels[2];
	}
	
	/**
	 * loads the channels to which the current user may subscribe
	 * 
	 * @return array  An array of stripped-down channel info objects to be used in rendering select lists
	 */
	function _loadChannels( $privacy )
	{
		$date = date( 'Y-m-d H:i:s' );
		
		$this->fChan->setParam( 'restrict', true );
		$cList = $this->fChan->getInstances( array('nonsubscriber'=>$this->_subscribers, 'privacy'=>$privacy, 'sub_from'=>$date, 'sub_to'=>$date, 'valid_from'=>$date, 'valid_to'=>$date) );
		$retVal = array();
		foreach( $cList as $cId ) {
			$chan = $this->fChan->getInstance( $cId );
			$o = new stdClass();
			$o->id = $chan->getId();
			$o->name = $chan->getName();
			$retVal[] = $o;
		}
		$this->_channels[$privacy] = $retVal;
	}
	
	function addSubs( $c )
	{
		$this->fChan->addSubsciptions( $this->_subscribers, $c );
		$this->resetChannelLists();
	}
	
	function delSubs( $c )
	{
		$this->fChan->delSubsciptions( $this->_subscribers, $c );
		$this->resetChannelLists();
	}
}