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
class ApothReportTarget_Free extends ApothReport
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
			$this->_data->stat_1 = 'A';
			$this->_data->stat_2 = 'A';
			$this->_data->stat_4 = 'A';
			$this->_data->text_1 = 'This text is to identify the pupil\'s current level of performace';
		}
		
		// field list / initialisation
		$this->_fields['subject']     = new ApothFieldHidden ( $this, 'subject',     '',         0,   0,   0,   0, 0, 0, 0, 0,   '0%', '1.5em', '',               $this->_data2->subject, '');
		$this->_fields['subjectname'] = new ApothFieldFixed  ( $this, 'subjectname', '',        36,  60, 198,  65, 0, 0, 0, 0, '100%', '1.5em', 'Subject Name: ', $this->_data2->subject_name, '');
		$this->_fields['pupilname']   = new ApothFieldFixed  ( $this, 'pupilname',   '',        40,  75, 130,  80, 0, 0, 0, 0,  '50%', '1.5em', 'Pupil: ',        $this->_data2->displayname, '');
		$this->_fields['tutorgroup']  = new ApothFieldFixed  ( $this, 'tutorgroup',  '',       140,  75, 198,  80, 0, 0, 0, 0,  '50%', '1.5em', 'Tutor Group: ',  $this->_data2->tutorgroup, '');
		$this->_fields['description'] = new ApothFieldText   ( $this, 'description', 'blurb_1', 36,  84, 198, 126, 1, 1, 0.5, 0.5, '100%',   '8em', 'Course Description:',     $this->_style->blurb_1, '');
		$this->_fields['coursework']  = new ApothFieldText   ( $this, 'coursework',  'blurb_2', 36,  84, 198, 126, 1, 1, 0.5, 0.5, '100%',   '8em', 'Coursework Description:', $this->_style->blurb_2, '');
		$this->_fields['set']         = new ApothFieldHidden ( $this, 'set',         'group',    0,   0,   0,   0, 0.5, 0.5, 0.5, 0.5,   '0%', '1.5em', '',                 $this->_data->group, '');
		$this->_fields['setname']     = new ApothFieldWord   ( $this, 'setname',     '',        36, 126,  76, 138, 0.5, 0, 0.5, 0.5,  '25%', '1.5em', 'Teaching Set:',    $this->_data2->group_name, '');
		$this->_fields['attitude']    = new ApothFieldList   ( $this, 'attitude',    'stat_1',  76, 126, 117, 138, 1, 1, 0.5, 0.5,  '25%', '1.5em', 'Attitude:',        $this->_data->stat_1, '', $this->_style->mark_style);
		$this->_fields['clp']         = new ApothFieldList   ( $this, 'clp',         'stat_2', 117, 126, 157, 138, 1, 1, 0.5, 0.5,  '25%', '1.5em', 'CLP:',             $this->_data->stat_2, '', $this->_style->mark_style);
		$this->_fields['predicted']   = new ApothFieldList   ( $this, 'predicted',   'stat_4', 157, 126, 198, 138, 1, 1, 0.5, 0.5,  '25%', '1.5em', 'Predicted Grade:', $this->_data->stat_4, '', $this->_style->mark_style);
		$this->_fields['current_txt'] = new ApothFieldText   ( $this, 'current_txt', 'text_1',  36, 138, 198, 239, 1, 1, 0.5, 0.5, '100%',  '25em', 'Current Performance:', $this->_data->text_1, 'Please write your main comments here');
		$this->_fields['author']      = new ApothFieldUser   ( $this, 'author',      'author',  40, 244, 130, 250, 0, 0, 0, 0,  '50%', '1.5em', 'Teacher: ',     $this->_data->author, $this->_data2->teacher, $this->_cycle);
		$this->_fields['date']        = new ApothFieldFixed  ( $this, 'date',        '',       140, 244, 198, 250, 0, 0, 0, 0,  '50%', '1.5em', '', date('F Y'),  '');
		
		
		$this->_fields['description']->htmlEnabled  = false;
		$this->_fields['description']->htmlSmallEnabled = false;
		$this->_fields['coursework']->htmlEnabled   = false;
		$this->_fields['coursework']->htmlSmallEnabled  = false;
		$this->_fields['setname']->htmlEnabled      = false;
		$this->_fields['setname']->htmlSmallEnabled = false;
		$this->_fields['coursework']->ownBox = false;
		$this->_fields['subjectname']->titleClearance = false;
		$this->_fields['subjectname']->valueAsTitle = true;
		$this->_fields['subjectname']->showTitle    = false;
		$this->_fields['subjectname']->dataAlign    = 'C';
		$this->_fields['subjectname']->hasBorder    = false;
		$this->_fields['pupilname']->hasBorder      = false;
		$this->_fields['tutorgroup']->hasBorder     = false;
		$this->_fields['author']->hasBorder         = false;
		$this->_fields['date']->hasBorder           = false;
		
		$this->_fields['pupilname']->titleClearance   = false;
		$this->_fields['tutorgroup']->titleClearance  = false;
		$this->_fields['author']->titleClearance      = false;
		
		$this->_fields['setname']->dataAlign   = 'C';
		$this->_fields['attitude']->dataAlign  = 'C';
		$this->_fields['clp']->dataAlign       = 'C';
		$this->_fields['predicted']->dataAlign = 'C';
		
		$banked = array('current_txt',);
		$lists = array('attitude', 'clp', 'predicted');
		foreach($banked as $b) {
			$this->_fields[$b]->setStatementBank( $this->_data->cycle, $group );
		}

		$this->_fields['date']->valueAsTitle = true;
		
		$this->_fields['attitude']->setRequired( true );
		$this->_fields['clp'     ]->setRequired( true );
		$this->_fields['predicted'  ]->setRequired( true );
		$this->_fields['current_txt']->setRequired( true );
		
		
		$this->_layout[0][] = &$this->_fields['subject'];
		$this->_layout[0][] = &$this->_fields['set'];
		
		$this->_layout[0][] = &$this->_fields['subjectname'];
		$this->_layout[1][] = &$this->_fields['pupilname'];
		$this->_layout[1][] = &$this->_fields['tutorgroup'];
		$this->_layout[2][] = &$this->_fields['description'];
		$this->_layout[3][] = &$this->_fields['coursework'];
		$this->_layout[4][] = &$this->_fields['setname'];
		$this->_layout[4][] = &$this->_fields['attitude'];
		$this->_layout[4][] = &$this->_fields['clp'];
		$this->_layout[4][] = &$this->_fields['predicted'];
		$this->_layout[5][] = &$this->_fields['current_txt'];
		$this->_layout[6][] = &$this->_fields['author'];
		$this->_layout[6][] = &$this->_fields['date'];
	}
}

?>