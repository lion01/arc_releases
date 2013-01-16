<?php
/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Core Admin Permissions Controller
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Core
 * @since      1.6
 */
class CoreAdminControllerPermissions extends CoreAdminController
{
	/**
	 * Toggles whether an action / role combination is allowed
	 * Only useable via ajax
	 */
	function toggleAllowed()
	{
		global $mainframe;
		$db = &JFactory::getDBO();
		$aId = JRequest::getVar( 'aId', false );
		$rId = JRequest::getVar( 'rId', false );
		
		if( ($aId !== false) && ($rId !== false) ) {
			$query = 'SELECT * FROM #__apoth_sys_acl'
				."\n".'WHERE `action` = '.$db->quote( $aId ).' AND `role` = '.$db->quote( $rId );
			$db->setQuery($query);
			$codeDetails = $db->loadObject();
			
			if( is_null($codeDetails) || ($codeDetails->allowed == 0) ) {
				$sees = $db->Quote( $rId );
				$allowed = $db->Quote( 1 );
				$newVal = 'restricted';
			}
			elseif( !is_null($codeDetails->sees) ) {
				$sees = 'NULL';
				$allowed = $db->Quote( 1 );
				$newVal = 'allowed';
			}
			else {
				$sees = $db->Quote( $rId );
				$allowed = $db->Quote( 0 );
				$newVal = 'denied';
			}
			$query = 'REPLACE INTO #__apoth_sys_acl ( `action`, `role`, `sees`, `allowed` )'
				."\n".'VALUES ( '.$db->Quote($aId).', '.$db->Quote($rId).', '.$sees.', '.$allowed.' )';
			
			$db->setQuery($query);
			$db->query();
		}

		$view = $this->getView( 'permissions', 'raw' );
		$view->state = $newVal;
		$view->aId = $aId;
		$view->rId = $rId;
		$view->display();
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	function flush()
	{
		global $mainframe;
		
		ApotheosisLibDbTmp::flush();
		
		$mainframe->enqueueMessage( 'privileges flushed' );
		$this->display();
	}
}
?>