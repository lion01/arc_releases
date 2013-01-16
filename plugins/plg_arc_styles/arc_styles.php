<?php
/**
 * @package     Arc
 * @subpackage  Plugin_Styles
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
jimport('joomla.event.plugin');

class plgSystemArc_styles extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for
	 * plugins because func_get_args ( void ) returns a copy of all passed arguments
	 * NOT references.  This causes problems with cross-referencing necessary for the
	 * observer design pattern.
	 */
	function plgSystemArc_styles( &$subject )
	{
		parent::__construct( $subject, false );
		
		// load plugin parameters
		$this->_plugin = JPluginHelper::getPlugin( 'system', 'arc_styles' );
	}
	
	/**
	 * Plugin methods with the same name as the event will be called automatically.
	 */
	
	/**
	 * Includes the css that deals with Arc-specific layout issues
	 */
	function onAfterDispatch()
	{
		global $mainframe;
		$doc = &JFactory::getDocument();
		if( $doc->getType() == 'html' ) {
			$doc->addStyleSheet( JURI::root().'plugins/system/arc_styles.css' );
			$doc->addCustomTag( '<!--[if IE]>'
				."\n".'<link href="'.JURI::root().'plugins/system/arc_styles_ie_all.css" rel="stylesheet" type="text/css" />'
				."\n".'<![endif]-->' );
			$doc->addCustomTag( '<!--[if gte IE 7]>'
				."\n".'<link href="'.JURI::root().'plugins/system/arc_styles_ie_7.css" rel="stylesheet" rel="stylesheet" type="text/css" />'
				."\n".'<![endif]-->' );
			$doc->addCustomTag( '<!--[if lte IE 6]>'
				."\n".'<link href="'.JURI::root().'plugins/system/arc_styles_ie_6.css" rel="stylesheet" rel="stylesheet" type="text/css" />'
				."\n".'<![endif]-->' );
			
			$contents = $doc->getBuffer('component');
			if( strpos( $contents, '<div id="arc_data">' ) !== false ) {
				$doc->addCustomTag(
					      '<script src="'.JURI::root().'plugins/system/arc_data_common.js" language="javascript" type="text/javascript"></script>'
					."\n".'<!--[if gte IE 7]><!-->'
					."\n".'<script src="'.JURI::root().'plugins/system/arc_data.js" language="javascript" type="text/javascript"></script>'
					."\n".'<!--<![endif]-->'
					."\n".'<!--[if lte IE 6]>'
					."\n".'<script src="'.JURI::root().'plugins/system/arc_data_ie6.js" language="javascript" type="text/javascript"></script>'
					."\n".'<![endif]-->' );
			}
		}
		return true;
	}
}
?>