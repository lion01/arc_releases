<?php
/**
 * @package     Arc
 * @subpackage  Timetable
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

/**
 * Timetable Reports Model
 */
class TimetableModelReports extends JModel
{
	function getToday( $requirements )
	{
		$db = &JFactory::getDBO();
		$date = date('Y-m-d');
		$day = ApotheosisLibCycles::dateToCycleDay( $date );
		$pattern = ApotheosisLibCycles::getPatternByDate( $date );
		$patternId = $pattern->id;
		
		$query = 'SELECT DISTINCT ppl.juserid AS j_user_id, t.room_id, dd.day_section, c.id, c.fullname, c.ext_course_id_2'
			."\n".'FROM #__apoth_tt_timetable AS t'
			."\n".'INNER JOIN #__apoth_cm_courses AS c'
			."\n".'   ON c.id = t.course'
			."\n".'  AND c.deleted = 0'
			."\n".'INNER JOIN #__apoth_tt_group_members AS gm'
			."\n".'   ON gm.group_id = t.course'
			."\n".'INNER JOIN #__apoth_tt_patterns AS p'
			."\n".'   ON t.pattern = p.id'
			."\n".'INNER JOIN #__apoth_tt_daydetails AS dd'
			."\n".'   ON t.pattern = dd.pattern'
								// t.day must be + 1 because MySQL starts indexing from 1, but we count from 0 in php land so the value in t.day counts from 0
			."\n".'  AND SUBSTRING(p.format, t.day + 1, 1) = dd.day_type'
			."\n".'  AND t.day_section = dd.day_section'
			."\n".'INNER JOIN #__apoth_ppl_people AS ppl'
			."\n".'   ON ppl.id = gm.person_id'
			."\n".'WHERE gm.person_id = '.$db->Quote( $requirements['person'] )
			."\n".'  AND t.day = '.$db->Quote( $day )
			."\n".'  AND t.pattern = '.$db->Quote( $patternId )
			."\n".'  AND '.ApotheosisLibDb::dateCheckSQL( 't.valid_from', 't.valid_to', $date, $date )
			."\n".'  AND '.ApotheosisLibDb::dateCheckSQL( 'gm.valid_from', 'gm.valid_to', $date, $date )
			."\n".'ORDER BY dd.start_time ASC';
		$db->setQuery($query);
		$r = $db->loadAssocList();
		if( !is_array($r) ) { $r = array(); }
		
		return $r;
	}
}
?>