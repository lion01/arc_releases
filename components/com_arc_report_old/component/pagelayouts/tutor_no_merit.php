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
class ApothReportTutor_No_Merit extends ApothReport
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
			$this->_style->blurb_2 = 'This is where to describe the coursework and what the pupil is supposed to achieve';
			
			// **** These need to get the mark from the unwritten function (based on mark_style)
			$this->_data->stat_1 = '3';
			$this->_data->stat_3 = '0';
			$this->_data->stat_4 = '0';
			$this->_data->stat_5 = '0';

			$this->_data->text_1 = 'Space for the tutor to make comments on the pupil and their respective achievements and / or targets';
			$this->_data->text_2 = 'This is where a pupil\'s targets are identified';
		}
		
		// field list / initialisation
		$this->_fields['subject']     = new ApothFieldHidden ( $this, 'subject',     '',  	      0,   0,   0,   0, 0, 0, 0, 0,  '0%',  '1.5em', '',               $this->_data2->subject, '');
		$this->_fields['subjectname'] = new ApothFieldFixed  ( $this, 'subjectname', '',  	     36,  60, 198,  65, 0, 0, 0, 0,'100%',  '1.5em', 'Subject Name: ', $this->_data2->subject_name, '');
		$this->_fields['pupilname']   = new ApothFieldFixed  ( $this, 'pupilname',   '',  	     40,  75, 130,  80, 0, 0, 0, 0, '50%',  '1.5em', 'Pupil: ',        $this->_data2->displayname, '');
		$this->_fields['tutorgroup']  = new ApothFieldFixed  ( $this, 'tutorgroup',  '',  	    140,  75, 198,  80, 0, 0, 0, 0, '50%',  '1.5em', 'Tutor Group: ',  $this->_data2->tutorgroup, '');
		$this->_fields['description'] = new ApothFieldText   ( $this, 'description', 'blurb_1', 36,  84, 198, 149, 1, 1, 0.25, 0.25, '100%',    '8em', 'Course Description:',     $this->_style->blurb_1, '');
		$this->_fields['coursework']  = new ApothFieldText   ( $this, 'coursework',  'blurb_2', 36,  84, 198, 149, 1, 1, 0.25, 0.25, '100%',    '8em', 'Coursework Description:', $this->_style->blurb_2, '');
//		$this->_fields['credits']     = new ApothFieldWord   ( $this, 'credits',     'stat_1',  36, 149,  76, 161, 1, 1, 0.25, 0.25, '17%',  '1.5em', 'Credits:',    $this->_data->stat_1, '');
		$this->_fields['attendance']  = new ApothFieldLookup ( $this, 'attendance',  '',        76, 149, 117, 161, 1, 1, 0.25, 0.25, '17%',  '1.5em', 'Attendance:', $student, NULL );
		$this->_fields['lates']       = new ApothFieldLookup ( $this, 'lates',       '',       117, 149, 157, 161, 1, 1, 0.25, 0.25, '17%',  '1.5em', 'Lates:',      $student, NULL );
		$this->_fields['detentions']  = new ApothFieldFixed  ( $this, 'detentions',  '',       157, 149, 198, 161, 1, 1, 0.25, 0.25, '15%',  '1.5em', 'Detentions:', '', '');
		$this->_fields['detentionsY'] = new ApothFieldWord   ( $this, 'detentionsY', 'stat_4', 157, 155, 177, 166, 1, 1, 0.25, 0.25, '17%',  '1.5em', 'Year',        $this->_data->stat_4, '');
		$this->_fields['detentionsS'] = new ApothFieldWord   ( $this, 'detentionsS', 'stat_5', 177, 155, 198, 166, 1, 1, 0.25, 0.25, '17%',  '1.5em', 'School',      $this->_data->stat_5, '');
		$this->_fields['comment']     = new ApothFieldText   ( $this, 'comment',     'text_1',  36, 161, 198, 213, 1, 1, 0.25, 0.25, '100%', '12.5em', 'Tutor\'s Comments:',$this->_data->text_1, 'Please write your main comments here');
		$this->_fields['targets']     = new ApothFieldText   ( $this, 'targets',     'text_2',  36, 213, 198, 239, 1, 1, 0.25, 0.25, '100%',    '5em', 'Academic / Attitude. Key issues identified for improvement:', $this->_data->text_2, 'Please write the pupil\'s targets here');
		$this->_fields['author']      = new ApothFieldUser   ( $this, 'author',      'author',  40, 244, 130, 250, 0, 0, 0, 0, '50%',  '1.5em', 'Teacher: ',   $this->_data->author, $this->_data2->teacher, $this->_cycle);
		$this->_fields['date']        = new ApothFieldFixed  ( $this, 'date',        '',       140, 244, 198, 250, 0, 0, 0, 0, '50%',  '1.5em', '', date('F Y'), '');
		$this->_fields['attitude']    = new ApothFieldLookup ( $this, 'attitude',    '',         0,   0,   0,   0, 0, 0, 0, 0,  '0%',  '1.5em', '',            $student, NULL );
		
		$this->_fields['description']->htmlEnabled  = false;
		$this->_fields['description']->htmlSmallEnabled = false;
		$this->_fields['coursework']->htmlEnabled   = false;
		$this->_fields['coursework']->htmlSmallEnabled  = false;
		$this->_fields['coursework']->ownBox        = false;
		$this->_fields['subjectname']->titleClearance = 0;
		$this->_fields['subjectname']->valueAsTitle = true;
		$this->_fields['subjectname']->showTitle    = false;
		$this->_fields['subjectname']->dataAlign    = 'C';
		$this->_fields['subjectname']->hasBorder    = false;
		$this->_fields['pupilname']->hasBorder      = false;
		$this->_fields['tutorgroup']->hasBorder     = false;
		$this->_fields['author']->hasBorder         = false;
		$this->_fields['date']->hasBorder           = false;
		$this->_fields['attendance']->htmlEnabled   = false;
		
		$this->_fields['pupilname']->titleClearance   = 0;
		$this->_fields['tutorgroup']->titleClearance  = 0;
		$this->_fields['author']->titleClearance      = 0;
		$this->_fields['detentionsY']->titleClearance = 6;
		$this->_fields['detentionsS']->titleClearance = 6;
	
