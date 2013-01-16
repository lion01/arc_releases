<?php
/**
 * @package     Arc
 * @subpackage  Timetable
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML Article View class for the Content component
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class AttendanceViewEreg extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		$dispatcher	=& JEventDispatcher::getInstance();
/*
		// Initialize some variables
		//$article	= & $this->get( 'Article' );
		$params 	= & $article->parameters;
		$params->def('introtext', 1);
		$params->set('intro_only', 0);

		// show/hides the intro text
		if ($params->get('introtext')) {
			$article->text = $article->introtext. ($params->get('intro_only') ? '' : chr(13).chr(13).$article->fulltext);
		} else {
			$article->text = $article->fulltext;
		}

		// process the new plugins
		JPluginHelper::importPlugin('content', 'image');
		$dispatcher->trigger('onPrepareContent', array (& $article, & $params, 0));
*/
		//$document = &JFactory::getDocument();

		// set document information
		$document->setTitle('title');
		//$document->setName('title_alias');
		//$document->setDescription('metadesc');
		//$document->setMetaData('keywords', 'metakey');

		// prepare header lines
		//$document->setHeader($this->_getHeaderText($article, $params));

		//$document->setData('blah');
		
		
	}

	function _getHeaderText(& $article, & $params)
	{
		// Initialize some variables
		$text = '';

		if ($params->get('showAuthor')) {
			// Display Author name
			if ($article->usertype == 'administrator' || $article->usertype == 'superadministrator') {
				$text .= "\n";
				$text .= JText::_('Written by').' '. ($article->created_by_alias ? $article->created_by_alias : $article->author);
			} else {
				$text .= "\n";
				$text .= JText::_('Contributed by').' '. ($article->created_by_alias ? $article->created_by_alias : $article->author);
			}
		}

		if ($params->get('createdate') && $params->get('showAuthor')) {
			// Display Separator
			$text .= "\n";
		}

		if ($params->get('createdate')) {
			// Display Created Date
			if (intval($article->created)) {
				$create_date = JHTML::Date($article->created);
				$text .= $create_date;
			}
		}

		if ($params->get('modifydate') && ($params->get('showAuthor') || $params->get('createdate'))) {
			// Display Separator
			$text .= " - ";
		}

		if ($params->get('modifydate')) {
			// Display Modified Date
			if (intval($article->modified)) {
				$mod_date = JHTML::Date($article->modified);
				$text .= JText::_('Last Updated').' '.$mod_date;
			}
		}
		$text = 'foo';
		return $text;
	}
}
?>