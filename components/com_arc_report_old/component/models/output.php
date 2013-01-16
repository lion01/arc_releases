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

require_once( JPATH_COMPONENT.DS.'models'.DS.'extension.php' );

/**
 * Reports Output Model
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsModelOutput extends ReportsModel
{
	/** @var The report instance that we want to deal with in this model */
	var $_report;
	
	/**
	 * Retrieves the report with the id given, or if none given / specified report doesn't exist, the current report object
	 */
	function &getReport( )
	{
		return $this->_report;
	}
	
	/**
	 * Sets the current report to be the new report object created from
	 * the given student, group, cycle combination
	 * Does NOT add the new report to the internal list of pre-existing reports
	 */
	function setReportNew( $student, $group, $cycle, $style = false )
	{
		$rpt = &ApothReport::newInstance( $student, $group, $cycle, $style );

		$this->_report = &$rpt;
		return true;
	}	
	
}

?>