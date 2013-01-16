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

/**
 * Utility class for generating arc specific HTML entities
 *
 * @static
 * @package    Arc
 * @subpackage HTML
 * @since      1.5
 */
class JHTMLArc
{
	function breadcrumbs( $trail )
	{
		$fCrumbs = ApothFactory::_( 'core.breadcrumb' );
		$ids = $fCrumbs->getInstances( array( 'trail'=>$trail ) );
		$count = count( $ids );
		$i = 0;
		
		$rv = '<div id="breadcrumbs_wrapper"><div id="breadcrumbs">';
		foreach( $ids as $id ) {
			$crumb = $fCrumbs->getInstance( $id );
			$style = ( ($s = $crumb->getData( 'style' )) == '' ? '' : ' style="'.$s.'"' );
			$link = ApotheosisLibAcl::getUserLinkAllowed( $crumb->getData( 'action' ), $crumb->getData( 'dependancies' ) );
			$first = ($i == 0);
			$last = (++$i == $count);
			
			$rv .= '<div class="breadcrumb'
				.($first ? ' first': '' )
				.($last  ? ' last' : '' )
				.'"><span'.$style.'>';
			if( !$last && $link ) {
				$rv .= '<a href="'.$link.'">'.$crumb->getLabel().'</a>';
			}
			else {
				$rv .= $crumb->getLabel();
			}
			$rv .= '</span></div>';
			
			if( !$last ) {
				$rv .= '<div class="breadcrumb_divider"></div>';
			}
		}
		$rv .= '</div></div>';
		return $rv;
	}
	
	/**
	 * Includes the relevant framework and file components for Arc tips
	 */
	function tip( $class = NULL )
	{
		$customClass = 'custom';
		if( is_null($class) ) {
			$tipClass = '';
		}
		else {
			$tipClass = '_'.$class;
			$customClass = $class.' '.$customClass;
		}
		
		JHTML::_( 'behavior.mootools' ); 
		JHTML::_( 'behavior.tooltip', '.arcTip'.$tipClass, array('className'=>$customClass) );
	}
	
	/**
	 * Returns a shortened version of the passed list with mouseover text of full list
	 * 
	 * @param $list array of objects  The array of objects from which to derive the list
	 * @param $property string  The object property to include in the list
	 * @param $title string  Tooltip title text
	 * @param $items int  The maximum number of propertes to display in the short text
	 * @param $chars int  The maximum number of characters to display in the short text
	 */
	function shortlist( $list, $property, $title, $items = false, $chars = false )
	{
		static $firstTime = true;
		
		if( $firstTime ) {
			JHTML::_( 'Arc.tip' );
			$firstTime = false;
		}
		//init arrays
		if( !($items || $chars) ) { $items = 5;}
		$stillAdding = true;
		$lengthSoFar = 0;
		$count = 0;
		foreach( $list as $k=>$obj ) {
			$value = ApotheosisLibArray::deepProperty( $obj, $property );
			$fullArray[] = $value;
			
			if( $stillAdding ) {
				if( ($items !== false ? (++$count <= $items ? true : false) : true) &&
					($chars !== false ? ($lengthSoFar + strlen($value) <= $chars ? true : false) : true) ) {
					$lengthSoFar = $lengthSoFar + strlen($value) + 2;
					$shortTextArray[] = $value;
				}
				else {
					$shortTextArray[] = '...';
					$stillAdding = false;
				}
			}
		}
		$fullText = htmlspecialchars( implode(', ', $fullArray) );
		$shortText = htmlspecialchars( implode(', ', $shortTextArray) );
		return '<span class="arcTip" title="'.$title.' :: '.$fullText.'">'.$shortText.'</span>';
	}
	
