<?php
/**
 * @package     Arc
 * @subpackage  Joomla Alterations
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// copyright info for original file of which this is a slight modification
/**
 * Configuration file for TCPDF.
 * @author Nicola Asuni
 * @copyright 2004-2008 Nicola Asuni - Tecnick.com S.r.l (www.tecnick.com) Via Della Pace, 11 - 09044 - Quartucciu (CA) - ITALY - www.tecnick.com - info@tecnick.com
 * @package com.tecnick.tcpdf
 * @version 4.0.014
 * @link http://tcpdf.sourceforge.net
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * @since 2004-10-27
 */

// If you define the constant K_TCPDF_EXTERNAL_CONFIG, the following settings will be ignored.

if (!defined('K_TCPDF_EXTERNAL_CONFIG')) {
	define ('K_TCPDF_EXTERNAL_CONFIG', true);

	/**
	 * Installation path (/var/www/tcpdf/).
	 * By default it is automatically calculated but you can also set it as a fixed string to improve performances.
	 */
	define ('K_PATH_MAIN', JPATH_LIBRARIES.DS.'tcpdf');

	/**
	 * URL path to tcpdf installation folder (http://localhost/tcpdf/).
	 * By default it is automatically calculated but you can also set it as a fixed string to improve performances..
	 */
	define ('K_PATH_URL', JPATH_BASE);
	
	/**
	 * path for PDF fonts
	 * use K_PATH_MAIN.'fonts/old/' for old non-UTF8 fonts
	 */
	define ('K_PATH_FONTS', JPATH_SITE.DS.'language'.DS."pdf_fonts".DS);
	
	/**
	 * cache directory for temporary files (full path)
	 */
	define ('K_PATH_CACHE', K_PATH_MAIN.DS.'cache');
	
	/**
	 * cache directory for temporary files (url path)
	 */
	define ('K_PATH_URL_CACHE', K_PATH_URL.DS.'cache');
	
	/**
	 *images directory
	 */
	define ('K_PATH_IMAGES', K_PATH_MAIN.DS.'images');
	
	/**
	 * blank image
	 */
	define ('K_BLANK_IMAGE', K_PATH_IMAGES.DS.'_blank.png');
	
	/**
	 * page format
	 */
	define ('PDF_PAGE_FORMAT', 'A4');
	
	/**
	 * page orientation (P=portrait, L=landscape)
	 */
	define ('PDF_PAGE_ORIENTATION', 'P');
	
	/**
	 * document creator
	 */
	define ('PDF_CREATOR', 'TCPDF');
	
	/**
	 * document author
	 */
	define ('PDF_AUTHOR', 'TCPDF');
	
	/**
	 * header title
	 */
	define ('PDF_HEADER_TITLE', 'TCPDF Example');
	
	/**
	 * header description string
	 */
	define ('PDF_HEADER_STRING', "by Nicola Asuni - Tecnick.com\nwww.tcpdf.org");
	
	/**
	 * image logo
	 */
	define ('PDF_HEADER_LOGO', 'tcpdf_logo.jpg');
	
	/**
	 * header logo image width [mm]
	 */
	define ('PDF_HEADER_LOGO_WIDTH', 30);
	
	/**
	 *  document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch]
	 */
	define ('PDF_UNIT', 'mm');
	
	/**
	 * header margin
	 */
	define ('PDF_MARGIN_HEADER', 5);
	
	/**
	 * footer margin
	 */
	define ('PDF_MARGIN_FOOTER', 10);
	
	/**
	 * top margin
	 */
	define ('PDF_MARGIN_TOP', 27);
	
	/**
	 * bottom margin
	 */
	define ('PDF_MARGIN_BOTTOM', 25);
	
	/**
	 * left margin
	 */
	define ('PDF_MARGIN_LEFT', 15);
	
	/**
	 * right margin
	 */
	define ('PDF_MARGIN_RIGHT', 15);
	
	/**
	 * main font name
	 */
	define ('PDF_FONT_NAME_MAIN', 'helvetica');
	
	/**
	 * main font size
	 */
	define ('PDF_FONT_SIZE_MAIN', 10);
	
	/**
	 * data font name
	 */
	define ('PDF_FONT_NAME_DATA', 'helvetica');
	
	/**
	 * data font size
	 */
	define ('PDF_FONT_SIZE_DATA', 8);
	
	/**
	 * Ratio used to scale the images
	 */
	define ('PDF_IMAGE_SCALE_RATIO', 4);
	
	/**
	 * magnification factor for titles
	 */
	define('HEAD_MAGNIFICATION', 1.1);
	
	/**
	 * height of cell repect font height
	 */
	define('K_CELL_HEIGHT_RATIO', 1.25);
	
	/**
	 * title magnification respect main font size
	 */
	define('K_TITLE_MAGNIFICATION', 1.3);
	
	/**
	 * reduction factor for small font
	 */
	define('K_SMALL_RATIO', 2/3);
}

//============================================================+
// END OF FILE                                                 
//============================================================+
?>
