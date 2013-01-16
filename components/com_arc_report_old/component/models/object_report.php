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
 * Apoth Report abstract class
 * Defines the interface for all actual report classes (whose type determines their page layout etc)
 * The field names "description" and "coursework" are special as they are used to display / set
 * the subject and coursework descriptions on the admin screens. If they are included as fields, they
 * will relate to blurb_1 and blurb_2 respectively.
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ApothReport extends JObject
{
	var $_fields;
	var $_curRow;
	
	var $_data;
	var $_style;
	var $_blurbCols	= array( 'blurb_1', 'blurb_2' );
	
	/** var must be defined by all subclasses, and they must provide data for get... calls that rely on this var */
	var $_data2;
	
	/** The cycle object for the report */
	var $_cycle;
	
	// publicly tweakable variables (mainly for pdf generation)
	var $breakAfter = true;
	
	/**
	 * Static function used to retrieve an appropriate instance of an ApothReport subclass
	 *
	 * @param string $rptId  The id of the report required
	 * @return object  The ApothReport subclass object for that student's report in that group
	 */
	function &getInstance( $rptId, $dataOnly = false )
	{
//		var_dump_pre($rptId, 'getting instance for id: ');
		$db = &JFactory::getDBO();
		$qStr = 'SELECT *'
			."\n".' FROM #__apoth_rpt_reports'
			."\n".' WHERE id = "'.$db->getEscaped( $rptId ).'"'
			."\n".' LIMIT 1';
		$db->setQuery( $qStr );
		$data = $db->loadObject();
		if( is_null($data) ) {
			$r = false;
			return $r;
		}
		$style = ApothReport::loadStyle( $data->group, $data->cycle );
		
		$fName = JPATH_SITE.DS.'components'.DS.'com_arc_report'.DS.'pagelayouts'.DS.$style->page_style.'.php';
		$className = 'ApothReport'.ucfirst($style->page_style);
		if (file_exists($fName)) {
			require_once($fName);
			$r = ( class_exists( $className ) ? new $className() : new ApothReport() );
		}
		else {
			$r = new ApothReport();
		}
		
		$r->_file = $fName;
		$r->_data = $data;
		$r->_style = $style;
		$r->_initNames( date('Y-m-d H:i:s'), $data->cycle );
		if( !$dataOnly ) {
			$r->init( $data->student, $data->group );
		}
		
		return $r;
	}
	
	/**
	 * Static function used to retrieve a fresh instance of an appropriate ApothReport subclass
	 *
	 * @param string $student  The person id of the student whose report is to be created
	 * @param string $group  The group id of the subject/course/class whose report is to be created
	 * @return object  The ApothReport subclass object for that student's report in that group
	 */
	function &newInstance( $student, $group, $cycleId, $style = false )
	{
/*
		echo 'creating instance with... <br />';
		echo 'student: ';var_dump_pre($student);
		echo 'group: ';var_dump_pre($group);
		echo 'cycleId: ';var_dump_pre($cycleId);
		echo 'style: ';var_dump_pre($style);
// */
		
		if( empty($group) ) {
			$group = ApotheosisLibDb::getRootItem('#__apoth_cm_courses');
		}
		
		// create a blank object that can be initialised per page style
		$data = new stdClass();
		$data->id = null;
		$data->cycle = $cycleId;
		$data->student = $student;
		$data->group = $group;
		$data->author = $data->last_modified = $data->last_modified_by
			= $data->checked_by_first = $data->checked_by = $data->checked_on
			= $data->status = $data->feedback
			= $data->stat_1 = $data->stat_2 = $data->stat_3 = $data->stat_4 = $data->stat_5 = $data->stat_6 = $data->stat_7 = $data->stat_8 = $data->stat_9 = $data->stat_10
			= $data->flag_1 = $data->flag_2
			= $data->text_1 = $data->text_2 = $data->text_3 = $data->text_4
			= null;
		
		if($style == false) {
			$style = ApothReport::loadStyle( $group, $cycleId );
		}
		else {
			$name = $style;
			$style = ApothReport::loadStyle( ApotheosisLibDb::getRootItem('#__apoth_cm_courses'), $cycleId );
			$style->page_style = $name;
		}
		
		$fName = JPATH_SITE.DS.'components'.DS.'com_arc_report'.DS.'pagelayouts'.DS.$style->page_style.'.php';
		$className = 'ApothReport'.ucfirst($style->page_style);
		if (file_exists($fName)) {
			require_once($fName);
		}
		$r = ( class_exists( $className ) ? new $className() : new ApothReport() );
		
		$r->_file = $fName;
		$r->_data = $data;
		$r->_style = $style;
		$r->_initNames( date('Y-m-d H:i:s'), $cycleId );
		$r->init( $student, $group );
		
		return $r;
	}
	
	/**
	 * Dummy function to prevent errors if invalid report type selected
	 */
	function init()
	{
		global $mainframe;
		$mainframe->enqueueMessage('Invalid report type selected, or no report type selected for the root course.', 'warning');
	}
	
	function outit()
	{
		unset($this->_fields); // save memory?
		unset($this->_layout); // save memory?
	}
	
	/**
	 * Sets up common data of reportee's name and tutor group
	 */
	function _initNames( $date, $cycleId )
	{
		$this->_cycle = ApothReportLib::getCycle( $cycleId );
		$from = $this->_cycle->valid_from;
		$to   = $this->_cycle->valid_to;
		if(($this->_data->student == false) && ($this->_data->group == false)) {
			$this->_data2->displayname = 'Pupil Name';
			$this->_data2->firstname = 'Pupil';
			$this->_data2->middlenames = 'Quentin';
			$this->_data2->surname = 'Name';
			$this->_data2->tutorgroup = 'Tutorgroup';
			
			$this->_data2->group_name = 'Group Name';
			$this->_data2->subject = 'Subject Name';
			$this->_data2->subject_name = 'Subject Name';
		}
		else {
			// Get the pupil details (name and tutor)
			$db = &JFactory::getDBO();
			$query = 'SELECT'
				."\n".'  t.fullname AS tutorgroup,'
				."\n".'  COALESCE( p.firstname, p.preferred_firstname ) AS firstname,'
				."\n".'  p.middlenames,'
				."\n".'  COALESCE( p.surname, p.preferred_surname ) AS surname'
				."\n".' FROM #__apoth_ppl_people AS p'
				."\n".' INNER JOIN #__apoth_tt_group_members AS gm'
				."\n".'  ON gm.person_id = p.id'
				."\n".'  AND gm.is_student = 1' // *** titikaka
				."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', $from, $to )
				."\n".' INNER JOIN #__apoth_cm_courses AS t'
				."\n".'  ON t.id = gm.group_id'
				."\n".'  AND t.type = "pastoral"'
				."\n".'  AND t.deleted = "0"'
				."\n".' WHERE p.id = "'.$db->getEscaped($this->_data->student).'"';
			$db->setQuery( $query );
			$r = $db->loadObject();
			if( is_object($r) ) {
				$this->_data2->displayname = ApotheosisLib::nameCase('pupil_text', '', $r->firstname, $r->middlenames, $r->surname);
				$this->_data2->firstname = $r->firstname;
				$this->_data2->middlenames = $r->middlenames;
				$this->_data2->surname = $r->surname;
				$this->_data2->tutorgroup = $r->tutorgroup;
			}
			else {
				$this->_data2->displayname = null;
				$this->_data2->firstname = null;
				$this->_data2->middlenames = null;
				$this->_data2->surname = null;
				$this->_data2->tutorgroup = null;
			}
			
			// Get the teacher's name
			$query = 'SELECT'
				."\n".' person_id'
				."\n".' FROM #__apoth_tt_group_members AS gm'
				."\n".' WHERE gm.group_id = '.$db->Quote( $this->_data->group )
				."\n".'   AND gm.is_teacher = 1' // *** titikaka
				."\n".'   AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', $from, $to );
			$db->setQuery($query);
			$tmp = $db->loadAssocList('person_id');
			if( !is_array($tmp) ) { $tmp = array(); }
			
			$u = ApotheosisLib::getUser();
			$tmp = ( isset($tmp[$u->person_id]) ? $tmp[$u->person_id] : reset($tmp) );
			$this->_data2->teacher = $tmp['person_id'];
			
			// get the class name and course name using the ancestors of the given group
			$heritage = ApotheosisLibDb::getAncestors($this->_data->group, '#__apoth_cm_courses', 'id', 'parent', true);
			unset($heritage[ApotheosisLibDb::getRootItem()]);
			
			$tmp = reset($heritage);
			$this->_data2->subject_name  = ( is_null($this->_style->print_name) ? (is_object($tmp) ? $tmp->fullname : 'Subject Name') : $this->_style->print_name );
			$this->_data2->subject       = ( is_object($tmp) ? $tmp->id : 0 );
			$this->_data2->subject_order = ( is_object($tmp) ? $tmp->sortorder : 0 );
			
			$tmp = end($heritage);
			$this->_data2->group_name = ( is_object($tmp) ? $tmp->fullname : 'Group Name' );
			$this->_data2->group      = ( is_object($tmp) ? $tmp->id : 0 );
		}
	}
	
	/**
	 * Does the initialising of a report's style
	 *
	 * @param string $group  The group id of the subject/course/class whose report is required
	 */
	function loadStyle( $group, $cycleId )
	{
		$db = JFactory::getDBO();
		
//		static $style = false;
//		if( ($style === false) || ($style->group != $group) ) {
			$style = new stdClass();
			$style->group = $group;
			$style->valid_to = NULL;
//		}
		
		$cycle = ApothReportLib::getCycle( $cycleId );
		$ancestors = ApotheosisLibDb::getAncestors( $group, '#__apoth_cm_courses' );
		$allStyles = array();
		$allFieldStyles = array();
		$visited = array(); // list of visited groups. use to avoid reference loops
		while ( !is_null($group = array_pop($ancestors)) ) {
			// load the style for the ancestors of the current branch
			// (in the loop so twinning is followed, in its own conditional to prevent repeated queries to the db)
			// $allStyles is emptied where we get new ancestors on twin
			if( empty($allStyles) ) {
			$gList = array();
				foreach( $ancestors as $gTmp ) {
					$gList[] = $db->Quote($gTmp->id);
				}
				$gList[] = $db->Quote($group->id);
				$query = 'SELECT *'
					."\n".' FROM #__apoth_rpt_style AS s'
					."\n".' INNER JOIN #__apoth_rpt_cycles AS c'
					."\n".'    ON (c.valid_from < s.valid_to OR s.valid_to IS NULL)'
					."\n".'   AND (c.valid_to > s.valid_from OR c.valid_to IS NULL)'
					."\n".' WHERE '.$db->nameQuote('group').' IN ( '.implode(', ', $gList).')'
					."\n".'   AND '.$db->nameQuote('cycle').' = '.$db->Quote($cycleId);
				$db->setQuery( $query );
				$allStyles = $db->loadAssocList('group');
			}
			// check we've not been here before
			if( !array_key_exists($group->id, $visited) ) {
				$visited[$group->id] = $group->id;
				
				if( isset($allStyles[$group->id]) ) {
					$res = $allStyles[$group->id];
					
					if( is_null($res['twin']) ) {
						// no twin means use this group's settings
						foreach( $res as $k=>$v ) {
							if( !isset($style->$k) || is_null($style->$k) ) {
								$style->$k = $v;
							}
						}
					}
					else {
						// twin exists so use its settins instead of ours
						$ancestors = ApotheosisLibDb::getAncestors( $res['twin'], '#__apoth_cm_courses' );
						$allStyles = array();
					}
				}
				
			}
		}
		
		// Now sort out the field styles. We'll follow the same hierarchy as before
		// so can just use the trail of visited group ids rather than checking for twins every time
		$gList = array();
		foreach( $visited as $gTmp ) {
			$gList[] = $db->Quote($gTmp);
		}
		$query = 'SELECT *'
			."\n".' FROM #__apoth_rpt_style_fields'
			."\n".' WHERE '.$db->nameQuote('group').' IN ( '.implode(', ', $gList).')'
			."\n".'   AND '.$db->nameQuote('cycle').' = '.$db->Quote($cycleId)
			."\n".'   AND ('.$db->nameQuote('template').' = '.$db->Quote($style->page_style)
			."\n".'        OR '.$db->nameQuote('template').' = '.$db->Quote('').')'
			."\n".' ORDER BY template DESC';
		$db->setQuery( $query );
		$allFieldStyles = $db->loadAssocList();
		
		foreach( $visited as $gId ) {
			foreach( $allFieldStyles as $fs ) {
				// look for matches and set up the info.
				if( $fs['group'] == $gId ) {
					foreach( $fs as $k=>$v ) {
						if( !isset($style->fields[$fs['field']]->$k) ) {
							$style->fields[$fs['field']]->$k = $v;
						}
					}
					if( is_null($style->fields[$fs['field']]->start_date) ) { $style->fields[$fs['field']]->start_date = $cycle->valid_from; }
					if( is_null($style->fields[$fs['field']]->end_date)   ) { $style->fields[$fs['field']]->end_date   = $cycle->valid_to;   }
				}
				
			}
			
		}
		
		return $style;
	}
	
	/**
	 * Updates the report and saves it in the database
	 *
	 * @param string $method  The source of the data to save ('GET', 'POST', anything supported by JRequest, or 'data')
	 */
	function save( $method, $data = NULL )
	{
		$db = JFactory::getDBO();
		$u = &ApotheosisLib::getUser();
		$uId = $u->person_id;
		
		switch( $method ) {
		case('data'):
			$vals = ( is_array($data) ? $data : array() );
			break;
		
		default:
			$vals = JRequest::get( $method );
			break;
		}
		
		$values = array(
			$db->nameQuote( 'cycle' )            => $db->Quote( $this->_data->cycle ),
			$db->nameQuote( 'student' )          => $db->Quote( $this->_data->student ),
			$db->nameQuote( 'group' )            => $db->Quote( $this->_data->group ),
			$db->nameQuote( 'last_modified' )    => $db->Quote( date('Y-m-d H:i:s') ),
			$db->nameQuote( 'last_modified_by' ) => $db->Quote( $uId ),
			$db->nameQuote( 'status' )           => $db->Quote( $this->_data->status) );
		
		$results = array();
		$this->resetRow();
		while( $row = &$this->getRow() ) {
			foreach ($row as $k=>$v) {
				$name = $v->getName();
				$col = $v->getColumn();
				// set value ...
				if( array_key_exists($name, $vals) ) {
					$row[$k]->setValue( $vals[$name] );
				}
				elseif( get_class($v) == 'ApothFieldBool' ) {
					$row[$k]->setValue( 'off' );
				}
				// ... and validate
				if( $v->getRequired() && ($row[$k]->getValue() == '') ) {
					$results['warnings'][] = 'Required field not filled in: '.$name;
				}
				if( ($valid = $row[$k]->validate()) !== true ) {
					$results['warnings'][] = $valid;
				}
				
				// prepare to save to database
				if( ($col !== false)
				 && (array_search($col, $this->_blurbCols) === false ) ) {
					$values[$db->nameQuote($col)] = $db->Quote($row[$k]->getValue());
					$this->_data->$col = $row[$k]->getValue();
				}
			}
		}
		
		// save to database
		if ( is_null($this->_data->id) ) {
			$query = 'INSERT INTO #__apoth_rpt_reports'
				."\n".' ('.implode(', ', array_keys($values)).')'
				."\n".' VALUES ('.implode(', ', $values).')';
			$db->setQuery( $query );
			$r = $db->query();
			$this->_data->id = $db->insertId();
			ApotheosisLibAcl::getUserTable( 'report.reports', null, true );
		}
		else {
			$assignments = array();
			foreach ($values as $col=>$val) {
				$assignments[] = $col.' = '.$val;
			}
			$query = 'UPDATE #__apoth_rpt_reports'
				."\n".' SET '.implode(', ', $assignments)
				."\n".' WHERE '.$db->nameQuote('id').' = '.$db->Quote($this->_data->id);
			$db->setQuery( $query );
			$r = $db->query();
		}
		if( $r === false ) {
			$results['errors'][] = 'Could not save report due to a database error';
		}
//		debugQuery($db, $r);
		return $results;
	}
	
	/**
	 * Removes the current report from the database
	 */
	function delete()
	{
		if( $this->_data->id == false ) {
			return true;
		}
		$db = &JFactory::getDBO();
		$query = 'DELETE FROM #__apoth_rpt_reports'
			."\n".' WHERE id = '.$db->Quote($this->_data->id)
			."\n".' LIMIT 1';
		$db->setQuery( $query );
		return $db->query();
	}
	
	/**
	 * Disables all the fields in this report
	 */
	function disable()
	{
		foreach($this->_fields as $k=>$v) {
			$this->_fields[$k]->htmlEnabled = false;
			$this->_fields[$k]->htmlSmallEnabled = false;
		}
	}
	
	/**
	 * Goes through all fields in a report checking that required fields are filled in
	 * and that all fields contain valid data.
	 *
	 * @return array  The array of all warning messages generated by the validation process (empty if report is valid)
	 */
	function validate()
	{
		$results = array();
		$this->resetRow();
		while( $row = &$this->getRow() ) {
			foreach ($row as $k=>$v) {
				if( $row[$k]->getRequired() && ($row[$k]->getValue() == '') ) {
					$results[] = 'Required field not filled in: '.$v->getName();
				}
				if( ($valid = $row[$k]->validate()) !== true ) {
					$results[] = $valid;
				}
			}
		}
		return $results;
	}
	
	/**
	 * Determines the named attribute for this report object
	 *
	 * @param string $attrib  The name of the attribute required
	 * @return string  The value of the named attribute
	 */
	function getStyle( $attrib )
	{
		return isset( $this->_style->$attrib ) ? $this->_style->$attrib : null;
	}
	
	function setFieldStyle()
	{
		$data = JRequest::get('post');
		foreach( $this->_fields as $k=>$f ) {
			$f = &$this->_fields[$k];
			$fName = $f->getName();
			if( isset($data[$fName]) && is_array($data[$fName]) ) {
				$t = $data['_tmpl'];
				unset( $data[$fName]['_tmpl'] );
				$f->setStyle( $data[$fName], (($t == 'general') ? '' : $this->_style->page_style) );
			}
			unset($f);
		}
	}
	
	/**
	 * Accessor method to retrieve id for this report
	 */
	function getId()
	{
		return $this->_data->id;
	}
	
	/**
	 * Accessor method to retrieve student id for this report
	 */
	function getStudent()
	{
		return $this->_data->student;
	}
	
	/**
	 * Accessor method to retrieve group id for this report
	 */
	function getGroup()
	{
		return $this->_data->group;
	}
	
	/**
	 * Accessor method to retrieve cycle id for this report
	 */
	function getCycle()
	{
		return $this->_data->cycle;
	}
	
	function getTutorGroupName()
	{
		return $this->_data2->tutorgroup;
	}
	
	function getSubjectName()
	{
		return $this->_data2->subject_name;
	}
	
	function getSubjectOrder()
	{
		return $this->_data2->subject_order;
	}
	
	function getStudentFirstname()
	{
		return $this->_data2->firstname;
	}
	function getStudentMiddlenames()
	{
		return $this->_data2->middlenames;
	}
	function getStudentSurname()
	{
		return $this->_data2->surname;
	}
	function getStudentName()
	{
		return $this->_data2->displayname;
	}
	
	/**
	 * Retrieves the field for the subject name (which may have the print_name as its value)
	 * *** Currently very dumbly assumes it'll be the field named "subjectname". Perhaps a more flexible approach could be better.
	 */
	function &getSubjectField()
	{
		return $this->_fields['subjectname'];
	}
	
	/**
	 * Retrieves only those fields which relate to the subject blurb
	 */
	function &getBlurbFields()
	{
		$retVal = array();
		foreach( $this->_fields as $k=>$v ) {
			if( array_search($v->getColumn(), $this->_blurbCols) !== false ) {
				$retVal[$k] = &$this->_fields[$k];
			}
		}
		return $retVal;
	}
	
	/**
	 * Retrieves only those fields which have a statement bank associated with them
	 */
	function &getStatementFields()
	{
		$retVal = array();
		if( is_array($retVal) ) {
			foreach( $this->_fields as $k=>$v ) {
				if( $v->hasStatementBank() ) {
					$retVal[$k] = &$this->_fields[$k];
				}
			}
		}
		return $retVal;
	}
	
	function &getInputFields( $context = 'normal' )
	{
		switch( $context ) {
		case('small'):
			$checkVar = 'htmlSmallEnabled';
			break;
		
		case('normal'):
			$checkVar = 'htmlEnabled';
			break;
		}
		
		$retVal = array();
		foreach( $this->_fields as $k=>$v ) {
			if( $v->$checkVar ) {
//			$class = strtolower(get_class($v));
//			if( ($class != 'apothfieldhidden')
//			 && ($class != 'apothfieldfixed')
//			 && ($class != 'apothfieldlookup')
//			 && ($v->htmlEnabled) ) {
				$retVal[$k] = &$this->_fields[$k];
			}
		}
		return $retVal;
	}
	
	/**
	 * Retrieves the named field
	 */
	function &getField( $name )
	{
		if( array_key_exists( $name, $this->_fields ) ) {
			return $this->_fields[$name];
		}
	}
	
	/**
	 * Retrieves all the fields
	 */
	function getFields()
	{
		return $this->_fields;
	}
	
	/**
	 * Resets the current row indicator 
	 */
	function resetRow()
	{
		$this->_curRow = -1;
	}
	
	/**
	 * Fetches the next or indicated row of the report's fields
	 */
	function &getRow( $rowNum=false )
	{
		$this->_curRow = (($rowNum === false) ? $this->_curRow + 1 : $rowNum);
		$retVal = (array_key_exists($this->_curRow, $this->_layout) ? $this->_layout[$this->_curRow] : false );
		return $retVal;
	}
	
	/**
	 * Sets the status of this report to one of the permitted values
	 *
	 * @param string $val  The value to set the status to. If this is not a valid option, the default is used
	 */
	function setStatus( $val )
	{
		$options = array('draft', 'submitted', 'rejected', 'approved', 'final');
		if (array_search( $val, $options ) === false) {
			$val = reset($options);
		}
		$this->_data->status = $val;
	}
	/**
	 * Accessor to the currently set status of the report
	 *
	 * @return string  The status string for this report
	 */
	function getStatus()
	{
		if( is_null($this->_data->status) ) {
			$this->_data->status = 'draft';
		}
		return $this->_data->status;
	}
	
	function getAuthor()
	{
		return $this->_data->author;
	}
	
	function getAuthorName()
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT CONCAT( IFNULL('.$db->nameQuote('title').', "-"), " ", IFNULL('.$db->nameQuote('surname').', "-")) AS name'
			."\n".' FROM #__apoth_ppl_people'
			."\n".' WHERE '.$db->nameQuote('id').' = ('.$db->quote($this->_data->author).')';
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	/**
	 * Sets the feedback on this report to the given value
	 *
	 * @param string $val  The value to set the feedback to.
	 */
	function setFeedback( $val )
	{
		$this->_data->feedback = $val;
	}
	
	function getFeedback()
	{
		if( is_null($this->_data->feedback) ) {
			$this->_data->feedback = '';
		}
		return $this->_data->feedback;
	}
	
	function setCheckedBy( $val )
	{
		if( is_null($this->_data->checked_by_first) ) {
			$this->_data->checked_by_first = $val;
			$db = &JFactory::getDBO();
			$query = 'UPDATE #__apoth_rpt_reports'
				."\n".' SET'
				."\n".' '.$db->nameQuote('checked_by_first').' = '.$db->Quote($val)
				."\n".' WHERE '.$db->nameQuote('id').' = '.$db->Quote($this->getId());
			$db->setQuery($query);
			$result = $db->query();
		}
		$this->_data->checked_by = $val;
	}
	
	function getCheckedBy()
	{
		if( is_null($this->_data->checked_by) ) {
			$this->_data->checked_by = '';
		}
		return $this->_data->checked_by;
	}
	
	function getCheckedByFirst()
	{
		if( is_null($this->_data->checked_by_first) ) {
			$this->_data->checked_by_first = '';
		}
		return $this->_data->checked_by_first;
	}
	
	function setCheckedOn( $val )
	{
		$this->_data->checked_on = $val;
	}
	
	function getCheckedOn()
	{
		if( is_null($this->_data->checked_on) ) {
			$this->_data->checked_on = '';
		}
		return $this->_data->checked_on;
	}
	
	function getFile()
	{
		return $this->_file;
	}
	
/*
	function __sleep()
	{
		$f = &$this->getStatementFields();
		if( is_array($f) ) {
			$f = reset($f);
			$s = $f->getStatementBank();
			$this->_rangeFields = $s->getRangeFields();
		}
		return( array_keys(get_object_vars($this)) );
	}
	function __wakeup()
	{
		$f = &$this->getStatementFields();
		if( is_array($f) && is_array($this->_rangeFields) ) {
			$fields = array();
			foreach($this->_rangeFields as $fName) {
				$fields[] = $this->_fields[$fName];
			}
			foreach($f as $k=>$v) {
				$s = &$f[$k]->getStatementBank();
				$s->setRangeFields($fields);
			}
		}
		unset( $this->_rangeFields );
	}
*/
}
?>