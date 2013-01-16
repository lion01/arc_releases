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

// include all the other library files for the front-end
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library_shared.php' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_controller.php' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_model.php' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_factory.php' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_pagination.php' );

// path the helpers
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_report'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_assessment'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_timetable'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_behaviour'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_message'.DS.'helpers'.DS.'html');
JHTML::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_arc_tv'.DS.'helpers'.DS.'html');

/**
 * Apotheosis Library
 *
 * @author     lightinthedark <code@lightinthedark.org.uk>
 * @package    Apotheosis
 * @subpackage Core
 * @since 0.1
 */
class ApotheosisLib extends ApotheosisLibParent
{
	
	/**
	 * Displays a nice neat table displaying the 2-d array passed to it
	 * @param dataset array  The 2-d array of rows and columns to display
	 */
	function display_table($dataset)
	{
		if (!is_array($dataset)) {
			$dataset = array($dataset);
		}
		$firstRow = reset($dataset);
		$type = gettype($firstRow);
		
		// table opener and heading row
		echo '<table class="data"><tr>';
		foreach ($firstRow as $heading=>$v) {
			// map column names into an associative array of header titles
			// if the header you are looking for is not in this array, check
			// the lang file, if not in there, display the raw heading
			echo '<th>'.JText::_( strtoupper($heading) ).'</th>';
		}
		echo '</tr>';
		$oddrow = false;
		
		// display each row
		foreach ($dataset as $row) {
			echo '<tr '.(($oddrow) ? 'class="oddrow"' : '').'>';
			
			// display each column in this row
			foreach ($row as $col=>$val) {
				echo '<td>';
				
				switch($col) {
				case('email'):
					echo '<a href="mailto:'.$val.'">'.$val.'</a>';
					break;
				
				default:
					echo $val;
					break;
				}
				
				echo '</td>';
			}
			echo '</tr>';
			$oddrow = !$oddrow;
		}
		
		// and finally, close the table
		echo '</table>';
	}
	
	/**
	 * Starts the various divs required for an arc_data section
	 * @param $highRows array  Associative array of classname=>height pairs used to define heights for potentially tall rows
	 */
	function arcDataStart()
	{
		JHTML::_('behavior.mootools'); 
		return '<div id="arc_data">'
			."\n".' <div id="arc_data_left">'
			."\n".' <div id="arc_data_left_inner">'
			."\n";
	}
	
	/**
	 * Ends the left part of the arc_data and starts the right part
	 */
	function arcDataMiddle()
	{
		return '</div></div><!-- end of arc_data_left -->'
			."\n".'<div id="arc_data_right">'
			."\n".'<div id="arc_data_right_inner">'
			."\n";
	}
	
	/**
	 * Ends the right part of the arc_data and the arc_data div itself
	 */
	function arcDataEnd()
	{
		return '</div><!-- end of arc_data_right_inner -->'
			."\n".'</div><!-- end of arc_data_right -->'
			."\n".'</div><!-- end of arc_data -->'
			."\n";
	}
	
	function arcLoadImgUrl()
	{
		return JURI::base().'media/system/images/mootree_loader.gif';
	}
}
?>
