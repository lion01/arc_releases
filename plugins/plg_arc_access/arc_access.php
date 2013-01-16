<?php
/**
 * @package     Arc
 * @subpackage  Plugin_Access
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
jimport('joomla.event.plugin');

require_once( JPATH_BASE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' ); 

class plgSystemArc_access extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for
	 * plugins because func_get_args ( void ) returns a copy of all passed arguments
	 * NOT references.  This causes problems with cross-referencing necessary for the
	 * observer design pattern.
	 */
	function plgSystemArc_access( &$subject, $config )
	{
		parent::__construct( $subject, $config );
	}
	
	/**
	 * Includes the code to operate Arc System Log
	 */
	function onAfterInitialise()
	{
		$debug = 0;
		$debugDie = 1;
		// set this to the action id for which additional info is required
		// if left on true all actions will give full output
		$debugAction = true;
/*
		define( '_ARC_ACL', true );
		$allowed = _ARC_ACL;
		return $allowed;
// */
		
		global $mainframe;
		if( $mainframe->isAdmin() ) {
			return true;
		}
		
		$allowed = true;
		$db = &JFactory::getDBO();
		$user = &ApotheosisLib::getUser();
		
		if( $debug ) {
			$o = '';
			ob_start();
			
			$actionId = ApotheosisLib::getActionId( false, array(), $debugAction );
			
			var_dump_pre($user, 'user');
			var_dump_pre($actionId, 'actionId: ');
			
			// set constant here to get true/false/null depending if we have the action or not
			// if user fails on dependancy check then arc doesn't need to know as redirect will happen
			define( '_ARC_ACL', ApotheosisLibAcl::getUserPermitted( $user->id, $actionId ) );
			$allowed = _ARC_ACL;
			
			if( $allowed !== true ) {
				var_dump_pre($allowed, 'initial allowed');
				$o .= ob_get_clean();
				ob_start();
			}
			else {
				ob_end_clean();
				ob_start();
				var_dump_pre($allowed, 'initial allowed');
			}
			var_dump_pre(ApotheosisLibAcl::getUserRestricted($user->id, $actionId), 'restricted?');
			var_dump_pre(ApotheosisLibAcl::checkDependancies($user->id, $actionId, null, true), 'dependancies met?' );
			
			if( $allowed && ApotheosisLibAcl::getUserRestricted($user->id, $actionId) ) {
				$allowed = ApotheosisLibAcl::checkDependancies( $user->id, $actionId );
			}
			
			$o .= ob_get_clean();
			if( $allowed !== true ) {
				echo $o;
				if( $debugDie ) {
					die();
				}
			}
		}
		else {
			$actionId = ApotheosisLib::getActionId( false, array(), false );
			
			// set constant here to get true/false/null depending if we have the action or not
			// if user fails on dependancy check then arc doesn't need to know as redirect will happen
			define( '_ARC_ACL', ApotheosisLibAcl::getUserPermitted( $user->id, $actionId ) );
			$allowed = _ARC_ACL;
			if( $allowed && ApotheosisLibAcl::getUserRestricted($user->id, $actionId) ) {
				$allowed = ApotheosisLibAcl::checkDependancies( $user->id, $actionId );
			}
		}
		
		$session =& JFactory::getSession();
		$isRaw = JRequest::getVar( 'format', null, 'GET' ) == 'raw';
//var_dump_pre( $_POST, 'post data' );
		if( $allowed === false && $isRaw ) {
			$user = &JFactory::getUser();
			?>
			<dl id="system-message">
			<dt class="error">Access denied</dt>
			<dd><?php echo ( $user->id == 0
				? 'You are not currently logged in. Perhaps your session expired. Please log in and try again.'
				: 'You are not permitted to view the requested resource' 
			)?></dd>
			</dl>
			<?php
			$mainframe->close();
		}
		elseif( $allowed === false ) {
			if( $debug ) {
				ob_start();
			}
			$ref = $_SERVER['HTTP_REFERER'];
			$rObj = JURI::getInstance($ref);
			$cObj = JURI::getInstance();
			
			$cAction = ApotheosisLib::getActionId( $cObj->getQuery(true), array() );
			if( ApotheosisLibAcl::getUserPermitted($user->id, $actionId)
			 && (!ApotheosisLibAcl::getUserRestricted($user->id, $actionId) || ApotheosisLibAcl::checkDependancies( $user->id, $actionId ))
			 && JURI::isInternal($ref)
			 && ($rObj->toString() != $cObj->toString()) ) {
				$target = $ref;
			}
			else {
				$menu = &JSite::getMenu();
				$itemId = $this->params->get( 'loginItem' );
				$item = $menu->getItem( $itemId );
				$target = $item->link.'&Itemid='.$itemId;
			}
			
			// non-logged-in users may have just been timed out.
			// remember where they were trying to get to
			// so after login they can be taken to their intended page
			$u = JFactory::getUser();
			if( is_null( $session->get( 'arc_access_denied' ) ) && $u->id == 0 ) {
				$session->set( 'arc_access_denied', true );
				$session->set( 'arc_access_goal', $cObj->toString() );
				$session->set( 'arc_access_post', $_POST );
			}
			
			$mainframe->enqueueMessage( 'Access Denied. You are not permitted to view the requested resource', 'error' );
			if( $debug ) {
				$o .= "\n".ob_get_clean();
				$mainframe->enqueueMessage( $o, 'error' );
				$mainframe->enqueueMessage( ( ($rObj->toString() == $cObj->toString()) ? 'back to referer' : 'back to index to avoid loop' ), 'error' );
			}
			$mainframe->redirect( $target );
		}
		
		// If the redirect has been done, restore the POST data from before
		if( $session->get( 'arc_access_redirected' ) ) {
			$_POST = $session->get( 'arc_access_post' );
			$_REQUEST = array_merge( $_REQUEST, $_POST );
			$session->clear( 'arc_access_redirected' );
			$session->clear( 'arc_access_post' );
		}
	}
	
	function onAfterDispatch()
	{
		global $mainframe;
		if( $mainframe->isAdmin() ) {
			return true;
		}
		
		$session = &JFactory::getSession();
		$u = JFactory::getUser();
		
		if( $session->get( 'arc_access_denied' ) ) {
			// if the user was redirected to login by this plugin and has now done so
			// take them back to where they wanted to be
			if( $u->id != 0 ) {
				$previous = $session->get( 'arc_access_goal' );
				
				$session->clear( 'arc_access_denied' );
				$session->clear( 'arc_access_goal' );
				$session->set( 'arc_access_redirected', true );
				$mainframe->redirect( $previous );
			}
			
			// going anywhere but the login page indicates the user doesn't want to be taken back
			$menu = &JSite::getMenu();
			$itemId = $this->params->get( 'loginItem' );
			$item = $menu->getItem( $itemId );
			$target = JURI::base().$item->link.'&Itemid='.$itemId;
			$cObj = JURI::getInstance();
			$cUrl = $cObj->toString();
			if( $cUrl != $target ) {
				$session->clear( 'arc_access_denied' );
				$session->clear( 'arc_access_goal' );
				$session->clear( 'arc_access_redirected' );
				$session->clear( 'arc_access_post' );
			}
		}
		
		
	}
	
}
?>