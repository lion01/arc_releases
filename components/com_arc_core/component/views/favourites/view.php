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

jimport('joomla.application.component.view');

/**
 * Extension Manager Install View
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class ApotheosisViewFavourites extends JView
{
	function display($tpl = NULL)
	{
		$document = &JFactory::getDocument();
		$document->setTitle(JText::_( 'Apotheosis Common Tasks' ));
		
		$items = &$this->get('Favourites');
		$this->assignRef('items', $items);
		
		parent::display($tpl);
	}

	function loadItem($index=0)
	{
		$item = &$this->items[$index];
		$item->title = $item->menu_text;
		$this->assignRef('item', $item);
	}
}
?>