	/**
	 * Returns a combo-box with optional auto-complete to select values from the lists provided
	 * 
	 * @param string $name  The name to use for the input
	 * @param array $params  Array of attributes for the input
	 * @param string $data  Array of objects to populate the input
	 * @param string $valProp The name of the object variable for the option value
	 * @param string $textProp  The name of the object variable for the option text
	 * @param mixed $oldVal  The key that is selected (accepts an array or a string)
	 * @param boolean $add  shall we extend the js to allow addition of new values
	 * @return string  The HTML for the combo select list
	 */
	function combo( $name, $params, $data, $valProp, $textProp, $oldVal = null, $add = null )
	{
		JHTML::stylesheet( 'combo.css', JURI::root().'components'.DS.'com_arc_core'.DS.'helpers'.DS.'html'.DS );
		JHTML::script( 'combo.js', JURI::root().'components'.DS.'com_arc_core'.DS.'helpers'.DS.'html'.DS, true );
		$class = 'arc_combo';
		if( !is_null($add) ) {
			JHTML::script( 'combo_add.js', JURI::root().'components'.DS.'com_arc_core'.DS.'helpers'.DS.'html'.DS, true );
			$class = 'arc_combo_add';
		}
		
		$name = (isset($params['multiple']) ? $name.'[]' : $name);
		$paramList = array();
		foreach( $params as $k=>$v ) {
			$paramList[] = htmlspecialchars($k).'="'.htmlspecialchars($v).'"';
		}
		$paramList = implode( ' ', $paramList );
		
		return JHTML::_( 'select.genericList', $data, $name, 'class="'.$class.'" '.$paramList, $valProp, $textProp, $oldVal );
	}
	
	function dot( $color, $mouseover = NULL, $opaque = false )
	{
		$opaque = ( $opaque ? '_opaque' : '' );
		$path = 'components'.DS.'com_arc_core'.DS.'helpers'.DS.'images'.DS.'dot_'.$color.$opaque.'.png';
		if( !file_exists(JPATH_ROOT.DS.$path) ) {
			return '';
		}
		if( $mouseover === false ) {
			$mouseoverTxt = '';
		}
		else {
			$mouseoverTxt = ' title="'.( is_null($mouseover) ? $color.' dot' : htmlspecialchars($mouseover) ).'"';
		}
		return '<img src="'.JURI::root().$path.'"'.$mouseoverTxt.' height="20" width="20" />';
	}
	
	function dotMini( $color, $mouseover = NULL )
	{
		if( $mouseover === false ) {
			$mouseoverTxt = '';
		}
		else {
			$mouseoverTxt = ' title="'.( is_null($mouseover) ? $color.' dot' : htmlspecialchars($mouseover) ).'"';
		}
		return '<img src="'.JURI::root().'components'.DS.'com_arc_core'.DS.'helpers'.DS.'images'.DS.'dot_'.$color.'_mini.png"'.$mouseoverTxt.' />';
	}
	
	/**
	 * Generate a loading image with associated text
	 * 
	 * @param string $text  The text to display next to the loading image
	 */
	function loading( $text = 'Loading...' )
	{
		return '<img src="'.ApotheosisLib::arcLoadImgUrl().'" /> '.$text;
	}
	
	/**
	 * Returns an image tag for the image of the requested name
	 * 
	 * @param string $name  The image name
	 * @param string $attribs  An optional string of extra attributes
	 * @param boolean $pathOnly  Do we just want the path to the image? Defaults to false
	 * @return string $retVal  The html or the path
	 */
	function image( $name, $attribs = '', $pathOnly = false )
	{
		$testPath = 'components'.DS.'com_arc_core'.DS.'helpers'.DS.'images'.DS.$name.'.png';
		$path = JURI::root().$testPath;
		if( !file_exists(JPATH_ROOT.DS.$testPath) ) {
			return '';
		}
		elseif( $pathOnly ) {
			$retVal = $path;
		}
		else {
			switch( $name ) {
			case ('add-16' ):
				$retVal = '<img src="'.$path.'" width="16" height="16" '.$attribs.' />';
				break;
			
			case ('remove-16' ):
				$retVal = '<img src="'.$path.'" width="16" height="16" '.$attribs.' />';
				break;
			
			case ('padlock_16' ):
				$retVal = '<img src="'.$path.'" width="19" height="32" '.$attribs.' />';
				break;
			}
		}
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a year group select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @return string $retVal  The HTML to display the required input
	 */	
	
	function yearGroup( $name, $default = null, $multiple = false )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		$year = new stdClass();
		$year->year = '';
		$years[''] = $year;
		
		$db = &JFactory::getDBO();
		$query = 'SELECT DISTINCT year'
			."\n".'FROM #__apoth_cm_courses AS c'
			."\n".'WHERE `year` IS NOT NULL'
			."\n".'  AND `deleted` = 0'
			."\n".'ORDER BY year';
		$db->setQuery( $query );
		$years = array_merge( $years, $db->loadObjectList('year') );
		
		$attribs = ($multiple ? 'multiple="multiple" class="multi_small"' : '');
		$name = ($multiple ? $name.'[]' : $name);
		
		$retVal =  JHTML::_('select.genericList', $years, $name, $attribs , 'year', 'year', $oldVal);
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class = "search_default"' );
		
		return $retVal;
	}	
	
