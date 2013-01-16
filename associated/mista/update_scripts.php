<?php
/**
 * @package     Arc
 * @subpackage  Mista
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

class Maint_1_6_4_03 extends Maint
{
	function beforeUpdateTo()
	{
		echo 'About to do 1.6.4-03'.PHP_EOL;
	}
	
	function afterUpdateTo()
	{
		echo 'Just did 1.6.4-03'.PHP_EOL;
		unlink( 'update.bat' );
	}
	
	function beforeUpdateFrom()
	{
		echo 'Leaving 1.6.4-03 behind'.PHP_EOL;
	}
}
?>