//		$this->_fields['credits']->dataAlign     = 'C';
		$this->_fields['attendance']->dataAlign  = 'C';
		$this->_fields['lates']->dataAlign       = 'C';
		$this->_fields['detentions']->dataAlign  = 'C';
		$this->_fields['detentionsY']->dataAlign = 'C';
		$this->_fields['detentionsS']->dataAlign = 'C';
	
		$this->_fields['attendance']->suffix = '%';

		$banked = array('comment', 'targets');
//		$lists = array('credits', 'attendance', 'lates', 'detentionsY', 'detentionsS');
		$lists = array('attendance', 'lates', 'detentionsY', 'detentionsS');
		foreach($banked as $b) {
			$this->_fields[$b]->setStatementBank( $this->_data->cycle, $group );
		}

		$this->_fields['date']->valueAsTitle = true;
		
//		$this->_fields['credits' ]->setRequired( true );
		$this->_fields['attendance' ]->setRequired( true );
		$this->_fields['lates'   ]->setRequired( true );
		$this->_fields['detentionsY']->setRequired( true );
		$this->_fields['detentionsS']->setRequired( true );
		$this->_fields['comment' ]->setRequired( true );
		$this->_fields['targets' ]->setRequired( true );
		
		$this->_layout[0][] = &$this->_fields['subject'];
		
		$this->_layout[0][] = &$this->_fields['subjectname'];
		$this->_layout[1][] = &$this->_fields['pupilname'];
		$this->_layout[1][] = &$this->_fields['tutorgroup'];
		$this->_layout[2][] = &$this->_fields['description'];
		$this->_layout[3][] = &$this->_fields['coursework'];
//		$this->_layout[4][] = &$this->_fields['credits'];
		$this->_layout[4][] = &$this->_fields['attendance'];
		$this->_layout[4][] = &$this->_fields['lates'];
		$this->_layout[4][] = &$this->_fields['detentions'];
		$this->_layout[4][] = &$this->_fields['detentionsY'];
		$this->_layout[4][] = &$this->_fields['detentionsS'];
		$this->_layout[5][] = &$this->_fields['comment'];
		$this->_layout[6][] = &$this->_fields['targets'];
		$this->_layout[7][] = &$this->_fields['author'];
		$this->_layout[7][] = &$this->_fields['date'];
		
	}
}

?>