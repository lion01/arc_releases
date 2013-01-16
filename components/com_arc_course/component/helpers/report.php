<?php
/**
 * @package     Arc
 * @subpackage  Course
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );


/**
 * Merge words handler
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportMergeWords_Course extends ApothReportMergeWords
{
	function name( $d, $o )
	{
		return htmlspecialchars( ApotheosisData::_( 'course.name', $d ) );
	}
}


// #####  Field subclasses  #####

/**
 * Report Text Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Course_Name extends ApothReportField
{
	function renderHTML( $value )
	{
		if( is_null( $value ) ) {
			switch( $this->_core['lookup_source'] ) {
			case( 'rpt_group_id' ):
			default:
				$value = ApotheosisData::_( 'course.name', $this->_rptData['rpt_group_id'] );
				break;
			
			case( 'tutorgroup' ):
				$value = ApotheosisData::_( 'course.name', ApotheosisData::_( 'timetable.tutorgroup', $this->_rptData['reportee_id'] ) );
			}
		}
		return parent::renderHTML( htmlspecialchars( $value ) );
	}
}
?>