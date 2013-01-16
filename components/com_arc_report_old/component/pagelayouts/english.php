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
class ApothReportEnglish extends ApothReport
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
			$this->_data->stat_3 = 'A';
			$this->_data->stat_4 = 'A';
			$this->_data->stat_5 = 'A';
			$this->_data->stat_6 = 'A';
			$this->_data->flag_1 = 1; 
			$this->_data->text_1 = 'This text is to identify the pupil\'s current level of performace';
			$this->_data->text_2 = 'This is where a pupil\'s targets are identified';
 			$this->_data->text_3 = 'You can specify how a pupil can attain the targets set above';
		}
		
		// field list / initialisation
		$this->_fields['subject']     = new ApothFieldHidden ( $this, 'subject',     '',         0,   0,   0,   0, 0, 0, 0, 0,   '0%', '1.5em', '',               $this->_data2->subject, '');
		$this->_fields['subjectname'] = new ApothFieldFixed  ( $this, 'subjectname', '',        36,  60, 198,  65, 0, 0, 0, 0, '100%', '1.5em', 'Subject Name: ', $this->_data2->subject_name, '');
		$this->_fields['pupilname']   = new ApothFieldFixed  ( $this, 'pupilname',   '',        40,  75, 130,  80, 0, 0, 0, 0,  '50%', '1.5em', 'Pupil: ',        $this->_data2->displayname, '');
		$this->_fields['tutorgroup']  = new ApothFieldFixed  ( $this, 'tutorgroup',  '',       140,  75, 198,  80, 0, 0, 0, 0,  '50%', '1.5em', 'Tutor Group: ',  $this->_data2->tutorgroup, '');
		$this->_fields['description'] = new ApothFieldText   ( $this, 'description', 'blurb_1', 36,  84, 198, 126, 1, 1, 0.5, 0.5, '100%',   '8em', 'Course Description:',     $this->_style->blurb_1, '');
		$this->_fields['coursework']  = new ApothFieldText   ( $this, 'coursework',  'blurb_2', 36,  84, 198, 126, 1, 1, 0.5, 0.5, '100%',   '8em', 'Coursework Description:', $this->_style->blurb_2, '');
		$this->_fields['set']         = new ApothFieldHidden ( $this, 'set',         'group',    0,   0,   0,   0, 0.5, 0.5, 0.5, 0.5,   '0%', '1.5em', '',              $this->_data->group, '');
		$this->_fields['setname']     = new ApothFieldWord   ( $this, 'setname',     '',        36, 126,  63, 138, 0.5, 0, 0.5, 0.5,  '17%', '1.5em', 'Teaching Set:', $this->_data2->group_name, '');
		$this->_fields['attitude']    = new ApothFieldList   ( $this, 'attitude',    'stat_1',  63, 126,  90, 138, 1, 1, 0.5, 0.5,  '17%', '1.5em', 'Attitude:',     $this->_data->stat_1, '', $this->_style->mark_style);
		$this->_fields['clp']         = new ApothFieldFixed  ( $this, 'clp',         '',        90, 126, 116, 138, 1, 1, 0.5, 0.5,  '15%', '1.5em', 'CLP:',          '', '', '');
		$this->_fields['clp1']        = new ApothFieldList   ( $this, 'clp1',        'stat_2',  90, 132, 103, 143, 1, 1, 0.5, 0.5,  '17%', '1.5em', 'Lang',          $this->_data->stat_2, '', $this->_style->mark_style);
		$this->_fields['clp2']        = new ApothFieldList   ( $this, 'clp2',        'stat_5', 103, 132, 116, 143, 1, 1, 0.5, 0.5,  '17%', '1.5em', 'Lit',           $this->_data->stat_5, '', $this->_style->mark_style);
		$this->_fields['exam']        = new ApothFieldList   ( $this, 'exam',        'stat_3', 116, 126, 144, 138, 1, 1, 0.5, 0.5,  '17%', '1.5em', 'Exam Grade:',   $this->_data->stat_3, '', $this->_style->mark_style);
		$this->_fields['target']      = new ApothFieldFixed  ( $this, 'target',      '',       144, 126, 170, 138, 0.25, 0, 0.5, 0.5,  '15%', '1.5em', 'Target Grade:', '', '', '');
		$this->_fields['target1']     = new ApothFieldList   ( $this, 'target1',     'stat_4', 144, 132, 157, 143, 1, 1, 0.5, 0.5,  '17%', '1.5em', 'Lang',          $this->_data->stat_4, '', $this->_style->mark_style);
		$this->_fields['target2']     = new ApothFieldList   ( $this, 'target2',     'stat_6', 157, 132, 170, 143, 1, 1, 0.5, 0.5,  '17%', '1.5em', 'Lit',           $this->_data->stat_6, '', $this->_style->mark_style);
		$this->_fields['ontarget']    = new ApothFieldBool   ( $this, 'ontarget',    'flag_1', 170, 126, 198, 138, 1, 1, 0.5, 0.5,  '17%', '1.5em', 'On Target?',    $this->_data->flag_1, '');
		$this->_fields['current_txt'] = new ApothFieldText   ( $this, 'current_txt', 'text_1',  36, 138, 198, 180, 1, 1, 0.5, 0.5, '100%',   '9em', 'Current Performance:',     $this->_data->text_1, 'Please write your main comments here');
		$this->_fields['targets']     = new ApothFieldText   ( $this, 'targets',     'text_2',  36, 180, 198, 212, 1, 1, 0.5, 0.5, '100%', '6.5em', 'Targets for Improvement:', $this->_data->text_2, 'Please write the pupil\'s targets here');
		$this->_fields['method']      = new ApothFieldText   ( $this, 'method',      'text_3',  36, 212, 198, 239, 1, 1, 0.5, 0.5, '100%',   '5em', 'Achieve Targets By:',      $this->_data->text_3, 'How will the pupil achieve these targets?');
		$this->_fields['author']      = new ApothFieldUser   ( $this, 'author',      'author',  40, 244, 130, 250, 0, 0, 0, 0,  '50%', '1.5em', 'Teacher: ',     $this->_data->author, $this->_data2->teacher, $this->_cycle);
		$this->_fields['date']        = new ApothFieldFixed  ( $this, 'date',        '',       140, 244, 198, 250, 0, 0, 0, 0,  '50%', '1.5em', '', date('F Y'), '');

		$filler = new ApothFieldFixed ( $this, 'filler', '', 10, 10, 10, 10, 0, 0, 0, 0,  '17%', 'opx', '', '', '', '');
		
		$this->_fields['description']->htmlEnabled  = false;
		$this->_fields['description']->htmlSmallEnabled = false;
		$this->_fields['coursework']->htmlEnabled   = false;
		$this->_fields['coursework']->htmlSmallEnabled  = false;
		$this->_fields['setname']->htmlEnabled      = false;
		$this->_fields['setname']->htmlSmallEnabled = false;
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
		
		$this->_fields['pupilname']->titleClearance   = 0;
		$this->_fields['tutorgroup']->titleClearance  = 0;
		$this->_fields['author']->titleClearance      = 0;
		$this->_fields['clp1']->titleClearance        = 6;
		$this->_fields['clp2']->titleClearance        = 6;
		$this->_fields['target1']->titleClearance     = 6;
		$this->_fields['target2']->titleClearance     = 6;
		
		$this->_fields['setname']->dataAlign  = 'C';
		$this->_fields['attitude']->dataAlign = 'C';
		$this->_fields['clp']->dataAlign      = 'C';
		$this->_fields['clp1']->dataAlign     = 'C';
		$this->_fields['clp2']->dataAlign     = 'C';
		$this->_fields['exam']->dataAlign     = 'C';
		$this->_fields['target']->dataAlign   = 'C';
		$this->_fields['target1']->dataAlign  = 'C';
		$this->_fields['target2']->dataAlign  = 'C';
		$this->_fields['ontarget']->dataAlign = 'C';
		
		$banked = array('current_txt', 'targets', 'method');
		$lists = array('attitude', 'clp1', 'clp2', 'exam', 'target1', 'target2');
		foreach($banked as $b) {
			$this->_fields[$b]->setStatementBank( $this->_data->cycle, $group );
		}
		
		$this->_fields['date']->valueAsTitle = true;
		
		$this->_fields['attitude']->setRequired( true );
		$this->_fields['clp1'    ]->setRequired( true );
		$this->_fields['clp2'    ]->setRequired( true );
		$this->_fields['target1' ]->setRequired( true );
		$this->_fields['target2' ]->setRequired( true );
		$this->_fields['current_txt']->setRequired( true );
		$this->_fields['targets' ]->setRequired( true );
		$this->_fields['method'  ]->setRequired( true );
		
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
		$this->_layout[4][] = &$this->_fields['clp1'];
		$this->_layout[4][] = &$this->_fields['clp2'];
		$this->_layout[4][] = $filler;
		$this->_layout[5][] = $filler;
		$this->_layout[5][] = &$this->_fields['exam'];
		$this->_layout[5][] = &$this->_fields['target'];
		$this->_layout[5][] = &$this->_fields['target1'];
		$this->_layout[5][] = &$this->_fields['target2'];
		$this->_layout[5][] = &$this->_fields['ontarget'];
		$this->_layout[6][] = &$this->_fields['current_txt'];
		$this->_layout[7][] = &$this->_fields['targets'];
		$this->_layout[8][] = &$this->_fields['method'];
		$this->_layout[9][] = &$this->_fields['author'];
		$this->_layout[9][] = &$this->_fields['date'];
		
	}
}

?>