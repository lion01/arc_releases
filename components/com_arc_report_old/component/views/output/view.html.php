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

jimport('joomla.application.component.view');

/**
 * Reports Output View
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsViewOutput extends JView 
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_varMap = array('state'=>'State');
	}
	
	/**
	 * Displays a generic page
	 * (for when there are no actions or selected registers)
	 *
	 * @param string $template  Optional name of the template to use
	 */
	function display( $tpl = NULL )
	{
		$this->_varMap2['courses'] = 'Subjects';
		$this->_varMap2['tutors']  = 'PastoralCourses';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		ApotheosisLib::setViewVars($this, $this->_varMap2, 'lists');
		$this->groups = array();
		$this->pupils = array();
		
		parent::display( $tpl );
	}

}
?>
