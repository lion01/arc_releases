<?php
/**
 * @package     Arc
 * @subpackage  Plugin_Menu
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

class plgSystemArc_menu extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for
	 * plugins because func_get_args ( void ) returns a copy of all passed arguments
	 * NOT references.  This causes problems with cross-referencing necessary for the
	 * observer design pattern.
	 */
	function plgSystemArc_menu( &$subject, $config )
	{
		parent::__construct( $subject, $config );
	}
	
	/**
	 * Plugin methods with the same name as the event will be called automatically.
	 */
	
	/**
	 * Includes the css that drives the menu
	 * Does a load of preg_replacing to make the generated menu all tagged up suitably for the css
	 * enabled menu system. Includes conditional comments to make IE behave
	 */
	function onAfterDispatch()
	{
		global $mainframe;
		$doc = &JFactory::getDocument();
		if( $doc->getType() == 'html' ) {
			$doc->addStyleSheet(JURI::root().'plugins/system/arc_menu.css');
			$contents = $doc->getBuffer('modules', $this->params->get( 'position' ));
			ob_start();
			
			// Remove <span> inside links
			$contents = preg_replace('~<\/?span>~','', $contents);
			
			// Add extra <div> (or <a> for ie6) for expansion
			// and inline conditional comments for IE 6/7
			$ds1 = '<div class="expand">';  // normal start
			$ds2a = '<a href="';            // ie <= 6 start
			$ds2b = '" class="expand"><span>';
			$de1 = '</div>';                // normal end
			$de2 = '</span></a>';           // ie <= 6 end

			$contents = preg_replace( '~<a([^>]*)href="([^"]*)"([^<]*)</a><ul~U', '<a${1}href="${2}"${3}</a><!--[if gte IE 7]><!-->'.$ds1.'<!--<![endif]--><!--[if lte IE 6]>'.$ds2a.'${2}'.$ds2b.'<table><tr><td><![endif]--><ul', $contents );
			$contents = preg_replace( '~</ul></li>~', '</ul><!--[if gte IE 7]><!-->'.$de1.'<!--<![endif]--><!--[if lte IE 6]></td></tr></table>'.$de2.'<![endif]--></li>', $contents );
			
			// This is just to appease IE6 and IE7 which is incapable of applying the "ul" based z-indices from the style sheet
			$z = 1000;
			$count = 0;
			do{
				$tmp = preg_replace( '~<li (id|class)~', '<li style="z-index: '.$z--.'" ${1}', $contents, 1 );
				if( $tmp != $contents ) {
					$contents = $tmp;
					$count = 1;
				}
				else {
					$count = 0;
				}
			} while( $count );
			// End of IE appeasement
			
			// improve readability of source
			$contents = preg_replace( '~(</?ul|<li|<!--\\[if)~', "\n".'${1}', $contents );
			
			
			//Adds a nested menu for childless elements (may be obsolete)
			$contents = preg_replace('/class="(current|select)">(.+?)<\/a><\/li>/','class="\\1">\\2</a><ul class="menu"><li><a>&nbsp;</a></li></ul></li>',$contents);
			
			$contents .= ob_get_clean();
			
			$doc->setBuffer('<div id="arc_menu">'.$contents.'</div>', 'modules', $this->params->get( 'position' ));
			
			$str = preg_replace('~<(ul|/ul|/li|--)>~', '<\\1>'."\n", $contents);
		}
		return true;
	}
	
}

?>