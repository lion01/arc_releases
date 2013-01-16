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
 * Attendance Manager Truants View
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since 0.1
 */
class AttendanceViewTruants extends JView 
{
	/**
	 * Creates a new attendance truants view
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Edit the truants list
	 */
	function editTruants()
	{
		$this->truants = $this->get( 'Truants' );
		$this->nonTruants = $this->get( 'NonTruants' );
		
		$this->display();
	}
}
?>
