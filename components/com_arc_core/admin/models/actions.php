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

// Give us access to the joomla model class
jimport( 'joomla.application.component.model' );

/**
 * Core Admin Actions Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Core
 * @since      1.6
 */
class CoreAdminModelActions extends ArcAdminModel
{
	/** @var array Array of actions */
	var $_items = array();
	
	
	// #####  Action list  #####
	
	function &getItems()
	{
		if (empty($this->_items)) {
			// Load the items
			$this->_loadItems();
		}
		return $this->_items;
	}
	
	function _loadItems()
	{
		global $mainframe;
		
		/* Get a database connector */
		$db =& JFactory::getDBO();

		$query = 'SELECT a.*, IF( (f.action IS NULL), 0, 1 ) AS favourite'
				."\n".'FROM #__apoth_sys_actions AS a'
				."\n".'LEFT JOIN #__apoth_sys_favourites AS f'
				."\n".'  ON f.action = a.id'
				."\n".'GROUP BY a.id'
				."\n".'ORDER BY a.name';
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		$numRows = count($rows);
		$this->setState('pagination.total', $numRows);
		$this->_items = $rows;
	}
	
//	/**
//	 * Save an action.
//	 * *** This can mainly only save its favourite-ness, but that's changed since this was made
//	 * *** Updates and testing required
//	 */
//	function save($ids)
//	{
//		// Get a database connector
//		$db =& JFactory::getDBO();
//		
//		$db->setQuery('SELECT * FROM #__apoth_sys_actions WHERE `favourite_for` IS NOT NULL');
//		$preset = $db->loadObjectList('menu_id');
//		
//		// set all given favourites as favourites if they aren't already
//		foreach ($ids as $id=>$on) {
//			if (array_key_exists($id, $preset)) {
//				unset($preset[$id]);
//			}
//			else {
//				$db->setQuery('UPDATE #__apoth_sys_actions'
//					."\n".' SET `favourite_for`="group_teacher"'
//					."\n".' WHERE `menu_id`='.$id);
//				$db->query();
//			}
//		}
//		
//		// unset all the remaining tags for actions already tagged as favourite
//		foreach ($preset as $id=>$obj) {
//			$db->setQuery('UPDATE #__apoth_sys_actions'
//				."\n".' SET `favourite`=NULL'
//				."\n".' WHERE `menu_id`='.$id);
//			$db->query();
//		}
//		
//		$this->setState('message', JText::_('Settings saved'));
//	}
	
	
	// #####  Individual action handling  #####
	
	function getAction()
	{
		return $this->_action;
	}
	
	function setAction( $id )
	{
		if( $id < 0 ) { // dummy action as base for new ones
			$this->_action = new stdClass();
			$this->_action->id = -1;
			$this->_action->menu_id = 0;
			$this->_action->option = null;
			$this->_action->task = null;
			$this->_action->params = null;
			$this->_action->name = '';
			$this->_action->menu_text = '';
			$this->_action->description = '';
			$this->_action->favourite = array();
		}
		else {
			$db =& JFactory::getDBO();
			$query = 'SELECT a.*'
					."\n".'FROM #__apoth_sys_actions AS a'
					."\n".'WHERE '.$db->nameQuote( 'id' ).' = '.$db->quote( $id );
			$db->setQuery( $query );
			$this->_action = $db->loadObject();
			
			$query = 'SELECT `role` FROM #__apoth_sys_favourites AS f'
				."\n".' WHERE '.$db->nameQuote( 'action' ).' = '.$db->quote( $id );
			$db->setQuery( $query );
			$this->_action->favourite = $db->loadResultArray();
		}
	}
	
	function setData( $data )
	{
		if( $data['id'] < 0 ) {
			$this->_action->id = null;
		}
		$this->_action->menu_id     = $data['menu_id'];
		$this->_action->option      = ( empty($data['option']) ? null : $data['option'] );
		$this->_action->task        = ( empty($data['task']  ) ? null : $data['task']   );
		$this->_action->params      = ( empty($data['params']) ? null : $data['params'] );
		$this->_action->name        = $data['name'];
		$this->_action->menu_text   = $data['menu_text'];
		$this->_action->description = $data['description'];
		$this->_action->favourite   = $data['favourite'];
	}
	
	/**
	 * Commit the action's current data to the database
	 * @return mixed  The id of the action updated or false on failure
	 */
	function save()
	{
		$db = &JFactory::getDBO();
		$query = 'REPLACE INTO #__apoth_sys_actions'
			.'( '.$db->nameQuote( 'id' )
			.', '.$db->nameQuote( 'menu_id' )
			.', '.$db->nameQuote( 'option' )
			.', '.$db->nameQuote( 'task' )
			.', '.$db->nameQuote( 'params' )
			.', '.$db->nameQuote( 'name' )
			.', '.$db->nameQuote( 'menu_text' )
			.', '.$db->nameQuote( 'description' )
			.')'
			."\n".' VALUES '
			.'( '.$db->Quote( $this->_action->id )
			.', '.$db->Quote( $this->_action->menu_id )
			.', '.( is_null($this->_action->option ) ? 'NULL' : $db->Quote( $this->_action->option ) )
			.', '.( is_null($this->_action->task   ) ? 'NULL' : $db->Quote( $this->_action->task )   )
			.', '.( is_null($this->_action->params ) ? 'NULL' : $db->Quote( $this->_action->params ) )
			.', '.$db->Quote( $this->_action->name )
			.', '.$db->Quote( $this->_action->menu_text )
			.', '.$db->Quote( $this->_action->description )
			.')';
		$db->setQuery( $query );
		$db->query();
		
		if( $this->_action->id < 0 ) {
			$this->_action->id = $db->insertid();
		}
		
		// now deal with the favourites
		$query = 'DELETE FROM #__apoth_sys_favourites'
			."\n".'WHERE '.$db->nameQuote( 'action' ).' = '.$db->quote( $this->_action->id );
		
		if( !empty($this->_action->favourite) ) {
			$dbAction = $db->Quote( $this->_action->id );
			foreach( $this->_action->favourite as $f ) {
				$values[] = '( '.$dbAction.', '.$db->Quote( $f ).' )';
			}
			
			$query .= "\n".'INSERT INTO #__apoth_sys_favourites'
				.'( '.$db->nameQuote( 'action' ).', '.$db->nameQuote( 'role' ).')'
				."\n".'VALUES'
				."\n".implode( "\n, ", $values );
		}
		$db->setQuery( $query );
		$db->queryBatch();
		return $db->getErrorMsg() == '';
	}
	
	function delete( $ids )
	{
		if( !is_array($ids) || empty($ids) ) {
			return false;
		}
		
		$db = &JFactory::getDBO();
		foreach( $ids as $k=>$v ) {
			$ids[$k] = $db->Quote($v);
		}
		$query = 'DELETE FROM #__apoth_sys_actions'
			."\n".'WHERE id IN ('.implode( ', ', $ids ).');'
			."\n".'DELETE FROM #__apoth_sys_favourites'
			."\n".'WHERE action IN ('.implode( ', ', $ids ).');';
		$db->setQuery( $query );
		$db->queryBatch();
		return $db->getErrorMsg() == '';
	}
	
	function getMenuOptions()
	{
		$tmp = new stdClass();
		$tmp->id = '';
		$tmp->label = 'select...';
		
		return JHTML::_( 'menu.linkoptions' );
	}
}
?>