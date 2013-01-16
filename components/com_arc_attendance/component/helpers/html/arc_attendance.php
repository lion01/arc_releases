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

/**
 * Utility class for generating arc attendance specific HTML entities
 *
 * @static
 * @package    Arc
 * @subpackage Attendance
 * @since      1.5
 */
class JHTMLArc_Attendance
{
	/**
	 * Generate HTML to display a day select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */
	function days( $name, $default = null, $multiple = false )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		$db = &JFactory::getDBO();
		$day = new stdClass();
		$day->day = '';
		$day->dayName = '';
		$days = array( $day );
		
		$pattern = &ApotheosisLibCycles::getPattern();
		$format = $pattern->format;
		$tmpDays = preg_split( '~~', $format, -1, PREG_SPLIT_NO_EMPTY );
		
		foreach( $tmpDays as $k=>$v ) {
			if( $v != '.' ) {
				$v = new stdClass();
				$v->pattern = $pattern->id;
				$v->day = $k;
				$v->dayName = ApotheosisLibCycles::cycleDayToDOW( $v->day, $v->pattern, 'text' );
				$days[] = $v;
			}
		}
		
		$attribs = ( $multiple ? 'multiple="multiple" class="multi_small"' : '' );
		$attribs .= ' size="1"';
		$name = ( $multiple ? $name.'[]' : $name );
			
		$retVal =  JHTML::_( 'select.genericList', $days, $name, $attribs, 'day', 'dayName', $oldVal );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a period type select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */
	function typeOfPeriod( $name, $default = null, $multiple = false )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		$periodType = new stdClass();
		$periodType->type = '';
		$periodType->description = '';
		$periodTypes[] = $periodType;
		
		$periodTypes['statutory']->type = '1';
		$periodTypes['statutory']->description = 'Statutory';
		$periodTypes['normal']->type = '0';
		$periodTypes['normal']->description = 'Non-statutory';
		
		$attribs = ( $multiple ? 'multiple="multiple" class="multi_medium"' : '' );
		$attribs .= ' style="width: 110px;"';
		$name = ( $multiple ? $name.'[]' : $name );
		
		$retVal =  JHTML::_( 'select.genericList', $periodTypes, $name, $attribs, 'type', 'description', $oldVal );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a subject select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */
	function subjects( $name, $default = null, $multiple = false )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		$subject = new stdClass();
		$subject->id = '';
		$subject->fullname = '';
		$subjects[''] = $subject;
		
		$db = &JFactory::getDBO();
		$query = 'SELECT DISTINCT c.id, c.type, c.fullname'
			."\n".'FROM #__apoth_cm_courses AS c'
			."\n".'WHERE '.JHTML::_( 'arc._dateCheck', 'c.start_date', 'c.end_date' )
			."\n".'  AND c.ext_type = "subject"'
			."\n".'  AND c.deleted = 0'
			."\n".'ORDER BY type, sortorder, shortname';
		$db->setQuery( $query );
		$subjects = array_merge( $subjects, $db->loadObjectList('id') );
		
		$attribs = ( $multiple ? 'multiple="multiple" class="multi_medium"' : '' );
		$name = ( $multiple ? $name.'[]' : $name );
		
		$retVal =  JHTML::_( 'select.genericList', $subjects, $name, $attribs, 'id', 'fullname', $oldVal );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a class type select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string  The HTML to display the required input
	 */
	function typeOfClass( $name, $multiple = false )
	{
		$oldVal = JRequest::getVar( $name, '' );
		$classType = new stdClass();
		$classType->type = '';
		$classType->description = '';
		$classTypes[] = classType;
		
		$db = &JFactory::getDBO();
		$query = 'SELECT *'
			."\n".' FROM `#__apoth_cm_types`'
			."\n".' ORDER BY `type` DESC';
//		$db->setQuery( $query );
//		$classTypes = array_merge($classTypes, $db->loadObjectList('type'));
		
		$classTypes['pastoral']->type = 'pastoral';
		$classTypes['pastoral']->description = 'Registration';
		$classTypes['normal']->type = 'normal';
		$classTypes['normal']->description = 'Normal Class';
		
		$attribs = ( $multiple ? 'multiple="multiple" class="multi_medium"' : '' );
		$attribs .= ' style="width: 110px;"';
		$name = ( $multiple ? $name.'[]' : $name );
		
		return JHTML::_( 'select.genericList', $classTypes , $name, $attribs, 'type', 'description', $oldVal );
	}
	
	/**
	 * Generate HTML to display an attendance code list select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */
	function codeList( $name, $default = null,  $multiple = false, $restrict = true )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		$code = new stdClass();
		$code->id = '';
		$code->code = '';
		$codes[''] = $code;
		
		require_once( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'data_access.php' );
		$codes += ApotheosisAttendanceData::getCodeObjects( array(), $restrict );
		
		$attribs = ( $multiple ? 'multiple="multiple" class="multi_small"' : '' );
		$name = ( $multiple ? $name.'[]' : $name );
		
