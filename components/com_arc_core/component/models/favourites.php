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

jimport( 'joomla.application.component.model' );
 /*
 * Extension Manager Summary Model
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class ApotheosisModelFavourites extends JModel
{
	/** @var array Array of tagged favourite tasks */
	var $_favourites = array();
	
	function getFavourites()
	{
		if ( empty($this->_favourites) ) {
			$this->_loadFavourites();
		}
		return  $this->_favourites;
	}
	
	function _loadFavourites()
	{
		$db = JFactory::getDBO();
		$roleTable = ApotheosisLibAcl::getUserTable( 'core.roles' );
		
		$query = 'SELECT a.*, m.link'
			."\n".'FROM #__apoth_sys_actions AS a'
			."\n".'INNER JOIN #__apoth_sys_favourites AS f'
			."\n".'   ON f.action = a.id'
			."\n".'INNER JOIN '.$roleTable.' AS r'
			."\n".'   ON r.id = f.role'
			."\n".'INNER JOIN #__menu AS m'
			."\n".'   ON a.menu_id = m.id'
			."\n".'GROUP BY a.id';
		$query = ApotheosisLibAcl::limitQuery( $query, 'core.roles', 'a', 'role' );
		$db->setquery( $query );
		$this->_favourites = $db->loadObjectList();
		if( !is_array($this->_favourites) ) { $this->_favourites = array(); }
	}
}
?>