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
 * Message Hub Controller
 */
class MessageControllerChannel extends MessageController
{
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		
		$this->registerTask( 'Update', 'saveChannel');
		$this->registerTask( 'SaveAsNew', 'saveChannel');
		$this->registerTask( 'Delete', 'deleteChannel');
		
		$this->map = array(
			'message.first'          =>array( 'input'=>'isFirst' ),
			'message.method'         =>array( 'input'=>'methods' ),
			'message.time'           =>array( 'input'=>'times'   ),
			'behaviour.color'        =>array( 'input'=>'colors'  ),
			'behaviour.studentTutor' =>array( 'input'=>'tutorGroup', 'varInput'=>'tutorGroup_var', 'varName'=>'user.tutorgroup' ),
			'behaviour.studentYear'  =>array( 'input'=>'yearGroup',  'varInput'=>'yearGroup_var',  'varName'=>'user.year'       ),
			'behaviour.group'        =>array( 'input'=>'group',      'varInput'=>'group_var',      'varName'=>'user.group'      ),
			'people.person'          =>array( 'input'=>'student',    'varInput'=>array( 'student_var_t', 'student_var_u', 'student_var_r' ), 'varName'=>array( 'user.teaches', 'user.person_id', 'user.related' )  ),
			'behaviour.action'       =>array( 'input'=>'actions'  )
		);
	}
	
	function display()
	{
		$model = &$this->getModel( 'channel' );
		$viewType = JRequest::getVar( 'format', 'html' );
		
		switch( strtolower(JRequest::getVar('scope', 'summary')) ) {
		case( 'subscriptions' ):
			$viewFunc = 'showSub';
			break;
		
		case( 'channel' ):
			$viewFunc = 'showChannel';
			break;
		
		default:
			$this->_populateFields( $model );
			$viewFunc = 'display';
		}
		$view = $this->getView( 'channel', $viewType );
		$view->setModel( $model, true );
		$view->$viewFunc();
		
		$this->saveModel();
	}
	
	function loadChannel()
	{
		$model = &$this->getModel( 'channel' );
		$model->setChannel( JRequest::getVar( 'channelId' ) );
		$this->_populateFields( $model );
		$this->display();
	}
	
	function _populateFields( $model )
	{
		$ch = $model->getChannel();
		JRequest::setVar( 'subscribed', $ch->getId() );
		JRequest::setVar( 'global',     $ch->getId() );
		JRequest::setVar( 'public',     $ch->getId() );
		
		$r = $ch->getRules();
		
		foreach( $r as $rule ) {
			$hc = $rule['handler'].'.'.$rule['check'];
			$negate = false;
			$vals = array();
			foreach( $rule['_params'] as $p ) {
				switch( $p['type'] ) {
				case( 'value' ):
					$vals[] = $p['data'];
					$negate = $negate || $p['negate'];
					break;
				
				case( 'variable' ):
					if( is_array($this->map[$hc]['varInput']) ) {
						$index = array_search( $p['data'], $this->map[$hc]['varName'] );
						JRequest::setVar( $this->map[$hc]['varInput'][$index], true );
					}
					else {
						JRequest::setVar( $this->map[$hc]['varInput'], true );
					}
					break;
				}
			}
			
			if( !empty( $vals ) ) {
				if( $hc == 'behaviour.group' ) {
					$vals = serialize( $vals );
				}
				JRequest::setVar( $this->map[$hc]['input'], $vals );
				JRequest::setVar( $this->map[$hc]['input'].'_neg', $negate );
			}
		}
		
	}
	
	function saveChannel()
	{
		ob_start();
		$model = &$this->getModel( 'channel' );
		$isNew = ( JRequest::getWord( 'task' ) == 'SaveAsNew' );
		
		if( $isNew ) {
			$fChan = ApothFactory::_( 'message.Channel' );
			$ch = &$fChan->getDummy( -1 );
		}
		else {
			$ch = &$model->getChannel();
		}
		
		$ch->setName( JRequest::getVar('name') );
		$ch->setDescription( JRequest::getVar('description') );
		$ch->setPrivacy( JRequest::getVar('privacy') );
		$ch->setFolder( JRequest::getVar('default_folder') );
		$ch->setExclusive( (bool)JRequest::getVar('exclusive') );
		
		$ch->resetRules();
		// gather all the value-based rules from the form
		foreach( $this->map as $rule=>$info ) {
			$rule = explode( '.', $rule );
			$params = JRequest::getVar( $info['input'] );
			$negate = (bool)JRequest::getVar( $info['input'].'_neg');
			if( $rule[0] == 'behaviour' && $rule[1] == 'group' ) {
				$params = unserialize( $params );
			}
			
			if( is_array($params) ) {
				foreach( $params as $k=>$v ) {
					if( empty($v) ) {
						unset( $params[$k] );
					}
					else {
						$params[$k] = array( 'type'=>'value', 'data'=>$v, 'negate'=>$negate );
					}
				}
			}
			else {
				if( !empty($params) ) {
					$params = array( array('type'=>'value', 'data'=>$params, 'negate'=>$negate) );
				}
			}
			if( !empty($params) ) {
				$ch->setRule( $rule[0], $rule[1], $params );
			}
			
			if( isset($info['varInput']) ) {
				if( !is_array( $info['varInput'] ) ) {
					$info['varInput'] = array( $info['varInput'] );
					$info['varName'] = array( $info['varName'] );
				}
				foreach( $info['varInput'] as $vk=>$vi ) {
					$params = JRequest::getVar( $vi );
					if( !empty($params) ) {
						$ch->setRule( $rule[0], $rule[1], array(array('type'=>'variable', 'data'=>$info['varName'][$vk], 'negate'=>false)) );
					}
				}
			}
		}
		
		$ch->commit();
		
		// The commit process re-initialises the factory copy
		// so we need to reconnect the model's reference
		$model->setChannel( $ch->getId() );
		$model->resetChannelLists();
		$this->saveModel();
		$o = ob_get_clean();
		
		global $mainframe;
		$link = ApotheosisLib::getActionLink();
		$mainframe->enqueueMessage( $o );
		$mainframe->redirect( $link, ( $isNew ? 'Channel created' : 'Channel updated' ) );
	}
	
	function deleteChannel()
	{
		$model = &$this->getModel( 'channel' );
		$r = $model->deleteChannel();
		
		$model->setChannel( -1 );
		$model->resetChannelLists();
		$this->saveModel();
		
		global $mainframe;
		$link = ApotheosisLib::getActionLink();
		if( $r ) {
			$mainframe->redirect( $link, 'Channel deleted' );
		}
		else {
			$mainframe->redirect( $link, 'Channel could not be deleted', 'warning' );
		}
	}
	
	function reloadSubs()
	{
		$model = &$this->getModel( 'channel' );
		$s1 = json_decode( JRequest::getVar('subscribers') );
		$s2 = json_decode( JRequest::getVar('subscriber_lists') );
		if( !is_array($s1) ) { $s1 = array(); }
		if( !is_array($s2) ) { $s2 = array(); }
		$s = array_merge( $s1, $s2 );
		foreach( $s as $k=>$id ) {
			if( $id == '' ) {
				unset( $s[$k] );
			}
		}
		if( empty($s) ) {
			$u = ApotheosisLib::getUser();
			$s = array( $u->person_id );
		}
		$r = $model->setSubscribers( $s );
		$this->display();
	}
	
	function addSubs()
	{
		$model = &$this->getModel( 'channel' );
		$r = $model->addSubs( json_decode(JRequest::getVar('channels')) );
		$this->display();
	}
	
	function delSubs()
	{
		$model = &$this->getModel( 'channel' );
		$r = $model->delSubs( json_decode(JRequest::getVar('channels')) );
		$this->display();
	}
}
?>