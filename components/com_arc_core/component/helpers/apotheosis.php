<?php
/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Component Helper
jimport('joomla.application.component.helper');

/**
 * Content Component Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class JApotheosisHelper
{
	function stripChars(&$inVal, $chars)
	{
		$search = '/'.implode('|', $chars).'/';
		$inVal = preg_replace($search, '', $inVal);
		return $inVal;
	}
	
	function generateLuhn($inVal, $stripChars = false)
	{
		if (is_string($stripChars)) {
			JApotheosisHelper::stripChars($inVal, array($stripChars));
		}
		elseif (is_array($stripChars)) {
			JApotheosisHelper::stripChars($inVal, $stripChars);
		}
		
		$inVal = str_replace(array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k',  'l',  'm',  'n',  'o',  'p',  'q',  'r',  's',  't',  'u',  'v',  'w',  'x',  'y',  'z'),
							 array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25'),
							 strtolower($inVal));
		
		$numOfDigits = 0 - strlen($inVal); 
		
		$i = -1;
		$total = 0;
		while ($i>=$numOfDigits) {
			if (($i % 2) != 0) {
				$double = 2*(substr($inVal, $i, 1));
				for ($j = 0, $len = strlen($double); $j < $len; $j++) {
					$total += substr($double, $j, 1);
				}
			}
			else {
				$total += substr($inVal, $i, 1);
			}
			$i--;
		}
		
		$num = (10 - ($total % 10)) % 10;
		
		return $num;
	}
	
	function checkLuhn($inVal, $stripChars = false)
	{
		
		if (is_string($stripChars)) {
			JApotheosisHelper::stripChars($inVal, array($stripChars));
		}
		elseif (is_array($stripChars)) {
			JApotheosisHelper::stripChars($inVal, $stripChars);
		}

		$required = JApotheosisHelper::generateLuhn(substr($inVal, 0, -1), $stripChars);
		$actual = substr($inVal, -1, 1);
		
		return ($required == $actual);
	}
}
?>