<?php
/**
 * @package     Arc
 * @subpackage  Pupil
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Content
*/
class TOOLBAR_pupilmanager
{
	function _EDIT()
	{
		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
		$cid = intval($cid[0]);

		$text = ( $cid ? JText::_( 'Edit' ) : JText::_( 'New' ) );

		JToolBarHelper::title( JText::_( 'Article' ).': <small><small>[ '. $text.' ]</small></small>', 'addedit.png' );
		JToolBarHelper::preview( 'index.php?option=com_content&id='.$cid.'&tmpl=component', true );
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ( $cid ) {
			// for existing articles the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		} else {
			JToolBarHelper::cancel();
		}
		JToolBarHelper::help( 'screen.content.edit' );
	}

	function _DEFAULT()
	{
		global $filter_state;

		$user = &ApotheosisLib::getUser();

		JToolBarHelper::title( JText::_( 'Apotheosis - Pupil Manager' ), 'addedit.png' );
		JToolBarHelper::trash();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX('copyMenu');
		if ($user->get('gid') == 25) {
			JToolBarHelper::configuration('com_content', '450');
		}
		JToolBarHelper::help( 'help article' );
	}
}
?>