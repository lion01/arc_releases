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
 * Assessments View Admin
 */
class AssessmentsViewAdmin extends JView
{
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$doc = &JFactory::getDocument();
		$docRaw = &JDocument::getInstance( 'raw' );
		$doc = $docRaw;
	}
	
	/**
	 * Generate a csv with the details of the assessment (and selected aspects)
	 */
	function exportAssessment()
	{
		JResponse::setHeader('Content-Type', 'application/octet-stream');
		JResponse::setHeader('Content-Disposition', 'attachment; filename="assessment.csv"');
		
		$this->assessment = &$this->get( 'Ass' );
		$this->setLayout( 'raw' );
		parent::display( 'assessment' );
	}
	
	
	/**
	 * Generate a csv with the details of the selected aspects
	 */
	function exportAspects()
	{
		JResponse::setHeader('Content-Type', 'application/octet-stream');
		JResponse::setHeader('Content-Disposition', 'attachment; filename="aspects.csv"');
		
		$this->setLayout( 'raw' );
		parent::display( 'aspects' );
	}
}
?>