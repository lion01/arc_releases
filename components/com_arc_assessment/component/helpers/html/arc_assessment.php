<?php
/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Utility class for generating arc assessment specific HTML entities
 *
 * @static
 * @package    Arc
 * @subpackage Assessment
 * @since      1.5
 */
class JHTMLArc_Assessment
{
	// #####  Inputs (for search form, etc)  #####
	
	/**
	 * Generate HTML to display an assessments select box
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */
	function assessments( $name, $default = null, $multiple = false )
	{
		$user = &ApotheosisLib::getUser();
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		$a = new stdClass();
		$a->id = '';
		$a->title = '';
		$ass[''] = $a;
		
		// get the users classes
		$db = JFactory::getDBO();
		$query = 'SELECT '.$db->nameQuote('c').'.'.$db->nameQuote('id')
			."\n".'FROM '.$db->nameQuote('#__apoth_tt_group_members').' AS '.$db->nameQuote('gm')
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_cm_courses').' AS '.$db->nameQuote('c')
			."\n".'   ON '.$db->nameQuote('gm').'.'.$db->nameQuote('group_id').' = '.$db->nameQuote('c').'.'.$db->nameQuote('id')
			."\n".'  AND '.$db->nameQuote('c').'.'.$db->nameQuote('deleted').' = '.$db->Quote('0')
			."\n".'INNER JOIN '.$db->nameQuote('#__apoth_ppl_people').' AS '.$db->nameQuote('p')
			."\n".'   ON '.$db->nameQuote('gm').'.'.$db->nameQuote('person_id').' = '.$db->nameQuote('p').'.'.$db->nameQuote('id')
			."\n".'WHERE '.$db->nameQuote('p').'.'.$db->nameQuote('juserid').' = '.$db->Quote($user->id)
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql('gm.valid_from', 'gm.valid_to', date('Y-m-d'), date('Y-m-d'));
		$db->setQuery( $query );
		$uClassArr = $db->loadObjectList( 'id' );
		
		$groups = array();
		foreach( $uClassArr as $k=>$v ) {
			$tmp = ApotheosisLibDb::getAncestors( $k, '#__apoth_cm_courses' );
			foreach( $tmp as $gId=>$g ) {
				if( $gId != '' ) {
					$groups[$gId] = $gId;
				}
			}
		}
		
		$query = 'SELECT aa.`id`, aa.`title` FROM #__apoth_ass_assessments AS aa'."\n".
				(!empty( $groups )
					?	 "\n".' INNER JOIN #__apoth_ass_course_map AS acm ON aa.id = acm.assessment'
						."\n".' WHERE acm.group IN ('.implode( ', ', $groups ).')'
					:	' WHERE 1=1'
				)
				."\n".'  AND aa.deleted != 1'
				."\n".'ORDER BY aa.valid_from, aa.valid_to';
		$db->setQuery( $query );
		$assessments = array_merge( $ass, $db->loadObjectList('id') );

		if( $multiple ) {
			$retVal = JHTML::_( 'select.genericList', $assessments, $name.'[]', 'multiple="multiple" class="multi_large"', 'id', 'title', $oldVal );
		}
		else {
			$retVal = JHTML::_( 'select.genericList', $assessments, $name, '', 'id', 'title', $oldVal );
		}
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class = "search_default"' );
		
		return $retVal;
	}

	/**
	 * Generate HTML to display an aspects select box
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */
	
	function aspects($name, $default = null, $multiple = false)
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		
		$db = JFactory::getDBO();
		$query = 'SELECT id, type, description FROM #__apoth_ass_aspect_types ORDER BY '.$db->nameQuote('description');
		$db->setQuery( $query );
		$results = $db->loadObjectList( 'id' );
		$aspects = $db->loadObjectList( 'description' );

		foreach( $aspects as $k=>$v ) {
			if( $v->description == '' ) {
				$aspects[$k]->description = ucfirst( $v->type );
			}
		}

		$aspect = new stdClass();
		$aspect->description = '';
		$aspect->id = '';
		array_unshift( $aspects, $aspect );
		