		$retVal =  JHTML::_( 'select.genericList', $codes, $name, $attribs, 'code', 'code', $oldVal );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display attendance marks
	 * 
	 * @param object|array|null $marks  The mark as an object, array of objects or null
	 * @param boolean $noImg  Return text mark only, no image
	 * @return string  The HTML to display the attendance mark
	 */
	function marks( $marks, $noImg = false )
	{
		// pull in the attendance data access helper so we can use getNoCode()
		require_once( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'helpers'.DS.'data_access.php' );
		
		// call in the mooTools/Arc tooltip behaviour
		static $firstTime = true;
		if( $firstTime ) {
			JHTML::_( 'Arc.tip' );
			$firstTime = false;
		}
		
		// convert to array if not already
		if( !is_array($marks) ) {
			$marks = array( $marks );
		}
		
		// process array of objects
		foreach( $marks as $code ) {
			// replace any null entries with 'no mark' code
			if( is_null($code) ) {
				$code = ApotheosisAttendanceData::getNoCode();
			}
			
			$tmp = ( (is_null($code->image_link) || empty($code->image_link)) || $noImg
				? $code->code
				: '<img src="'.$code->image_link.'" alt="'.$code->code.'" style="vertical-align: bottom;" />' );
			$html[] = '<span class="arcTip" title="'.$code->sc_meaning.'">'.$tmp.'</span>';
		}
		$retVal = implode( ', ', $html );
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a known truant select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */
	function truant( $name, $default = null, $multiple = false )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		$truant = new stdClass();
		$truant->id = '';
		$truant->displayname = '';
		$dummyTruants[''] = $truant;
		
		$db = &JFactory::getDBO();
		$query = 'SELECT DISTINCT '.$db->nameQuote('p').'.'.$db->nameQuote('id').', '
				.$db->nameQuote('p').'.'.$db->nameQuote('title').', 
				COALESCE( '.$db->nameQuote('p').'.'.$db->nameQuote('preferred_firstname').', '.$db->nameQuote('p').'.'.$db->nameQuote('firstname').' ) AS '.$db->nameQuote('firstname').', '
				.$db->nameQuote('p').'.'.$db->nameQuote('middlenames').', 
				COALESCE( '.$db->nameQuote('p').'.'.$db->nameQuote('preferred_surname').', '.$db->nameQuote('p').'.'.$db->nameQuote('surname').' ) AS '.$db->nameQuote('surname')
			."\n".'FROM '.$db->nameQuote('#__apoth_ppl_people').' AS '.$db->nameQuote('p')
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_att_truants').' AS '.$db->nameQuote('t')
			."\n".'ON '.$db->nameQuote('t').'.'.$db->nameQuote('pupil_id').' = '.$db->nameQuote('p').'.'.$db->nameQuote('id')
			."\n".'WHERE '.JHTML::_( 'arc._dateCheck', 't.valid_from', 't.valid_to' )
			."\n".'ORDER BY '.$db->nameQuote('p').'.'.$db->nameQuote('surname').', '.$db->nameQuote('p').'.'.$db->nameQuote('firstname');
		$db->setQuery( $query );
		$truants = $db->loadObjectList('id');
		
		foreach( $truants as $key=>$row ) {
			$truants[$key]->displayname = ApotheosisLib::nameCase( 'pupil', $row->title, $row->firstname, $row->middlenames, $row->surname );
		}
		$truants = array_merge( $dummyTruants, $truants );
		
		$attribs = ( $multiple ? 'multiple="multiple" class="multi_medium"' : '' );
		$name = ( $multiple ? $name.'[]' : $name );
		
		$retVal =  JHTML::_( 'select.genericList', $truants, $name, $attribs , 'id', 'displayname', $oldVal );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a known truant select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */
	function percent( $name, $default = null )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		
		$retVal = '<input type="text" id="'.$name.'" name="'.$name.'" value="'.$oldVal.'" size="3" />';
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal; 
	}
	
	/**
	 * Generate HTML to display a radio select box of attendance comparators
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @return string $retVal  The HTML to display the required input
	 */
	function percentCom( $name, $default = null )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		
		$comTypes[] = array( 'value'=>'less_than', 'text'=>'Less than:', 'checked'=>((($oldVal == 'less_than') || ($oldVal == '')) ? true : false) );
		$comTypes[] = array( 'value'=>'exactly',   'text'=>'Exactly:',   'checked'=>(($oldVal == 'exactly') ? true : false) );
		$comTypes[] = array( 'value'=>'more_than', 'text'=>'More than:', 'checked'=>(($oldVal == 'more_than') ? true : false) );
		
		$retVal = '';
		foreach( $comTypes as $comArray ) {
			$retVal .= '<input type="radio" class="'.$name.'" name="'.$name.'" value="'.$comArray['value'].'" '.(($comArray['checked']) ? 'checked="checked"' : '').' />'.$comArray['text'].'<br />'."\n";
		}
		
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a select box of toggle choices
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */
	function toggle( $name, $default = null, $multiple = false )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		
		$toggle = new stdClass();
		$toggle->id = '';
		$toggle->displayname = '';
		$toggles[''] = $toggle;
		
		$toggle = new stdClass();
		$toggle->id = 'year';
		$toggle->displayname = 'Year';
		$toggles['year'] = $toggle;
		
		$toggle = new stdClass();
		$toggle->id = 'group';
		$toggle->displayname = 'Group';
		$toggles['group'] = $toggle;
		
		$toggle = new stdClass();
		$toggle->id = 'pupil';
		$toggle->displayname = 'Pupil';
		$toggles['pupil'] = $toggle;
		
		$toggle = new stdClass();
		$toggle->id = 'day';
		$toggle->displayname = 'Day';
		$toggles['day'] = $toggle;
		
		$attribs = ( $multiple ? 'multiple="multiple" class="multi_small"' : '' );
		$name = ( $multiple ? $name.'[]' : $name );
		
		$retVal =  JHTML::_( 'select.genericList', $toggles, $name, $attribs , 'id', 'displayname', $oldVal );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class = "search_default"' );
		
		return $retVal;
	}
}
?>