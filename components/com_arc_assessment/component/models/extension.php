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

jimport( 'joomla.application.component.model' );

/**
 * Assessments Model Extension
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage Assessments
 * @since 0.1
 */
class AssessmentsModel extends JModel
{
	function __construct()
	{
		parent::__construct();
		$this->fAsp = &ApothFactory::_( 'assessment.aspect', $this->fAsp );
		$this->fAss = &ApothFactory::_( 'assessment.assessment', $this->fAss );
	}
	
	function __sleep()
	{
		return( array_keys(get_object_vars($this)) );
	}
	
	function __wakeup()
	{
		$this->fAsp = &ApothFactory::_( 'assessment.aspect', $this->fAsp );
		$this->fAss = &ApothFactory::_( 'assessment.assessment', $this->fAss );
	}
}
?>
