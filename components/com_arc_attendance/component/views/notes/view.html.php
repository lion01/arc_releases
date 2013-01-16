<?php
/**
 * @package     Arc
 * @subpackage  Attendance
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
 * Attendance Manager Reports View
 *
 * - Required reports: (though perhaps we should build a more general, user-definable system for this)
 * View historical register
 * Daily breakdown: pupil rows = pupils, cols = periods, select pupils from multi-select list, selectall box (mouseover to show course) (grouped by tutor)
 * Course breakdown: rows = pupils, cols = date/periods for that course, remember adhocs, select date range
 * Individual (or multiple, grouped or separate) pupil summary marks over time (graph) (select period, select marks of concern)
 * Individual (or multiple, grouped or separate) daily summary: total attendance marks for that day (bar graph) (select marks of interest)
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class AttendanceViewNotes extends JView 
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_varMap = array('state'=>'State');
	}
	
	
	/**
	 * Displays a page where notes can be added to and removed from a pupil
	 */
	function edit()
	{
		$this->_varMap['notes'] = 'Notes';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		if( !empty($this->pupil) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT title, COALESCE( p.preferred_firstname, p.firstname ) AS firstname, middlenames, COALESCE( p.preferred_surname, p.surname ) AS surname'
				."\n".' FROM #__apoth_ppl_people AS p'
				."\n".' WHERE id = '.$db->Quote($this->pupil).';';
			$db->setQuery( $query );
			$blank = $db->loadObject();
			
			$blank->id = NULL;
			$blank->pupil_id = $this->pupil;
			$blank->message = '';
			array_unshift($this->notes, $blank);
		}
		
		if( is_array($this->notes) ) {
			$this->note = reset($this->notes);
		}
		
		$this->edits = true;
		$this->delivering = true;
		$this->showDelivered = true;
		
		parent::display();
	}
	
	
	/**
	 * Displays a page listing notes for a pupil
	 * (for when there are no actions or selected registers)
	 *
	 * @param string $template  Optional name of the template to use
	 */
	function display()
	{
		$this->_varMap['notes'] = 'Notes';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		if( is_array($this->notes) ) {
			$this->note = reset($this->notes);
		}
		
		$this->edits = false;
		$this->delivering = true;
		$this->showDelivered = (boolean)JRequest::getVar( 'all', true );
		$this->link .= '&all='.(int)$this->showDelivered;
		
		parent::display();
	}

}

?>
