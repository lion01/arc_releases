<?php
/**
 * @package     Arc
 * @subpackage  Course
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
require_once( JPATH_SITE.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' ); 

 /*
 * Course Manager Course Model
 *
 * @author     Lightinthedark<code@lightinthedark.org.uk>
 * @package    Arc
 * @subpackage Course Manager
 * @since      1.5
 */
class CourseModelCourse extends JModel
{
	/** @var array Array of items */
	var $_items = array();
}
?>