		if( $multiple ) {
			$retVal = JHTML::_( 'select.genericList', $aspects, $name.'[]', 'multiple="multiple" class="multi_small"', 'id', 'description', $oldVal );
		}
		else {
			$retVal = JHTML::_( 'select.genericList', $aspects, $name, '', 'id', 'description', $oldVal );
		}
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class = "search_default"' );
		
		return $retVal;
	}	
	
	/**
	 * Generate HTML to display a class select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string  The HTML to display the required input
	 */
	function tutor( $name, $multiple = false )
	{
		$oldVal = JRequest::getVar($name, '');
		$class = new stdClass();
		$class->room = '';
		$classes[''] = $class;
		$db = &JFactory::getDBO();
		$query = 'SELECT DISTINCT c.id, c.type, c.shortname'
			."\n".'FROM #__apoth_cm_courses AS c'
			."\n".'INNER JOIN #__apoth_tt_timetable AS t'
			."\n".'   ON t.course = c.id'
			."\n".'WHERE '.JHTML::_( 'arc._dateCheck', 'c.start_date', 'c.end_date' )
			."\n".'  AND c.type = \'pastoral\''
			."\n".'  AND c.deleted = 0'
			."\n".'ORDER BY type, sortorder, shortname';
		$db->setQuery( $query );
		$classes = array_merge($classes, $db->loadObjectList('id'));
		
		$attribs = ($multiple ? 'multiple="multiple" class="multi_medium"' : '');
		if($type == 'pastoral') { $attribs .= ' style="width:70px;"'; }
		$name = ($multiple ? $name.'[]' : $name);
		return JHTML::_('select.genericList', $classes, $name, $attribs , 'id', 'shortname', $oldVal);
	}
	
	/**
	 * Generate HTML to display a radio list for identifying complete status
	 *
	 * @param string $name The name to use for the input
	 * @param string $default  Default input value
	 * @return string $retVal  The HTML to display the required field
	 */
	function complete( $name, $default = null )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		
		$listArr = array();
		$yes->name  = 'Yes';
		$yes->value = 1;
		$no->name   = 'No';
		$no->value  = -1;
		$na->name   = 'N/A';
		$na->value  = 0;
		
		$listArr[] = $yes;
		$listArr[] = $no;
		$listArr[] = $na;
		
		$attribs = 'class="'.$name.'"';
		
		$retVal =  JHTML::_('select.radioList', $listArr, $name, $attribs, 'value', 'name', $oldVal);
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}

	/**
	 * Generate HTML to display a select list for aspect mark styles
	 *
	 * @param string $name The name to use for the input
	 * @return string The HTML to display the required field
	 */
	function markstyle( $name )
	{
		$oldVal = JRequest::getVar( $name , '');
		$db = JFactory::getDBO();
		$query = 'SELECT '.$db->nameQuote( 'style' ).', '.$db->nameQuote( 'label' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_sys_markstyles_info' );
		$db->setQuery( $query );
		$markStyles = $db->loadObjectList();
		
		return JHTML::_( 'select.genericList', $markStyles, $name, '', 'style', 'label', $oldVal );
	}
	
	/**
	 * Generate HTML to display a select list for aspect display styles
	 *
	 * @param string $name The name to use for the input
	 * @return string The HTML to display the required field
	 */
	function displaystyle( $name )
	{
		$oldVal = JRequest::getVar( $name , '');
		$db = JFactory::getDBO();
		$query = 'SELECT '.$db->nameQuote( 'style' ).', '.$db->nameQuote( 'label' )
			."\n".'FROM '.$db->nameQuote( '#__apoth_sys_markstyles_info' )
			."\n".'WHERE '.$db->nameQuote( 'type' ).' = '.$db->Quote( 'mark' )
			."\n".'   OR '.$db->nameQuote( 'type' ).' = '.$db->Quote( 'numeric' );
		$db->setQuery( $query );
		$markStyles = $db->loadObjectList();
		
		// add --As Marked-- as a display option
		array_unshift( $markStyles, (object)array('style'=>'', 'label'=>'-- As Marked --') );
		
		return JHTML::_( 'select.genericList', $markStyles, $name, '', 'style', 'label', $oldVal );
	}
	
	
	// #####  Output of marks in various formats  #####
	
	function markHtml( $aspId, $mark, $usage, $inputId = null, $inputName = null )
	{
		static $cache = array();
		if( !isset($cache[$aspId]) ) {
			$fAsp = &ApothFactory::_( 'assessment.aspect' );
			$cache[$aspId][0] = &$fAsp->getInstance( $aspId );
			$cache[$aspId][1] = &$cache[$aspId][0]->getBoundaries();
		}
		$asp = &$cache[$aspId][0];
		$boundaries = &$cache[$aspId][1];
		
		switch( $usage ) {
		// Generate an input for the mark
		case( 'edit' ):
			$style = $asp->getMarkStyle();
			$b = &$boundaries['mark_values'];
			switch( $style['type'] ) {
			case( 'mark' ):
				if( !isset($cache[$aspId][2]) ) {
					$opt = new stdClass();
					$opt->mark = '';
					$opt->description = '';
					$cache[$aspId][2][] = $opt;
					// list the marks in the order they were originally set in the aspect
					foreach( $boundaries['mark_values_orig'] as $optDisp=>$optVal ) {
						$opt = new stdClass();
						$opt->mark = $optVal;
						$opt->description = $optDisp;
						$cache[$aspId][2][] = $opt;
					}
				}
				$retVal = JHTML::_( 'select.genericlist', $cache[$aspId][2], $inputName, '', 'mark', 'description', $mark, $inputId );
				break;
			
			case( 'numeric'):
				$mOut = ( ($mark === false) ? '' : $asp->convertMark( $mark, $usage ) );
				end($b);
				$high = key($b);
				
				$input = '<input id="'.$inputId.'" name="'.$inputName.'" type="text" value="'.htmlspecialchars($mOut).'" >';
				$search = array( '[[p]]', '[[s]]', '[[t]]' );
				$replace = array( $input, $input, $high );
				$retVal = str_replace( $search, $replace, $style['format'] );
				break;
			
			case( 'text' ):
				$retVal = '<textarea id="'.$inputId.'" name="'.$inputName.'">'.htmlspecialchars($mark).'</textarea>';
				break;
			
			default:
				$retVal = $mark;
				break;
			}
			break;
		// End of input generation
		
		// Generate plain html display of the mark
		case( 'mark' ):
			$style = $asp->getMarkStyle();
			$b = &$boundaries['mark_values'];
		case( 'display' ):
		case( 'raw' ):
			if( $usage == 'raw' ) {
				$usage = 'display';
			}
			if( !isset($style) ) {
				$style = $asp->getDisplayStyle();
				$b = &$boundaries['mark_values'];
			}
			
			switch( $style['type'] ) {
			case( 'numeric' ):
				$mOut = ( ($mark === false) ? '--' : $asp->convertMark( $mark, $usage ) );
				end($b);
				$high = key($b);
				
				$search = array( '[[p]]', '[[s]]', '[[t]]' );
				$replace = array( $mOut, $mOut, '' );
				$retVal = str_replace( $search, $replace, $style['format'] );
				break;
			
			case( 'mark' ):
			case( 'text' ):
			default:
				$retVal = ( ($mark === false) ? '--' : $asp->convertMark( $mark, $usage ) );
				break;
			}
			
			break;
		// End plain html generation
		}
		
		return $retVal;
	}
	
	
	/**
	 * Generates the html to display a mark
	 * Pass through false for the first 3 arguments to get a "no mark" array
	 * @param boolean $e  Is this for editing?
	 */
	function mark( $aspId, $pId, $gId, $usage = 'display', $inputId = null )
	{
//		var_dump_pre( func_get_args(), 'args for mark' );
		static $cache = array();
		
		if( ($aspId == false)
		 && ($pId == false)
		 && ($gId == false) ) {
			$retVal = array( 'html'=>'&nbsp;', 'raw'=>'', 'mark'=>null, 'color'=>'grey', 'hasMark'=>false );
		}
		else {
			// static caching to speed things up a little
			if( !isset($cache[$aspId]) ) {
				$fAsp = &ApothFactory::_( 'assessment.aspect' );
				$cache[$aspId][0] = &$fAsp->getInstance( $aspId );
			}
			$asp = &$cache[$aspId][0];
			$m = $asp->getMark( $pId, $gId );
			$style = $asp->getDisplayStyle();
			
			if( is_null($m) ) {
				$retVal = array( 'html'=>'&nbsp;', 'htmlLong'=>'&nbsp;', 'raw'=>'', 'mark'=>null, 'color'=>'grey', 'group'=>$gId, 'hasMark'=>false );
			}
			else {
				$inputName = ( $usage == 'edit' ? 'marks['.$aspId.']['.$pId.']['.$gId.']' : '' );
				
				$retVal = $m;
				$retVal['hasMark'] = true;
				$retVal['html'] = $retVal['raw'] = JHTML::_( 'arc_assessment.markHtml', $aspId, $m['mark'], $usage, $inputId, $inputName );
				$mData = ApotheosisData::_( 'assessment.markInfo', $m['mark'], $style['style'] );
				$retVal['htmlLong'] = $mData['description'];
			}
		}
		return $retVal;
	}
	
	/**
	 * Use like mark (above) but supply an array of aspect ids.
	 * The first id in this list which gets a mark will determine the mark returned
	 * 
	 * @param unknown_type $aspIds
	 * @param unknown_type $pId
	 * @param unknown_type $gId
	 * @param unknown_type $usage
	 * @param unknown_type $inputId
	 */
	function markCoalesce( $aspIds, $pId, $gId, $usage = 'display', $inputId = null)
	{
		if( !is_array($aspIds) ) {
			$aspIds = array( $aspIds );
		}
		$m = null;
		foreach( $aspIds as $aspId ) {
			$tmp = JHTML::_( 'arc_assessment.mark', $aspId, $pId, $gId, $usage, $inputId );
			if( $tmp['hasMark'] ) {
				// store this potentially empty-but-present mark as the best so far
				$m = $tmp;
				if( $tmp['mark'] !== false ) {
					// if it's a non-empty mark stop looking
					break;
				}
			}
		}
		if( is_null($m) ) {
			$m = JHTML::_( 'arc_assessment.mark', false, false, false, $usage, $inputId );
		}
		return $m;
	}
	
	/**
	 * Find the average mark for a pupil or group
	 * 
	 * @param unknown_type $aspIds
	 * @param unknown_type $id
	 * @param unknown_type $idType
	 * @param unknown_type $usage
	 */
	function markAverage( $aspIds, $id, $idType, $usage = 'display' )
	{
		$fAsp = &ApothFactory::_( 'assessment.aspect' );
		$total = 0;
		$count = 0;
		
		if( !is_array($aspIds) ) {
			$aspIds = array( $aspIds );
		}
		
		if( $idType == 'pupil' ) {
			foreach( $aspIds as $aspId ) {
				$asp = &$fAsp->getInstance( $aspId );
				$marks = $asp->getMarks();
				if( isset($marks[$id]) && is_array($marks[$id]) ) {
					foreach( $marks[$id] as $gId=>$mark ) {
						if( ($mark !== false) && ($mark['mark'] !== false) ) {
							$total += $mark['mark'];
							$count++;
						}
					}
				}
			}
		}
		else {
			foreach( $aspIds as $aspId ) {
				$asp = &$fAsp->getInstance( $aspId );
				$marks = $asp->getMarks();
				if( is_array($marks) ) {
					foreach( $marks as $pId=>$groups ) {
						if( isset($groups[$gId]) && ($groups[$gId] !== false) ) {
							$total += $mark['mark'];
							$count++;
						}
					}
				}
			}
		}
		
		$m = JHTML::_( 'arc_assessment.mark', false, false, false, $usage );
		if( $count !== 0 ) {
			$m['mark'] = $total / $count;
			$m['hasMark'] = true;
			$m['html'] = JHTML::_( 'arc_assessment.markHtml', reset($aspIds), $m['mark'], $usage );
			$m['raw']  = JHTML::_( 'arc_assessment.markHtml', reset($aspIds), $m['mark'], 'raw' );
		}
		
		return $m;
		
	}
}
?>