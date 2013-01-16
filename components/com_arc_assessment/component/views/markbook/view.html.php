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
	 * Displays a generic page
	 * (for when there are no actions or selected assessments)
	 *
	 * @param string $template  Optional name of the template to use
	 */
	function display( $tpl = NULL )
	{
		$this->_varMap['ass'] = 'Assessments';
		$this->_varMap['rows'] = 'SortedEnrolments';
		$this->_varMap['edits'] = 'Edits';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		$this->fAss = &ApothFactory::_( 'assessment.assessment', $this->fAss );
		
		$this->rowRepeatHeaders = 40;
		$this->setLayout( 'markbook' );
		parent::display();
	}
}
?>
