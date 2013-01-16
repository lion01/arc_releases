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


/**
 * Apoth Field abstract class
 * Defines the standard report layout
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ApothReportHoy extends ApothReport
{
	/**
	 * Create a new standard report object
	 * 
	 * @param string $student  The person_id of the student whose report this is
	 * @param mixed $group  The group_id of the course for this report
	 * @return object  A fresh standard report object
	 */
	function init( $student, $group )
	{
		$this->resetRow();
		
		if(($student == false) && ($group == false)) {
			$this->_style->blurb_1 = 'A section to describe the course and what it aims to achieve';
			
			$this->_data->text_1 = 'Comments to be made about the pupil and their achievements';
 			$this->_data->text_4 = $this->_data2->teacher;
		}
		else {
			$db = &JFactory::getDBO();
			$anc = ApotheosisLibDb::getAncestors($group, '#__apoth_cm_courses', 'id', 'parent');
			if( !is_array($anc) ) { $anc = array(); }
			$anc = array_keys($anc);
			$head = NULL;
			$admins = array();
			$teachers = array();
			$curGrp = reset($anc);
			while( ($curGrp !== false) ) {
				$query = 'SELECT p1.id AS admin, p2.id AS teacher'
					."\n".' FROM #__apoth_tt_group_members AS gm'
					."\n".' LEFT JOIN #__apoth_ppl_people AS p1'
					."\n".'    ON p1.id = gm.person_id'
					."\n".'   AND gm.is_admin = 1' // *** titikaka
					."\n".'   AND '.ApotheosisLibDb::dateCheckSql('gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s'))
					."\n".' LEFT JOIN #__apoth_ppl_people AS p2'
					."\n".'    ON p2.id = gm.person_id'
					."\n".'   AND gm.is_teacher = 1' // *** titikaka
					."\n".'   AND '.ApotheosisLibDb::dateCheckSql('gm.valid_from', 'gm.valid_to', date('Y-m-d H:i:s'), date('Y-m-d H:i:s'))
					."\n".' WHERE gm.group_id = '.$db->Quote($curGrp);
				$db->setQuery($query);
				$r = $db->loadAssocList();
				foreach( $r as $row ) {
					if( !is_null($row['admin']) ) { $admins[] = $row['admin']; }
					elseif( !is_null($row['teacher']) ) { $teachers[] = $row['teacher']; }
				}
				
				$curGrp = next($anc);
			}
			$user = &ApotheosisLib::getUser();
			if( (array_search($user->person_id, $admins) !== false)
			 || (array_search($user->person_id, $teachers) !== false) ) {
				$head = $user->person_id;
			}
			elseif( !empty($admins) ) {
				$head = reset( $admins );
			}
			elseif( !empty($teachers) ) {
				$head = reset( $teachers );
			}
		}
		
		// field list / initialisation
		$this->_fields['subject']     = new ApothFieldHidden ( $this, 'subject',     '',         0,   0,   0,   0, 0, 0, 0, 0,   '0%',  '1.5em', '',                   $this->_data2->subject, '');
		$this->_fields['subjectname'] = new ApothFieldFixed  ( $this, 'subjectname', '',        36,  60, 198,  65, 0, 0, 0, 0, '100%',  '1.5em', 'Subject Name: ',     $this->_data2->subject_name, '');
		$this->_fields['pupilname']   = new ApothFieldFixed  ( $this, 'pupilname',   '',        40,  75, 130,  80, 0, 0, 0, 0,  '50%',  '1.5em', 'Pupil: ',            $this->_data2->displayname, '');
		$this->_fields['tutorgroup']  = new ApothFieldFixed  ( $this, 'tutorgroup',  '',       140,  75, 198,  80, 0, 0, 0, 0,  '50%',  '1.5em', 'Tutor Group: ',      $this->_data2->tutorgroup, '');
		$this->_fields['description'] = new ApothFieldText   ( $this, 'description', 'blurb_1', 36,  84, 198, 176, 1, 1, 0.5, 0.5, '100%',   '15em', 'Year Group Report:', $this->_style->blurb_1, '');
		$this->_fields['attitude']    = new ApothFieldLookup ( $this, 'attitude',    '',         0,   0,   0,   0, 0, 0, 0, 0,   '0%',  '1.5em', 'Avg. Attitude',      $student, NULL );
		$this->_fields['comment']     = new ApothFieldText   ( $this, 'comment',     'text_1',  36, 176, 198, 211, 1, 1, 0.5, 0.5, '100%', '12.5em', 'Director of Progress and Achievement\'s comments:', $this->_data->text_1, 'Please write your main comments here');
		$this->_fields['author1']     = new ApothFieldUser   ( $this, 'author1',     'author',  40, 220, 140, 230, 0, 0, 0, 0,  '40%',  '1.5em', 'Director of Progress and Achievement: ',            $this->_data->author, $head, $this->_cycle);
//		$this->_fields['author2']     = new ApothFieldUser   ( $this, 'author2',     'text_4',  40, 230, 140, 240, 0, 0, 0, 0,  '40%',  '1.5em', 'Assistant Director of Progress and Achievement: ',  $this->_data->text_4, '');
		$this->_fields['date']        = new ApothFieldFixed  ( $this, 'date',        '',       140, 244, 198, 250, 0, 0, 0, 0,  '20%',  '1.5em', '', date('F Y'), '');
		
		
		$this->_fields['description']->htmlEnabled    = false;
		$this->_fields['description']->htmlSmallEnabled = false;
		$this->_fields['subjectname']->titleClearance = 0;
		$this->_fields['subjectname']->valueAsTitle   = true;
		$this->_fields['subjectname']->showTitle      = false;
		$this->_fields['subjectname']->dataAlign      = 'C';
		$this->_fields['subjectname']->hasBorder      = false;
		$this->_fields['pupilname']->hasBorder        = false;
		$this->_fields['tutorgroup']->hasBorder       = false;
		$this->_fields['author1']->hasBorder          = false;
//		$this->_fields['author2']->hasBorder          = false;
		$this->_fields['date']->hasBorder             = false;
		$this->_fields['attitude']->htmlSmallEnabled  = true;
		
		$this->_fields['pupilname']->titleClearance   = 0;
		$this->_fields['tutorgroup']->titleClearance  = 0;
		
		$banked = array('comment');
		$lists = array();
		foreach($banked as $b) {
			$this->_fields[$b]->setStatementBank( $this->_data->cycle, $group );
		}
		
		$this->_fields['date']->valueAsTitle = true;
		
		$this->_fields['comment']->setRequired( true );
		
		$this->_layout[0][] = &$this->_fields['subject'];
		
		$this->_layout[0][] = &$this->_fields['subjectname'];
		$this->_layout[1][] = &$this->_fields['pupilname'];
		$this->_layout[1][] = &$this->_fields['tutorgroup'];
		$this->_layout[2][] = &$this->_fields['description'];
		$this->_layout[3][] = &$this->_fields['comment'];
		$this->_layout[4][] = &$this->_fields['author1'];
//		$this->_layout[4][] = &$this->_fields['author2'];
		$this->_layout[4][] = &$this->_fields['date'];
		
	}
}

?>