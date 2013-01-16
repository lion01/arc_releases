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

jimport('joomla.application.component.view');

/**
 * Assessments Markbook View
 *
 * @author    lightinthedark <code@lightinthedark.org.uk>
 * @package   Apotheosis
 * @subpackage Assessments
 * @since 0.1
 */
class AssessmentsViewMarkbook extends JView
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_varMap = array();
	}
	
	
	/**
	 * Generates a csv
	 *
	 * @param string $template  Optional name of the template to use
	 */
	function display( $tpl = NULL )
	{
		$this->_varMap['ass'] = 'Assessments';
		$this->_varMap['rows'] = 'SortedEnrolments';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		$this->fAss = &ApothFactory::_( 'assessment.assessment', $this->fAss );
		
		JResponse::setHeader('Content-Type', 'application/octet-stream');
		JResponse::setHeader('Content-Disposition', 'attachment; filename="markbook.csv"');
		$this->setLayout( 'raw' );
		parent::display();
	}
	
	
	function summary()
	{
		$this->_varMap['ass'] = 'Assessments';
		$this->_varMap['rows'] = 'SortedEnrolments';
		$this->_varMap['edits'] = 'Edits';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		$this->fAss = &ApothFactory::_( 'assessment.assessment', $this->fAss );
		$this->fAsp = &ApothFactory::_( 'assessment.aspect', $this->fAsp );
		
		$this->groups = array();
		$this->aspectCols = JRequest::getVar( 'ass' , array() );
		foreach( $this->aspectCols as $aId=>$cols ) {
			if( !is_array($cols) ) {
				// use assessment's own columns then
				if( array_search($aId, $this->ass) === false ) {
					continue; // if we've not got an object, don't try and render it.
				}
				$a = &$this->fAss->getInstance( $aId );
				$aspects = $a->getAspects();
				
				foreach( $aspects as $aspId=>$asp ) {
					$this->aspectCols[$aId][][] = $aspId;
				}
				$cols = $this->aspectCols[$aId];
			}
			
			// having ensured we have an array of cols=>aspects, get the groups for all those aspects' assessments
			foreach( $cols as $col=>$aspIds ) {
				foreach( $aspIds as $aspId ) {
					$asp = &$this->fAsp->getInstance($aspId);
					$aspAId = $asp->getAssessmentId();
					if( !isset($done[$aspAId]) ) {
						$done[$aspAId] = true;
						$a = &$asp->getAssessment();
						$this->groups = array_merge( $this->groups, $a->getGroupIds() );
					}
				}
			}
		}
		$this->groups = array_flip( $this->groups );
		
		$this->setLayout( 'panel' );
		parent::display();
		
		return;
	}
	
}
?>