<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Renders a category element
 *
 * @author 		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementHistoricalCycles extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'historical_cycles';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$helper =	&ReportsStatements::getInstance();
		$cycles = $helper->getHistoricalCycles();

		if(is_null($cycles)) {
			$html = "\n<input type=\"text\" id=\"".$control_name.$name."\" name=\"".$control_name."[".$name."]\" value=\"\" /></div>";
			return $html;
		}
		else {	
			return JHTML::_('select.genericlist',  $cycles, ''.$control_name.'['.$name.']', 'class=""', 'id', 'display', $value, $control_name.$name );
		}
	}
}