	/**
	 * Generate HTML to display a date select box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @return string $retVal  The HTML to display the required input
	 */
	function date( $name, $default = null )
	{
		$default = ( !is_null($default) ? $default : date('Y-m-d') );
		$oldVal = JRequest::getVar( $name, $default );
		
		$retVal =  JHTML::_( 'calendar', $oldVal, $name, $name );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a color picker box with the given name
	 *
	 * @param string $name  The name and ID to use for the input
	 * @param string $imgID  The ID of the rainbow image
	 * @param string $default  Default value for field (#RGB or colour name)
	 * @return string  The HTML to display the required input and color picker
	 */
	function color( $name, $imgID, $default = '' )
	{
		$oldVal = JRequest::getVar( $name, $default );
		
		return '
			<input type="text" name="'.$name.'" id="'.$name.'" value="'.JRequest::getVar( $name , $default ).'" /> 
			<img
				src="'.JURI::base().'components'.DS.'com_arc_core'.DS.'images'.DS.'rainbow.png'.'"
				id="'.$imgID.'"
				alt="[r]"
				class="mooRainbowImg"
			/>
			<script type="text/javascript">
				window.addEvent(\'domready\', function() {
					$(\''.$name.'\').setStyle( \'background-color\', $(\''.$name.'\').value);
					$(\''.$name.'\').onchange = function() {
						$(\''.$name.'\').setStyle( \'background-color\', $(\''.$name.'\').value);
					}
					apothMooRainbow($(\''.$name.'\').value , \''.$imgID.'\', \''.$name.'\');
				});
			</script>
		';
	}
	
	/**
	 * Generate HTML to display the hammer/spanner admin image
	 *
	 * @param string $link  Link to follow when image clicked
	 * @param string $mouseover  Text to display as mouseover text (title field)
	 * @param string $type  Optional link template type
	 * @param string $properties  Optional properties for the a tag
	 * @return string $html  The HTML to display the hammer/spanner admin image with mouseover text
	 */
	function adminLink( $link, $mouseover = '', $type = 'plain', $properties = '' )
	{
		switch( $type ) {
		case "plain":
			$tmpProps = '';
			break;
		case "modal_refresh":
			$tmpProps = 'class="modal" rel="{handler: \'iframe\', size: {x: 640, y: 480}, onClose: function(){window.parent.location.reload();}}"';
			break;
		case "modal":
			$tmpProps = 'class="modal" rel="{handler: \'iframe\', size: {x: 640, y: 480}}"';
			break;
		}
		$properties = $tmpProps.' '.$properties;
		
		$html = '<a href="'.$link.'" '.(empty($properties) ? '' : $properties).'>
			<img
			 src="'.JURI::base().'components'.DS.'com_arc_core'.DS.'helpers'.DS.'images'.DS.'spanner.png"
			 title="'.(empty($mouseover) ? 'Administrate' : htmlspecialchars($mouseover)).'"
			 alt="Administrate"
			/>
			</a>';
		
		return $html;
	}
	
	/**
	 * Creates an sql string for use in the WHERE clause of a query to limit the results to those that
	 * fall within the currently set date range.
	 * 
	 * @param string $from  The field name of the valid_from field
	 * @param string $to  The field name of the valid_to field
	 * @return string  The bracket-encapsulated string to add to the WHERE clause
	 */
	function _dateCheck($fromField, $toField)
	{
//		$dateFrom = ( isset($this->_dateFrom) ? $this->_dateFrom : date('Y-m-d H:i:s') );
//		$dateTo   = ( isset($this->_dateTo)   ? $this->_dateTo   : date('Y-m-d H:i:s') );
		$dateFrom = date('Y-m-d H:i:s');
		$dateTo   = date('Y-m-d H:i:s');
		return ApotheosisLibDb::dateCheckSql($fromField, $toField, $dateFrom, $dateTo);
	}	
	
	/**
	 * Generate HTML to display a pupil select box with the given name, and excluding the given list of person ids
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value
	 * @param boolean $multiple  Allow multiple selects?
	 * @param array $exceptionList  List of person ids to exclude from the select box
	 * @return string  The HTML to display the required input
	 */
	function _renderOtherPupils($name, $default = null, $multiple = false, $exceptionList = array())
	{
		$u = ApotheosisLib::getUser();
		$default = ( !is_null($default) ? $default : $u->person_id );
		$oldVal = JRequest::getVar( $name, $default );
		
		$db = &JFactory::getDBO();
		$query = 'SELECT DISTINCT ppl.id, ppl.title, COALESCE( ppl.preferred_firstname, ppl.firstname ) AS firstname, ppl.middlenames, COALESCE( ppl.preferred_surname, ppl.surname ) AS surname'
			."\n".' FROM #__apoth_ppl_people AS ppl'
			."\n".' ~LIMITINGJOIN~'
			."\n".' INNER JOIN #__apoth_tt_group_members AS gm ON gm.person_id = ppl.id'
			."\n".' WHERE gm.is_student = 1' // *** titikaka
			."\n".' AND '.JHTML::_( 'arc._dateCheck', 'gm.valid_from', 'gm.valid_to' )
			.(empty($exceptionList) ? '' : "\n".' AND ppl.id NOT IN ("'.implode('", "', $exceptionList).'")')
			."\n".' ORDER BY surname, firstname ';
		$db->setQuery( ApotheosisLibAcl::limitQuery($query, 'people.people', 'ppl') );
		$pupils = $db->loadObjectList( 'id' );
		foreach( $pupils as $key=>$row ) {
			$pupils[$key]->displayname = ApotheosisLib::nameCase('pupil', $row->title, $row->firstname, $row->middlenames, $row->surname);
		}
		
		if( empty($pupils) ) {
			$pupil = new stdClass();
			$pupil->id = 'Denied Access';
			$pupil->displayname = '--Denied Access--';
			$pupils[''] = $pupil;
		}
		else {
			$pupil = new stdClass();
			$pupil->id = '';
			$pupil->displayname = '';
			$pupils = array(''=>$pupil) + $pupils;
		}
		
		if ($multiple) {
			return JHTML::_('select.genericList', $pupils, $name.'[]', 'multiple="multiple" class="multi_medium"', 'id', 'displayname', $oldVal);
		}
		else {
			return JHTML::_('select.genericList', $pupils, $name, '', 'id', 'displayname', $oldVal);
		}
	}
	
	function searchStart()
	{
		JHTML::_('behavior.mootools');
		JHTML::script( 'search.js', JURI::base().'components'.DS.'com_arc_core'.DS.'helpers'.DS.'html'.DS );
		?>
		<span id="toggle" name="toggle" style="font-weight: bold; font-size: 14px; cursor: pointer;">Search <span id="arrow"><img src="components/com_arc_core/images/sort0.png" /></span></span>
		<?php
	}
			
	/**
	 * Displays hidden inputs if any are listed in apoth_passthrough from JRequest
	 */
	function hidden( $name, $value = '', $attributes = '' )
	{
		$value = JRequest::getVar( $name, $value );
		return '<input type="hidden" name="'.$name.'" value="'.$value.'" '.$attributes.' />'."\n";
	}
	
	/**
	 * Generate HTML to display any required buttons (always including a "submit" button with "Search" as the text)
	 */
	function submit( $value = 'Search' )
	{
		return '<input type="submit" class="btn" name="task" value="'.$value.'" />';
	}	
	
	/**
	 * Generate HTML to display  search form reset button
	 */
	function reset( $value = 'Reset' )
	{
		return '<input type="button" class="btn" id="search_reset" value="'.$value.'" />';
	}	
	
	/**
	 * Generate HTML to display a checkbox for identifying current
	 *
	 * @param string $name The name to use for the input
	 * @return string The HTML to display the required field
	 */
	function _renderCurrent($name)
	{
		$oldVal = JRequest::getVar($name, '');
		return '<input type="checkbox" name="'.$name.'" value="1" disabled="disabled" '.($oldVal ? 'checked="checked"' : '').'/>';
	}
	
	/**
	 * Generate HTML to display a text box with the given name
	 *
	 * @param string $name  The name to use for the input
	 * @return string  The HTML to display the required input
	 */
	function _renderGeneric($name)
	{
		$oldVal = JRequest::getVar($name, '');
		return '<input type="text" name="'.$name.'" value="'.$oldVal.'" style="width: 8em" />';
	}
	
	/**
	 * Generate HTML to display a person select box with the given name, and excluding the given list of person ids
	 *
	 * @param string $name  The name to use for the input
	 * @param boolean $multiple  Allow multiple selects?
	 * @param array $exceptionList  List of person ids to exclude from the select box
	 * @return string  The HTML to display the required input
	 */
	function _renderOtherPeople($name, $multiple = false, $exceptionList = array())
	{
		$u = ApotheosisLib::getUser();
		$oldVal = JRequest::getVar($name, $u->person_id);
		$person = new stdClass();
		$person->id = '';
		$person->firstname = '';
		$person->surname = '';
		$people[''] = $person;
		$db = &JFactory::getDBO();
		$query = 'SELECT ppl.id, ppl.title, COALESCE( ppl.preferred_firstname, ppl.firstname ) AS firstname, ppl.middlenames, COALESCE( ppl.preferred_surname, ppl.surname ) AS surname'
			."\n".' FROM #__apoth_ppl_people AS ppl'
			.(empty($exceptionList) ? '' : "\n".' WHERE `ppl`.`id` NOT IN ("'.implode('", "', $exceptionList).'")')
			."\n".' ORDER BY surname, firstname ';
		$db->setQuery( $query );
		$people = array_merge($people, $db->loadObjectList('id'));
		foreach( $people as $key=>$row ) {
			$people[$key]->displayname = ApotheosisLib::nameCase('person', $row->title, $row->firstname, $row->middlenames, $row->surname);
		}
		
		if ($multiple) {
			return JHTML::_('select.genericList', $people, $name.'[]', 'multiple="multiple" class="multi_large"', 'id', 'displayname');
		}
		else {
			return JHTML::_('select.genericList', $people, $name, '', 'id', 'displayname');
		}
	}

	
	/**
	 * Convert the given number of seconds to hours, minutes and seconds
	 *
	 * @param int $seconds  The number of seconds to convert
	 * @return string  The HTML to show 'Xh Ym Zs' 
	 */
	function secsToTime( $seconds )
	{
		// hours
		$hours = floor( $seconds / (60 * 60) );
		$timeStr['h'] = ( $hours > 0 ) ? $hours.'h' : '';
		
		// minutes
		$spareMinutes = $seconds % ( 60 * 60 );
		$minutes = floor( $spareMinutes / 60 );
		$timeStr['m'] = ( $minutes > 0 ) ? $minutes.'m' : '';
		
		// seconds
		$spareSeconds = $spareMinutes % 60;
		$seconds = ceil( $spareSeconds );
		$timeStr['s'] = ( $seconds > 0 ) ? $seconds.'s' : '';
		
		return implode( ' ', $timeStr );
	}
}
?>
