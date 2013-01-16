<?php
/**
 * @package     Arc
 * @subpackage  Homepage
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

/*
 * Homepage Panels Model
 * 
 * @author     d.swain@wildern.hants.sch.uk
 * @package    Arc
 * @subpackage Homepage
 * @since      1.5
 */
class HomepageModelPanels extends JModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	function getPeopleList()
	{
		if( empty($this->_peopleList) ) {
			$u = ApotheosisLib::getUser();
			$pId = $u->person_id;
			$this->setPeopleList( $pId );
		}
		return $this->_peopleList;
	}
	
	function setPeopleList( $pId )
	{
		$action = ApotheosisLib::getActionIdByName( 'apoth_homepage_home_someone' );
		$db = &JFactory::getDBO();
		$query = 'SELECT p.id, p.firstname, p.middlenames, p.surname'
			."\n".'FROM #__apoth_ppl_people AS p'
			."\n".'~LIMITINGJOIN~';
		$query = ApotheosisLibAcl::limitQuery( $query, 'people.people', 'p', 'id', $pId, $action );
		$db->setQuery( $query );
		$r = $db->loadAssocList();
		$this->_peopleList = array();
		foreach( $r as $row ) {
			$this->_peopleList[$row['id']] = ApotheosisLib::nameCase( 'person', '', $row['firstname'], $row['middlenames'], $row['surname'] );
		}
	}
	
	/**
	 * Generates the html for a calendar month with links
	 */
	function getHtmlCalendar( $date, $baseLink, $article = false )
	{
		ob_start(); // buffer output until we are done
		
//		$articleLink = ( empty($article) ? '' : '&article='.$article );
		$link = $this->link;
		
		$p = explode('-', $date);
		$year  = $p[0];
		$month = $p[1];
		$day   = $p[2];
		
		// Prepare variables to generate the calendar dates
		$firstDay = date( 'w', mktime(0, 0, 1, $month, 1, $year) );
		$time = mktime( 0, 0, 1, $month, $day, $year );
		$daysInMonth = date( 't', $time );
		$curDate = 0;
		$curDay = 0;
		$today = $this->_getDateStr( '' );
		$lastMonth = ( ($month == 1)  ? $this->_getDateStr( $year-1, 12, $day) : $this->_getDateStr( $year, $month-1, $day ) );
		$nextMonth = ( ($month == 12) ? $this->_getDateStr( $year+1,  1, $day) : $this->_getDateStr( $year, $month+1, $day ) );
		
		// Read in events to go on the calendar
//		$events = loadEvents( $year );
//		$news   = loadNews(   $year );
//		if( is_null($events) ) { $events = array(); }
//		if( is_null($news)   ) { $news   = array(); }
		$events = array();
		$news   = array();
		?>
		<div id="homepage_cal">
		<table>
		<tr>
<!--			<th><a id="calPrevMonth" href="<?php echo $link.'&format=raw&date='.$lastMonth.$articleLink; ?>">&lt;</a></th>-->
			<th class="homepage_cal_date" colspan="7"><?php echo ucfirst(date('F Y', $time)); ?></th>
<!--			<th><a id="calNextMonth" href="<?php echo $link.'&format=raw&date='.$nextMonth.$articleLink; ?>">&gt;</a></th>-->
		</tr>
		<tr>
			<th>S</th>
			<th>M</th>
			<th>T</th>
			<th>W</th>
			<th>T</th>
			<th>F</th>
			<th>S</th>
		</tr>
		<tr>
		<?php while( ($curDate < $daysInMonth) || ($curDay <= 6)) {
			$curDate = ( (($curDate == 0) && ($curDay != $firstDay)) ? 0 : $curDate + 1 );
			
			if( $curDay == 7 ) {
				$curDay = 0;
				$classes = array( 'first_cell' );
				echo "\n".'</tr>'."\n".'<tr>'."\n";
			}
			else {
				$classes = array();
			}
			
			if( ($curDate > 0) && ($curDate <= $daysInMonth) ) {
				$content = $curDate;
				
				$section = $this->_getDateStr( $year.'-'.$month.'-'.$curDate );
				if( array_key_exists( $section, $events ) ) {
					$classes[] = 'tooltip';
					$classes[] = $events[$section]['class'];
					$title = ' title="'.$events[$section]['title'].' :: '.$events[$section]['text'].'"';
				}
				else {
					$title = '';
				}
				
				if( $section == $today ) {
					$classes[] = 'homepage_cal_today';
				}
				
				$class = ( empty($classes) ? '' : ' class="'.implode(' ', $classes).'"' );
				
				if( array_key_exists( $section, $news ) ) {
					$content = '<a class="newslink" href="'.$link.'&date='.$section.'&article='.$section.'">'.$content.'</a>';
				}
			}
			else {
				$content = '&nbsp;';
				$class = '';
				$title = '';
			}
			
			echo "\t".'<td'.$class.$title.'>'.$content.'</td>'."\n";
			$curDay++;
		} ?>
		</tr>
		</table>
		</div>
		<?php
		return ob_get_clean();
	}
	
	/**
	 * Retrieves the date from the url if no date provided.
	 * Checks the date found / given is made up of numbers separated by '-'
	 * inserts the current value in place of any suspicious values
	 * The returned date is formatted with leading zeros if applicable on the month and day
	 * @params  Takes any of:
	 *   no params (url 'date' is used)
	 *   1 param (assumed to be date, exploded on '-')
	 *   or 3 params (assumed to be year, month, dayOfMonth)
	 */
	function _getDateStr()
	{
		$p = func_get_args();
		switch( count($p) ) {
		case( 0 ):
			$parts = explode( '-', $_GET['date'] );
			break;
		case( 1 ):
			$parts = explode( '-', $p[0] );
			break;
		case( 3 ):
			$parts = array( $p[0], $p[1], $p[2] );
			break;
		default:
			$parts = array( false, false, false );
		}
		
		$year  = ( (isset($parts[0]) && is_numeric($parts[0])) ? (int)$parts[0] : date('Y') );
		$month = ( (isset($parts[1]) && is_numeric($parts[1])) ? (int)$parts[1] : date('n') );
		$day   = ( (isset($parts[2]) && is_numeric($parts[2])) ? (int)$parts[2] : date('j') );
		
		return $year.'-'.(($month < 10) ? '0' : '').$month.'-'.(($day < 10) ? '0' : '').$day;
	}
	
}
?>